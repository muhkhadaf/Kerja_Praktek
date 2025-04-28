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

// Fungsi untuk memformat tanggal
function formatDateIndo($date) {
    $date = date('Y-m-d', strtotime($date));
    $day = date('d', strtotime($date));
    $month = date('m', strtotime($date));
    $year = date('Y', strtotime($date));
    
    $monthNames = [
        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', 
        '04' => 'April', '05' => 'Mei', '06' => 'Juni', 
        '07' => 'Juli', '08' => 'Agustus', '09' => 'September', 
        '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
    ];
    
    return $day . ' ' . $monthNames[$month] . ' ' . $year;
}

// Fungsi untuk format waktu
function formatTime($time) {
    if (!$time) return '-';
    return date('H:i', strtotime($time));
}

// Filter data
$filter_tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');
$filter_outlet = isset($_GET['outlet']) ? $_GET['outlet'] : '';
$filter_karyawan = isset($_GET['karyawan']) ? $_GET['karyawan'] : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';

// Query untuk mendapatkan daftar outlet
$query_outlet = "SELECT DISTINCT outlet FROM users WHERE role = 'karyawan' ORDER BY outlet";
$result_outlet = mysqli_query($koneksi, $query_outlet);
$outlet_list = [];
if ($result_outlet) {
    while ($row = mysqli_fetch_assoc($result_outlet)) {
        $outlet_list[] = $row['outlet'];
    }
}

// Query untuk mendapatkan daftar karyawan
$query_karyawan = "SELECT id_karyawan, nama FROM users WHERE role = 'karyawan' ORDER BY nama";
if ($filter_outlet) {
    $query_karyawan .= " AND outlet = '$filter_outlet'";
}
$result_karyawan = mysqli_query($koneksi, $query_karyawan);
$karyawan_list = [];
if ($result_karyawan) {
    while ($row = mysqli_fetch_assoc($result_karyawan)) {
        $karyawan_list[$row['id_karyawan']] = $row['nama'];
    }
}

// Query untuk absensi dengan filter
$query_absensi = "SELECT a.*, u.nama, u.outlet, s.nama_shift, s.jam_mulai, s.jam_selesai 
                 FROM absensi a 
                 JOIN users u ON a.id_karyawan = u.id_karyawan 
                 LEFT JOIN shift s ON a.id_shift = s.id 
                 WHERE a.tanggal = '$filter_tanggal'";

if ($filter_outlet) {
    $query_absensi .= " AND u.outlet = '$filter_outlet'";
}

if ($filter_karyawan) {
    $query_absensi .= " AND a.id_karyawan = '$filter_karyawan'";
}

if ($filter_status) {
    if ($filter_status === 'tepat_waktu') {
        $query_absensi .= " AND a.status_check_in = 'tepat waktu'";
    } else if ($filter_status === 'terlambat') {
        $query_absensi .= " AND a.status_check_in = 'terlambat'";
    } else if ($filter_status === 'tidak_absen') {
        $query_absensi .= " AND a.status_check_in = 'tidak absen'";
    }
}

$query_absensi .= " ORDER BY u.nama";
$result_absensi = mysqli_query($koneksi, $query_absensi);
$absensi_list = [];
if ($result_absensi) {
    while ($row = mysqli_fetch_assoc($result_absensi)) {
        $absensi_list[] = $row;
    }
}

// Mendapatkan jadwal untuk tanggal yang dipilih
$query_jadwal = "SELECT j.*, u.nama, u.outlet, s.nama_shift, s.jam_mulai, s.jam_selesai 
                FROM jadwal j 
                JOIN users u ON j.id_karyawan = u.id_karyawan 
                LEFT JOIN shift s ON j.id_shift = s.id 
                WHERE j.tanggal = '$filter_tanggal' AND j.status = 'masuk'";

if ($filter_outlet) {
    $query_jadwal .= " AND u.outlet = '$filter_outlet'";
}

if ($filter_karyawan) {
    $query_jadwal .= " AND j.id_karyawan = '$filter_karyawan'";
}

$query_jadwal .= " ORDER BY u.nama";
$result_jadwal = mysqli_query($koneksi, $query_jadwal);
$jadwal_list = [];
if ($result_jadwal) {
    while ($row = mysqli_fetch_assoc($result_jadwal)) {
        $jadwal_list[$row['id_karyawan']] = $row;
    }
}

// Menggabungkan data absensi dan jadwal
$merged_data = [];
foreach ($jadwal_list as $id_karyawan => $jadwal) {
    $absensi_data = null;
    foreach ($absensi_list as $absensi) {
        if ($absensi['id_karyawan'] === $id_karyawan) {
            $absensi_data = $absensi;
            break;
        }
    }
    
    $merged_data[] = [
        'id_karyawan' => $id_karyawan,
        'nama' => $jadwal['nama'],
        'outlet' => $jadwal['outlet'],
        'id_shift' => $jadwal['id_shift'],
        'nama_shift' => $jadwal['nama_shift'],
        'jam_mulai' => $jadwal['jam_mulai'],
        'jam_selesai' => $jadwal['jam_selesai'],
        'check_in' => isset($absensi_data['check_in']) ? $absensi_data['check_in'] : null,
        'foto_check_in' => isset($absensi_data['foto_check_in']) ? $absensi_data['foto_check_in'] : null,
        'check_out' => isset($absensi_data['check_out']) ? $absensi_data['check_out'] : null,
        'foto_check_out' => isset($absensi_data['foto_check_out']) ? $absensi_data['foto_check_out'] : null,
        'status_check_in' => isset($absensi_data['status_check_in']) ? $absensi_data['status_check_in'] : 'tidak absen',
        'status_check_out' => isset($absensi_data['status_check_out']) ? $absensi_data['status_check_out'] : 'tidak absen',
        'latitude_in' => isset($absensi_data['latitude_in']) ? $absensi_data['latitude_in'] : null,
        'longitude_in' => isset($absensi_data['longitude_in']) ? $absensi_data['longitude_in'] : null,
        'latitude_out' => isset($absensi_data['latitude_out']) ? $absensi_data['latitude_out'] : null,
        'longitude_out' => isset($absensi_data['longitude_out']) ? $absensi_data['longitude_out'] : null,
        'location_status_in' => isset($absensi_data['location_status_in']) ? $absensi_data['location_status_in'] : null,
        'location_info_in' => isset($absensi_data['location_info_in']) ? $absensi_data['location_info_in'] : null,
        'location_status_out' => isset($absensi_data['location_status_out']) ? $absensi_data['location_status_out'] : null,
        'location_info_out' => isset($absensi_data['location_info_out']) ? $absensi_data['location_info_out'] : null,
    ];
}

// Mendapatkan tanggal kemarin dan besok
$yesterday = date('Y-m-d', strtotime($filter_tanggal . ' -1 day'));
$tomorrow = date('Y-m-d', strtotime($filter_tanggal . ' +1 day'));
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Wakacao Admin - Data Absensi</title>
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
    .attendance-table th, .attendance-table td {
      vertical-align: middle;
    }
    .attendance-img {
      width: 60px;
      height: 60px;
      object-fit: cover;
      border-radius: 4px;
      cursor: pointer;
    }
    .modal-img {
      max-width: 100%;
      max-height: 500px;
    }
    .badge-sm {
      font-size: 0.7rem;
      padding: 0.2rem 0.5rem;
    }
    .date-navigator {
      font-size: 1.2rem;
      font-weight: 500;
    }
    .date-navigator a {
      padding: 0 10px;
    }
  </style>
</head>

<body>
  <div class="container-scroller">
    <!-- partial:partials/_navbar.html -->
    <nav class="navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
      <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
        <a class="navbar-brand brand-logo mr-5" href="index.php"><img src="../images/logo.svg" class="mr-2" alt="logo"/></a>
        <a class="navbar-brand brand-logo-mini" href="index.php"><img src="../images/logo-mini.svg" alt="logo"/></a>
      </div>
      <div class="navbar-menu-wrapper d-flex align-items-center justify-content-end">
        <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
          <span class="icon-menu"></span>
        </button>
        <ul class="navbar-nav navbar-nav-right">
          <li class="nav-item nav-profile dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" id="profileDropdown">
              <img src="../images/faces/face28.jpg" alt="profile"/>
            </a>
            <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="profileDropdown">
              <a class="dropdown-item">
                <i class="ti-settings text-primary"></i>
                Pengaturan
              </a>
              <a class="dropdown-item" href="../logout.php">
                <i class="ti-power-off text-primary"></i>
                Logout
              </a>
            </div>
          </li>
        </ul>
        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
          <span class="icon-menu"></span>
        </button>
      </div>
    </nav>
  
      <?php include 'sidebar.php'; ?>
      
      <!-- partial -->
      <div class="main-panel">
        <div class="content-wrapper">
          <div class="row">
            <div class="col-md-12 grid-margin">
              <div class="row">
                <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                  <h3 class="font-weight-bold">Data Absensi Karyawan</h3>
                  <h6 class="font-weight-normal mb-0">Monitoring absensi karyawan secara real-time</h6>
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
          
          <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
              <div class="card">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title">Daftar Absensi</h4>
                    <div class="d-flex">
                      <a href="set_sample_data.php" class="btn btn-sm btn-info mr-3">
                        <i class="ti-plus mr-1"></i> Tambah Data Contoh
                      </a>
                      <div class="date-navigator">
                        <a href="?tanggal=<?php echo $yesterday; ?>&outlet=<?php echo $filter_outlet; ?>&karyawan=<?php echo $filter_karyawan; ?>&status=<?php echo $filter_status; ?>" class="text-decoration-none">
                          <i class="ti-arrow-left"></i>
                        </a>
                        <span class="mx-2">
                          <?php echo formatDateIndo($filter_tanggal); ?>
                        </span>
                        <a href="?tanggal=<?php echo $tomorrow; ?>&outlet=<?php echo $filter_outlet; ?>&karyawan=<?php echo $filter_karyawan; ?>&status=<?php echo $filter_status; ?>" class="text-decoration-none">
                          <i class="ti-arrow-right"></i>
                        </a>
                      </div>
                    </div>
                  </div>
                  
                  <div class="row mb-4">
                    <div class="col-md-12">
                      <form method="GET" action="" class="form-inline">
                        <input type="hidden" name="tanggal" value="<?php echo $filter_tanggal; ?>">
                        
                        <div class="form-group mr-3">
                          <label for="outlet" class="mr-2">Outlet:</label>
                          <select name="outlet" id="outlet" class="form-control form-control-sm">
                            <option value="">Semua Outlet</option>
                            <?php foreach ($outlet_list as $outlet): ?>
                            <option value="<?php echo $outlet; ?>" <?php echo $filter_outlet === $outlet ? 'selected' : ''; ?>>
                              <?php echo $outlet; ?>
                            </option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                        
                        <div class="form-group mr-3">
                          <label for="karyawan" class="mr-2">Karyawan:</label>
                          <select name="karyawan" id="karyawan" class="form-control form-control-sm">
                            <option value="">Semua Karyawan</option>
                            <?php foreach ($karyawan_list as $id_karyawan => $nama): ?>
                            <option value="<?php echo $id_karyawan; ?>" <?php echo $filter_karyawan === $id_karyawan ? 'selected' : ''; ?>>
                              <?php echo $nama; ?>
                            </option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                        
                        <div class="form-group mr-3">
                          <label for="status" class="mr-2">Status:</label>
                          <select name="status" id="status" class="form-control form-control-sm">
                            <option value="">Semua Status</option>
                            <option value="tepat_waktu" <?php echo $filter_status === 'tepat_waktu' ? 'selected' : ''; ?>>Tepat Waktu</option>
                            <option value="terlambat" <?php echo $filter_status === 'terlambat' ? 'selected' : ''; ?>>Terlambat</option>
                            <option value="tidak_absen" <?php echo $filter_status === 'tidak_absen' ? 'selected' : ''; ?>>Tidak Absen</option>
                          </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-sm">
                          <i class="ti-filter mr-1"></i> Filter
                        </button>
                      </form>
                    </div>
                  </div>
                  
                  <div class="table-responsive">
                    <table class="table table-striped table-hover attendance-table">
                      <thead>
                        <tr>
                          <th>No</th>
                          <th>Karyawan</th>
                          <th>Outlet</th>
                          <th>Shift</th>
                          <th>Jam Kerja</th>
                          <th>Check In</th>
                          <th>Foto In</th>
                          <th>Check Out</th>
                          <th>Foto Out</th>
                          <th>Status</th>
                          <th>Lokasi</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php if (count($merged_data) > 0): ?>
                          <?php $no = 1; foreach ($merged_data as $data): ?>
                          <tr>
                            <td><?php echo $no++; ?></td>
                            <td>
                              <strong><?php echo $data['nama']; ?></strong>
                              <br>
                              <small class="text-muted"><?php echo $data['id_karyawan']; ?></small>
                            </td>
                            <td><?php echo $data['outlet']; ?></td>
                            <td><?php echo $data['nama_shift'] ?: '-'; ?></td>
                            <td>
                              <?php if ($data['jam_mulai'] && $data['jam_selesai']): ?>
                              <?php echo substr($data['jam_mulai'], 0, 5); ?> - <?php echo substr($data['jam_selesai'], 0, 5); ?>
                              <?php else: ?>
                              -
                              <?php endif; ?>
                            </td>
                            <td>
                              <?php if ($data['check_in']): ?>
                              <?php echo date('H:i', strtotime($data['check_in'])); ?>
                              <?php else: ?>
                              -
                              <?php endif; ?>
                            </td>
                            <td>
                              <?php if ($data['foto_check_in']): ?>
                              <img src="../<?php echo $data['foto_check_in']; ?>" class="attendance-img" data-toggle="modal" data-target="#imageModal" data-img="../<?php echo $data['foto_check_in']; ?>" alt="Check In">
                              <br>
                              <a href="view_image.php?path=<?php echo $data['foto_check_in']; ?>" target="_blank" class="badge badge-primary mt-1">Lihat langsung</a>
                              <?php else: ?>
                              <span class="badge badge-light">Tidak ada foto</span>
                              <?php endif; ?>
                            </td>
                            <td>
                              <?php if ($data['check_out']): ?>
                              <?php echo date('H:i', strtotime($data['check_out'])); ?>
                              <?php else: ?>
                              -
                              <?php endif; ?>
                            </td>
                            <td>
                              <?php if ($data['foto_check_out']): ?>
                              <img src="../<?php echo $data['foto_check_out']; ?>" class="attendance-img" data-toggle="modal" data-target="#imageModal" data-img="../<?php echo $data['foto_check_out']; ?>" alt="Check Out">
                              <br>
                              <a href="view_image.php?path=<?php echo $data['foto_check_out']; ?>" target="_blank" class="badge badge-primary mt-1">Lihat langsung</a>
                              <?php else: ?>
                              <span class="badge badge-light">Tidak ada foto</span>
                              <?php endif; ?>
                            </td>
                            <td>
                              <?php
                              // Status Check In
                              $status_in_class = '';
                              switch ($data['status_check_in']) {
                                case 'tepat waktu': $status_in_class = 'badge-success'; break;
                                case 'terlambat': $status_in_class = 'badge-warning'; break;
                                case 'tidak absen': $status_in_class = 'badge-danger'; break;
                              }
                              ?>
                              <div>
                                <span class="badge <?php echo $status_in_class; ?> badge-sm">
                                  Check In: <?php echo ucfirst($data['status_check_in']); ?>
                                </span>
                              </div>
                              
                              <?php
                              // Status Check Out
                              $status_out_class = '';
                              switch ($data['status_check_out']) {
                                case 'tepat waktu': $status_out_class = 'badge-success'; break;
                                case 'lebih awal': $status_out_class = 'badge-warning'; break;
                                case 'tidak absen': $status_out_class = 'badge-danger'; break;
                              }
                              ?>
                              <div class="mt-1">
                                <span class="badge <?php echo $status_out_class; ?> badge-sm">
                                  Check Out: <?php echo ucfirst($data['status_check_out']); ?>
                                </span>
                              </div>
                            </td>
                            <td>
                              <?php if ($data['location_status_in']): ?>
                              <div>
                                <strong>Check In:</strong> 
                                <span class="badge <?php echo $data['location_status_in'] === 'valid' ? 'badge-success' : 'badge-danger'; ?>">
                                  <?php echo ucfirst($data['location_status_in']); ?>
                                </span>
                                <?php if ($data['location_info_in']): ?>
                                  <button type="button" class="btn btn-sm btn-info btn-icon ml-1" data-toggle="tooltip" data-placement="top" title="<?php echo $data['location_info_in']; ?>">
                                    <i class="ti-info-alt"></i>
                                  </button>
                                <?php endif; ?>
                                <?php if ($data['latitude_in'] && $data['longitude_in']): ?>
                                  <a href="https://www.google.com/maps?q=<?php echo $data['latitude_in']; ?>,<?php echo $data['longitude_in']; ?>" target="_blank" class="btn btn-sm btn-primary btn-icon ml-1" data-toggle="tooltip" data-placement="top" title="Lihat di Maps">
                                    <i class="ti-map-alt"></i>
                                  </a>
                                <?php endif; ?>
                              </div>
                              <?php endif; ?>
                              
                              <?php if ($data['location_status_out']): ?>
                              <div class="mt-2">
                                <strong>Check Out:</strong>
                                <span class="badge <?php echo $data['location_status_out'] === 'valid' ? 'badge-success' : 'badge-danger'; ?>">
                                  <?php echo ucfirst($data['location_status_out']); ?>
                                </span>
                                <?php if ($data['location_info_out']): ?>
                                  <button type="button" class="btn btn-sm btn-info btn-icon ml-1" data-toggle="tooltip" data-placement="top" title="<?php echo $data['location_info_out']; ?>">
                                    <i class="ti-info-alt"></i>
                                  </button>
                                <?php endif; ?>
                                <?php if ($data['latitude_out'] && $data['longitude_out']): ?>
                                  <a href="https://www.google.com/maps?q=<?php echo $data['latitude_out']; ?>,<?php echo $data['longitude_out']; ?>" target="_blank" class="btn btn-sm btn-primary btn-icon ml-1" data-toggle="tooltip" data-placement="top" title="Lihat di Maps">
                                    <i class="ti-map-alt"></i>
                                  </a>
                                <?php endif; ?>
                              </div>
                              <?php endif; ?>
                              
                              <?php if (!$data['location_status_in'] && !$data['location_status_out']): ?>
                              <span class="text-muted">Belum ada data lokasi</span>
                              <?php endif; ?>
                            </td>
                          </tr>
                          <?php endforeach; ?>
                        <?php else: ?>
                        <tr>
                          <td colspan="10" class="text-center">Tidak ada data absensi untuk tanggal ini</td>
                        </tr>
                        <?php endif; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="row mt-4">
            <div class="col-md-12">
              <div class="card">
                <div class="card-body">
                  <h4 class="card-title">Keterangan Status</h4>
                  <div class="row">
                    <div class="col-md-2 col-sm-4 mb-3">
                      <div class="p-2 bg-success text-white border">
                        <strong>Tepat Waktu</strong>: Absen sesuai jadwal
                      </div>
                    </div>
                    <div class="col-md-2 col-sm-4 mb-3">
                      <div class="p-2 bg-warning text-dark border">
                        <strong>Terlambat</strong>: Absen melebihi jadwal
                      </div>
                    </div>
                    <div class="col-md-2 col-sm-4 mb-3">
                      <div class="p-2 bg-warning text-dark border">
                        <strong>Lebih Awal</strong>: Pulang sebelum jadwal
                      </div>
                    </div>
                    <div class="col-md-2 col-sm-4 mb-3">
                      <div class="p-2 bg-danger text-white border">
                        <strong>Tidak Absen</strong>: Belum melakukan absensi
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- content-wrapper ends -->
        
        <!-- Modal untuk menampilkan gambar -->
        <div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">Foto Absensi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body text-center">
                <img src="" id="modalImage" class="modal-img" alt="Foto Absensi">
              </div>
            </div>
          </div>
        </div>
        
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
      // Image modal
      $('#imageModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var imgSrc = button.data('img');
        $(this).find('img.modal-img').attr('src', imgSrc);
      });
      
      // Aktivasi tooltip
      $('[data-toggle="tooltip"]').tooltip();
      
      // Filter ajax untuk karyawan berdasarkan outlet
      $('#outlet').change(function() {
        const outlet = $(this).val();
        if (outlet) {
          $.ajax({
            url: 'get_karyawan.php',
            type: 'post',
            data: {outlet: outlet},
            dataType: 'json',
            success: function(response) {
              let options = '<option value="">Semua Karyawan</option>';
              $.each(response, function(id, name) {
                options += `<option value="${id}">${name}</option>`;
              });
              $('#karyawan').html(options);
            }
          });
        }
      });
    });
  </script>
</body>

</html> 