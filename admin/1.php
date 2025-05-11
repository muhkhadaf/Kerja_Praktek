<?php
// Include konfigurasi
require_once '../config.php';

// Cek apakah pengguna sudah login sebagai admin
if (!isLoggedIn() || getUserRole() !== 'admin') {
    header("Location: ../admin_login.php");
    exit();
}

// Ambil data admin yang login
$nama = getUserName();

// Query untuk statistik dashboard
$query_karyawan = "SELECT COUNT(*) as total_karyawan FROM users WHERE role = 'karyawan'";
$result_karyawan = mysqli_query($koneksi, $query_karyawan);
$total_karyawan = 0;
if ($result_karyawan) {
    $row = mysqli_fetch_assoc($result_karyawan);
    $total_karyawan = $row['total_karyawan'];
}

$query_izin = "SELECT COUNT(*) as total_izin FROM izin WHERE status = 'pending'";
$result_izin = mysqli_query($koneksi, $query_izin);
$total_izin_pending = 0;
if ($result_izin) {
    $row = mysqli_fetch_assoc($result_izin);
    $total_izin_pending = $row['total_izin'];
}

$query_shift = "SELECT COUNT(*) as total_shift FROM shift";
$result_shift = mysqli_query($koneksi, $query_shift);
$total_shift = 0;
if ($result_shift) {
    $row = mysqli_fetch_assoc($result_shift);
    $total_shift = $row['total_shift'];
}

$query_jadwal = "SELECT COUNT(*) as total_jadwal FROM jadwal WHERE tanggal >= CURDATE()";
$result_jadwal = mysqli_query($koneksi, $query_jadwal);
$total_jadwal = 0;
if ($result_jadwal) {
    $row = mysqli_fetch_assoc($result_jadwal);
    $total_jadwal = $row['total_jadwal'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Wakacao Admin</title>
  <!-- plugins:css -->
  <link rel="stylesheet" href="../vendors/feather/feather.css">
  <link rel="stylesheet" href="../vendors/ti-icons/css/themify-icons.css">
  <link rel="stylesheet" href="../vendors/css/vendor.bundle.base.css">
  <!-- endinject -->
  <!-- Plugin css for this page -->
  <link rel="stylesheet" href="../vendors/datatables.net-bs4/dataTables.bootstrap4.css">
  <link rel="stylesheet" type="text/css" href="../js/select.dataTables.min.css">
  <!-- End plugin css for this page -->
  <!-- inject:css -->
  <link rel="stylesheet" href="../css/vertical-layout-light/style.css">
  <!-- endinject -->
  <link rel="shortcut icon" href="../images/favicon.png" />
</head>

<body>
<div class="container-scroller">
    <!-- partial:partials/_navbar.html -->
    <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
      <span class="icon-menu"></span>
    </button>
    <nav class="navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
      <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
        <a class="navbar-brand brand-logo mr-5" href="index.html"><img src="../images/logo.svg" class="mr-2" alt="logo"/></a>
        <a class="navbar-brand brand-logo-mini" href="index.html"><img src="../images/logo-mini.svg" alt="logo"/></a>
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
              <a class="dropdown-item" href="../logout.php">
                <i class="ti-power-off text-primary"></i>
                Logout
              </a>
            </div>
          </li>
        </ul>
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
                  <h3 class="font-weight-bold">Selamat Datang, Admin <?php echo $nama; ?></h3>
                  <h6 class="font-weight-normal mb-0">Panel admin untuk mengelola seluruh sistem Wakacao</h6>
                </div>
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-3 mb-4 stretch-card transparent">
              <div class="card card-tale">
                <div class="card-body">
                  <p class="mb-4">Total Karyawan</p>
                  <p class="fs-30 mb-2"><?php echo $total_karyawan; ?></p>
                  <p>Karyawan aktif saat ini</p>
                </div>
              </div>
            </div>
            <div class="col-md-3 mb-4 stretch-card transparent">
              <div class="card card-dark-blue">
                <div class="card-body">
                  <p class="mb-4">Total Shift</p>
                  <p class="fs-30 mb-2"><?php echo $total_shift; ?></p>
                  <p>Shift yang tersedia</p>
                </div>
              </div>
            </div>
            <div class="col-md-3 mb-4 stretch-card transparent">
              <div class="card card-light-blue">
                <div class="card-body">
                  <p class="mb-4">Jadwal Aktif</p>
                  <p class="fs-30 mb-2"><?php echo $total_jadwal; ?></p>
                  <p>Jadwal yang akan datang</p>
                </div>
              </div>
            </div>
            <div class="col-md-3 mb-4 stretch-card transparent">
              <div class="card card-light-danger">
                <div class="card-body">
                  <p class="mb-4">Persetujuan Tertunda</p>
                  <p class="fs-30 mb-2"><?php echo $total_izin_pending; ?></p>
                  <p>Menunggu persetujuan anda</p>
                </div>
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6 grid-margin stretch-card">
              <div class="card">
                <div class="card-body">
                  <p class="card-title">Pengajuan Izin Terbaru</p>
                  <div class="table-responsive">
                    <table class="table table-striped table-borderless">
                      <thead>
                        <tr>
                          <th>Nama</th>
                          <th>Jenis Izin</th>
                          <th>Tanggal</th>
                          <th>Status</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        $query_izin_terbaru = "SELECT i.*, u.nama FROM izin i 
                                               JOIN users u ON i.id_karyawan = u.id_karyawan 
                                               ORDER BY i.created_at DESC LIMIT 5";
                        $result_izin_terbaru = mysqli_query($koneksi, $query_izin_terbaru);
                        if ($result_izin_terbaru && mysqli_num_rows($result_izin_terbaru) > 0) {
                          while ($row = mysqli_fetch_assoc($result_izin_terbaru)) {
                            $status_class = '';
                            switch ($row['status']) {
                              case 'pending': $status_class = 'badge-warning'; break;
                              case 'disetujui': $status_class = 'badge-success'; break;
                              case 'ditolak': $status_class = 'badge-danger'; break;
                            }
                            echo '<tr>';
                            echo '<td>' . $row['nama'] . '</td>';
                            echo '<td>' . ucfirst($row['jenis_izin']) . '</td>';
                            echo '<td>' . formatTanggal($row['tanggal_mulai']) . '</td>';
                            echo '<td><label class="badge ' . $status_class . '">' . ucfirst($row['status']) . '</label></td>';
                            echo '</tr>';
                          }
                        } else {
                          echo '<tr><td colspan="4" class="text-center">Tidak ada data izin</td></tr>';
                        }
                        ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-6 grid-margin stretch-card">
              <div class="card">
                <div class="card-body">
                  <p class="card-title">Menu Cepat</p>
                  <div class="d-flex flex-wrap mb-5">
                    <a href="manage_shift.php" class="btn btn-primary mr-3 mb-3">
                      <i class="ti-time mr-1"></i> Kelola Shift
                    </a>
                    <a href="manage_schedule.php" class="btn btn-success mr-3 mb-3">
                      <i class="ti-calendar mr-1"></i> Atur Jadwal
                    </a>
                    <a href="approval_requests.php" class="btn btn-info mr-3 mb-3">
                      <i class="ti-check-box mr-1"></i> Setujui Izin
                    </a>
                    <a href="reports.php" class="btn btn-warning mr-3 mb-3">
                      <i class="ti-bar-chart mr-1"></i> Lihat Laporan
                    </a>
                    <a href="manage_employee.php" class="btn btn-danger mr-3 mb-3">
                      <i class="ti-user mr-1"></i> Tambah Karyawan
                    </a>
                    <a href="settings.php" class="btn btn-secondary mr-3 mb-3">
                      <i class="ti-settings mr-1"></i> Pengaturan
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <script>
          $(document).ready(function() {
            $('.navbar-toggler').click(function() {
              $('.sidebar').toggleClass('active');
            });
          });
        </script>
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
  <script src="../vendors/chart.js/Chart.min.js"></script>
  <script src="../vendors/datatables.net/jquery.dataTables.js"></script>
  <script src="../vendors/datatables.net-bs4/dataTables.bootstrap4.js"></script>
  <script src="../js/dataTables.select.min.js"></script>

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
  <script src="../js/Chart.roundedBarCharts.js"></script>
  <!-- End custom js for this page-->
</body>



</html> 