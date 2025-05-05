<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Fungsi untuk mendapatkan data rekap
function getRekapData($koneksi, $month, $year) {
    $data = [];
    
    // Query untuk absensi
    $sql_absensi = "SELECT 
        u.nama as nama_lengkap,
        COUNT(CASE WHEN a.status_check_in = 'tepat waktu' THEN 1 END) as total_hadir,
        COUNT(CASE WHEN a.status_check_in = 'terlambat' THEN 1 END) as total_terlambat,
        COUNT(CASE WHEN a.status_check_in = 'tidak absen' THEN 1 END) as total_tidak_hadir
        FROM absensi a
        JOIN users u ON a.id_karyawan = u.id_karyawan
        WHERE MONTH(a.tanggal) = ? AND YEAR(a.tanggal) = ?
        GROUP BY u.id_karyawan, u.nama";
    
    // Query untuk cuti
    $sql_cuti = "SELECT 
        u.nama as nama_lengkap,
        COUNT(*) as total_cuti
        FROM cuti_tahunan c
        JOIN users u ON c.id_karyawan = u.id_karyawan
        WHERE MONTH(c.tanggal_mulai) = ? AND YEAR(c.tanggal_mulai) = ?
        AND c.status = 'disetujui'
        GROUP BY u.id_karyawan, u.nama";
    
    // Query untuk izin
    $sql_izin = "SELECT 
        u.nama as nama_lengkap,
        COUNT(*) as total_izin
        FROM izin i
        JOIN users u ON i.id_karyawan = u.id_karyawan
        WHERE MONTH(i.tanggal_mulai) = ? AND YEAR(i.tanggal_mulai) = ?
        AND i.status = 'disetujui'
        GROUP BY u.id_karyawan, u.nama";
    
    // Query untuk shift
    $sql_shift = "SELECT 
        u.nama as nama_lengkap,
        COUNT(*) as total_shift
        FROM jadwal j
        JOIN users u ON j.id_karyawan = u.id_karyawan
        WHERE MONTH(j.tanggal) = ? AND YEAR(j.tanggal) = ?
        AND j.status = 'masuk'
        GROUP BY u.id_karyawan, u.nama";
    
    $stmt = $koneksi->prepare($sql_absensi);
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $result_absensi = $stmt->get_result();
    while ($row = $result_absensi->fetch_assoc()) {
        $data[$row['nama_lengkap']]['absensi'] = $row;
    }
    
    $stmt = $koneksi->prepare($sql_cuti);
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $result_cuti = $stmt->get_result();
    while ($row = $result_cuti->fetch_assoc()) {
        $data[$row['nama_lengkap']]['cuti'] = $row;
    }
    
    $stmt = $koneksi->prepare($sql_izin);
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $result_izin = $stmt->get_result();
    while ($row = $result_izin->fetch_assoc()) {
        $data[$row['nama_lengkap']]['izin'] = $row;
    }
    
    $stmt = $koneksi->prepare($sql_shift);
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $result_shift = $stmt->get_result();
    while ($row = $result_shift->fetch_assoc()) {
        $data[$row['nama_lengkap']]['shift'] = $row;
    }
    
    return $data;
}

// Fungsi untuk download CSV
function downloadCSV($data, $month, $year) {
    $filename = 'Rekap_Karyawan_' . date('F_Y', mktime(0, 0, 0, $month, 1, $year)) . '.csv';
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Header
    fputcsv($output, ['Laporan Rekap Karyawan - ' . date('F Y', mktime(0, 0, 0, $month, 1, $year))]);
    fputcsv($output, []); // Empty line
    
    // Column headers
    fputcsv($output, ['Nama Karyawan', 'Total Hadir', 'Total Terlambat', 'Total Tidak Hadir', 'Total Cuti', 'Total Izin', 'Total Shift']);
    
    // Data
    foreach ($data as $nama => $rekap) {
        fputcsv($output, [
            $nama,
            $rekap['absensi']['total_hadir'] ?? 0,
            $rekap['absensi']['total_terlambat'] ?? 0,
            $rekap['absensi']['total_tidak_hadir'] ?? 0,
            $rekap['cuti']['total_cuti'] ?? 0,
            $rekap['izin']['total_izin'] ?? 0,
            $rekap['shift']['total_shift'] ?? 0
        ]);
    }
    
    fclose($output);
    exit;
}

// Handle download request
if (isset($_GET['download'])) {
    $month = $_GET['month'];
    $year = $_GET['year'];
    $data = getRekapData($koneksi, $month, $year);
    downloadCSV($data, $month, $year);
}

// Get current month and year
$current_month = date('n');
$current_year = date('Y');

// Get data for current month
$data_bulan_ini = getRekapData($koneksi, $current_month, $current_year);

// Get data for previous month
$prev_month = $current_month - 1;
$prev_year = $current_year;
if ($prev_month == 0) {
    $prev_month = 12;
    $prev_year--;
}
$data_bulan_lalu = getRekapData($koneksi, $prev_month, $prev_year);

// Get data for next month
$next_month = $current_month + 1;
$next_year = $current_year;
if ($next_month == 13) {
    $next_month = 1;
    $next_year++;
}
$data_bulan_depan = getRekapData($koneksi, $next_month, $next_year);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Wakacao - Laporan</title>
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
    <nav class="navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
      <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
        <a class="navbar-brand brand-logo mr-5" href="../index.php"><img src="../images/logowakacao.png" class="mr-2" alt="logo" style="height: 60px; width: auto;"/></a>
        <a class="navbar-brand brand-logo-mini" href="../index.php"><img src="../images/logowakacao.png" alt="logo" style="height: 45px; width: auto;"/></a>
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
                Settings
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
    <!-- partial -->
    <?php include_once 'sidebar.php'; ?>
      <!-- partial -->
      <div class="main-panel">
        <div class="content-wrapper" style="background-color: #f5f7fa;">
          <div class="row">
            <div class="col-md-12 grid-margin">
              <div class="row">
                <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                  <h3 class="font-weight-bold">Laporan Rekap Karyawan</h3>
                </div>
              </div>
            </div>
          </div>

          <!-- Tab Navigation -->
          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-body">
                  <ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
                    <li class="nav-item">
                      <a class="nav-link active" id="current-tab" data-bs-toggle="tab" href="#current" role="tab">
                        Bulan Ini (<?php echo date('F Y'); ?>)
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" id="prev-tab" data-bs-toggle="tab" href="#prev" role="tab">
                        Bulan Lalu (<?php echo date('F Y', mktime(0, 0, 0, $prev_month, 1, $prev_year)); ?>)
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" id="next-tab" data-bs-toggle="tab" href="#next" role="tab">
                        Bulan Depan (<?php echo date('F Y', mktime(0, 0, 0, $next_month, 1, $next_year)); ?>)
                      </a>
                    </li>
                  </ul>
                  
                  <!-- Tab Content -->
                  <div class="tab-content" id="myTabContent">
                    <!-- Current Month -->
                    <div class="tab-pane fade show active" id="current" role="tabpanel">
                      <div class="d-flex justify-content-end mb-3">
                        <a href="?download=1&month=<?php echo $current_month; ?>&year=<?php echo $current_year; ?>" 
                           class="btn btn-success">
                          <i class="ti-download"></i> Download CSV
                        </a>
                      </div>
                      <div class="table-responsive">
                        <table class="table table-striped table-bordered" id="table-current">
                          <thead>
                            <tr>
                              <th>Nama Karyawan</th>
                              <th>Total Hadir</th>
                              <th>Total Terlambat</th>
                              <th>Total Tidak Hadir</th>
                              <th>Total Cuti</th>
                              <th>Total Izin</th>
                              <th>Total Shift</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php foreach ($data_bulan_ini as $nama => $rekap): ?>
                            <tr>
                              <td><?php echo $nama; ?></td>
                              <td><?php echo $rekap['absensi']['total_hadir'] ?? 0; ?></td>
                              <td><?php echo $rekap['absensi']['total_terlambat'] ?? 0; ?></td>
                              <td><?php echo $rekap['absensi']['total_tidak_hadir'] ?? 0; ?></td>
                              <td><?php echo $rekap['cuti']['total_cuti'] ?? 0; ?></td>
                              <td><?php echo $rekap['izin']['total_izin'] ?? 0; ?></td>
                              <td><?php echo $rekap['shift']['total_shift'] ?? 0; ?></td>
                            </tr>
                            <?php endforeach; ?>
                          </tbody>
                        </table>
                      </div>
                    </div>
                    
                    <!-- Previous Month -->
                    <div class="tab-pane fade" id="prev" role="tabpanel">
                      <div class="d-flex justify-content-end mb-3">
                        <a href="?download=1&month=<?php echo $prev_month; ?>&year=<?php echo $prev_year; ?>" 
                           class="btn btn-success">
                          <i class="ti-download"></i> Download CSV
                        </a>
                      </div>
                      <div class="table-responsive">
                        <table class="table table-striped table-bordered" id="table-prev">
                          <thead>
                            <tr>
                              <th>Nama Karyawan</th>
                              <th>Total Hadir</th>
                              <th>Total Terlambat</th>
                              <th>Total Tidak Hadir</th>
                              <th>Total Cuti</th>
                              <th>Total Izin</th>
                              <th>Total Shift</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php foreach ($data_bulan_lalu as $nama => $rekap): ?>
                            <tr>
                              <td><?php echo $nama; ?></td>
                              <td><?php echo $rekap['absensi']['total_hadir'] ?? 0; ?></td>
                              <td><?php echo $rekap['absensi']['total_terlambat'] ?? 0; ?></td>
                              <td><?php echo $rekap['absensi']['total_tidak_hadir'] ?? 0; ?></td>
                              <td><?php echo $rekap['cuti']['total_cuti'] ?? 0; ?></td>
                              <td><?php echo $rekap['izin']['total_izin'] ?? 0; ?></td>
                              <td><?php echo $rekap['shift']['total_shift'] ?? 0; ?></td>
                            </tr>
                            <?php endforeach; ?>
                          </tbody>
                        </table>
                      </div>
                    </div>
                    
                    <!-- Next Month -->
                    <div class="tab-pane fade" id="next" role="tabpanel">
                      <div class="d-flex justify-content-end mb-3">
                        <a href="?download=1&month=<?php echo $next_month; ?>&year=<?php echo $next_year; ?>" 
                           class="btn btn-success">
                          <i class="ti-download"></i> Download CSV
                        </a>
                      </div>
                      <div class="table-responsive">
                        <table class="table table-striped table-bordered" id="table-next">
                          <thead>
                            <tr>
                              <th>Nama Karyawan</th>
                              <th>Total Hadir</th>
                              <th>Total Terlambat</th>
                              <th>Total Tidak Hadir</th>
                              <th>Total Cuti</th>
                              <th>Total Izin</th>
                              <th>Total Shift</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php foreach ($data_bulan_depan as $nama => $rekap): ?>
                            <tr>
                              <td><?php echo $nama; ?></td>
                              <td><?php echo $rekap['absensi']['total_hadir'] ?? 0; ?></td>
                              <td><?php echo $rekap['absensi']['total_terlambat'] ?? 0; ?></td>
                              <td><?php echo $rekap['absensi']['total_tidak_hadir'] ?? 0; ?></td>
                              <td><?php echo $rekap['cuti']['total_cuti'] ?? 0; ?></td>
                              <td><?php echo $rekap['izin']['total_izin'] ?? 0; ?></td>
                              <td><?php echo $rekap['shift']['total_shift'] ?? 0; ?></td>
                            </tr>
                            <?php endforeach; ?>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- content-wrapper ends -->
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
  
  <script>
    $(document).ready(function() {
      $('#table-current, #table-prev, #table-next').DataTable({
        "language": {
          "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
        }
      });
    });
  </script>
</body>
</html>
