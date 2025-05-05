<?php
// Include konfigurasi
require_once '../config.php';

// Cek apakah pengguna sudah login sebagai admin
if (!isLoggedIn() || getUserRole() !== 'admin') {
    header("Location: ../admin_login.php");
    exit();
}

// Alert message
$alert = '';
$alert_type = '';

// Filter cabang/outlet
$selectedOutlet = isset($_GET['outlet']) ? sanitize($_GET['outlet']) : '';

// Ambil semua outlet/cabang
$query_outlets = "SELECT DISTINCT outlet_name FROM locations ORDER BY outlet_name";
$result_outlets = mysqli_query($koneksi, $query_outlets);
$outlets = [];
if ($result_outlets) {
    while ($row = mysqli_fetch_assoc($result_outlets)) {
        $outlets[] = $row['outlet_name'];
    }
}

// Mengambil semua data karyawan
$query_karyawan = "SELECT id_karyawan, nama, outlet FROM users WHERE role = 'karyawan'";
// Tambahkan filter cabang jika dipilih
if (!empty($selectedOutlet)) {
    $query_karyawan .= " AND outlet = '$selectedOutlet'";
}
$query_karyawan .= " ORDER BY nama";

$result_karyawan = mysqli_query($koneksi, $query_karyawan);
$karyawan_list = [];
if ($result_karyawan) {
    while ($row = mysqli_fetch_assoc($result_karyawan)) {
        $karyawan_list[] = $row;
    }
}

// Mengambil semua data shift
$query_shift = "SELECT id, nama_shift, jam_mulai, jam_selesai FROM shift ORDER BY jam_mulai";
$result_shift = mysqli_query($koneksi, $query_shift);
$shift_list = [];
if ($result_shift) {
    while ($row = mysqli_fetch_assoc($result_shift)) {
        $shift_list[] = $row;
    }
}

// Mendapatkan tanggal hari ini dan tanggal awal minggu ini (Senin)
$today = new DateTime();
$weekStart = clone $today;
$weekStart->modify('this week monday');

// Jika ada parameter tanggal
if (isset($_GET['week_start'])) {
    $weekStart = new DateTime($_GET['week_start']);
}

// Tanggal awal dan akhir minggu
$weekStartStr = $weekStart->format('Y-m-d');
$weekEnd = clone $weekStart;
$weekEnd->modify('+6 days');
$weekEndStr = $weekEnd->format('Y-m-d');

// Proses penyimpanan jadwal karyawan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_schedule') {
    // Hapus jadwal lama untuk karyawan dan minggu yang dipilih
    $id_karyawan_values = array_keys($_POST['jadwal']);
    $id_karyawan_list = "'" . implode("','", $id_karyawan_values) . "'";
    $query_delete = "DELETE FROM jadwal WHERE tanggal BETWEEN '$weekStartStr' AND '$weekEndStr' AND id_karyawan IN ($id_karyawan_list)";
    mysqli_query($koneksi, $query_delete);
    
    // Loop untuk semua hari dalam seminggu dan semua karyawan
    foreach ($_POST['jadwal'] as $id_karyawan => $days) {
        foreach ($days as $day => $shift_data) {
            $tanggal = date('Y-m-d', strtotime($weekStartStr . ' + ' . $day . ' days'));
            $status = $shift_data['status'];
            
            // Perbaikan: hanya gunakan id_shift jika status "masuk"
            if ($status === 'masuk') {
                // Jika shift tidak dipilih, gunakan default berdasarkan hari
                if (empty($shift_data['shift'])) {
                    // Tentukan waktu sekarang untuk bandingkan
                    $waktu_sekarang = date('H:i:s');
                    
                    // Default: jika pagi (07:00-15:00) gunakan shift 1, 
                    // jika siang (15:00-23:00) gunakan shift 2, 
                    // jika malam (23:00-07:00) gunakan shift 3
                    if ($waktu_sekarang >= '07:00:00' && $waktu_sekarang < '15:00:00') {
                        $id_shift = 1; // Shift pagi
                    } elseif ($waktu_sekarang >= '15:00:00' && $waktu_sekarang < '23:00:00') {
                        $id_shift = 2; // Shift siang
                    } else {
                        $id_shift = 3; // Shift malam
                    }
                } else {
                    $id_shift = (int)$shift_data['shift'];
                }
            } else {
                $id_shift = NULL;
            }
            
            $query_insert = "INSERT INTO jadwal (id_karyawan, tanggal, id_shift, status) 
                           VALUES ('$id_karyawan', '$tanggal', " . ($id_shift ? $id_shift : "NULL") . ", '$status')";
            mysqli_query($koneksi, $query_insert);
        }
    }
    
    $alert = 'Jadwal berhasil disimpan!';
    $alert_type = 'success';
}

// Ambil jadwal karyawan untuk minggu ini
$jadwal_karyawan = [];
$query_jadwal = "SELECT j.*, s.nama_shift, s.jam_mulai, s.jam_selesai 
                FROM jadwal j 
                LEFT JOIN shift s ON j.id_shift = s.id 
                WHERE j.tanggal BETWEEN '$weekStartStr' AND '$weekEndStr'";

// Jika ada filter karyawan berdasarkan outlet
if (!empty($selectedOutlet)) {
    $query_jadwal .= " AND j.id_karyawan IN (SELECT id_karyawan FROM users WHERE outlet = '$selectedOutlet')";
}

$result_jadwal = mysqli_query($koneksi, $query_jadwal);

if ($result_jadwal) {
    while ($row = mysqli_fetch_assoc($result_jadwal)) {
        $jadwal_karyawan[$row['id_karyawan']][date('w', strtotime($row['tanggal']))] = [
            'id_shift' => $row['id_shift'],
            'nama_shift' => $row['nama_shift'],
            'jam_mulai' => $row['jam_mulai'],
            'jam_selesai' => $row['jam_selesai'],
            'status' => $row['status']
        ];
    }
}

// Mendapatkan minggu sebelumnya dan minggu berikutnya
$prevWeek = clone $weekStart;
$prevWeek->modify('-1 week');
$nextWeek = clone $weekStart;
$nextWeek->modify('+1 week');

// Nama-nama hari
$dayNames = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Wakacao Admin - Kelola Jadwal</title>
  <!-- plugins:css -->
  <link rel="stylesheet" href="../vendors/feather/feather.css">
  <link rel="stylesheet" href="../vendors/ti-icons/css/themify-icons.css">
  <link rel="stylesheet" href="../vendors/css/vendor.bundle.base.css">
  <!-- endinject -->
  <!-- Plugin css for this page -->
  <link rel="stylesheet" href="../vendors/datatables.net-bs4/dataTables.bootstrap4.css">
  <!-- End plugin css for this page -->
  <!-- inject:css -->
  <link rel="stylesheet" href="../css/vertical-layout-light/style.css">
  <!-- endinject -->
  <link rel="shortcut icon" href="../images/favicon.png" />
  <style>
    .schedule-table th, .schedule-table td {
      text-align: center;
      vertical-align: middle;
    }
    .shift-cell {
      position: relative;
    }
    .shift-badge {
      font-size: 0.7rem;
      padding: 0.2rem 0.5rem;
      margin-top: 3px;
    }
    .week-navigator {
      font-size: 1.2rem;
      font-weight: 500;
    }
    .week-navigator a {
      padding: 0 10px;
    }
  </style>
</head>

<body>
  <div class="container-scroller">
    <!-- partial:partials/_navbar.html -->
    <?php include_once 'navbar.php'; ?>
    <?php include_once 'sidebar.php'; ?>
    
      
      <!-- partial -->
      <div class="main-panel">
        <div class="content-wrapper">
          <div class="row">
            <div class="col-md-12 grid-margin">
              <div class="row">
                <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                  <h3 class="font-weight-bold">Kelola Jadwal Shift Karyawan</h3>
                  <h6 class="font-weight-normal mb-0">Atur jadwal shift mingguan untuk seluruh karyawan</h6>
                </div>
              </div>
            </div>
          </div>
          
          <?php if ($alert): ?>
          <div class="alert alert-<?php echo $alert_type; ?> alert-dismissible fade show" role="alert">
            <?php echo $alert; ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <?php endif; ?>
          
          <!-- Filter Outlet dan Navigasi Tanggal -->
          <div class="row mb-4">
            <div class="col-md-6">
              <div class="card">
                <div class="card-body">
                  <h4 class="card-title">Filter Outlet</h4>
                  <form method="GET" action="" class="d-flex align-items-center">
                    <input type="hidden" name="week_start" value="<?php echo $weekStartStr; ?>">
                    <div class="form-group mb-0 mr-3 flex-grow-1">
                      <select class="form-control" name="outlet" id="outlet-filter">
                        <option value="">Semua Outlet</option>
                        <?php foreach ($outlets as $outlet): ?>
                        <option value="<?php echo $outlet; ?>" <?php echo ($selectedOutlet === $outlet) ? 'selected' : ''; ?>>
                          <?php echo $outlet; ?>
                        </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <button type="submit" class="btn btn-primary">
                      <i class="ti-filter mr-1"></i> Filter
                    </button>
                  </form>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="card">
                <div class="card-body">
                  <h4 class="card-title">Periode Jadwal</h4>
                  <div class="d-flex justify-content-between align-items-center">
                    <a href="?week_start=<?php echo $prevWeek->format('Y-m-d'); ?><?php echo !empty($selectedOutlet) ? '&outlet=' . $selectedOutlet : ''; ?>" class="btn btn-outline-secondary">
                      <i class="ti-arrow-left mr-1"></i> Minggu Sebelumnya
                    </a>
                    <div class="text-center font-weight-bold">
                      <?php echo date('d M Y', strtotime($weekStartStr)); ?> - <?php echo date('d M Y', strtotime($weekEndStr)); ?>
                    </div>
                    <a href="?week_start=<?php echo $nextWeek->format('Y-m-d'); ?><?php echo !empty($selectedOutlet) ? '&outlet=' . $selectedOutlet : ''; ?>" class="btn btn-outline-secondary">
                      Minggu Berikutnya <i class="ti-arrow-right ml-1"></i>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
              <div class="card">
                <div class="card-body">
                  <h4 class="card-title">Jadwal Shift Mingguan <?php echo !empty($selectedOutlet) ? 'Outlet ' . $selectedOutlet : 'Semua Outlet'; ?></h4>
                  
                  <?php if (count($karyawan_list) > 0): ?>
                  <form method="POST" action="">
                    <input type="hidden" name="action" value="save_schedule">
                    <div class="table-responsive">
                      <table class="table table-bordered schedule-table">
                        <thead>
                          <tr>
                            <th>Karyawan</th>
                            <?php
                            for ($i = 0; $i < 7; $i++) {
                                $currentDate = clone $weekStart;
                                $currentDate->modify("+$i days");
                                echo '<th>' . $dayNames[$i] . '<br><small>' . $currentDate->format('d/m') . '</small></th>';
                            }
                            ?>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach ($karyawan_list as $karyawan): ?>
                          <tr>
                            <td>
                              <strong><?php echo $karyawan['nama']; ?></strong>
                              <br>
                              <small><?php echo $karyawan['outlet']; ?></small>
                            </td>
                            <?php for ($i = 0; $i < 7; $i++): ?>
                            <td class="shift-cell">
                              <select name="jadwal[<?php echo $karyawan['id_karyawan']; ?>][<?php echo $i; ?>][shift]" class="form-control form-control-sm mb-2">
                                <option value="">Pilih Shift</option>
                                <?php foreach ($shift_list as $shift): ?>
                                <option value="<?php echo $shift['id']; ?>" <?php 
                                  echo isset($jadwal_karyawan[$karyawan['id_karyawan']][$i]) && 
                                       $jadwal_karyawan[$karyawan['id_karyawan']][$i]['id_shift'] == $shift['id'] ? 'selected' : ''; 
                                ?>>
                                  <?php echo $shift['nama_shift']; ?> (<?php echo substr($shift['jam_mulai'], 0, 5); ?>-<?php echo substr($shift['jam_selesai'], 0, 5); ?>)
                                </option>
                                <?php endforeach; ?>
                              </select>
                              <select name="jadwal[<?php echo $karyawan['id_karyawan']; ?>][<?php echo $i; ?>][status]" class="form-control form-control-sm">
                                <option value="masuk" <?php 
                                  echo isset($jadwal_karyawan[$karyawan['id_karyawan']][$i]) && 
                                       $jadwal_karyawan[$karyawan['id_karyawan']][$i]['status'] == 'masuk' ? 'selected' : ''; 
                                ?>>Masuk</option>
                                <option value="libur" <?php 
                                  echo isset($jadwal_karyawan[$karyawan['id_karyawan']][$i]) && 
                                       $jadwal_karyawan[$karyawan['id_karyawan']][$i]['status'] == 'libur' ? 'selected' : ''; 
                                ?>>Libur</option>
                                <option value="izin" <?php 
                                  echo isset($jadwal_karyawan[$karyawan['id_karyawan']][$i]) && 
                                       $jadwal_karyawan[$karyawan['id_karyawan']][$i]['status'] == 'izin' ? 'selected' : ''; 
                                ?>>Izin</option>
                                <option value="sakit" <?php 
                                  echo isset($jadwal_karyawan[$karyawan['id_karyawan']][$i]) && 
                                       $jadwal_karyawan[$karyawan['id_karyawan']][$i]['status'] == 'sakit' ? 'selected' : ''; 
                                ?>>Sakit</option>
                                <option value="cuti" <?php 
                                  echo isset($jadwal_karyawan[$karyawan['id_karyawan']][$i]) && 
                                       $jadwal_karyawan[$karyawan['id_karyawan']][$i]['status'] == 'cuti' ? 'selected' : ''; 
                                ?>>Cuti</option>
                              </select>
                            </td>
                            <?php endfor; ?>
                          </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
                    <div class="mt-4 text-right">
                      <button type="submit" class="btn btn-primary">
                        <i class="ti-save mr-1"></i> Simpan Jadwal
                      </button>
                    </div>
                  </form>
                  <?php else: ?>
                  <div class="alert alert-info">
                    <?php echo !empty($selectedOutlet) ? 'Tidak ada karyawan di outlet ' . $selectedOutlet : 'Tidak ada karyawan yang tersedia'; ?>
                  </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
          
          <div class="row mt-4">
            <div class="col-md-12">
              <div class="card">
                <div class="card-body">
                  <h4 class="card-title">Panduan Status</h4>
                  <div class="row">
                    <div class="col-md-2 col-sm-4 mb-3">
                      <div class="p-2 bg-white border">
                        <strong>Masuk</strong>: Karyawan masuk sesuai jadwal shift
                      </div>
                    </div>
                    <div class="col-md-2 col-sm-4 mb-3">
                      <div class="p-2 bg-light border">
                        <strong>Libur</strong>: Karyawan libur kerja
                      </div>
                    </div>
                    <div class="col-md-2 col-sm-4 mb-3">
                      <div class="p-2 bg-info text-white border">
                        <strong>Izin</strong>: Karyawan izin tidak masuk
                      </div>
                    </div>
                    <div class="col-md-2 col-sm-4 mb-3">
                      <div class="p-2 bg-warning text-dark border">
                        <strong>Sakit</strong>: Karyawan sakit
                      </div>
                    </div>
                    <div class="col-md-2 col-sm-4 mb-3">
                      <div class="p-2 bg-success text-white border">
                        <strong>Cuti</strong>: Karyawan cuti
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- content-wrapper ends -->
        
        <!-- partial:partials/_footer.html -->
        <footer class="footer">
          <div class="d-sm-flex justify-content-center justify-content-sm-between">
            <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">Copyright © <?php echo date('Y'); ?> Wakacao. All rights reserved.</span>
          </div>
        </footer>
        <!-- partial -->
      </div>
      <!-- main-panel ends -->
    </div>
    <!-- page-body-wrapper ends -->
  </div>
  <!-- container-scroller -->

  <!-- plugins:js -->
  <script src="../vendors/js/vendor.bundle.base.js"></script>
  <!-- endinject -->
  <!-- Plugin js for this page -->
  <script src="../vendors/datatables.net/jquery.dataTables.js"></script>
  <script src="../vendors/datatables.net-bs4/dataTables.bootstrap4.js"></script>
  <!-- End plugin js for this page -->
  <!-- inject:js -->
  <script src="../js/off-canvas.js"></script>
  <script src="../js/hoverable-collapse.js"></script>
  <script src="../js/template.js"></script>
  <script src="../js/settings.js"></script>
  <script src="../js/todolist.js"></script>
  <!-- endinject -->
  <!-- Custom js for this page-->
  <script src="../js/dashboard.js"></script>
  <!-- End custom js for this page-->
  
  <script>
    $(document).ready(function() {
      // Script untuk menangani perubahan status jadwal
      $('select[name$="[status]"]').on('change', function() {
        const status = $(this).val();
        const shiftSelect = $(this).prev('select');
        
        if (status !== 'masuk') {
          shiftSelect.prop('disabled', true);
          shiftSelect.val('');
        } else {
          shiftSelect.prop('disabled', false);
        }
      });
      
      // Set initial state
      $('select[name$="[status]"]').trigger('change');
      
      // Tambahan: Pastikan select yang disabled tetap dikirim saat form disubmit
      $('form').on('submit', function() {
        $('select:disabled').prop('disabled', false);
      });
    });
  </script>
</body>

</html> 