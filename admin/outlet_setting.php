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

// Proses penyimpanan atau update outlet
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $outlet_name = sanitize($_POST['outlet_name']);
    $address = sanitize($_POST['address']);
    $active = isset($_POST['active']) ? 1 : 0;
    $location_id = isset($_POST['location_id']) ? (int)$_POST['location_id'] : 0;
    
    // Validasi input
    if (empty($outlet_name) || empty($address)) {
        $alert = "Semua field harus diisi dengan benar!";
        $alert_type = "danger";
    } else {
        // Cek apakah ini update atau insert baru
        if ($location_id > 0) {
            $query = "UPDATE locations SET 
                      outlet_name = '$outlet_name', 
                      address = '$address', 
                      active = $active,
                      updated_at = NOW()
                      WHERE id = $location_id";
            
            if (mysqli_query($koneksi, $query)) {
                $alert = "Outlet berhasil diperbarui!";
                $alert_type = "success";
            } else {
                $alert = "Error: " . mysqli_error($koneksi);
                $alert_type = "danger";
            }
        } else {
            // Insert outlet baru
            $query = "INSERT INTO locations (outlet_name, address, active, created_at) 
                      VALUES ('$outlet_name', '$address', $active, NOW())";
            
            if (mysqli_query($koneksi, $query)) {
                $alert = "Outlet baru berhasil ditambahkan!";
                $alert_type = "success";
            } else {
                $alert = "Error: " . mysqli_error($koneksi);
                $alert_type = "danger";
            }
        }
    }
}

// Proses hapus outlet
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $query = "DELETE FROM locations WHERE id = $id";
    
    if (mysqli_query($koneksi, $query)) {
        $alert = "Outlet berhasil dihapus!";
        $alert_type = "success";
    } else {
        $alert = "Error: " . mysqli_error($koneksi);
        $alert_type = "danger";
    }
}

// Ambil semua outlet dari database
$query_locations = "SELECT * FROM locations ORDER BY outlet_name";
$result_locations = mysqli_query($koneksi, $query_locations);
$locations = [];

if ($result_locations) {
    while ($row = mysqli_fetch_assoc($result_locations)) {
        $locations[] = $row;
    }
}

// Data untuk edit (jika ada)
$editData = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $query = "SELECT * FROM locations WHERE id = $id";
    $result = mysqli_query($koneksi, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $editData = mysqli_fetch_assoc($result);
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Wakacao Admin - Pengaturan Outlet</title>
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
                <h3 class="font-weight-bold">Pengaturan Outlet</h3>
                <h6 class="font-weight-normal mb-0">Kelola data outlet untuk pencatatan informasi absensi</h6>
              </div>
              <div class="col-12 col-xl-4">
                <div class="justify-content-end d-flex">
                  <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addOutletModal">
                    <i class="ti-plus mr-1"></i> Tambah Outlet Baru
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
                <h4 class="card-title">Daftar Outlet</h4>
                <div class="table-responsive">
                  <table class="table table-striped table-hover">
                    <thead>
                      <tr>
                        <th>No</th>
                        <th>Outlet</th>
                        <th>Alamat</th>
                        <th>Status</th>
                        <th>Aksi</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (count($locations) > 0): ?>
                        <?php $no = 1; foreach ($locations as $location): ?>
                        <tr>
                          <td><?php echo $no++; ?></td>
                          <td><?php echo $location['outlet_name']; ?></td>
                          <td><?php echo $location['address']; ?></td>
                          <td>
                            <span class="badge <?php echo $location['active'] ? 'badge-success' : 'badge-danger'; ?>">
                              <?php echo $location['active'] ? 'Aktif' : 'Tidak Aktif'; ?>
                            </span>
                          </td>
                          <td>
                            <a href="?action=edit&id=<?php echo $location['id']; ?>" class="btn btn-sm btn-info">
                              <i class="ti-pencil"></i> Edit
                            </a>
                            <a href="?action=delete&id=<?php echo $location['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus outlet ini?')">
                              <i class="ti-trash"></i> Hapus
                            </a>
                          </td>
                        </tr>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <tr>
                          <td colspan="5" class="text-center">Belum ada data outlet</td>
                        </tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
          
        <!-- Modal Tambah Outlet -->
        <div class="modal fade" id="addOutletModal" tabindex="-1" role="dialog" aria-labelledby="addOutletModalLabel" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="addOutletModalLabel">Tambah Outlet Baru</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <form method="POST" action="">
                  <div class="form-group">
                    <label for="outlet_name">Nama Outlet</label>
                    <input type="text" class="form-control" id="outlet_name" name="outlet_name" placeholder="Masukkan nama outlet" required>
                  </div>
                  
                  <div class="form-group">
                    <label for="address">Alamat</label>
                    <textarea class="form-control" id="address" name="address" rows="3" placeholder="Masukkan alamat outlet" required></textarea>
                  </div>
                  
                  <div class="form-group">
                    <div class="custom-control custom-checkbox">
                      <input type="checkbox" class="custom-control-input" id="active" name="active">
                      <label class="custom-control-label" for="active">Aktifkan Outlet</label>
                    </div>
                  </div>
                  
                  <button type="submit" class="btn btn-primary">
                    <i class="ti-save mr-1"></i> Simpan Outlet
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
          
        <!-- Modal Edit Outlet -->
        <?php if ($editData): ?>
        <div class="modal fade" id="editOutletModal" tabindex="-1" role="dialog" aria-labelledby="editOutletModalLabel" aria-hidden="true" data-backdrop="static">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="editOutletModalLabel">Edit Outlet</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <form method="POST" action="" onsubmit="return submitForm(this)">
                  <input type="hidden" name="location_id" value="<?php echo $editData['id']; ?>">
                  
                  <div class="form-group">
                    <label for="edit_outlet_name">Nama Outlet</label>
                    <input type="text" class="form-control" id="edit_outlet_name" name="outlet_name" placeholder="Masukkan nama outlet" value="<?php echo $editData['outlet_name']; ?>" required>
                  </div>
                  
                  <div class="form-group">
                    <label for="edit_address">Alamat</label>
                    <textarea class="form-control" id="edit_address" name="address" rows="3" placeholder="Masukkan alamat outlet" required><?php echo $editData['address']; ?></textarea>
                  </div>
                  
                  <div class="form-group">
                    <div class="custom-control custom-checkbox">
                      <input type="checkbox" class="custom-control-input" id="edit_active" name="active" <?php echo $editData['active'] ? 'checked' : ''; ?>>
                      <label class="custom-control-label" for="edit_active">Aktifkan Outlet</label>
                    </div>
                  </div>
                  
                  <button type="submit" class="btn btn-primary">
                    <i class="ti-save mr-1"></i> Update Outlet
                  </button>
                  <a href="outlet_setting.php" class="btn btn-light">Batal</a>
                </form>
              </div>
            </div>
          </div>
        </div>
        <?php endif; ?>
          
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
  
  <script>
    // Tampilkan modal edit jika ada dan tutup setelah selesai edit
    <?php if ($editData): ?>
    $(document).ready(function() {
      $('#editOutletModal').modal('show');
    });
    
    function submitForm(form) {
      $.ajax({
        type: $(form).attr('method'),
        url: $(form).attr('action'),
        data: $(form).serialize(),
        success: function(response) {
          $('#editOutletModal').modal('hide');
          window.location.href = 'outlet_setting.php';
        },
        error: function() {
          alert('Terjadi kesalahan saat memperbarui outlet.');
        }
      });
      return false;
    }
    <?php endif; ?>
  </script>
</body>

</html>