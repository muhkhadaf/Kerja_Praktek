<?php
// Start output buffering to prevent headers already sent error
ob_start();
// Include database configuration
require_once '../../config.php';

// Check if user is logged in
requireLogin();

// Get employee ID from session
$id_karyawan = getKaryawanId();

// Ambil data karyawan dari database
$user = null;
$query = "SELECT * FROM users WHERE id_karyawan = '" . sanitize($id_karyawan) . "' LIMIT 1";
$result = mysqli_query($koneksi, $query);
if ($result && mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
} else {
    // Jika data tidak ditemukan, isi default kosong
    $user = [
        'id_karyawan' => $id_karyawan,
        'nama' => '-',
        'outlet' => '-',
        'created_at' => '-',
    ];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Skydash Admin</title>
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
      <?php include_once 'sidebar.php'; ?>
      <div class="content-wrapper">
        <div class="row">
          <div class="col-md-12 grid-margin">
            <div class="row">
              <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                <h3 class="font-weight-bold">Data Diri Karyawan</h3>
                <h6 class="font-weight-normal mb-0">Berikut adalah data diri Anda yang tersimpan dalam sistem.</h6>
              </div>
              <!-- Form Data Diri -->
              <div class="col-12">
                <div class="card">
                  <div class="card-body">
                    <form>
                      <div class="form-group">
                        <label>ID Karyawan</label>
                        <p class="form-control-static"><?= htmlspecialchars($user['id_karyawan']) ?></p>
                      </div>
                      <div class="form-group">
                        <label>Nama</label>
                        <p class="form-control-static"><?= htmlspecialchars($user['nama']) ?></p>
                      </div>
                      <div class="form-group">
                        <label>Outlet</label>
                        <p class="form-control-static"><?= htmlspecialchars($user['outlet']) ?></p>
                      </div>
                      <div class="form-group">
                        <label>Tanggal Mulai Kerja</label>
                        <p class="form-control-static"><?= htmlspecialchars($user['created_at']) ?></p>
                      </div>
                      <!-- Tombol untuk membuka modal ganti password -->
                      <button type="button" class="btn btn-warning mt-3" data-toggle="modal" data-target="#gantiPasswordModal">
                        <i class="ti ti-lock"></i> Ganti Password
                      </button>
                    </form>
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
  <!-- Custom js for this page-->
  <!-- End custom js for this page-->

  <!-- Modal Ganti Password -->
  <div class="modal fade" id="gantiPasswordModal" tabindex="-1" role="dialog" aria-labelledby="gantiPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="gantiPasswordModalLabel">Ganti Password</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form method="post" action="">
          <div class="modal-body">
            <div class="form-group">
              <label>Password Lama</label>
              <input type="password" name="old_password" class="form-control" required>
            </div>
            <div class="form-group">
              <label>Password Baru</label>
              <input type="password" name="new_password" class="form-control" required>
            </div>
            <div class="form-group">
              <label>Konfirmasi Password Baru</label>
              <input type="password" name="confirm_password" class="form-control" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
            <button type="submit" name="ubah_password" class="btn btn-primary">Ubah Password</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</body>

</html>
