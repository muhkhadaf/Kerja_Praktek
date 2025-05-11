<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Fungsi untuk mendapatkan data rekap per outlet
function getRekapData($koneksi, $month, $year) {
    $data = [];
    
    // Dapatkan semua outlet yang ada
    $sql_outlets = "SELECT DISTINCT outlet FROM users WHERE role = 'karyawan' ORDER BY outlet";
    $result_outlets = $koneksi->query($sql_outlets);
    $outlets = [];
    
    while ($row = $result_outlets->fetch_assoc()) {
        $outlets[] = $row['outlet'];
    }
    
    // Dapatkan semua karyawan untuk setiap outlet
    $karyawan_per_outlet = [];
    
    foreach ($outlets as $outlet) {
        $data[$outlet] = [];
        $karyawan_per_outlet[$outlet] = [];
        
        // Query untuk mendapatkan semua karyawan di outlet
        $sql_karyawan = "SELECT id_karyawan, nama FROM users WHERE role = 'karyawan' AND outlet = ?";
        $stmt = $koneksi->prepare($sql_karyawan);
        $stmt->bind_param("s", $outlet);
        $stmt->execute();
        $result_karyawan = $stmt->get_result();
        
        while ($row = $result_karyawan->fetch_assoc()) {
            $karyawan_per_outlet[$outlet][$row['id_karyawan']] = $row['nama'];
            // Inisialisasi data untuk setiap karyawan
            $data[$outlet][$row['nama']] = [
                'absensi' => [
                    'total_hadir' => 0,
                    'total_terlambat' => 0,
                    'total_tidak_hadir' => 0
                ],
                'cuti' => ['total_cuti' => 0],
                'izin' => ['total_izin' => 0],
                'shift' => ['total_shift' => 0]
            ];
        }
        
        // Query untuk absensi per outlet
        $sql_absensi = "SELECT 
            u.nama as nama_lengkap,
            u.outlet as outlet,
            COUNT(CASE WHEN a.status_check_in = 'tepat waktu' THEN 1 END) as total_hadir,
            COUNT(CASE WHEN a.status_check_in = 'terlambat' THEN 1 END) as total_terlambat,
            COUNT(CASE WHEN a.status_check_in = 'tidak absen' THEN 1 END) as total_tidak_hadir
            FROM absensi a
            JOIN users u ON a.id_karyawan = u.id_karyawan
            WHERE MONTH(a.tanggal) = ? AND YEAR(a.tanggal) = ? AND u.outlet = ?
            GROUP BY u.id_karyawan, u.nama, u.outlet";
        
        // Query untuk cuti per outlet
        $sql_cuti = "SELECT 
            u.nama as nama_lengkap,
            u.outlet as outlet,
            COUNT(*) as total_cuti
            FROM cuti_tahunan c
            JOIN users u ON c.id_karyawan = u.id_karyawan
            WHERE MONTH(c.tanggal_mulai) = ? AND YEAR(c.tanggal_mulai) = ?
            AND c.status = 'disetujui' AND u.outlet = ?
            GROUP BY u.id_karyawan, u.nama, u.outlet";
        
        // Query untuk izin per outlet
        $sql_izin = "SELECT 
            u.nama as nama_lengkap,
            u.outlet as outlet,
            COUNT(*) as total_izin
            FROM izin i
            JOIN users u ON i.id_karyawan = u.id_karyawan
            WHERE MONTH(i.tanggal_mulai) = ? AND YEAR(i.tanggal_mulai) = ?
            AND i.status = 'disetujui' AND u.outlet = ?
            GROUP BY u.id_karyawan, u.nama, u.outlet";
        
        // Query untuk shift per outlet
        $sql_shift = "SELECT 
            u.nama as nama_lengkap,
            u.outlet as outlet,
            COUNT(*) as total_shift
            FROM jadwal j
            JOIN users u ON j.id_karyawan = u.id_karyawan
            WHERE MONTH(j.tanggal) = ? AND YEAR(j.tanggal) = ?
            AND j.status = 'masuk' AND u.outlet = ?
            GROUP BY u.id_karyawan, u.nama, u.outlet";
        
        $stmt = $koneksi->prepare($sql_absensi);
        $stmt->bind_param("iis", $month, $year, $outlet);
        $stmt->execute();
        $result_absensi = $stmt->get_result();
        while ($row = $result_absensi->fetch_assoc()) {
            $data[$outlet][$row['nama_lengkap']]['absensi'] = $row;
        }
        
        $stmt = $koneksi->prepare($sql_cuti);
        $stmt->bind_param("iis", $month, $year, $outlet);
        $stmt->execute();
        $result_cuti = $stmt->get_result();
        while ($row = $result_cuti->fetch_assoc()) {
            $data[$outlet][$row['nama_lengkap']]['cuti'] = $row;
        }
        
        $stmt = $koneksi->prepare($sql_izin);
        $stmt->bind_param("iis", $month, $year, $outlet);
        $stmt->execute();
        $result_izin = $stmt->get_result();
        while ($row = $result_izin->fetch_assoc()) {
            $data[$outlet][$row['nama_lengkap']]['izin'] = $row;
        }
        
        $stmt = $koneksi->prepare($sql_shift);
        $stmt->bind_param("iis", $month, $year, $outlet);
        $stmt->execute();
        $result_shift = $stmt->get_result();
        while ($row = $result_shift->fetch_assoc()) {
            $data[$outlet][$row['nama_lengkap']]['shift'] = $row;
        }
    }
    
    return $data;
}

// Fungsi untuk download CSV
function downloadCSV($data, $month, $year, $outlet = null) {
    if ($outlet) {
        $filename = 'Rekap_Karyawan_' . $outlet . '_' . date('F_Y', mktime(0, 0, 0, $month, 1, $year)) . '.csv';
    } else {
        $filename = 'Rekap_Karyawan_Semua_Outlet_' . date('F_Y', mktime(0, 0, 0, $month, 1, $year)) . '.csv';
    }
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Header
    if ($outlet) {
        fputcsv($output, ['Laporan Rekap Karyawan - Outlet: ' . $outlet . ' - ' . date('F Y', mktime(0, 0, 0, $month, 1, $year))]);
    } else {
        fputcsv($output, ['Laporan Rekap Karyawan - Semua Outlet - ' . date('F Y', mktime(0, 0, 0, $month, 1, $year))]);
    }
    fputcsv($output, []); // Empty line
    
    // Column headers
    fputcsv($output, ['Nama Karyawan', 'Outlet', 'Total Hadir', 'Total Terlambat', 'Total Tidak Hadir', 'Total Cuti', 'Total Izin', 'Total Shift']);
    
    // Data
    if ($outlet) {
        // Satu outlet saja
        foreach ($data[$outlet] as $nama => $rekap) {
            fputcsv($output, [
                $nama,
                $outlet,
                $rekap['absensi']['total_hadir'] ?? 0,
                $rekap['absensi']['total_terlambat'] ?? 0,
                $rekap['absensi']['total_tidak_hadir'] ?? 0,
                $rekap['cuti']['total_cuti'] ?? 0,
                $rekap['izin']['total_izin'] ?? 0,
                $rekap['shift']['total_shift'] ?? 0
            ]);
        }
    } else {
        // Semua outlet
        foreach ($data as $outlet_name => $outlet_data) {
            foreach ($outlet_data as $nama => $rekap) {
                fputcsv($output, [
                    $nama,
                    $outlet_name,
                    $rekap['absensi']['total_hadir'] ?? 0,
                    $rekap['absensi']['total_terlambat'] ?? 0,
                    $rekap['absensi']['total_tidak_hadir'] ?? 0,
                    $rekap['cuti']['total_cuti'] ?? 0,
                    $rekap['izin']['total_izin'] ?? 0,
                    $rekap['shift']['total_shift'] ?? 0
                ]);
            }
        }
    }
    
    fclose($output);
    exit;
}

// Handle download request
if (isset($_GET['download'])) {
    $month = $_GET['month'];
    $year = $_GET['year'];
    $outlet = isset($_GET['outlet']) ? $_GET['outlet'] : null;
    $data = getRekapData($koneksi, $month, $year);
    downloadCSV($data, $month, $year, $outlet);
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

// Get data for 2 months ago
$prev_month2 = $prev_month - 1;
$prev_year2 = $prev_year;
if ($prev_month2 == 0) {
    $prev_month2 = 12;
    $prev_year2--;
}
$data_bulan_lalu2 = getRekapData($koneksi, $prev_month2, $prev_year2);

// Get data for 3 months ago
$prev_month3 = $prev_month2 - 1;
$prev_year3 = $prev_year2;
if ($prev_month3 == 0) {
    $prev_month3 = 12;
    $prev_year3--;
}
$data_bulan_lalu3 = getRekapData($koneksi, $prev_month3, $prev_year3);

// Dapatkan daftar outlet
$sql_outlets = "SELECT DISTINCT outlet FROM users WHERE role = 'karyawan' ORDER BY outlet";
$result_outlets = $koneksi->query($sql_outlets);
$outlets = [];

while ($row = $result_outlets->fetch_assoc()) {
    $outlets[] = $row['outlet'];
}
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
    <?php include_once 'navbar.php'; ?>
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
                      <a class="nav-link" id="prev2-tab" data-bs-toggle="tab" href="#prev2" role="tab">
                        2 Bulan Lalu (<?php echo date('F Y', mktime(0, 0, 0, $prev_month2, 1, $prev_year2)); ?>)
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" id="prev3-tab" data-bs-toggle="tab" href="#prev3" role="tab">
                        3 Bulan Lalu (<?php echo date('F Y', mktime(0, 0, 0, $prev_month3, 1, $prev_year3)); ?>)
                      </a>
                    </li>
                  </ul>
                  
                  <?php if ($_SESSION['user_role'] === 'admin' && isset($_GET['debug'])): ?>
                  <div class="alert alert-info mb-4">
                    <h5>Debug Info</h5>
                    <p>Koneksi Database: <?php echo $koneksi->ping() ? 'Terhubung' : 'Tidak Terhubung'; ?></p>
                    <p>Bulan saat ini: <?php echo $current_month; ?>, Tahun saat ini: <?php echo $current_year; ?></p>
                    <p>Bulan lalu: <?php echo $prev_month; ?>, Tahun lalu: <?php echo $prev_year; ?></p>
                    <h6>Outlets:</h6>
                    <pre><?php print_r($outlets); ?></pre>
                    <h6>Data Bulan Ini (Sample):</h6>
                    <pre><?php 
                      foreach ($data_bulan_ini as $outlet => $data) {
                          echo "Outlet: $outlet - " . count($data) . " karyawan\n";
                          break;
                      }
                    ?></pre>
                    <h6>Data Bulan Lalu (Sample):</h6>
                    <pre><?php 
                      foreach ($data_bulan_lalu as $outlet => $data) {
                          echo "Outlet: $outlet - " . count($data) . " karyawan\n";
                          break;
                      }
                    ?></pre>
                  </div>
                  <?php endif; ?>
                  
                  <!-- Tab Content -->
                  <div class="tab-content" id="myTabContent">
                    <!-- Current Month -->
                    <div class="tab-pane fade show active" id="current" role="tabpanel">
                      <div class="d-flex justify-content-end mb-3">
                        <a href="?download=1&month=<?php echo $current_month; ?>&year=<?php echo $current_year; ?>" 
                           class="btn btn-success">
                          <i class="ti-download"></i> Download CSV (Semua Outlet)
                        </a>
                      </div>
                      
                      <?php foreach ($outlets as $outlet): ?>
                      <div class="mb-5">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                          <h4 class="font-weight-bold">Outlet: <?php echo $outlet; ?></h4>
                          <a href="?download=1&month=<?php echo $current_month; ?>&year=<?php echo $current_year; ?>&outlet=<?php echo $outlet; ?>" 
                             class="btn btn-outline-success btn-sm">
                            <i class="ti-download"></i> Download CSV
                          </a>
                        </div>
                        <div class="table-responsive">
                          <table class="table table-striped table-bordered" id="table-current-<?php echo str_replace(' ', '-', strtolower($outlet)); ?>">
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
                              <?php if (isset($data_bulan_ini[$outlet])): ?>
                                <?php foreach ($data_bulan_ini[$outlet] as $nama => $rekap): ?>
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
                              <?php else: ?>
                                <tr>
                                  <td colspan="7" class="text-center">Tidak ada data</td>
                                </tr>
                              <?php endif; ?>
                            </tbody>
                          </table>
                        </div>
                      </div>
                      <?php endforeach; ?>
                    </div>
                    
                    <!-- Previous Month -->
                    <div class="tab-pane fade" id="prev" role="tabpanel">
                      <div class="d-flex justify-content-end mb-3">
                        <a href="?download=1&month=<?php echo $prev_month; ?>&year=<?php echo $prev_year; ?>" 
                           class="btn btn-success">
                          <i class="ti-download"></i> Download CSV (Semua Outlet)
                        </a>
                      </div>
                      
                      <?php foreach ($outlets as $outlet): ?>
                      <div class="mb-5">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                          <h4 class="font-weight-bold">Outlet: <?php echo $outlet; ?></h4>
                          <a href="?download=1&month=<?php echo $prev_month; ?>&year=<?php echo $prev_year; ?>&outlet=<?php echo $outlet; ?>" 
                             class="btn btn-outline-success btn-sm">
                            <i class="ti-download"></i> Download CSV
                          </a>
                        </div>
                        <div class="table-responsive">
                          <table class="table table-striped table-bordered" id="table-prev-<?php echo str_replace(' ', '-', strtolower($outlet)); ?>">
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
                              <?php if (isset($data_bulan_lalu[$outlet])): ?>
                                <?php foreach ($data_bulan_lalu[$outlet] as $nama => $rekap): ?>
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
                              <?php else: ?>
                                <tr>
                                  <td colspan="7" class="text-center">Tidak ada data</td>
                                </tr>
                              <?php endif; ?>
                            </tbody>
                          </table>
                        </div>
                      </div>
                      <?php endforeach; ?>
                    </div>
                    
                    <!-- 2 Months Ago -->
                    <div class="tab-pane fade" id="prev2" role="tabpanel">
                      <div class="d-flex justify-content-end mb-3">
                        <a href="?download=1&month=<?php echo $prev_month2; ?>&year=<?php echo $prev_year2; ?>" 
                           class="btn btn-success">
                          <i class="ti-download"></i> Download CSV (Semua Outlet)
                        </a>
                      </div>
                      
                      <?php foreach ($outlets as $outlet): ?>
                      <div class="mb-5">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                          <h4 class="font-weight-bold">Outlet: <?php echo $outlet; ?></h4>
                          <a href="?download=1&month=<?php echo $prev_month2; ?>&year=<?php echo $prev_year2; ?>&outlet=<?php echo $outlet; ?>" 
                             class="btn btn-outline-success btn-sm">
                            <i class="ti-download"></i> Download CSV
                          </a>
                        </div>
                        <div class="table-responsive">
                          <table class="table table-striped table-bordered" id="table-prev2-<?php echo str_replace(' ', '-', strtolower($outlet)); ?>">
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
                              <?php if (isset($data_bulan_lalu2[$outlet])): ?>
                                <?php foreach ($data_bulan_lalu2[$outlet] as $nama => $rekap): ?>
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
                              <?php else: ?>
                                <tr>
                                  <td colspan="7" class="text-center">Tidak ada data</td>
                                </tr>
                              <?php endif; ?>
                            </tbody>
                          </table>
                        </div>
                      </div>
                      <?php endforeach; ?>
                    </div>
                    
                    <!-- 3 Months Ago -->
                    <div class="tab-pane fade" id="prev3" role="tabpanel">
                      <div class="d-flex justify-content-end mb-3">
                        <a href="?download=1&month=<?php echo $prev_month3; ?>&year=<?php echo $prev_year3; ?>" 
                           class="btn btn-success">
                          <i class="ti-download"></i> Download CSV (Semua Outlet)
                        </a>
                      </div>
                      
                      <?php foreach ($outlets as $outlet): ?>
                      <div class="mb-5">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                          <h4 class="font-weight-bold">Outlet: <?php echo $outlet; ?></h4>
                          <a href="?download=1&month=<?php echo $prev_month3; ?>&year=<?php echo $prev_year3; ?>&outlet=<?php echo $outlet; ?>" 
                             class="btn btn-outline-success btn-sm">
                            <i class="ti-download"></i> Download CSV
                          </a>
                        </div>
                        <div class="table-responsive">
                          <table class="table table-striped table-bordered" id="table-prev3-<?php echo str_replace(' ', '-', strtolower($outlet)); ?>">
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
                              <?php if (isset($data_bulan_lalu3[$outlet])): ?>
                                <?php foreach ($data_bulan_lalu3[$outlet] as $nama => $rekap): ?>
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
                              <?php else: ?>
                                <tr>
                                  <td colspan="7" class="text-center">Tidak ada data</td>
                                </tr>
                              <?php endif; ?>
                            </tbody>
                          </table>
                        </div>
                      </div>
                      <?php endforeach; ?>
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
      // Inisialisasi DataTable untuk semua tabel
      $('table.table').DataTable({
        "language": {
          "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
        }
      });
    });
  </script>
</body>
</html>
