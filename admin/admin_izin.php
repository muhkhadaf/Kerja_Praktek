<?php
require_once '../config.php';

// Cek apakah user adalah admin
requireAdmin(); // Pastikan fungsi ini ada di config.php

// Proses aksi setujui/tolak
if (isset($_POST['action']) && isset($_POST['izin_id'])) {
    $id_izin = intval($_POST['izin_id']);
    $aksi = $_POST['action'] === 'approve' ? 'disetujui' : 'ditolak';
    $keterangan = isset($_POST['keterangan']) ? mysqli_real_escape_string($koneksi, $_POST['keterangan']) : '';
    $admin_name = getUserName();
    
    // Cek apakah kolom yang diperlukan sudah ada
    $check_columns = mysqli_query($koneksi, "SHOW COLUMNS FROM izin LIKE 'keterangan_admin'");
    $column_exists = mysqli_num_rows($check_columns) > 0;
    
    if ($column_exists) {
        $query = "UPDATE izin SET status = '$aksi', keterangan_admin = '$keterangan', approved_by = '$admin_name', approved_at = NOW() WHERE id = $id_izin";
    } else {
        // Jika kolom belum ada, gunakan query yang hanya mengupdate status
        $query = "UPDATE izin SET status = '$aksi' WHERE id = $id_izin";
    }
    
    $result = mysqli_query($koneksi, $query);
    
    if (!$result) {
        // Tampilkan pesan error yang lebih informatif
        echo "<div style='background-color: #f8d7da; color: #721c24; padding: 10px; margin: 10px;'>";
        echo "<h3>Error Database</h3>";
        echo "<p>Terjadi kesalahan saat menjalankan query:</p>";
        echo "<p><code>" . htmlspecialchars($query) . "</code></p>";
        echo "<p>Error: " . mysqli_error($koneksi) . "</p>";
        echo "<p>Silakan jalankan file SQL berikut di phpMyAdmin untuk menambahkan kolom yang diperlukan:</p>";
        echo "<pre>
-- Menambahkan kolom keterangan_admin
ALTER TABLE izin ADD COLUMN keterangan_admin TEXT NULL AFTER status;

-- Menambahkan kolom approved_by
ALTER TABLE izin ADD COLUMN approved_by VARCHAR(100) NULL;

-- Menambahkan kolom approved_at
ALTER TABLE izin ADD COLUMN approved_at DATETIME NULL;
        </pre>";
        echo "<p><a href='admin_izin.php' class='btn btn-primary'>Kembali</a></p>";
        echo "</div>";
        exit();
    }
    
    header('Location: admin_izin.php');
    exit();
}

// Ambil data izin
$query = "SELECT izin.*, users.nama FROM izin JOIN users ON izin.id_karyawan = users.id_karyawan ORDER BY izin.id DESC";
$result = mysqli_query($koneksi, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Manajemen Izin - Admin</title>
  <!-- plugins:css -->
  <link rel="stylesheet" href="../vendors/feather/feather.css">
  <link rel="stylesheet" href="../vendors/ti-icons/css/themify-icons.css">
  <link rel="stylesheet" href="../vendors/css/vendor.bundle.base.css">
  <!-- endinject -->
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
      <div class="content-wrapper">
        <div class="row">
          <div class="col-md-12 grid-margin">
            <div class="row">
              <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                <h3 class="font-weight-bold">Manajemen Pengajuan Izin</h3>
                <h6 class="font-weight-normal mb-0">Kelola pengajuan izin karyawan</h6>
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
              <div class="card-body">
                <h4 class="card-title">Daftar Pengajuan Izin</h4>
                <div class="table-responsive">
                  <table class="table table-hover">
                    <thead>
                      <tr>
                        <th>No</th>
                        <th>Nama Karyawan</th>
                        <th>Tanggal Mulai</th>
                        <th>Tanggal Selesai</th>
                        <th>Jenis Izin</th>
                        <th>Keterangan</th>
                        <th>Bukti</th>
                        <th>Solusi Pengganti</th>
                        <th>Status</th>
                        <th>Waktu Pengajuan</th>
                        <th>Aksi</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                          <td><?= $no++ ?></td>
                          <td><?= htmlspecialchars($row['nama']) ?></td>
                          <td><?= htmlspecialchars($row['tanggal_mulai']) ?></td>
                          <td><?= htmlspecialchars($row['tanggal_selesai']) ?></td>
                          <td><?= htmlspecialchars($row['jenis_izin']) ?></td>
                          <td><?= htmlspecialchars($row['keterangan']) ?></td>
                          <td>
                            <?php if ($row['bukti_file']): ?>
                              <a href="../<?= htmlspecialchars($row['bukti_file']) ?>" target="_blank">Lihat</a>
                            <?php else: ?>
                              -
                            <?php endif; ?>
                          </td>
                          <td><?= htmlspecialchars($row['solusi_pengganti']) ?></td>
                          <td>
                            <?php
                              $status = strtolower($row['status'] ?? 'pending');
                              $badge = 'badge-secondary';
                              if ($status === 'disetujui') $badge = 'badge-success';
                              elseif ($status === 'ditolak') $badge = 'badge-danger';
                              elseif ($status === 'pending') $badge = 'badge-warning';
                            ?>
                            <span class="badge <?= $badge ?>"><?= ucfirst($status) ?></span>
                          </td>
                          <td><?= htmlspecialchars($row['created_at']) ?></td>
                          <td>
                            <?php if ($status === 'pending'): ?>
                              <button type="button" class="btn btn-sm btn-success" onclick="approveModal(<?= $row['id'] ?>)">Setujui</button>
                              <button type="button" class="btn btn-sm btn-danger" onclick="rejectModal(<?= $row['id'] ?>)">Tolak</button>
                            <?php else: ?>
                              <?php if (!empty($row['approved_by'])): ?>
                                <small>Diproses oleh: <?= htmlspecialchars($row['approved_by']) ?></small><br>
                                <?php if (!empty($row['keterangan_admin'])): ?>
                                  <small>Catatan: <?= htmlspecialchars($row['keterangan_admin']) ?></small>
                                <?php endif; ?>
                              <?php else: ?>
                                -
                              <?php endif; ?>
                            <?php endif; ?>
                          </td>
                        </tr>
                      <?php endwhile; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- content-wrapper ends -->
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

  <!-- Approval Modal -->
  <div class="modal fade" id="approveModal" tabindex="-1" role="dialog" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="approveModalLabel">Setujui Pengajuan Izin</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form action="" method="POST">
          <div class="modal-body">
            <input type="hidden" name="izin_id" id="approve_izin_id">
            <input type="hidden" name="action" value="approve">
            <div class="form-group">
              <label for="keterangan">Keterangan (opsional)</label>
              <textarea class="form-control" name="keterangan" rows="3"></textarea>
            </div>
            <div class="alert alert-info">
              <small>Persetujuan izin akan memperbarui status pengajuan.</small>
            </div>
            <p>Apakah Anda yakin ingin menyetujui pengajuan izin ini?</p>
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
          <h5 class="modal-title" id="rejectModalLabel">Tolak Pengajuan Izin</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form action="" method="POST">
          <div class="modal-body">
            <input type="hidden" name="izin_id" id="reject_izin_id">
            <input type="hidden" name="action" value="reject">
            <div class="form-group">
              <label for="keterangan">Alasan Penolakan <span class="text-danger">*</span></label>
              <textarea class="form-control" name="keterangan" rows="3" required></textarea>
            </div>
            <p>Apakah Anda yakin ingin menolak pengajuan izin ini?</p>
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
  <!-- inject:js -->
  <script src="../js/off-canvas.js"></script>
  <script src="../js/hoverable-collapse.js"></script>
  <script src="../js/template.js"></script>
  <script src="../js/settings.js"></script>
  <script src="../js/todolist.js"></script>
  <!-- endinject -->
  <script>
    function approveModal(id) {
      document.getElementById('approve_izin_id').value = id;
      $('#approveModal').modal('show');
    }
    function rejectModal(id) {
      document.getElementById('reject_izin_id').value = id;
      $('#rejectModal').modal('show');
    }
  </script>
</body>
</html> 