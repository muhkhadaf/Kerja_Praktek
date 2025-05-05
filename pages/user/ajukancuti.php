<?php
// Start output buffering to prevent headers already sent error
ob_start();
// Mulai session untuk menyimpan pesan
if (session_status() === PHP_SESSION_NONE) session_start();
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
        // Simpan pesan sukses ke session dan redirect
        $_SESSION['success_message'] = '<div class="alert alert-success mt-3" role="alert">
                Pengajuan cuti berhasil disubmit! Status pengajuan Anda dapat dilihat di halaman Riwayat Cuti.
              </div>';
        header('Location: ajukancuti.php');
        exit();
    } else {
        // Simpan pesan error ke session dan redirect
        $_SESSION['error_message'] = '<div class="alert alert-danger mt-3" role="alert">
                Maaf, terjadi kesalahan: ' . mysqli_error($koneksi) . '
              </div>';
        header('Location: ajukancuti.php');
        exit();
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
    
      <!-- partial -->
      <!-- partial:../../partials/_sidebar.html -->
      <?php include_once 'navbar.php'; ?>
      <?php include_once 'sidebar.php'; ?>
      <div class="content-wrapper">
        <div class="row">
          <div class="col-md-12 grid-margin">
            <div class="row">
              <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                <h3 class="font-weight-bold">Form Pengajuan Cuti Tahunan</h3>
                <h6 class="font-weight-normal mb-0">Silakan isi form di bawah ini untuk mengajukan cuti tahunan Anda.</h6>
              </div>
              
              <?php
              // Tampilkan pesan dari session jika ada
              if (isset($_SESSION['success_message'])) {
                  echo $_SESSION['success_message'];
                  unset($_SESSION['success_message']);
              }
              if (isset($_SESSION['error_message'])) {
                  echo $_SESSION['error_message'];
                  unset($_SESSION['error_message']);
              }
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
