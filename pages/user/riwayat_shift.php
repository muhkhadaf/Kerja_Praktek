<?php
// Include konfigurasi
require_once '../../config.php';

// Cek apakah pengguna sudah login
requireLogin();

// Ambil data karyawan yang login
$id_karyawan = getKaryawanId();

// Query untuk mengambil riwayat shift dari tabel absensi
$query = "SELECT a.tanggal, s.nama_shift, u.outlet, 
          a.check_in, a.check_out,
          a.status_check_in, a.status_check_out,
          a.location_status_in, a.location_status_out,
          a.location_info_in, a.location_info_out,
          CASE 
            WHEN a.status_check_in = 'tepat waktu' AND a.status_check_out = 'tepat waktu' THEN 'Hadir'
            WHEN a.status_check_in = 'terlambat' OR a.status_check_out = 'lebih awal' THEN 'Terlambat/Pulang Awal'
            WHEN a.status_check_in = 'tidak absen' AND a.status_check_out = 'tidak absen' THEN 'Tidak Hadir'
            ELSE 'Belum Absen'
          END as status
          FROM absensi a
          LEFT JOIN shift s ON a.id_shift = s.id
          LEFT JOIN users u ON a.id_karyawan = u.id_karyawan
          WHERE a.id_karyawan = '$id_karyawan'
          ORDER BY a.tanggal DESC
          LIMIT 30"; // Ambil 30 data terakhir

$result = mysqli_query($koneksi, $query);
$riwayat_shift = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $riwayat_shift[] = $row;
    }
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
                <h3 class="font-weight-bold">Riwayat Shift</h3>
                <h6 class="font-weight-normal mb-0">Berikut adalah riwayat shift Anda.</h6>
              </div>
              <!-- Tabel Riwayat Shift -->
              <div class="col-12">
                <div class="card">
                  <div class="card-body">
                    <table class="table table-striped">
                      <thead>
                        <tr>
                          <th>Tanggal</th>
                          <th>Shift</th>
                          <th>Outlet</th>
                          <th>Check In</th>
                          <th>Status Check In</th>
                          <th>Check Out</th>
                          <th>Status Check Out</th>
                          <th>Lokasi</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php if (count($riwayat_shift) > 0): ?>
                          <?php foreach ($riwayat_shift as $shift): ?>
                            <tr>
                              <td><?php echo date('d/m/Y', strtotime($shift['tanggal'])); ?></td>
                              <td><?php echo $shift['nama_shift']; ?></td>
                              <td><?php echo $shift['outlet']; ?></td>
                              <td>
                                <?php if ($shift['check_in']): ?>
                                  <?php echo date('H:i', strtotime($shift['check_in'])); ?>
                                <?php else: ?>
                                  -
                                <?php endif; ?>
                              </td>
                              <td>
                                <?php if ($shift['status_check_in']): ?>
                                  <?php
                                  $status_in_class = '';
                                  switch ($shift['status_check_in']) {
                                      case 'tepat waktu':
                                          $status_in_class = 'text-success';
                                          break;
                                      case 'terlambat':
                                          $status_in_class = 'text-warning';
                                          break;
                                      case 'tidak absen':
                                          $status_in_class = 'text-danger';
                                          break;
                                      default:
                                          $status_in_class = 'text-muted';
                                  }
                                  ?>
                                  <span class="<?php echo $status_in_class; ?>"><?php echo $shift['status_check_in']; ?></span>
                                <?php else: ?>
                                  -
                                <?php endif; ?>
                              </td>
                              <td>
                                <?php if ($shift['check_out']): ?>
                                  <?php echo date('H:i', strtotime($shift['check_out'])); ?>
                                <?php else: ?>
                                  -
                                <?php endif; ?>
                              </td>
                              <td>
                                <?php if ($shift['status_check_out']): ?>
                                  <?php
                                  $status_out_class = '';
                                  switch ($shift['status_check_out']) {
                                      case 'tepat waktu':
                                          $status_out_class = 'text-success';
                                          break;
                                      case 'lebih awal':
                                          $status_out_class = 'text-warning';
                                          break;
                                      case 'tidak absen':
                                          $status_out_class = 'text-danger';
                                          break;
                                      default:
                                          $status_out_class = 'text-muted';
                                  }
                                  ?>
                                  <span class="<?php echo $status_out_class; ?>"><?php echo $shift['status_check_out']; ?></span>
                                <?php else: ?>
                                  -
                                <?php endif; ?>
                              </td>
                              <td>
                                <?php if ($shift['location_status_in'] == 'valid' && $shift['location_status_out'] == 'valid'): ?>
                                  <span class="text-success">Valid</span>
                                <?php elseif ($shift['location_status_in'] == 'invalid' || $shift['location_status_out'] == 'invalid'): ?>
                                  <span class="text-danger">Invalid</span>
                                  <br>
                                  <small class="text-muted">
                                    <?php echo $shift['location_info_in'] ?: $shift['location_info_out']; ?>
                                  </small>
                                <?php else: ?>
                                  -
                                <?php endif; ?>
                              </td>
                            </tr>
                          <?php endforeach; ?>
                        <?php else: ?>
                          <tr>
                            <td colspan="8" class="text-center">Tidak ada data riwayat shift</td>
                          </tr>
                        <?php endif; ?>
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
<!-- Custom js for this page-->
<!-- End custom js for this page-->
</body>

</html>
