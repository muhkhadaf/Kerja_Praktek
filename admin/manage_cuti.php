<?php
// Start output buffering to prevent headers already sent error
ob_start();
// Include database configuration
require_once '../config.php';

// Check if user is logged in as admin
if (!isLoggedIn() || getUserRole() !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$outlet_filter = isset($_GET['outlet']) ? sanitize($_GET['outlet']) : '';
$date_start = isset($_GET['date_start']) ? sanitize($_GET['date_start']) : '';
$date_end = isset($_GET['date_end']) ? sanitize($_GET['date_end']) : '';

// Handle approval/rejection
if (isset($_POST['action']) && isset($_POST['cuti_id'])) {
    $cuti_id = sanitize($_POST['cuti_id']);
    $action = sanitize($_POST['action']);
    $keterangan = sanitize($_POST['keterangan'] ?? '');
    $admin_name = getUserName();
    
    if ($action === 'approve') {
        $status = 'disetujui';
    } elseif ($action === 'reject') {
        $status = 'ditolak';
    } else {
        $status = 'pending';
    }
    
    // Get cuti details first to update jadwal if approved
    $cuti_query = "SELECT id_karyawan, tanggal_mulai, tanggal_selesai FROM cuti_tahunan WHERE id = $cuti_id";
    $cuti_result = mysqli_query($koneksi, $cuti_query);
    $cuti_data = mysqli_fetch_assoc($cuti_result);
    
    // Update cuti_tahunan table
    $sql = "UPDATE cuti_tahunan SET 
            status = '$status', 
            keterangan_admin = '$keterangan', 
            approved_by = '$admin_name', 
            approved_at = NOW() 
            WHERE id = $cuti_id";
    
    $update_result = mysqli_query($koneksi, $sql);
    
    // If approved, update jadwal table with cuti status
    if ($update_result && $status === 'disetujui' && $cuti_data) {
        $id_karyawan = $cuti_data['id_karyawan'];
        $start_date = $cuti_data['tanggal_mulai'];
        $end_date = $cuti_data['tanggal_selesai'];
        
        // Update all days in range
        $current_date = $start_date;
        while (strtotime($current_date) <= strtotime($end_date)) {
            // Check if jadwal exists for this date
            $check_jadwal = "SELECT id FROM jadwal WHERE id_karyawan = '$id_karyawan' AND tanggal = '$current_date'";
            $check_result = mysqli_query($koneksi, $check_jadwal);
            
            if (mysqli_num_rows($check_result) > 0) {
                // Update existing jadwal
                $jadwal_id = mysqli_fetch_assoc($check_result)['id'];
                $update_jadwal = "UPDATE jadwal SET status = 'cuti' WHERE id = $jadwal_id";
                mysqli_query($koneksi, $update_jadwal);
            } else {
                // Insert new jadwal entry with cuti status
                $insert_jadwal = "INSERT INTO jadwal (id_karyawan, tanggal, status) VALUES ('$id_karyawan', '$current_date', 'cuti')";
                mysqli_query($koneksi, $insert_jadwal);
            }
            
            // Increment date by 1 day
            $current_date = date('Y-m-d', strtotime($current_date . ' +1 day'));
        }
    }
    
    // Redirect to prevent form resubmission
    header("Location: manage_cuti.php");
    exit;
}

// Get available outlets for filter
$outlets = [];
$outlet_query = "SELECT DISTINCT outlet FROM cuti_tahunan ORDER BY outlet";
$outlet_result = mysqli_query($koneksi, $outlet_query);
if ($outlet_result) {
    while ($row = mysqli_fetch_assoc($outlet_result)) {
        if (!empty($row['outlet'])) {
            $outlets[] = $row['outlet'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Manajemen Cuti - Admin</title>
  <!-- plugins:css -->
  <link rel="stylesheet" href="../vendors/feather/feather.css">
  <link rel="stylesheet" href="../vendors/ti-icons/css/themify-icons.css">
  <link rel="stylesheet" href="../vendors/css/vendor.bundle.base.css">
  <!-- endinject -->
  <!-- Plugin css for this page -->
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
    <!-- partial -->
    <div class="container-fluid page-body-wrapper">
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
            <a class="nav-link" href="manage_users.php">
              <i class="icon-head menu-icon"></i>
              <span class="menu-title">Manajemen Karyawan</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="manage_jadwal.php">
              <i class="icon-paper menu-icon"></i>
              <span class="menu-title">Manajemen Jadwal</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="manage_cuti.php">
              <i class="icon-paper menu-icon"></i>
              <span class="menu-title">Manajemen Cuti</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="manage_izin.php">
              <i class="icon-paper menu-icon"></i>
              <span class="menu-title">Manajemen Izin</span>
            </a>
          </li>
        </ul>
      </nav>
      <!-- partial -->
      <div class="main-panel">
        <div class="content-wrapper">
          <div class="row">
            <div class="col-md-12 grid-margin">
              <div class="row">
                <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                  <h3 class="font-weight-bold">Manajemen Pengajuan Cuti</h3>
                  <h6 class="font-weight-normal mb-0">Kelola pengajuan cuti karyawan</h6>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Filter section -->
          <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
              <div class="card">
                <div class="card-body">
                  <h4 class="card-title">Filter</h4>
                  <form action="" method="GET">
                    <div class="row">
                      <div class="col-md-3">
                        <div class="form-group">
                          <label for="status">Status</label>
                          <select class="form-control" id="status" name="status">
                            <option value="">Semua Status</option>
                            <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="disetujui" <?php echo $status_filter == 'disetujui' ? 'selected' : ''; ?>>Disetujui</option>
                            <option value="ditolak" <?php echo $status_filter == 'ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                          </select>
                        </div>
                      </div>
                      <div class="col-md-3">
                        <div class="form-group">
                          <label for="outlet">Outlet</label>
                          <select class="form-control" id="outlet" name="outlet">
                            <option value="">Semua Outlet</option>
                            <?php foreach ($outlets as $outlet): ?>
                            <option value="<?php echo $outlet; ?>" <?php echo $outlet_filter == $outlet ? 'selected' : ''; ?>><?php echo $outlet; ?></option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                      </div>
                      <div class="col-md-3">
                        <div class="form-group">
                          <label for="date_start">Tanggal Mulai</label>
                          <input type="date" class="form-control" id="date_start" name="date_start" value="<?php echo $date_start; ?>">
                        </div>
                      </div>
                      <div class="col-md-3">
                        <div class="form-group">
                          <label for="date_end">Tanggal Selesai</label>
                          <input type="date" class="form-control" id="date_end" name="date_end" value="<?php echo $date_end; ?>">
                        </div>
                      </div>
                    </div>
                    <div class="mt-3">
                      <button type="submit" class="btn btn-primary">Filter</button>
                      <a href="manage_cuti.php" class="btn btn-light">Reset</a>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-lg-12 grid-margin stretch-card">
              <div class="card">
                <div class="card-body">
                  <h4 class="card-title">Daftar Pengajuan Cuti</h4>
                  <div class="table-responsive">
                    <table class="table table-hover">
                      <thead>
                        <tr>
                          <th>ID</th>
                          <th>ID Karyawan</th>
                          <th>Nama</th>
                          <th>Outlet</th>
                          <th>Tanggal Mulai</th>
                          <th>Tanggal Selesai</th>
                          <th>Durasi</th>
                          <th>Alasan</th>
                          <th>Status</th>
                          <th>Tanggal Pengajuan</th>
                          <th>Aksi</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        // Build the query with filters
                        $query = "SELECT * FROM cuti_tahunan WHERE 1=1";
                        
                        if (!empty($status_filter)) {
                            $query .= " AND status = '$status_filter'";
                        }
                        
                        if (!empty($outlet_filter)) {
                            $query .= " AND outlet = '$outlet_filter'";
                        }
                        
                        if (!empty($date_start)) {
                            $query .= " AND tanggal_mulai >= '$date_start'";
                        }
                        
                        if (!empty($date_end)) {
                            $query .= " AND tanggal_selesai <= '$date_end'";
                        }
                        
                        $query .= " ORDER BY created_at DESC";
                        
                        $result = mysqli_query($koneksi, $query);
                        
                        if ($result && mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                // Format dates
                                $tanggal_mulai = date('d/m/Y', strtotime($row['tanggal_mulai']));
                                $tanggal_selesai = date('d/m/Y', strtotime($row['tanggal_selesai']));
                                $tanggal_pengajuan = date('d/m/Y H:i', strtotime($row['created_at']));
                                
                                // Set status class
                                $status_class = '';
                                switch ($row['status']) {
                                    case 'pending':
                                        $status_class = 'badge-warning';
                                        break;
                                    case 'disetujui':
                                        $status_class = 'badge-success';
                                        break;
                                    case 'ditolak':
                                        $status_class = 'badge-danger';
                                        break;
                                }
                                
                                echo '<tr>
                                        <td>' . $row['id'] . '</td>
                                        <td>' . $row['id_karyawan'] . '</td>
                                        <td>' . $row['nama_karyawan'] . '</td>
                                        <td>' . $row['outlet'] . '</td>
                                        <td>' . $tanggal_mulai . '</td>
                                        <td>' . $tanggal_selesai . '</td>
                                        <td>' . $row['durasi'] . ' hari</td>
                                        <td>' . $row['alasan'] . '</td>
                                        <td><span class="badge ' . $status_class . '">' . ucfirst($row['status']) . '</span></td>
                                        <td>' . $tanggal_pengajuan . '</td>
                                        <td>';
                                
                                // Show action buttons only for pending requests
                                if ($row['status'] === 'pending') {
                                    echo '<button type="button" class="btn btn-sm btn-success" onclick="approveModal(' . $row['id'] . ')">Setujui</button>
                                          <button type="button" class="btn btn-sm btn-danger" onclick="rejectModal(' . $row['id'] . ')">Tolak</button>';
                                } else {
                                    // Display info if already processed
                                    if ($row['approved_by']) {
                                        echo '<small>Diproses oleh: ' . $row['approved_by'] . '</small><br>';
                                        if ($row['keterangan_admin']) {
                                            echo '<small>Catatan: ' . $row['keterangan_admin'] . '</small>';
                                        }
                                    }
                                }
                                
                                echo '</td>
                                    </tr>';
                            }
                        } else {
                            echo '<tr><td colspan="11" class="text-center">Tidak ada data pengajuan cuti</td></tr>';
                        }
                        ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Statistics -->
          <div class="row mt-4">
            <div class="col-md-4 grid-margin stretch-card">
              <div class="card">
                <div class="card-body">
                  <h4 class="card-title">Statistik Pengajuan</h4>
                  <canvas id="statusChart" height="200"></canvas>
                </div>
              </div>
            </div>
            <div class="col-md-8 grid-margin stretch-card">
              <div class="card">
                <div class="card-body">
                  <h4 class="card-title">Pengajuan per Outlet</h4>
                  <canvas id="outletChart" height="200"></canvas>
                </div>
              </div>
            </div>
          </div>
          
        </div>
        <!-- content-wrapper ends -->
        <!-- partial:partials/_footer.html -->
        <footer class="footer">
          <div class="d-sm-flex justify-content-center justify-content-sm-between">
            <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">Copyright © 2025. All rights reserved.</span>
          </div>
        </footer>
        <!-- partial -->
      </div>
      <!-- main-panel ends -->
    </div>
    <!-- page-body-wrapper ends -->
  </div>
  
  <!-- Approval Modal -->
  <div class="modal fade" id="approveModal" tabindex="-1" role="dialog" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="approveModalLabel">Setujui Pengajuan Cuti</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form action="" method="POST">
          <div class="modal-body">
            <input type="hidden" name="cuti_id" id="approve_cuti_id">
            <input type="hidden" name="action" value="approve">
            <div class="form-group">
              <label for="keterangan">Keterangan (opsional)</label>
              <textarea class="form-control" name="keterangan" rows="3"></textarea>
            </div>
            <div class="alert alert-info">
              <small>Persetujuan cuti akan secara otomatis memperbarui jadwal karyawan untuk tanggal bersangkutan.</small>
            </div>
            <p>Apakah Anda yakin ingin menyetujui pengajuan cuti ini?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-success">Setujui</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  
  <!-- Rejection Modal -->
  <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="rejectModalLabel">Tolak Pengajuan Cuti</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form action="" method="POST">
          <div class="modal-body">
            <input type="hidden" name="cuti_id" id="reject_cuti_id">
            <input type="hidden" name="action" value="reject">
            <div class="form-group">
              <label for="keterangan">Alasan Penolakan <span class="text-danger">*</span></label>
              <textarea class="form-control" name="keterangan" rows="3" required></textarea>
            </div>
            <p>Apakah Anda yakin ingin menolak pengajuan cuti ini?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-danger">Tolak</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  
  <!-- plugins:js -->
  <script src="../vendors/js/vendor.bundle.base.js"></script>
  <!-- endinject -->
  <!-- Plugin js for this page -->
  <script src="../vendors/chart.js/Chart.min.js"></script>
  <!-- End plugin js for this page -->
  <!-- inject:js -->
  <script src="../js/off-canvas.js"></script>
  <script src="../js/hoverable-collapse.js"></script>
  <script src="../js/template.js"></script>
  <script src="../js/settings.js"></script>
  <script src="../js/todolist.js"></script>
  <!-- endinject -->
  
  <script>
    function approveModal(id) {
      document.getElementById('approve_cuti_id').value = id;
      $('#approveModal').modal('show');
    }
    
    function rejectModal(id) {
      document.getElementById('reject_cuti_id').value = id;
      $('#rejectModal').modal('show');
    }
    
    // Charts
    document.addEventListener('DOMContentLoaded', function() {
      // Status chart
      <?php
      // Get status statistics
      $status_query = "SELECT status, COUNT(*) as count FROM cuti_tahunan GROUP BY status";
      $status_result = mysqli_query($koneksi, $status_query);
      
      $pending = 0;
      $approved = 0;
      $rejected = 0;
      
      if ($status_result) {
          while ($row = mysqli_fetch_assoc($status_result)) {
              if ($row['status'] === 'pending') $pending = $row['count'];
              if ($row['status'] === 'disetujui') $approved = $row['count'];
              if ($row['status'] === 'ditolak') $rejected = $row['count'];
          }
      }
      ?>
      
      var statusCtx = document.getElementById('statusChart').getContext('2d');
      var statusChart = new Chart(statusCtx, {
          type: 'pie',
          data: {
              labels: ['Pending', 'Disetujui', 'Ditolak'],
              datasets: [{
                  data: [<?php echo $pending; ?>, <?php echo $approved; ?>, <?php echo $rejected; ?>],
                  backgroundColor: ['#ffc107', '#28a745', '#dc3545']
              }]
          },
          options: {
              responsive: true,
              legend: {
                  position: 'bottom',
              }
          }
      });
      
      // Outlet chart
      <?php
      // Get outlet statistics
      $outlet_stats_query = "SELECT outlet, COUNT(*) as count FROM cuti_tahunan GROUP BY outlet ORDER BY count DESC";
      $outlet_stats_result = mysqli_query($koneksi, $outlet_stats_query);
      
      $outlet_labels = [];
      $outlet_data = [];
      
      if ($outlet_stats_result) {
          while ($row = mysqli_fetch_assoc($outlet_stats_result)) {
              if (!empty($row['outlet'])) {
                  $outlet_labels[] = $row['outlet'];
                  $outlet_data[] = $row['count'];
              }
          }
      }
      ?>
      
      var outletCtx = document.getElementById('outletChart').getContext('2d');
      var outletChart = new Chart(outletCtx, {
          type: 'bar',
          data: {
              labels: <?php echo json_encode($outlet_labels); ?>,
              datasets: [{
                  label: 'Jumlah Pengajuan',
                  data: <?php echo json_encode($outlet_data); ?>,
                  backgroundColor: 'rgba(54, 162, 235, 0.2)',
                  borderColor: 'rgba(54, 162, 235, 1)',
                  borderWidth: 1
              }]
          },
          options: {
              scales: {
                  yAxes: [{
                      ticks: {
                          beginAtZero: true,
                          stepSize: 1
                      }
                  }]
              }
          }
      });
    });
  </script>
</body>

</html> 