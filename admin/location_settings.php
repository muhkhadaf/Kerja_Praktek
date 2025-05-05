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

// Proses penyimpanan atau update lokasi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $outlet_name = sanitize($_POST['outlet_name']);
    $latitude = floatval(sanitize($_POST['latitude']));
    $longitude = floatval(sanitize($_POST['longitude']));
    $radius = (int)sanitize($_POST['radius']);
    $active = isset($_POST['active']) ? 1 : 0;
    $location_id = isset($_POST['location_id']) ? (int)$_POST['location_id'] : 0;
    
    // Validasi input
    if (empty($outlet_name) || empty($_POST['latitude']) || empty($_POST['longitude']) || empty($radius)) {
        $alert = "Semua field harus diisi dengan benar!";
        $alert_type = "danger";
    } else {
        // Cek apakah ini update atau insert baru
        if ($location_id > 0) {
            $query = "UPDATE locations SET 
                      outlet_name = '$outlet_name', 
                      latitude = '$latitude', 
                      longitude = '$longitude', 
                      radius = $radius, 
                      active = $active,
                      updated_at = NOW()
                      WHERE id = $location_id";
            
            if (mysqli_query($koneksi, $query)) {
                $alert = "Lokasi berhasil diperbarui!";
                $alert_type = "success";
            } else {
                $alert = "Error: " . mysqli_error($koneksi);
                $alert_type = "danger";
            }
        } else {
            // Insert lokasi baru
            $query = "INSERT INTO locations (outlet_name, latitude, longitude, radius, active, created_at) 
                      VALUES ('$outlet_name', '$latitude', '$longitude', $radius, $active, NOW())";
            
            if (mysqli_query($koneksi, $query)) {
                $alert = "Lokasi baru berhasil ditambahkan!";
                $alert_type = "success";
            } else {
                $alert = "Error: " . mysqli_error($koneksi);
                $alert_type = "danger";
            }
        }
    }
}

// Proses hapus lokasi
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $query = "DELETE FROM locations WHERE id = $id";
    
    if (mysqli_query($koneksi, $query)) {
        $alert = "Lokasi berhasil dihapus!";
        $alert_type = "success";
    } else {
        $alert = "Error: " . mysqli_error($koneksi);
        $alert_type = "danger";
    }
}

// Ambil semua lokasi dari database
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
  <title>Wakacao Admin - Pengaturan Lokasi Absensi</title>
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
  <!-- Google Maps API -->
  <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap" async defer></script>
  <style>
    #map {
      height: 400px;
      width: 100%;
      margin-bottom: 20px;
    }
    .radius-circle {
      border: 2px solid #007bff;
      background-color: rgba(0, 123, 255, 0.1);
      border-radius: 50%;
    }
  </style>
</head>

<body>
  <div class="container-scroller">
    <!-- partial:partials/_navbar.html -->
    <nav class="navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
      <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
        <a class="navbar-brand brand-logo mr-5" href="index.php"><img src="../images/logo.svg" class="mr-2" alt="logo"/></a>
        <a class="navbar-brand brand-logo-mini" href="index.php"><img src="../images/logo-mini.svg" alt="logo"/></a>
      </div>
      <div class="navbar-menu-wrapper d-flex align-items-center justify-content-end">
        <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
          <span class="icon-menu"></span>
        </button>
        <ul class="navbar-nav navbar-nav-right">
          <li class="nav-item nav-profile dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" id="profileDropdown">
              <img src="../images/faces/face28.jpg" alt="profile"/>
            </a>
            <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="profileDropdown">
              <a class="dropdown-item">
                <i class="ti-settings text-primary"></i>
                Pengaturan
              </a>
              <a class="dropdown-item" href="../logout.php">
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
    
      <?php include 'sidebar.php'; ?>
      
      <!-- partial -->
      <div class="main-panel">
        <div class="content-wrapper">
          <div class="row">
            <div class="col-md-12 grid-margin">
              <div class="row">
                <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                  <h3 class="font-weight-bold">Pengaturan Lokasi Absensi</h3>
                  <h6 class="font-weight-normal mb-0">Kelola data lokasi outlet untuk pencatatan informasi absensi</h6>
                </div>
                <div class="col-12 col-xl-4">
                  <div class="justify-content-end d-flex">
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addLocationModal">
                      <i class="ti-plus mr-1"></i> Tambah Lokasi Baru
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
                  <h4 class="card-title">Peta Lokasi Outlet</h4>
                  <p class="card-description">
                    Menampilkan semua lokasi outlet yang sudah didaftarkan
                  </p>
                  <div id="map"></div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
              <div class="card">
                <div class="card-body">
                  <h4 class="card-title">Daftar Lokasi Outlet</h4>
                  <div class="table-responsive">
                    <table class="table table-striped table-hover">
                      <thead>
                        <tr>
                          <th>No</th>
                          <th>Outlet</th>
                          <th>Latitude</th>
                          <th>Longitude</th>
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
                            <td><?php echo $location['latitude']; ?></td>
                            <td><?php echo $location['longitude']; ?></td>
                            <td>
                              <span class="badge <?php echo $location['active'] ? 'badge-success' : 'badge-danger'; ?>">
                                <?php echo $location['active'] ? 'Aktif' : 'Tidak Aktif'; ?>
                              </span>
                            </td>
                            <td>
                              <a href="?action=edit&id=<?php echo $location['id']; ?>" class="btn btn-sm btn-info">
                                <i class="ti-pencil"></i> Edit
                              </a>
                              <a href="?action=delete&id=<?php echo $location['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus lokasi ini?')">
                                <i class="ti-trash"></i> Hapus
                              </a>
                            </td>
                          </tr>
                          <?php endforeach; ?>
                        <?php else: ?>
                          <tr>
                            <td colspan="7" class="text-center">Belum ada data lokasi outlet</td>
                          </tr>
                        <?php endif; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Modal Tambah Lokasi -->
          <div class="modal fade" id="addLocationModal" tabindex="-1" role="dialog" aria-labelledby="addLocationModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="addLocationModalLabel">Tambah Lokasi Outlet Baru</h5>
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
                    
                    <div class="form-row">
                      <div class="form-group col-md-6">
                        <label for="latitude">Latitude</label>
                        <input type="text" class="form-control" id="latitude" name="latitude" placeholder="Contoh: -6.123456" required>
                      </div>
                      <div class="form-group col-md-6">
                        <label for="longitude">Longitude</label>
                        <input type="text" class="form-control" id="longitude" name="longitude" placeholder="Contoh: 106.123456" required>
                      </div>
                    </div>
                    
                    <div class="form-group">
                      <label for="radius">Radius (meter)</label>
                      <input type="hidden" id="radius" name="radius" value="100">
                    </div>
                    
                    <div class="form-group">
                      <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="active" name="active">
                        <label class="custom-control-label" for="active">Aktifkan Lokasi</label>
                      </div>
                    </div>
                    
                    <div class="form-group">
                      <label>Pilih Lokasi di Peta</label>
                      <div id="form-map" style="height: 300px; width: 100%;"></div>
                      <small class="form-text text-muted">Klik pada peta untuk memilih lokasi.</small>
                    </div>
                    <button type="submit" class="btn btn-primary">
                      <i class="ti-save mr-1"></i> Simpan Lokasi
                    </button>
                  </form>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Modal Edit Lokasi -->
          <?php if ($editData): ?>
          <div class="modal fade" id="editLocationModal" tabindex="-1" role="dialog" aria-labelledby="editLocationModalLabel" aria-hidden="true" data-backdrop="static">
            <div class="modal-dialog modal-lg" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="editLocationModalLabel">Edit Lokasi Outlet</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <form method="POST" action="">
                    <input type="hidden" name="location_id" value="<?php echo $editData['id']; ?>">
                    
                    <div class="form-group">
                      <label for="edit_outlet_name">Nama Outlet</label>
                      <input type="text" class="form-control" id="edit_outlet_name" name="outlet_name" placeholder="Masukkan nama outlet" value="<?php echo $editData['outlet_name']; ?>" required>
                    </div>
                    
                    <div class="form-row">
                      <div class="form-group col-md-6">
                        <label for="edit_latitude">Latitude</label>
                        <input type="text" class="form-control" id="edit_latitude" name="latitude" placeholder="Contoh: -6.123456" value="<?php echo $editData['latitude']; ?>" required>
                      </div>
                      <div class="form-group col-md-6">
                        <label for="edit_longitude">Longitude</label>
                        <input type="text" class="form-control" id="edit_longitude" name="longitude" placeholder="Contoh: 106.123456" value="<?php echo $editData['longitude']; ?>" required>
                      </div>
                    </div>
                    
                    <div class="form-group">
                      <label for="edit_radius">Radius (meter)</label>
                      <input type="hidden" id="edit_radius" name="radius" value="100">
                    </div>
                    
                    <div class="form-group">
                      <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="edit_active" name="active" <?php echo $editData['active'] ? 'checked' : ''; ?>>
                        <label class="custom-control-label" for="edit_active">Aktifkan Lokasi</label>
                      </div>
                    </div>
                    
                    <div class="form-group">
                      <label>Pilih Lokasi di Peta</label>
                      <div id="edit_form_map" style="height: 300px; width: 100%;"></div>
                      <small class="form-text text-muted">Klik pada peta untuk memilih lokasi.</small>
                    </div>
                    <button type="submit" class="btn btn-primary">
                      <i class="ti-save mr-1"></i> Update Lokasi
                    </button>
                    <a href="location_settings.php" class="btn btn-light">Batal</a>
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
  <!-- Google Maps API -->

  <script>
    // Data lokasi dari PHP
    const locations = <?php echo json_encode($locations); ?>;
    let map, formMap, editFormMap, marker, editMarker;
    
    // Fungsi inisialisasi peta
    function initMap() {
      // Peta utama
      map = new google.maps.Map(document.getElementById('map'), {
        center: { lat: -6.2088, lng: 106.8456 }, // Default Jakarta
        zoom: 11
      });
      
      // Tambahkan marker untuk setiap lokasi
      locations.forEach(location => {
        if (location.active === "1") {
          const position = { 
            lat: parseFloat(location.latitude), 
            lng: parseFloat(location.longitude) 
          };
          
          // Validasi koordinat
          if (isNaN(position.lat) || isNaN(position.lng)) {
            console.error('Invalid coordinates for location:', location.outlet_name);
            return;
          }
          
          const marker = new google.maps.Marker({
            position: position,
            map: map,
            title: location.outlet_name
          });
          
          const infoWindow = new google.maps.InfoWindow({
            content: `<strong>${location.outlet_name}</strong>`
          });
          
          marker.addListener('click', () => {
            infoWindow.open(map, marker);
          });
        }
      });
      
      // Peta untuk form tambah
      const formMapElement = document.getElementById('form-map');
      if (formMapElement) {
        formMap = new google.maps.Map(formMapElement, {
          center: { lat: -6.2088, lng: 106.8456 }, // Default Jakarta
          zoom: 13
        });
        
        // Tambahkan marker ketika user klik pada peta
        formMap.addListener('click', function(event) {
          placeMarker(event.latLng, formMap, 'add');
        });
      }
      
      // Peta untuk form edit
      const editFormMapElement = document.getElementById('edit_form_map');
      if (editFormMapElement) {
        <?php if ($editData): ?>
        const editPosition = { 
          lat: parseFloat('<?php echo $editData['latitude']; ?>'), 
          lng: parseFloat('<?php echo $editData['longitude']; ?>') 
        };
        
        // Validasi koordinat
        if (isNaN(editPosition.lat) || isNaN(editPosition.lng)) {
          editPosition.lat = -6.2088;
          editPosition.lng = 106.8456;
          console.error('Invalid edit coordinates, using default.');
        }
        
        editFormMap = new google.maps.Map(editFormMapElement, {
          center: editPosition,
          zoom: 13
        });
        
        editMarker = new google.maps.Marker({
          position: editPosition,
          map: editFormMap,
          draggable: true
        });
        
        // Update koordinat saat marker di-drag
        editMarker.addListener('dragend', function() {
          updateEditCoordinates();
        });
        
        // Tambahkan marker ketika user klik pada peta
        editFormMap.addListener('click', function(event) {
          placeMarker(event.latLng, editFormMap, 'edit');
        });
        <?php endif; ?>
      }
    }
    
    // Fungsi untuk menempatkan marker di peta form
    function placeMarker(location, targetMap, mode) {
      if (mode === 'add') {
        if (marker) {
          marker.setPosition(location);
        } else {
          marker = new google.maps.Marker({
            position: location,
            map: targetMap,
            draggable: true
          });
          
          // Update koordinat saat marker di-drag
          marker.addListener('dragend', updateCoordinates);
        }
        
        updateCoordinates();
      } else if (mode === 'edit') {
        if (editMarker) {
          editMarker.setPosition(location);
        } else {
          editMarker = new google.maps.Marker({
            position: location,
            map: targetMap,
            draggable: true
          });
          
          // Update koordinat saat marker di-drag
          editMarker.addListener('dragend', updateEditCoordinates);
        }
        
        updateEditCoordinates();
      }
    }
    
    // Update nilai input latitude longitude untuk form tambah
    function updateCoordinates() {
      if (marker) {
        const position = marker.getPosition();
        // Pastikan nilai latitude dan longitude selalu string dengan 6 angka dibelakang koma
        const lat = position.lat().toFixed(6);
        const lng = position.lng().toFixed(6);
        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;
      }
    }
    
    // Update nilai input latitude longitude untuk form edit
    function updateEditCoordinates() {
      if (editMarker) {
        const position = editMarker.getPosition();
        // Pastikan nilai latitude dan longitude selalu string dengan 6 angka dibelakang koma
        const lat = position.lat().toFixed(6);
        const lng = position.lng().toFixed(6);
        document.getElementById('edit_latitude').value = lat;
        document.getElementById('edit_longitude').value = lng;
      }
    }
    
    // Tampilkan modal edit jika ada
    <?php if ($editData): ?>
    $(document).ready(function() {
      $('#editLocationModal').modal('show');
    });
    <?php endif; ?>
  </script>
</body>

</html> 