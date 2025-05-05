<?php
// Include konfigurasi
require_once '../config.php';

// Cek apakah pengguna sudah login sebagai admin
if (!isLoggedIn() || getUserRole() !== 'admin') {
    header("Location: ../admin_login.php");
    exit();
}

// Alert message
$alert = '';
$alert_type = '';

// Proses tambah shift baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $nama_shift = sanitize($_POST['nama_shift']);
    $jam_mulai = sanitize($_POST['jam_mulai']);
    $jam_selesai = sanitize($_POST['jam_selesai']);
    
    if (empty($nama_shift) || empty($jam_mulai) || empty($jam_selesai)) {
        $alert = 'Semua field harus diisi!';
        $alert_type = 'danger';
    } else {
        $query = "INSERT INTO shift (nama_shift, jam_mulai, jam_selesai) 
                 VALUES ('$nama_shift', '$jam_mulai', '$jam_selesai')";
        
        if (mysqli_query($koneksi, $query)) {
            $alert = 'Shift berhasil ditambahkan!';
            $alert_type = 'success';
        } else {
            $alert = 'Terjadi kesalahan: ' . mysqli_error($koneksi);
            $alert_type = 'danger';
        }
    }
}

// Proses edit shift
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id_shift = (int)$_POST['id_shift'];
    $nama_shift = sanitize($_POST['nama_shift']);
    $jam_mulai = sanitize($_POST['jam_mulai']);
    $jam_selesai = sanitize($_POST['jam_selesai']);
    
    if (empty($nama_shift) || empty($jam_mulai) || empty($jam_selesai)) {
        $alert = 'Semua field harus diisi!';
        $alert_type = 'danger';
    } else {
        $query = "UPDATE shift SET 
                  nama_shift = '$nama_shift', 
                  jam_mulai = '$jam_mulai', 
                  jam_selesai = '$jam_selesai'
                  WHERE id = $id_shift";
        
        if (mysqli_query($koneksi, $query)) {
            $alert = 'Shift berhasil diperbarui!';
            $alert_type = 'success';
        } else {
            $alert = 'Terjadi kesalahan: ' . mysqli_error($koneksi);
            $alert_type = 'danger';
        }
    }
}

// Proses hapus shift
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id_shift = (int)$_POST['id_shift'];
    
    // Cek apakah shift digunakan dalam jadwal
    $check_query = "SELECT COUNT(*) as count FROM jadwal WHERE id_shift = $id_shift";
    $check_result = mysqli_query($koneksi, $check_query);
    $check_data = mysqli_fetch_assoc($check_result);
    
    if ($check_data['count'] > 0) {
        $alert = 'Shift tidak dapat dihapus karena sedang digunakan dalam jadwal!';
        $alert_type = 'warning';
    } else {
        $query = "DELETE FROM shift WHERE id = $id_shift";
        
        if (mysqli_query($koneksi, $query)) {
            $alert = 'Shift berhasil dihapus!';
            $alert_type = 'success';
        } else {
            $alert = 'Terjadi kesalahan: ' . mysqli_error($koneksi);
            $alert_type = 'danger';
        }
    }
}

// Ambil semua data shift
$query_shifts = "SELECT * FROM shift ORDER BY jam_mulai";
$result_shifts = mysqli_query($koneksi, $query_shifts);
$shifts = [];

if ($result_shifts) {
    while ($row = mysqli_fetch_assoc($result_shifts)) {
        $shifts[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Wakacao Admin - Kelola Shift</title>
  <!-- plugins:css -->
  <link rel="stylesheet" href="../vendors/feather/feather.css">
  <link rel="stylesheet" href="../vendors/ti-icons/css/themify-icons.css">
  <link rel="stylesheet" href="../vendors/css/vendor.bundle.base.css">
  <!-- endinject -->
  <!-- Plugin css for this page -->
  <link rel="stylesheet" href="../vendors/datatables.net-bs4/dataTables.bootstrap4.css">
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
        <div class="content-wrapper">
          <div class="row">
            <div class="col-md-12 grid-margin">
              <div class="row">
                <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                  <h3 class="font-weight-bold">Kelola Shift</h3>
                  <h6 class="font-weight-normal mb-0">Pengaturan shift untuk karyawan Wakacao</h6>
                </div>
                <div class="col-12 col-xl-4">
                  <div class="justify-content-end d-flex">
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addShiftModal">
                      <i class="ti-plus mr-1"></i> Tambah Shift Baru
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <?php if ($alert): ?>
          <div class="alert alert-<?php echo $alert_type; ?> alert-dismissible fade show" role="alert">
            <?php echo $alert; ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <?php endif; ?>
          
          <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
              <div class="card">
                <div class="card-body">
                  <h4 class="card-title">Daftar Shift</h4>
                  <div class="table-responsive">
                    <table class="table table-striped table-hover">
                      <thead>
                        <tr>
                          <th>ID</th>
                          <th>Nama Shift</th>
                          <th>Jam Mulai</th>
                          <th>Jam Selesai</th>
                          <th>Durasi</th>
                          <th>Aksi</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($shifts as $shift): ?>
                          <?php 
                            // Hitung durasi shift
                            $mulai = new DateTime($shift['jam_mulai']);
                            $selesai = new DateTime($shift['jam_selesai']);
                            
                            // Jika selesai lebih kecil dari mulai, berarti melewati tengah malam
                            if ($selesai < $mulai) {
                                $selesai->modify('+1 day');
                            }
                            
                            $durasi = $mulai->diff($selesai);
                            $durasi_jam = $durasi->h;
                            $durasi_menit = $durasi->i;
                          ?>
                          <tr>
                            <td><?php echo $shift['id']; ?></td>
                            <td><?php echo $shift['nama_shift']; ?></td>
                            <td><?php echo date('H:i', strtotime($shift['jam_mulai'])); ?></td>
                            <td><?php echo date('H:i', strtotime($shift['jam_selesai'])); ?></td>
                            <td><?php echo $durasi_jam . ' jam ' . ($durasi_menit > 0 ? $durasi_menit . ' menit' : ''); ?></td>
                            <td>
                              <button type="button" class="btn btn-sm btn-info edit-shift" 
                                      data-id="<?php echo $shift['id']; ?>"
                                      data-nama="<?php echo $shift['nama_shift']; ?>"
                                      data-mulai="<?php echo $shift['jam_mulai']; ?>"
                                      data-selesai="<?php echo $shift['jam_selesai']; ?>">
                                <i class="ti-pencil"></i> Edit
                              </button>
                              <button type="button" class="btn btn-sm btn-danger delete-shift"
                                      data-id="<?php echo $shift['id']; ?>"
                                      data-nama="<?php echo $shift['nama_shift']; ?>">
                                <i class="ti-trash"></i> Hapus
                              </button>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                        
                        <?php if (count($shifts) === 0): ?>
                          <tr>
                            <td colspan="6" class="text-center">Tidak ada data shift</td>
                          </tr>
                        <?php endif; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="row mt-4">
            <div class="col-md-12 grid-margin stretch-card">
              <div class="card">
                <div class="card-body">
                  <h4 class="card-title">Panduan Pengelolaan Shift</h4>
                  <div class="row">
                    <div class="col-md-6">
                      <div class="alert alert-info">
                        <h5>Ketentuan Shift:</h5>
                        <ul>
                          <li>Shift digunakan sebagai acuan waktu kerja karyawan</li>
                          <li>Karyawan wajib melakukan absensi check-in dan check-out sesuai jam shift</li>
                          <li>Toleransi keterlambatan check-in adalah 5 menit dari waktu shift dimulai</li>
                          <li>Shift yang melewati tengah malam akan dihitung sebagai 1 hari kerja</li>
                        </ul>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="alert alert-warning">
                        <h5>Peringatan:</h5>
                        <ul>
                          <li>Shift yang sedang digunakan dalam jadwal tidak dapat dihapus</li>
                          <li>Perubahan jam shift akan berpengaruh pada perhitungan keterlambatan</li>
                          <li>Konsultasikan dengan karyawan sebelum mengubah shift yang sudah berjalan</li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
        </div>
        <!-- content-wrapper ends -->
        
        <!-- partial:partials/_footer.html -->
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

  <!-- Modal Tambah Shift -->
  <div class="modal fade" id="addShiftModal" tabindex="-1" role="dialog" aria-labelledby="addShiftModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addShiftModalLabel">Tambah Shift Baru</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form method="POST" action="">
          <div class="modal-body">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
              <label for="nama_shift">Nama Shift</label>
              <input type="text" class="form-control" id="nama_shift" name="nama_shift" required>
              <small class="form-text text-muted">Contoh: Pagi, Siang, Malam, dll.</small>
            </div>
            <div class="form-group">
              <label for="jam_mulai">Jam Mulai</label>
              <input type="time" class="form-control" id="jam_mulai" name="jam_mulai" required>
            </div>
            <div class="form-group">
              <label for="jam_selesai">Jam Selesai</label>
              <input type="time" class="form-control" id="jam_selesai" name="jam_selesai" required>
              <small class="form-text text-muted">Jika shift melewati tengah malam, jam selesai diisi dengan jam di hari berikutnya.</small>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary">Simpan</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal Edit Shift -->
  <div class="modal fade" id="editShiftModal" tabindex="-1" role="dialog" aria-labelledby="editShiftModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editShiftModalLabel">Edit Shift</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form method="POST" action="">
          <div class="modal-body">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" id="edit_id_shift" name="id_shift">
            <div class="form-group">
              <label for="edit_nama_shift">Nama Shift</label>
              <input type="text" class="form-control" id="edit_nama_shift" name="nama_shift" required>
            </div>
            <div class="form-group">
              <label for="edit_jam_mulai">Jam Mulai</label>
              <input type="time" class="form-control" id="edit_jam_mulai" name="jam_mulai" required>
            </div>
            <div class="form-group">
              <label for="edit_jam_selesai">Jam Selesai</label>
              <input type="time" class="form-control" id="edit_jam_selesai" name="jam_selesai" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal Hapus Shift -->
  <div class="modal fade" id="deleteShiftModal" tabindex="-1" role="dialog" aria-labelledby="deleteShiftModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="deleteShiftModalLabel">Hapus Shift</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form method="POST" action="">
          <div class="modal-body">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" id="delete_id_shift" name="id_shift">
            <p>Anda yakin ingin menghapus shift <strong id="delete_nama_shift"></strong>?</p>
            <div class="alert alert-warning">
              Peringatan: Shift yang sedang digunakan dalam jadwal tidak dapat dihapus. Harap periksa jadwal terlebih dahulu.
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-danger">Hapus</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- plugins:js -->
  <script src="../vendors/js/vendor.bundle.base.js"></script>
  <!-- endinject -->
  <!-- Plugin js for this page -->
  <script src="../vendors/datatables.net/jquery.dataTables.js"></script>
  <script src="../vendors/datatables.net-bs4/dataTables.bootstrap4.js"></script>
  <!-- End plugin js for this page -->
  <!-- inject:js -->
  <script src="../js/off-canvas.js"></script>
  <script src="../js/hoverable-collapse.js"></script>
  <script src="../js/template.js"></script>
  <script src="../js/settings.js"></script>
  <script src="../js/todolist.js"></script>
  <!-- endinject -->
  
  <script>
    $(document).ready(function() {
      // Edit shift
      $('.edit-shift').click(function() {
        const id = $(this).data('id');
        const nama = $(this).data('nama');
        const jamMulai = $(this).data('mulai');
        const jamSelesai = $(this).data('selesai');
        
        $('#edit_id_shift').val(id);
        $('#edit_nama_shift').val(nama);
        $('#edit_jam_mulai').val(jamMulai);
        $('#edit_jam_selesai').val(jamSelesai);
        
        $('#editShiftModal').modal('show');
      });
      
      // Delete shift
      $('.delete-shift').click(function() {
        const id = $(this).data('id');
        const nama = $(this).data('nama');
        
        $('#delete_id_shift').val(id);
        $('#delete_nama_shift').text(nama);
        
        $('#deleteShiftModal').modal('show');
      });
    });
  </script>
</body>

</html> 