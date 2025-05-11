<?php
// Include konfigurasi
require_once 'config.php';

// Cek apakah pengguna sudah login
requireLogin();

// Ambil data karyawan yang login
$id_karyawan = getKaryawanId();
$nama = getUserName();
$outlet = getOutlet();

// Query untuk mengambil jadwal karyawan untuk 7 hari ke depan
$today = date('Y-m-d');

// Cek apakah hari ini adalah Senin, jika ya hapus jadwal minggu sebelumnya
$hari_ini = date('N'); // 1 (Senin) sampai 7 (Minggu)
if ($hari_ini == 1) {
    // Hapus jadwal minggu sebelumnya (lebih dari 7 hari kebelakang)
    $seminggu_lalu = date('Y-m-d', strtotime('-7 days'));
    $query_hapus = "DELETE FROM jadwal WHERE tanggal < '$seminggu_lalu' AND id_karyawan = '$id_karyawan'";
    mysqli_query($koneksi, $query_hapus);
}

// Ambil jadwal 7 hari ke depan
$query = "SELECT j.*, s.nama_shift, s.jam_mulai, s.jam_selesai,
          a.check_in, a.check_out, a.status_check_in, a.status_check_out
          FROM jadwal j
          LEFT JOIN shift s ON j.id_shift = s.id
          LEFT JOIN absensi a ON j.id_karyawan = a.id_karyawan AND j.tanggal = a.tanggal
          WHERE j.id_karyawan = '$id_karyawan'
          AND j.tanggal >= '$today'
          ORDER BY j.tanggal ASC
          LIMIT 7";

$result = mysqli_query($koneksi, $query);
$jadwal_array = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $jadwal_array[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Wakacao</title>
  <!-- plugins:css -->
  <link rel="stylesheet" href="vendors/feather/feather.css">
  <link rel="stylesheet" href="vendors/ti-icons/css/themify-icons.css">
  <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
  <!-- endinject -->
  <!-- Plugin css for this page -->
  <link rel="stylesheet" href="vendors/datatables.net-bs4/dataTables.bootstrap4.css">
  <link rel="stylesheet" type="text/css" href="js/select.dataTables.min.css">
  <!-- End plugin css for this page -->
  <!-- inject:css -->
  <link rel="stylesheet" href="css/vertical-layout-light/style.css">
  <!-- endinject -->
  <link rel="shortcut icon" href="images/favicon.png" />
</head>

<body>
  <div class="container-scroller">
    <!-- partial:partials/_navbar.html -->
    <nav class="navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
      <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
        <a class="navbar-brand brand-logo mr-5" href="index.php"><img src="images/logowakacao.png" class="mr-2" alt="logo" style="height: 60px; width: auto;"/></a>
        <a class="navbar-brand brand-logo-mini" href="index.php"><img src="images/logowakacao.png" alt="logo" style="height: 45px; width: auto;"/></a>
      </div>
      <div class="navbar-menu-wrapper d-flex align-items-center justify-content-end">
        <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
          <span class="icon-menu"></span>
        </button>
        <ul class="navbar-nav mr-lg-2">
       
        </ul>
        <ul class="navbar-nav navbar-nav-right">
          <li class="nav-item nav-profile dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" id="profileDropdown">
              <img src="images/profile.png" alt="profile"/>
            </a>
            <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="profileDropdown">
            <a class="dropdown-item" href="pages\user\akun.php">
                <i class="ti-settings text-primary"></i>
                Akun Saya
              </a>
              <a class="dropdown-item" href="logout.php">
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
    <!-- partial -->
    <div class="container-fluid page-body-wrapper">
      <!-- Bagian theme-setting-wrapper dihapus -->
      <div id="right-sidebar" class="settings-panel">
        <i class="settings-close ti-close"></i>
        <!-- Tab navigasi dan konten chat dihapus -->
      </div>
      <!-- partial -->
      <!-- partial:partials/_sidebar.html -->
      <nav class="sidebar sidebar-offcanvas" id="sidebar">
        <ul class="nav">
          <li class="nav-item">
            <a class="nav-link" href="index.php">
              <i class="icon-grid menu-icon"></i>
              <span class="menu-title">Dashboard</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="pages/user/ajukancuti.php">
              <i class="icon-paper menu-icon"></i>
              <span class="menu-title">Ajukan Cuti</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" data-toggle="collapse" href="#auth" aria-expanded="false" aria-controls="auth">
              <i class="icon-head menu-icon"></i>
              <span class="menu-title">Halaman Karyawan</span>
              <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="auth">
              <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="pages/user/akun.php"> Akun Saya </a></li>
                <li class="nav-item"> <a class="nav-link" href="pages/user/riwayat_shift.php"> Riwayat Shift </a></li>
                <li class="nav-item"> <a class="nav-link" href="pages/user/riwayat_cuti.php"> Riwayat Cuti </a></li>
                <li class="nav-item"> <a class="nav-link" href="pages/user/riwayat_izin.php"> Riwayat Izin </a></li>
              </ul>
            </div>
          </li>
        </ul>
      </nav>
      <!-- partial -->
      <div class="main-panel">
        <div class="content-wrapper" style="background-color: #f5f7fa;">
          <div class="row">
            <div class="col-md-12 grid-margin">
              <div class="row">
                <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                  <h3 class="font-weight-bold">Selamat Datang, <?php echo $nama; ?></h3>
                </div>
                </div>
              </div>
            </div>
          </div>
          
          <?php if (isset($_GET['status']) && isset($_GET['jenis'])): ?>
            <script>
              document.addEventListener('DOMContentLoaded', function() {
                <?php if ($_GET['status'] === 'success'): ?>
                  <?php if ($_GET['jenis'] === 'izin'): ?>
                    Swal.fire({
                      icon: 'success',
                      title: 'Berhasil!',
                      text: 'Pengajuan izin berhasil disimpan dan sedang menunggu persetujuan.',
                      timer: 3000,
                      showConfirmButton: false
                    });
                  <?php elseif ($_GET['jenis'] === 'check_in' || $_GET['jenis'] === 'check_out'): ?>
                    Swal.fire({
                      icon: 'success',
                      title: 'Berhasil!',
                      text: 'Absensi <?php echo ($_GET['jenis'] === 'check_in') ? 'masuk' : 'pulang'; ?> berhasil disimpan.',
                      timer: 3000,
                      showConfirmButton: false
                    });
                  <?php endif; ?>
                <?php elseif ($_GET['status'] === 'error'): ?>
                  Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: 'Terjadi kesalahan. Silakan coba lagi nanti.',
                    timer: 3000,
                    showConfirmButton: false
                  });
                <?php endif; ?>

                // Hapus parameter status & jenis dari URL setelah Swal muncul
                if (window.history.replaceState) {
                  const url = new URL(window.location);
                  url.searchParams.delete('status');
                  url.searchParams.delete('jenis');
                  window.history.replaceState({}, document.title, url.pathname + url.search);
                }
              });
            </script>
          <?php endif; ?>

          <!-- Card Absensi -->
          <div class="col-12">
            <div class="row">
              <div class="col-12 mb-4">
                <div class="card">
                  <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Jadwal Hari Ini</h4>
                  </div>
                  <div class="card-body">
                    <div class="row">
                    <?php 
                      $today_date = date('Y-m-d');
                      $today_found = false;
                      
                      // Tampilkan jadwal hari ini
                      foreach ($jadwal_array as $jadwal) {
                        if ($jadwal['tanggal'] == $today_date) {
                          $today_found = true;
                          ?>
                          <div class="col-md-6 mb-4">
                            <div class="card border-primary shadow rounded-4" style="border-radius: 20px; min-height: 100%;">
                              <div class="card-body" style="border-radius: 0 0 20px 20px;">
                                <h5 class="card-title"><?php echo date('l', strtotime($jadwal['tanggal'])) . ', ' . formatTanggal($jadwal['tanggal']); ?></h5>
                                <p>ID Karyawan: <?php echo $id_karyawan; ?></p>
                                <p>Nama Karyawan: <?php echo $nama; ?></p>
                                <p>Outlet: <?php echo $outlet; ?></p>
                                <p>Shift: <?php echo isset($jadwal['nama_shift']) ? $jadwal['nama_shift'] . ' (' . formatWaktu($jadwal['jam_mulai']) . ' - ' . formatWaktu($jadwal['jam_selesai']) . ')' : 'Belum ditentukan'; ?></p>
                                
                                <?php 
                                // Logika untuk menentukan status absensi
                                $status_checkin = 'Belum Absen';
                                $status_checkout = 'Belum Absen';
                                $badge_checkin = 'badge-danger';
                                $badge_checkout = 'badge-danger';
                                
                                if (isset($jadwal['check_in']) && !empty($jadwal['check_in'])) {
                                    $status_checkin = $jadwal['status_check_in'];
                                    $badge_checkin = ($status_checkin == 'tepat waktu') ? 'badge-success' : (($status_checkin == 'tidak valid') ? 'badge-dark' : 'badge-warning');
                                }
                                
                                if (isset($jadwal['check_out']) && !empty($jadwal['check_out'])) {
                                    $status_checkout = $jadwal['status_check_out'];
                                    $badge_checkout = ($status_checkout == 'tepat waktu') ? 'badge-success' : (($status_checkout == 'tidak valid') ? 'badge-dark' : 'badge-warning');
                                }
                                ?>
                                
                                <p>Status Check in: <span class="badge <?php echo $badge_checkin; ?>"><?php echo ucfirst($status_checkin); ?></span></p>
                                <p>Status Check Out: <span class="badge <?php echo $badge_checkout; ?>"><?php echo ucfirst($status_checkout); ?></span></p>
                                
                                <?php if ($jadwal['status'] == 'masuk'): ?>
                                  <?php if (isset($jadwal['check_in']) && !empty($jadwal['check_in']) && isset($jadwal['check_out']) && !empty($jadwal['check_out'])): ?>
                                    <button class="btn btn-primary" disabled>Sudah Absen</button>
                                  <?php else: ?>
                                    <button class="btn btn-primary absen-btn" data-date="<?php echo $jadwal['tanggal']; ?>" data-shift="<?php echo $jadwal['id_shift']; ?>" data-checkin="<?php echo !empty($jadwal['check_in']) ? '1' : '0'; ?>" data-checkout="<?php echo !empty($jadwal['check_out']) ? '1' : '0'; ?>">Absen</button>
                                  <?php endif; ?>
                                  <button class="btn btn-primary izin-btn" data-date="<?php echo $jadwal['tanggal']; ?>">Ajukan Libur</button>
                                <?php else: ?>
                                  <span class="badge badge-info"><?php echo ucfirst($jadwal['status']); ?></span>
                                <?php endif; ?>
                              </div>
                            </div>
                          </div>
                        <?php
                        }
                      }
                      
                      if (!$today_found) {
                        echo '<div class="col-12"><div class="alert alert-info rounded-4">Tidak ada jadwal untuk hari ini.</div></div>';
                      }
                    ?>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Jadwal Mendatang dan Sebelumnya -->
              <div class="col-12">
                <div class="card">
                  <div class="card-header bg-primary text-white rounded-top">
                    <h4 class="mb-0">Jadwal Mendatang & Sebelumnya</h4>
                  </div>
                  <div class="card-body">
                    <div class="row">
                    <?php if (count($jadwal_array) > 0): ?>
                      <?php foreach ($jadwal_array as $jadwal): ?>
                        <?php if ($jadwal['tanggal'] != $today_date): ?>
                          <div class="col-md-4 mb-4">
                            <div class="card shadow rounded-4 <?php echo ($jadwal['tanggal'] < $today_date) ? 'border-primary' : 'border-info'; ?>" style="border-radius: 20px; min-height: 100%;">
                              <div class="card-header <?php echo ($jadwal['tanggal'] < $today_date) ? 'bg-primary' : 'bg-info'; ?> text-white rounded-top" style="border-top-left-radius: 20px; border-top-right-radius: 20px;">
                                <h5 class="card-title mb-0">
                                  <?php 
                                    if ($jadwal['tanggal'] < $today_date) {
                                      echo 'Sebelumnya';
                                    } elseif ($jadwal['tanggal'] > $today_date) {
                                      echo 'Mendatang';
                                    }
                                  ?>
                                </h5>
                              </div>
                              <div class="card-body" style="border-radius: 0 0 20px 20px;">
                                <h5 class="card-title"><?php echo date('l', strtotime($jadwal['tanggal'])) . ', ' . formatTanggal($jadwal['tanggal']); ?></h5>
                                <p>ID Karyawan: <?php echo $id_karyawan; ?></p>
                                <p>Nama Karyawan: <?php echo $nama; ?></p>
                                <p>Outlet: <?php echo $outlet; ?></p>
                                <p>Shift: <?php echo isset($jadwal['nama_shift']) ? $jadwal['nama_shift'] . ' (' . formatWaktu($jadwal['jam_mulai']) . ' - ' . formatWaktu($jadwal['jam_selesai']) . ')' : 'Belum ditentukan'; ?></p>
                                
                                <?php 
                                // Logika untuk menentukan status absensi
                                $status_checkin = 'Belum Absen';
                                $status_checkout = 'Belum Absen';
                                $badge_checkin = 'badge-danger';
                                $badge_checkout = 'badge-danger';
                                
                                if (isset($jadwal['check_in']) && !empty($jadwal['check_in'])) {
                                    $status_checkin = $jadwal['status_check_in'];
                                    $badge_checkin = ($status_checkin == 'tepat waktu') ? 'badge-success' : (($status_checkin == 'tidak valid') ? 'badge-dark' : 'badge-warning');
                                }
                                
                                if (isset($jadwal['check_out']) && !empty($jadwal['check_out'])) {
                                    $status_checkout = $jadwal['status_check_out'];
                                    $badge_checkout = ($status_checkout == 'tepat waktu') ? 'badge-success' : (($status_checkout == 'tidak valid') ? 'badge-dark' : 'badge-warning');
                                }
                                ?>
                                
                                <p>Status Check in: <span class="badge <?php echo $badge_checkin; ?>"><?php echo ucfirst($status_checkin); ?></span></p>
                                <p>Status Check Out: <span class="badge <?php echo $badge_checkout; ?>"><?php echo ucfirst($status_checkout); ?></span></p>
                                
                                <?php if ($jadwal['status'] == 'masuk'): ?>
                                  <!-- Tombol Absen dinonaktifkan untuk jadwal yang bukan hari ini -->
                                  <button class="btn btn-primary" disabled>Absen</button>
                                  <button class="btn btn-primary izin-btn" data-date="<?php echo $jadwal['tanggal']; ?>">Ajukan Libur</button>
                                <?php else: ?>
                                  <span class="badge badge-info"><?php echo ucfirst($jadwal['status']); ?></span>
                                <?php endif; ?>
                              </div>
                            </div>
                          </div>
                        <?php endif; ?>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <div class="col-12">
                        <div class="alert alert-info rounded-4">
                          Tidak ada jadwal untuk beberapa hari ke depan. Silakan hubungi supervisor Anda.
                        </div>
                      </div>
                    <?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- content-wrapper ends -->
        <!-- partial:partials/_footer.html -->
        <!-- partial -->
      </div>
      <!-- main-panel ends -->
    </div>
    <!-- page-body-wrapper ends -->
  </div>
  <!-- container-scroller -->

  <!-- plugins:js -->
  <script src="vendors/js/vendor.bundle.base.js"></script>
  <!-- endinject -->
  <!-- Plugin js for this page -->
  <script src="vendors/chart.js/Chart.min.js"></script>
  <script src="vendors/datatables.net/jquery.dataTables.js"></script>
  <script src="vendors/datatables.net-bs4/dataTables.bootstrap4.js"></script>
  <script src="js/dataTables.select.min.js"></script>

  <!-- End plugin js for this page -->
  <!-- inject:js -->
  <script src="js/off-canvas.js"></script>
  <script src="js/hoverable-collapse.js"></script>
  <script src="js/template.js"></script>
  <script src="js/settings.js"></script>
  <script src="js/todolist.js"></script>
  <!-- endinject -->
  <!-- Custom js for this page-->
  <script src="js/dashboard.js"></script>
  <script src="js/Chart.roundedBarCharts.js"></script>
  <!-- End custom js for this page-->
  <script src="js/absensi.js"></script>
  
  <!-- Modal HTML untuk Absensi -->
  <div class="modal fade" id="absenModal" tabindex="-1" role="dialog" aria-labelledby="absenModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="absenModalLabel">Absensi</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <!-- Informasi Ketentuan Absensi -->
          <div class="alert alert-info">
            <h6>Ketentuan Absensi:</h6>
            <ol>
              <li>Absensi Checkin akan dibuka 15 menit sebelum shift dimulai dan Checkout dibuka setelah shift Selesai</li>
              <li>Lakukan absensi dengan mengirimkan foto saat ini (Bukan foto lama)</li>
              <li>Sistem akan mendeteksi waktu dan lokasi dari foto tersebut diambil</li>
              <li>Absensi hanya dapat dilakukan jika Anda berada di lokasi kantor atau outlet dalam radius yang ditentukan</li>
              <li>Sistem akan memberikan toleransi keterlambatan selama 5 menit dari waktu shift dimulai</li>
              <li>Seluruh karyawan wajib untuk melakukan absensi check in dan check out jika salah satu tidak valid maka akan ada pengurangan gaji</li>
              <li>Segala bentuk kecurangan akan ada sanksi dari manajemen</li>
            </ol>
          </div>
          
          <!-- Tombol Check-in dan Check-out -->
          <div class="text-center mb-3">
            <button id="btnCheckIn" class="btn btn-success">Check-in</button>
            <button id="btnCheckOut" class="btn btn-danger">Check-out</button>
          </div>
          
          <!-- Video dari webcam -->
          <div class="text-center">
            <video id="webcam" width="320" height="240" autoplay></video>
            <canvas id="canvas" width="320" height="240" style="display: none;"></canvas>
            <div id="locationStatus" class="alert alert-warning mt-2" style="display: none;">
              <small>Memperoleh lokasi Anda...</small>
            </div>
            <button id="btnCapture" class="btn btn-primary mt-2" style="display: none;">Ambil Foto</button>
          </div>
          
          <!-- Form untuk data absensi -->
          <form id="absenForm" method="post" action="absen.php" enctype="multipart/form-data">
            <input type="hidden" id="inputTanggal" name="tanggal">
            <input type="hidden" id="inputShift" name="id_shift">
            <input type="hidden" id="jenisAbsensi" name="jenis_absensi">
            <input type="hidden" id="inputFoto" name="foto">
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal HTML untuk Izin -->
  <div class="modal fade" id="izinModal" tabindex="-1" role="dialog" aria-labelledby="izinModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="izinModalLabel">Form Pengajuan Izin</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form method="post" action="izin.php" enctype="multipart/form-data">
            <div class="form-group">
              <label for="tanggal_mulai">Tanggal Mulai</label>
              <input type="date" class="form-control" id="izinDate" name="tanggal_mulai" required>
            </div>
            <div class="form-group">
              <label for="tanggal_selesai">Tanggal Selesai</label>
              <input type="date" class="form-control" id="tanggal_selesai" name="tanggal_selesai">
              <small class="text-muted">Kosongkan jika hanya 1 hari</small>
            </div>
            <div class="form-group">
              <label for="jenis_izin">Jenis Izin</label>
              <select class="form-control" id="izinType" name="jenis_izin" required>
                <option value="">Pilih Jenis Izin</option>
                <option value="sakit">Sakit</option>
                <option value="izin">Izin</option>
                <option value="cuti">Cuti</option>
                <option value="lainnya">Lainnya</option>
              </select>
            </div>
            <div class="form-group">
              <label for="keterangan">Keterangan</label>
              <textarea class="form-control" id="keterangan" name="keterangan" rows="3" required></textarea>
            </div>
            <div class="form-group" id="sakitForm" style="display: none;">
              <label for="bukti_file">Upload Surat Sakit</label>
              <input type="file" class="form-control-file" id="bukti_file" name="bukti_file" accept=".pdf,.jpg,.jpeg,.png">
              <small class="form-text text-muted">Format file yang diterima: PDF, JPG, JPEG, PNG</small>
            </div>
            <div class="form-group">
              <label for="solusi_pengganti">Solusi Pengganti</label>
              <select class="form-control" id="solusi_pengganti" name="solusi_pengganti" required>
                <option value="">Pilih Pengganti</option>
                <option value="shift">Tukaran Shift</option>
                <option value="libur">Tukaran Libur</option>
                <option value="cuti">Potong Cuti</option>
                <option value="gaji">Potong Gaji</option>
              </select>
            </div>
            <button type="submit" class="btn btn-primary">Ajukan Izin</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script>
    // JavaScript untuk menampilkan modal ketika tombol 'Absen' diklik
    document.querySelectorAll('.absen-btn').forEach(button => {
      button.addEventListener('click', () => {
        $('#absenModal').modal('show');
      });
    });
  </script>

  <script>
  // JavaScript untuk menampilkan modal ketika tombol 'Absen' diklik
    document.querySelectorAll('.izin-btn').forEach(button => {
      button.addEventListener('click', () => {
        $('#izinModal').modal('show');
      });
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>