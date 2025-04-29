<?php
// Start output buffering to prevent headers already sent error
ob_start();
// Include database configuration
require_once '../../config.php';

// Check if user is logged in
requireLogin();

// Get user data from session
$id_karyawan = getKaryawanId();
$outlet = getOutlet();
$nama_karyawan = getUserName();

// If needed, fetch additional user info from database
if (empty($outlet) || $outlet == '-') {
    // Query to get user outlet if not available in session
    $user_query = "SELECT outlet FROM karyawan WHERE id_karyawan = '$id_karyawan'";
    $user_result = mysqli_query($koneksi, $user_query);
    if ($user_result && mysqli_num_rows($user_result) > 0) {
        $user_data = mysqli_fetch_assoc($user_result);
        $outlet = $user_data['outlet'];
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $tanggal_mulai = sanitize($_POST['startDate']);
    $tanggal_selesai = sanitize($_POST['endDate']);
    $alasan = sanitize($_POST['reason']);
    
    // Calculate duration
    $start_date = new DateTime($tanggal_mulai);
    $end_date = new DateTime($tanggal_selesai);
    $interval = $start_date->diff($end_date);
    $durasi = $interval->days + 1; // Including both start and end dates
    
    // Insert data into cuti_tahunan table
    $sql = "INSERT INTO cuti_tahunan (id_karyawan, outlet, nama_karyawan, tanggal_mulai, tanggal_selesai, durasi, alasan, status) 
            VALUES ('$id_karyawan', '$outlet', '$nama_karyawan', '$tanggal_mulai', '$tanggal_selesai', $durasi, '$alasan', 'pending')";
    
    if (mysqli_query($koneksi, $sql)) {
        // Success message
        $success_message = '<div class="alert alert-success mt-3" role="alert">
                Pengajuan cuti berhasil disubmit! Status pengajuan Anda dapat dilihat di halaman Riwayat Cuti.
              </div>';
    } else {
        // Error message
        $error_message = '<div class="alert alert-danger mt-3" role="alert">
                Maaf, terjadi kesalahan: ' . mysqli_error($koneksi) . '
              </div>';
    }
}

// Get outlets for dropdown (if needed)
$outlets = [];
$outlet_query = "SELECT DISTINCT outlet FROM karyawan ORDER BY outlet";
$outlet_result = mysqli_query($koneksi, $outlet_query);
if ($outlet_result) {
    while ($row = mysqli_fetch_assoc($outlet_result)) {
        $outlets[] = $row['outlet'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Form Pengajuan Cuti - User</title>
  <!-- plugins:css -->
  <link rel="stylesheet" href="../../vendors/feather/feather.css">
  <link rel="stylesheet" href="../../vendors/ti-icons/css/themify-icons.css">
  <link rel="stylesheet" href="../../vendors/css/vendor.bundle.base.css">
  <!-- endinject -->
  <!-- inject:css -->
  <link rel="stylesheet" href="../../css/vertical-layout-light/style.css">
  <!-- endinject -->
  <link rel="shortcut icon" href="../../images/favicon.png" />
</head>

<body>
  <div class="container-scroller">
    <!-- partial:../../partials/_navbar.html -->
    <nav class="navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
      <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
        <a class="navbar-brand brand-logo mr-5" href="../../index.html"><img src="../../images/logo.svg" class="mr-2" alt="logo"/></a>
        <a class="navbar-brand brand-logo-mini" href="../../index.html"><img src="../../images/logo-mini.svg" alt="logo"/></a>
      </div>
      <div class="navbar-menu-wrapper d-flex align-items-center justify-content-end">
        <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
          <span class="icon-menu"></span>
        </button>
        <ul class="navbar-nav mr-lg-2">
          <li class="nav-item nav-search d-none d-lg-block">
            <div class="input-group">
              <div class="input-group-prepend hover-cursor" id="navbar-search-icon">
                <span class="input-group-text" id="search">
                  <i class="icon-search"></i>
                </span>
              </div>
              <input type="text" class="form-control" id="navbar-search-input" placeholder="Search now" aria-label="search" aria-describedby="search">
            </div>
          </li>
        </ul>
        <ul class="navbar-nav navbar-nav-right">
          <li class="nav-item dropdown">
            <a class="nav-link count-indicator dropdown-toggle" id="notificationDropdown" href="#" data-toggle="dropdown">
              <i class="icon-bell mx-0"></i>
              <span class="count"></span>
            </a>
            <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list" aria-labelledby="notificationDropdown">
              <p class="mb-0 font-weight-normal float-left dropdown-header">Notifications</p>
              <a class="dropdown-item preview-item">
                <div class="preview-thumbnail">
                  <div class="preview-icon bg-success">
                    <i class="ti-info-alt mx-0"></i>
                  </div>
                </div>
                <div class="preview-item-content">
                  <h6 class="preview-subject font-weight-normal">Application Error</h6>
                  <p class="font-weight-light small-text mb-0 text-muted">
                    Just now
                  </p>
                </div>
              </a>
              <a class="dropdown-item preview-item">
                <div class="preview-thumbnail">
                  <div class="preview-icon bg-warning">
                    <i class="ti-settings mx-0"></i>
                  </div>
                </div>
                <div class="preview-item-content">
                  <h6 class="preview-subject font-weight-normal">Settings</h6>
                  <p class="font-weight-light small-text mb-0 text-muted">
                    Private message
                  </p>
                </div>
              </a>
              <a class="dropdown-item preview-item">
                <div class="preview-thumbnail">
                  <div class="preview-icon bg-info">
                    <i class="ti-user mx-0"></i>
                  </div>
                </div>
                <div class="preview-item-content">
                  <h6 class="preview-subject font-weight-normal">New user registration</h6>
                  <p class="font-weight-light small-text mb-0 text-muted">
                    2 days ago
                  </p>
                </div>
              </a>
            </div>
          </li>
          <li class="nav-item nav-profile dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" id="profileDropdown">
              <img src="../../images/faces/face28.jpg" alt="profile"/>
            </a>
            <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="profileDropdown">
              <a class="dropdown-item">
                <i class="ti-settings text-primary"></i>
                Settings
              </a>
              <a class="dropdown-item" href="../../logout.php">
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
      <!-- partial:../../partials/_settings-panel.html -->
      <div class="theme-setting-wrapper">
        <div id="settings-trigger"><i class="ti-settings"></i></div>
        <div id="theme-settings" class="settings-panel">
          <i class="settings-close ti-close"></i>
          <p class="settings-heading">SIDEBAR SKINS</p>
          <div class="sidebar-bg-options selected" id="sidebar-light-theme"><div class="img-ss rounded-circle bg-light border mr-3"></div>Light</div>
          <div class="sidebar-bg-options" id="sidebar-dark-theme"><div class="img-ss rounded-circle bg-dark border mr-3"></div>Dark</div>
          <p class="settings-heading mt-2">HEADER SKINS</p>
          <div class="color-tiles mx-0 px-4">
            <div class="tiles success"></div>
            <div class="tiles warning"></div>
            <div class="tiles danger"></div>
            <div class="tiles info"></div>
            <div class="tiles dark"></div>
            <div class="tiles default"></div>
          </div>
        </div>
      </div>
      <div id="right-sidebar" class="settings-panel">
        <i class="settings-close ti-close"></i>
        <ul class="nav nav-tabs border-top" id="setting-panel" role="tablist">
          <li class="nav-item">
            <a class="nav-link active" id="todo-tab" data-toggle="tab" href="#todo-section" role="tab" aria-controls="todo-section" aria-expanded="true">TO DO LIST</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" id="chats-tab" data-toggle="tab" href="#chats-section" role="tab" aria-controls="chats-section">CHATS</a>
          </li>
        </ul>
        <div class="tab-content" id="setting-content">
          <div class="tab-pane fade show active scroll-wrapper" id="todo-section" role="tabpanel" aria-labelledby="todo-section">
            <div class="add-items d-flex px-3 mb-0">
              <form class="form w-100">
                <div class="form-group d-flex">
                  <input type="text" class="form-control todo-list-input" placeholder="Add To-do">
                  <button type="submit" class="add btn btn-primary todo-list-add-btn" id="add-task">Add</button>
                </div>
              </form>
            </div>
            <div class="list-wrapper px-3">
              <ul class="d-flex flex-column-reverse todo-list">
                <li>
                  <div class="form-check">
                    <label class="form-check-label">
                      <input class="checkbox" type="checkbox">
                      Team review meeting at 3.00 PM
                    </label>
                  </div>
                  <i class="remove ti-close"></i>
                </li>
                <li>
                  <div class="form-check">
                    <label class="form-check-label">
                      <input class="checkbox" type="checkbox">
                      Prepare for presentation
                    </label>
                  </div>
                  <i class="remove ti-close"></i>
                </li>
                <li>
                  <div class="form-check">
                    <label class="form-check-label">
                      <input class="checkbox" type="checkbox">
                      Resolve all the low priority tickets due today
                    </label>
                  </div>
                  <i class="remove ti-close"></i>
                </li>
                <li class="completed">
                  <div class="form-check">
                    <label class="form-check-label">
                      <input class="checkbox" type="checkbox" checked>
                      Schedule meeting for next week
                    </label>
                  </div>
                  <i class="remove ti-close"></i>
                </li>
                <li class="completed">
                  <div class="form-check">
                    <label class="form-check-label">
                      <input class="checkbox" type="checkbox" checked>
                      Project review
                    </label>
                  </div>
                  <i class="remove ti-close"></i>
                </li>
              </ul>
            </div>
            <h4 class="px-3 text-muted mt-5 font-weight-light mb-0">Events</h4>
            <div class="events pt-4 px-3">
              <div class="wrapper d-flex mb-2">
                <i class="ti-control-record text-primary mr-2"></i>
                <span>Feb 11 2018</span>
              </div>
              <p class="mb-0 font-weight-thin text-gray">Creating component page build a js</p>
              <p class="text-gray mb-0">The total number of sessions</p>
            </div>
            <div class="events pt-4 px-3">
              <div class="wrapper d-flex mb-2">
                <i class="ti-control-record text-primary mr-2"></i>
                <span>Feb 7 2018</span>
              </div>
              <p class="mb-0 font-weight-thin text-gray">Meeting with Alisa</p>
              <p class="text-gray mb-0 ">Call Sarah Graves</p>
            </div>
          </div>
          <!-- To do section tab ends -->
          <div class="tab-pane fade" id="chats-section" role="tabpanel" aria-labelledby="chats-section">
            <div class="d-flex align-items-center justify-content-between border-bottom">
              <p class="settings-heading border-top-0 mb-3 pl-3 pt-0 border-bottom-0 pb-0">Friends</p>
              <small class="settings-heading border-top-0 mb-3 pt-0 border-bottom-0 pb-0 pr-3 font-weight-normal">See All</small>
            </div>
            <ul class="chat-list">
              <li class="list active">
                <div class="profile"><img src="../../images/faces/face1.jpg" alt="image"><span class="online"></span></div>
                <div class="info">
                  <p>Thomas Douglas</p>
                  <p>Available</p>
                </div>
                <small class="text-muted my-auto">19 min</small>
              </li>
              <li class="list">
                <div class="profile"><img src="../../images/faces/face2.jpg" alt="image"><span class="offline"></span></div>
                <div class="info">
                  <div class="wrapper d-flex">
                    <p>Catherine</p>
                  </div>
                  <p>Away</p>
                </div>
                <div class="badge badge-success badge-pill my-auto mx-2">4</div>
                <small class="text-muted my-auto">23 min</small>
              </li>
              <li class="list">
                <div class="profile"><img src="../../images/faces/face3.jpg" alt="image"><span class="online"></span></div>
                <div class="info">
                  <p>Daniel Russell</p>
                  <p>Available</p>
                </div>
                <small class="text-muted my-auto">14 min</small>
              </li>
              <li class="list">
                <div class="profile"><img src="../../images/faces/face4.jpg" alt="image"><span class="offline"></span></div>
                <div class="info">
                  <p>James Richardson</p>
                  <p>Away</p>
                </div>
                <small class="text-muted my-auto">2 min</small>
              </li>
              <li class="list">
                <div class="profile"><img src="../../images/faces/face5.jpg" alt="image"><span class="online"></span></div>
                <div class="info">
                  <p>Madeline Kennedy</p>
                  <p>Available</p>
                </div>
                <small class="text-muted my-auto">5 min</small>
              </li>
              <li class="list">
                <div class="profile"><img src="../../images/faces/face6.jpg" alt="image"><span class="online"></span></div>
                <div class="info">
                  <p>Sarah Graves</p>
                  <p>Available</p>
                </div>
                <small class="text-muted my-auto">47 min</small>
              </li>
            </ul>
          </div>
          <!-- chat tab ends -->
        </div>
      </div>
      <!-- partial -->
      <!-- partial:../../partials/_sidebar.html -->
      <nav class="sidebar sidebar-offcanvas" id="sidebar">
        <ul class="nav">
          <li class="nav-item">
            <a class="nav-link" href="../../index.html">
              <i class="icon-grid menu-icon"></i>
              <span class="menu-title">Dashboard</span>
            </a>
          </li>
              <li class="nav-item">
                <a class="nav-link" href="ajukancuti.php">
                  <i class="icon-paper menu-icon"></i>
                  <span class="menu-title">Ajukan Cuti</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-toggle="collapse" href="#auth" aria-expanded="false" aria-controls="auth">
                  <i class="icon-head menu-icon"></i>
                  <span class="menu-title">Status Saya</span>
                  <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="auth">
                  <ul class="nav flex-column sub-menu">
                    <li class="nav-item"> <a class="nav-link" href="akun.html"> Akun Saya </a></li>
                    <li class="nav-item"> <a class="nav-link" href="riwayat_shift.html"> Riwayat Shift </a></li>
                    <li class="nav-item"> <a class="nav-link" href="riwayat_cuti.php"> Riwayat Cuti </a></li>
                    <li class="nav-item"> <a class="nav-link" href="riwayat_izin.html"> Riwayat Izin </a></li>
                  </ul>
                </div>
              </li>
          <li class="nav-item">
            <a class="nav-link" data-toggle="collapse" href="#error" aria-expanded="false" aria-controls="error">
              <i class="icon-ban menu-icon"></i>
              <span class="menu-title">Error pages</span>
              <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="error">
              <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="pages/samples/error-404.html"> 404 </a></li>
                <li class="nav-item"> <a class="nav-link" href="pages/samples/error-500.html"> 500 </a></li>
              </ul>
            </div>
          </li>
        </ul>
      </nav>
      <div class="content-wrapper">
        <div class="row">
          <div class="col-md-12 grid-margin">
            <div class="row">
              <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                <h3 class="font-weight-bold">Form Pengajuan Cuti Tahunan</h3>
                <h6 class="font-weight-normal mb-0">Silakan isi form di bawah ini untuk mengajukan cuti tahunan Anda.</h6>
              </div>
              
              <?php
              // Display messages if they exist
              if (isset($success_message)) echo $success_message;
              if (isset($error_message)) echo $error_message;
              ?>
                
              <!-- Form Pengajuan Cuti -->
              <div class="col-12">
                <div class="card">
                  <div class="card-body">
                    <form method="POST" action="">
                      <div class="form-group">
                        <label for="employeeId">ID Karyawan</label>
                        <input type="text" class="form-control" id="employeeId" name="employeeId" value="<?php echo $id_karyawan; ?>" readonly>
                      </div>
                      <div class="form-group">
                        <label for="employeeOutlet">Outlet</label>
                        <input type="text" class="form-control" id="employeeOutlet" name="employeeOutlet" value="<?php echo $outlet; ?>" readonly>
                      </div>
                      <div class="form-group">
                        <label for="employeeName">Nama Karyawan</label>
                        <input type="text" class="form-control" id="employeeName" name="employeeName" value="<?php echo $nama_karyawan; ?>" readonly>
                      </div>
                      <div class="form-group">
                        <label for="startDate">Tanggal Mulai Cuti</label>
                        <input type="date" class="form-control" id="startDate" name="startDate" required>
                      </div>
                      <div class="form-group">
                        <label for="endDate">Tanggal Akhir Cuti</label>
                        <input type="date" class="form-control" id="endDate" name="endDate" required>
                      </div>
                      <div class="form-group">
                        <label for="reason">Alasan Cuti</label>
                        <textarea class="form-control" id="reason" name="reason" rows="3" placeholder="Masukkan Alasan Cuti" required></textarea>
                      </div>
                      <button type="submit" class="btn btn-primary">Ajukan Cuti</button>
                    </form>
                  </div>
                </div>
              </div>
              
              <!-- Tabel Riwayat Pengajuan Cuti -->
              <div class="col-12 mt-4">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title">Riwayat Pengajuan Cuti</h4>
                    <div class="table-responsive">
                      <table class="table table-hover">
                        <thead>
                          <tr>
                            <th>ID</th>
                            <th>Tanggal Pengajuan</th>
                            <th>Periode Cuti</th>
                            <th>Durasi</th>
                            <th>Alasan</th>
                            <th>Status</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php
                          // Get user's leave history
                          $query = "SELECT * FROM cuti_tahunan WHERE id_karyawan = '$id_karyawan' ORDER BY created_at DESC";
                          $result = mysqli_query($koneksi, $query);
                          
                          if ($result && mysqli_num_rows($result) > 0) {
                              while ($row = mysqli_fetch_assoc($result)) {
                                  // Format dates
                                  $tanggal_pengajuan = date('d/m/Y', strtotime($row['created_at']));
                                  $periode = date('d/m/Y', strtotime($row['tanggal_mulai'])) . ' - ' . date('d/m/Y', strtotime($row['tanggal_selesai']));
                                  
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
                                          <td>' . $tanggal_pengajuan . '</td>
                                          <td>' . $periode . '</td>
                                          <td>' . $row['durasi'] . ' hari</td>
                                          <td>' . $row['alasan'] . '</td>
                                          <td><span class="badge ' . $status_class . '">' . ucfirst($row['status']) . '</span></td>
                                        </tr>';
                              }
                          } else {
                              echo '<tr><td colspan="6" class="text-center">Tidak ada riwayat pengajuan cuti</td></tr>';
                          }
                          ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
              
            </div>
          </div>
        </div>
        <!-- content-wrapper ends -->
        <!-- partial:../../partials/_footer.html -->
        <!-- partial -->
      </div>
      <!-- main-panel ends -->
    </div>
    <!-- page-body-wrapper ends -->
  </div>
  <!-- container-scroller -->
  <!-- plugins:js -->
  <script src="../../vendors/js/vendor.bundle.base.js"></script>
  <!-- endinject -->
  <!-- Plugin js for this page -->
  <!-- End plugin js for this page -->
  <!-- inject:js -->
  <script src="../../js/off-canvas.js"></script>
  <script src="../../js/hoverable-collapse.js"></script>
  <script src="../../js/template.js"></script>
  <script src="../../js/settings.js"></script>
  <script src="../../js/todolist.js"></script>
  <!-- endinject -->
  
  <!-- Date validation script -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const startDateInput = document.getElementById('startDate');
      const endDateInput = document.getElementById('endDate');
      const form = document.querySelector('form');
      
      // Set minimum date as today
      const today = new Date();
      const yyyy = today.getFullYear();
      const mm = String(today.getMonth() + 1).padStart(2, '0');
      const dd = String(today.getDate()).padStart(2, '0');
      const todayFormatted = yyyy + '-' + mm + '-' + dd;
      
      startDateInput.setAttribute('min', todayFormatted);
      
      // Update end date min attribute when start date changes
      startDateInput.addEventListener('change', function() {
        endDateInput.setAttribute('min', startDateInput.value);
        if (endDateInput.value && new Date(endDateInput.value) < new Date(startDateInput.value)) {
          endDateInput.value = startDateInput.value;
        }
      });
      
      // Form validation
      form.addEventListener('submit', function(event) {
        if (new Date(endDateInput.value) < new Date(startDateInput.value)) {
          alert('Tanggal akhir cuti tidak boleh sebelum tanggal mulai cuti');
          event.preventDefault();
        }
      });
    });
  </script>
</body>

</html>
