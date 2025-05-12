<?php
// Include konfigurasi
require_once '../../config.php';

// Cek apakah pengguna sudah login
requireLogin();

// Ambil data karyawan yang login
$id_karyawan = getKaryawanId();
$nama = getUserName();

// Query untuk mengambil data riwayat izin karyawan
$query = "SELECT i.*, u.nama
          FROM izin i
          JOIN users u ON i.id_karyawan = u.id_karyawan
          WHERE i.id_karyawan = '$id_karyawan'
          ORDER BY i.created_at DESC";

$result = mysqli_query($koneksi, $query);
$izin_array = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $izin_array[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Wakacao - Riwayat Izin</title>
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
      
      <div class="container-fluid page-body-wrapper">
      <?php include_once 'sidebar.php'; ?>

      
      <div class="content-wrapper">
        <div class="row">
          <div class="col-md-12 grid-margin">
            <div class="row">
              <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                <h3 class="font-weight-bold">Riwayat Izin</h3>
                <p class="font-weight-normal mb-0">Berikut adalah riwayat pengajuan izin Anda</p>
              </div>
              <!-- Card Riwayat Izin -->
              <div class="col-12 mt-4">
                <div class="card">
                  <div class="card-body">
                    <?php if (count($izin_array) > 0): ?>
                      <?php foreach ($izin_array as $izin): ?>
                        <div class="card mb-3">
                          <div class="card-body">
                            <h5 class="card-title"><?php echo ucfirst($izin['jenis_izin']); ?></h5>
                            <div class="row">
                              <div class="col-md-6">
                                <p class="card-text">Tanggal Mulai: <?php echo formatTanggal($izin['tanggal_mulai']); ?></p>
                                <p class="card-text">Tanggal Selesai: <?php echo formatTanggal($izin['tanggal_selesai']); ?></p>
                                <p class="card-text">Keterangan: <?php echo $izin['keterangan']; ?></p>
                                <p class="card-text">Solusi Pengganti: <?php echo ucfirst($izin['solusi_pengganti']); ?></p>
                              </div>
                              <div class="col-md-6">
                                <p class="card-text">Diajukan pada: <?php echo date('d/m/Y H:i', strtotime($izin['created_at'])); ?></p>
                                <p class="card-text">Status: 
                                  <?php 
                                    $badge_class = 'badge-secondary';
                                    if ($izin['status'] == 'disetujui') {
                                      $badge_class = 'badge-success';
                                    } elseif ($izin['status'] == 'ditolak') {
                                      $badge_class = 'badge-danger';
                                    } elseif ($izin['status'] == 'pending') {
                                      $badge_class = 'badge-warning';
                                    }
                                  ?>
                                  <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($izin['status']); ?></span>
                                </p>
                                <?php if (!empty($izin['bukti_file'])): ?>
                                <p class="card-text">
                                  <a href="../../<?php echo $izin['bukti_file']; ?>" target="_blank" class="btn btn-sm btn-info">Lihat Bukti</a>
                                </p>
                                <?php endif; ?>
                              </div>
                            </div>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <div class="alert alert-info">
                        Belum ada riwayat izin. Silakan ajukan izin melalui halaman utama.
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
      <!-- partial:../../partials/_footer.html -->
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
</body>

</html>
