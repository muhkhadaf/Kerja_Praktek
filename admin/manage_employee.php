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

// Ambil daftar outlet/cabang
$query_outlets = "SELECT DISTINCT outlet_name FROM locations ORDER BY outlet_name";
$result_outlets = mysqli_query($koneksi, $query_outlets);
$outlets = [];
if ($result_outlets) {
    while ($row = mysqli_fetch_assoc($result_outlets)) {
        $outlets[] = $row['outlet_name'];
    }
}

// Proses tambah karyawan baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $id_karyawan = sanitize($_POST['id_karyawan']);
    $nama = sanitize($_POST['nama']);
    $email = sanitize($_POST['email']);
    $outlet = sanitize($_POST['outlet']);
    $role = sanitize($_POST['role']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Validasi data
    $error = false;
    
    // Cek ID Karyawan
    $query_check = "SELECT * FROM users WHERE id_karyawan = '$id_karyawan'";
    $result_check = mysqli_query($koneksi, $query_check);
    if (mysqli_num_rows($result_check) > 0) {
        $alert = "ID Karyawan sudah digunakan!";
        $alert_type = "danger";
        $error = true;
    }
    
    // Cek Email
    $query_check = "SELECT * FROM users WHERE email = '$email'";
    $result_check = mysqli_query($koneksi, $query_check);
    if (mysqli_num_rows($result_check) > 0) {
        $alert = "Email sudah digunakan!";
        $alert_type = "danger";
        $error = true;
    }
    
    if (!$error) {
        $query = "INSERT INTO users (id_karyawan, nama, email, password, outlet, role) 
                  VALUES ('$id_karyawan', '$nama', '$email', '$password', '$outlet', '$role')";
        
        if (mysqli_query($koneksi, $query)) {
            $alert = "Karyawan baru berhasil ditambahkan!";
            $alert_type = "success";
        } else {
            $alert = "Error: " . mysqli_error($koneksi);
            $alert_type = "danger";
        }
    }
}

// Proses update karyawan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = (int)$_POST['id'];
    $id_karyawan = sanitize($_POST['id_karyawan']);
    $nama = sanitize($_POST['nama']);
    $email = sanitize($_POST['email']);
    $outlet = sanitize($_POST['outlet']);
    $role = sanitize($_POST['role']);
    
    // Cek apakah password baru diinput
    $password_sql = "";
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $password_sql = ", password = '$password'";
    }
    
    // Validasi data
    $error = false;
    
    // Cek ID Karyawan
    $query_check = "SELECT * FROM users WHERE id_karyawan = '$id_karyawan' AND id != $id";
    $result_check = mysqli_query($koneksi, $query_check);
    if (mysqli_num_rows($result_check) > 0) {
        $alert = "ID Karyawan sudah digunakan!";
        $alert_type = "danger";
        $error = true;
    }
    
    // Cek Email
    $query_check = "SELECT * FROM users WHERE email = '$email' AND id != $id";
    $result_check = mysqli_query($koneksi, $query_check);
    if (mysqli_num_rows($result_check) > 0) {
        $alert = "Email sudah digunakan!";
        $alert_type = "danger";
        $error = true;
    }
    
    if (!$error) {
        $query = "UPDATE users SET 
                  id_karyawan = '$id_karyawan', 
                  nama = '$nama', 
                  email = '$email', 
                  outlet = '$outlet', 
                  role = '$role'
                  $password_sql
                  WHERE id = $id";
        
        if (mysqli_query($koneksi, $query)) {
            $alert = "Data karyawan berhasil diperbarui!";
            $alert_type = "success";
        } else {
            $alert = "Error: " . mysqli_error($koneksi);
            $alert_type = "danger";
        }
    }
}

// Proses hapus karyawan
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Ambil id_karyawan untuk menghapus data terkait
    $query_get_id = "SELECT id_karyawan FROM users WHERE id = $id";
    $result_get_id = mysqli_query($koneksi, $query_get_id);
    
    if ($result_get_id && mysqli_num_rows($result_get_id) > 0) {
        $row = mysqli_fetch_assoc($result_get_id);
        $id_karyawan = $row['id_karyawan'];
        
        // Hapus data jadwal terlebih dahulu
        $query_delete_jadwal = "DELETE FROM jadwal WHERE id_karyawan = '$id_karyawan'";
        mysqli_query($koneksi, $query_delete_jadwal);
        
        // Hapus data absensi
        $query_delete_absensi = "DELETE FROM absensi WHERE id_karyawan = '$id_karyawan'";
        mysqli_query($koneksi, $query_delete_absensi);
        
        // Hapus data izin
        $query_delete_izin = "DELETE FROM izin WHERE id_karyawan = '$id_karyawan'";
        mysqli_query($koneksi, $query_delete_izin);
        
        // Hapus data users
        $query_delete_user = "DELETE FROM users WHERE id = $id";
        if (mysqli_query($koneksi, $query_delete_user)) {
            $alert = "Karyawan berhasil dihapus!";
            $alert_type = "success";
        } else {
            $alert = "Error: " . mysqli_error($koneksi);
            $alert_type = "danger";
        }
    } else {
        $alert = "Karyawan tidak ditemukan!";
        $alert_type = "danger";
    }
}

// Data untuk edit
$editData = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $query = "SELECT * FROM users WHERE id = $id";
    $result = mysqli_query($koneksi, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $editData = mysqli_fetch_assoc($result);
    }
}

// Ambil semua data karyawan
$query_karyawan = "SELECT * FROM users ORDER BY id_karyawan";
$result_karyawan = mysqli_query($koneksi, $query_karyawan);
$karyawan_list = [];
$admin_list = [];
$karyawan_by_outlet = [];

if ($result_karyawan) {
    while ($row = mysqli_fetch_assoc($result_karyawan)) {
        if ($row['role'] === 'admin') {
            $admin_list[] = $row;
        } else {
            if (!isset($karyawan_by_outlet[$row['outlet']])) {
                $karyawan_by_outlet[$row['outlet']] = [];
            }
            $karyawan_by_outlet[$row['outlet']][] = $row;
        }
        $karyawan_list[] = $row;
    }
}

// Urutkan outlet berdasarkan nama
ksort($karyawan_by_outlet);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Wakacao Admin - Kelola Karyawan</title>
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
      <?php include_once 'navbar.php'; ?>
      <?php include_once 'sidebar.php'; ?>
    
      
      <!-- partial -->
      <div class="main-panel">
        <div class="content-wrapper">
          <div class="row">
            <div class="col-md-12 grid-margin">
              <div class="row">
                <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                  <h3 class="font-weight-bold">Kelola Data Karyawan</h3>
                  <h6 class="font-weight-normal mb-0">Mengelola data dan akun karyawan Wakacao</h6>
                </div>
                <div class="col-12 col-xl-4">
                  <div class="justify-content-end d-flex">
                    <button class="btn btn-primary btn-icon-text" data-toggle="modal" data-target="#addEmployeeModal">
                      <i class="ti-plus btn-icon-prepend"></i>
                      Tambah Karyawan Baru
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
          
          <!-- Tabel Admin -->
          <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
              <div class="card">
                <div class="card-body">
                  <h4 class="card-title">Daftar Admin</h4>
                  <div class="table-responsive">
                    <table class="table table-striped datatable-admin">
                      <thead>
                        <tr>
                          <th>ID Karyawan</th>
                          <th>Nama</th>
                          <th>Email</th>
                          <th>Outlet/Cabang</th>
                          <th>Role</th>
                          <th>Aksi</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($admin_list as $admin): ?>
                        <tr>
                          <td><?php echo $admin['id_karyawan']; ?></td>
                          <td><?php echo $admin['nama']; ?></td>
                          <td><?php echo $admin['email']; ?></td>
                          <td><?php echo $admin['outlet']; ?></td>
                          <td>
                            <span class="badge badge-danger"><?php echo ucfirst($admin['role']); ?></span>
                          </td>
                          <td>
                            <a href="?action=edit&id=<?php echo $admin['id']; ?>" class="btn btn-sm btn-info">
                              <i class="ti-pencil"></i> Edit
                            </a>
                          </td>
                        </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Tabel Karyawan per Outlet -->
          <?php foreach ($karyawan_by_outlet as $outlet_name => $outlet_karyawan): ?>
          <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
              <div class="card">
                <div class="card-body">
                  <h4 class="card-title">Karyawan Outlet: <?php echo $outlet_name; ?></h4>
                  <div class="table-responsive">
                    <table class="table table-striped datatable-outlet">
                      <thead>
                        <tr>
                          <th>ID Karyawan</th>
                          <th>Nama</th>
                          <th>Email</th>
                          <th>Role</th>
                          <th>Aksi</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($outlet_karyawan as $karyawan): ?>
                        <tr>
                          <td><?php echo $karyawan['id_karyawan']; ?></td>
                          <td><?php echo $karyawan['nama']; ?></td>
                          <td><?php echo $karyawan['email']; ?></td>
                          <td>
                            <span class="badge badge-<?php 
                              echo $karyawan['role'] === 'supervisor' ? 'warning' : 'success'; 
                            ?>"><?php echo ucfirst($karyawan['role']); ?></span>
                          </td>
                          <td>
                            <a href="?action=edit&id=<?php echo $karyawan['id']; ?>" class="btn btn-sm btn-info">
                              <i class="ti-pencil"></i> Edit
                            </a>
                            <a href="?action=delete&id=<?php echo $karyawan['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus karyawan ini? Semua data terkait (jadwal, absensi, izin) juga akan dihapus!')">
                              <i class="ti-trash"></i> Hapus
                            </a>
                          </td>
                        </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <!-- content-wrapper ends -->
        
        <!-- Modal Tambah Karyawan -->
        <div class="modal fade" id="addEmployeeModal" tabindex="-1" role="dialog" aria-labelledby="addEmployeeModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="addEmployeeModalLabel">Tambah Karyawan Baru</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <form method="POST" action="">
                  <input type="hidden" name="action" value="add">
                  
                  <div class="form-row">
                    <div class="form-group col-md-6">
                      <label for="id_karyawan">ID Karyawan</label>
                      <input type="text" class="form-control" id="id_karyawan" name="id_karyawan" placeholder="Contoh: K001" required>
                    </div>
                    <div class="form-group col-md-6">
                      <label for="nama">Nama Lengkap</label>
                      <input type="text" class="form-control" id="nama" name="nama" placeholder="Nama lengkap karyawan" required>
                    </div>
                  </div>
                  
                  <div class="form-row">
                    <div class="form-group col-md-6">
                      <label for="email">Email</label>
                      <input type="email" class="form-control" id="email" name="email" placeholder="Email karyawan" required>
                    </div>
                    <div class="form-group col-md-6">
                      <label for="password">Password</label>
                      <input type="password" class="form-control" id="password" name="password" placeholder="Password akun" required>
                    </div>
                  </div>
                  
                  <div class="form-row">
                    <div class="form-group col-md-6">
                      <label for="outlet">Outlet/Cabang</label>
                      <select class="form-control" id="outlet" name="outlet" required>
                        <?php foreach ($outlets as $outlet): ?>
                        <option value="<?php echo $outlet; ?>"><?php echo $outlet; ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="form-group col-md-6">
                      <label for="role">Role</label>
                      <select class="form-control" id="role" name="role" required>
                        <option value="karyawan">Karyawan</option>
                        <option value="supervisor">Supervisor</option>
                        <option value="admin">Admin</option>
                      </select>
                    </div>
                  </div>
                  
                  <button type="submit" class="btn btn-primary">
                    <i class="ti-save mr-1"></i> Simpan Karyawan
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Modal Edit Karyawan -->
        <?php if ($editData): ?>
        <div class="modal fade" id="editEmployeeModal" tabindex="-1" role="dialog" aria-labelledby="editEmployeeModalLabel" aria-hidden="true" data-backdrop="static">
          <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="editEmployeeModalLabel">Edit Data Karyawan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="window.location='manage_employee.php'">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <form method="POST" action="">
                  <input type="hidden" name="action" value="edit">
                  <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
                  
                  <div class="form-row">
                    <div class="form-group col-md-6">
                      <label for="edit_id_karyawan">ID Karyawan</label>
                      <input type="text" class="form-control" id="edit_id_karyawan" name="id_karyawan" value="<?php echo $editData['id_karyawan']; ?>" required>
                    </div>
                    <div class="form-group col-md-6">
                      <label for="edit_nama">Nama Lengkap</label>
                      <input type="text" class="form-control" id="edit_nama" name="nama" value="<?php echo $editData['nama']; ?>" required>
                    </div>
                  </div>
                  
                  <div class="form-row">
                    <div class="form-group col-md-6">
                      <label for="edit_email">Email</label>
                      <input type="email" class="form-control" id="edit_email" name="email" value="<?php echo $editData['email']; ?>" required>
                    </div>
                    <div class="form-group col-md-6">
                      <label for="edit_password">Password (Kosongkan jika tidak ingin mengubah)</label>
                      <input type="password" class="form-control" id="edit_password" name="password" placeholder="Kosongkan jika tidak ingin mengubah">
                    </div>
                  </div>
                  
                  <div class="form-row">
                    <div class="form-group col-md-6">
                      <label for="edit_outlet">Outlet/Cabang</label>
                      <select class="form-control" id="edit_outlet" name="outlet" required>
                        <?php foreach ($outlets as $outlet): ?>
                        <option value="<?php echo $outlet; ?>" <?php echo ($editData['outlet'] === $outlet) ? 'selected' : ''; ?>><?php echo $outlet; ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="form-group col-md-6">
                      <label for="edit_role">Role</label>
                      <select class="form-control" id="edit_role" name="role" required>
                        <option value="karyawan" <?php echo ($editData['role'] === 'karyawan') ? 'selected' : ''; ?>>Karyawan</option>
                        <option value="supervisor" <?php echo ($editData['role'] === 'supervisor') ? 'selected' : ''; ?>>Supervisor</option>
                        <option value="admin" <?php echo ($editData['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                      </select>
                    </div>
                  </div>
                  
                  <button type="submit" class="btn btn-primary">
                    <i class="ti-save mr-1"></i> Update Karyawan
                  </button>
                  <a href="manage_employee.php" class="btn btn-light">Batal</a>
                </form>
              </div>
            </div>
          </div>
        </div>
        <script>
          // Tampilkan modal edit saat halaman dimuat
          document.addEventListener('DOMContentLoaded', function() {
            $('#editEmployeeModal').modal('show');
          });
        </script>
        <?php endif; ?>
        
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
  <!-- Custom js for this page-->
  <script>
    $(document).ready(function() {
      $('.datatable-admin').DataTable({
        "order": [[0, "asc"]],
        "language": {
          "lengthMenu": "Tampilkan _MENU_ data per halaman",
          "zeroRecords": "Tidak ada data yang ditemukan",
          "info": "Menampilkan halaman _PAGE_ dari _PAGES_",
          "infoEmpty": "Tidak ada data yang tersedia",
          "infoFiltered": "(difilter dari _MAX_ total data)",
          "search": "Cari:",
          "paginate": {
            "first": "Pertama",
            "last": "Terakhir",
            "next": "Selanjutnya",
            "previous": "Sebelumnya"
          }
        }
      });
      
      $('.datatable-outlet').DataTable({
        "order": [[0, "asc"]],
        "language": {
          "lengthMenu": "Tampilkan _MENU_ data per halaman",
          "zeroRecords": "Tidak ada data yang ditemukan",
          "info": "Menampilkan halaman _PAGE_ dari _PAGES_",
          "infoEmpty": "Tidak ada data yang tersedia",
          "infoFiltered": "(difilter dari _MAX_ total data)",
          "search": "Cari:",
          "paginate": {
            "first": "Pertama",
            "last": "Terakhir",
            "next": "Selanjutnya",
            "previous": "Sebelumnya"
          }
        }
      });
    });
  </script>
  <!-- End custom js for this page-->
</body>

</html> 