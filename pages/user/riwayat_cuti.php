<?php
// Start output buffering to prevent headers already sent error
ob_start();
// Include database configuration
require_once '../../config.php';

// Check if user is logged in
requireLogin();

// Get employee ID from session
$id_karyawan = getKaryawanId();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Riwayat Cuti - User</title>
  <!-- plugins:css -->
  <link rel="stylesheet" href="../../vendors/feather/feather.css">
  <link rel="stylesheet" href="../../vendors/ti-icons/css/themify-icons.css">
  <link rel="stylesheet" href="../../vendors/css/vendor.bundle.base.css">
  <!-- endinject -->
  <!-- Plugin css for this page -->
  <!-- End plugin css for this page -->
  <!-- inject:css -->
  <link rel="stylesheet" href="../../css/vertical-layout-light/style.css">
  <!-- endinject -->
  <link rel="shortcut icon" href="../../images/favicon.png" />
</head>

<body>
  <div class="container-scroller">
    <!-- partial:../../partials/_navbar.html -->
    <?php include_once 'navbar.php'; ?>
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
      <!-- partial -->
      <!-- partial:../../partials/_sidebar.html -->
      <?php include_once 'sidebar.php'; ?>
      <!-- partial -->
      
          
          <!-- Content -->
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
                          <th>Tanggal Pengajuan</th>
                          <th>Periode Cuti</th>
                          <th>Durasi</th>
                          <th>Alasan</th>
                          <th>Status</th>
                          <th>Keterangan Admin</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        // Query to get leave history for the employee
                        $query = "SELECT * FROM cuti_tahunan WHERE id_karyawan = '$id_karyawan' ORDER BY created_at DESC";
                        $result = mysqli_query($koneksi, $query);
                        
                        if (mysqli_num_rows($result) > 0) {
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
                                        <td>' . ($row['keterangan_admin'] ?: '-') . '</td>
                                      </tr>';
                            }
                        } else {
                            echo '<tr><td colspan="7" class="text-center">Tidak ada riwayat pengajuan cuti</td></tr>';
                        }
                        ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Info Card -->
          <div class="row mt-4">
            <div class="col-md-12 grid-margin">
              <div class="card">
                <div class="card-body">
                  <h4 class="card-title">Informasi Cuti</h4>
                  <p>Berikut adalah ketentuan cuti di perusahaan:</p>
                  <ul>
                    <li>Setiap karyawan berhak mendapatkan 12 hari cuti tahunan.</li>
                    <li>Pengajuan cuti harus dilakukan minimal 3 hari sebelum tanggal cuti.</li>
                    <li>Approval cuti akan diproses dalam waktu 1-2 hari kerja.</li>
                    <li>Untuk cuti mendadak karena alasan darurat, harap hubungi supervisor atau HRD.</li>
                  </ul>
                  <a href="ajukancuti.php" class="btn btn-primary mt-3">Ajukan Cuti Baru</a>
                </div>
              </div>
            </div>
          </div>
          
        </div>
        <!-- content-wrapper ends -->
        <!-- partial:../../partials/_footer.html -->
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
</body>

</html>
