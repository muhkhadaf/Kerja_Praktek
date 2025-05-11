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

// Cek apakah tabel jadwal_updates sudah ada, jika belum buat
$check_table = mysqli_query($koneksi, "SHOW TABLES LIKE 'jadwal_updates'");
if (mysqli_num_rows($check_table) === 0) {
    // Buat tabel jadwal_updates
    $create_table = "CREATE TABLE `jadwal_updates` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `outlet` varchar(100) NOT NULL,
        `admin_id` varchar(50) NOT NULL,
        `admin_name` varchar(100) NOT NULL,
        `update_time` datetime NOT NULL,
        `week_start` date NOT NULL,
        `week_end` date NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `outlet_week` (`outlet`, `week_start`, `week_end`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    mysqli_query($koneksi, $create_table);
    
    if (mysqli_error($koneksi)) {
        error_log("Error creating jadwal_updates table: " . mysqli_error($koneksi));
    } else {
        error_log("Successfully created jadwal_updates table");
    }
}

// Filter cabang/outlet
$selectedOutlet = isset($_GET['outlet']) ? sanitize($_GET['outlet']) : '';

// Ambil semua outlet/cabang
$query_outlets = "SELECT DISTINCT outlet_name FROM locations ORDER BY outlet_name";
$result_outlets = mysqli_query($koneksi, $query_outlets);
$outlets = [];
if ($result_outlets) {
    while ($row = mysqli_fetch_assoc($result_outlets)) {
        $outlets[] = $row['outlet_name'];
    }
}

// Mengambil semua data karyawan
$query_karyawan = "SELECT id_karyawan, nama, outlet FROM users WHERE role = 'karyawan'";
// Tambahkan filter cabang jika dipilih
if (!empty($selectedOutlet)) {
    $query_karyawan .= " AND outlet = '$selectedOutlet'";
}
$query_karyawan .= " ORDER BY nama";

$result_karyawan = mysqli_query($koneksi, $query_karyawan);
$karyawan_list = [];
if ($result_karyawan) {
    while ($row = mysqli_fetch_assoc($result_karyawan)) {
        $karyawan_list[] = $row;
    }
}

// Mengambil semua data shift
$query_shift = "SELECT id, nama_shift, jam_mulai, jam_selesai FROM shift ORDER BY jam_mulai";
$result_shift = mysqli_query($koneksi, $query_shift);
$shift_list = [];
if ($result_shift) {
    while ($row = mysqli_fetch_assoc($result_shift)) {
        $shift_list[] = $row;
    }
}

// Mendapatkan tanggal hari ini dan tanggal awal minggu ini (Senin)
$today = new DateTime();
$weekStart = clone $today;
$weekStart->modify('this week monday');

// Jika ada parameter tanggal
if (isset($_GET['week_start'])) {
    $weekStart = new DateTime($_GET['week_start']);
}

// Tanggal awal dan akhir minggu
$weekStartStr = $weekStart->format('Y-m-d');
$weekEnd = clone $weekStart;
$weekEnd->modify('+6 days');
$weekEndStr = $weekEnd->format('Y-m-d');

// Proses penyimpanan jadwal karyawan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_schedule') {
    // Cek apakah tabel jadwal_updates sudah ada, jika belum buat
    $check_table = mysqli_query($koneksi, "SHOW TABLES LIKE 'jadwal_updates'");
    if (mysqli_num_rows($check_table) === 0) {
        // Buat tabel jadwal_updates
        $create_table = "CREATE TABLE `jadwal_updates` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `outlet` varchar(100) NOT NULL,
            `admin_id` varchar(50) NOT NULL,
            `admin_name` varchar(100) NOT NULL,
            `update_time` datetime NOT NULL,
            `week_start` date NOT NULL,
            `week_end` date NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `outlet_week` (`outlet`, `week_start`, `week_end`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        mysqli_query($koneksi, $create_table);
        
        if (mysqli_error($koneksi)) {
            error_log("Error creating jadwal_updates table: " . mysqli_error($koneksi));
        } else {
            error_log("Successfully created jadwal_updates table");
        }
    }

    // Tentukan outlet yang akan diproses
    $target_outlet = isset($_POST['save_outlet']) ? $_POST['save_outlet'] : '';
    
    // Debug log untuk membantu troubleshooting
    error_log("Processing schedule save for outlet: " . $target_outlet);
    error_log("POST data: " . json_encode(array_keys($_POST)));
    
    // Validasi: Pastikan outlet ada
    if (empty($target_outlet)) {
        $alert = 'Error: Outlet tidak ditemukan!';
        $alert_type = 'danger';
        error_log("Error: No target outlet specified in POST data");
    } else {
        // Mendapatkan id_karyawan yang akan diproses
        $id_karyawan_values = array_keys($_POST['jadwal']);
        
        // Debug log untuk data jadwal
        error_log("Karyawan IDs in POST: " . json_encode($id_karyawan_values));
        
        // Filter array id_karyawan untuk outlet yang dipilih saja
        $id_karyawan_filtered = [];
        foreach ($id_karyawan_values as $id_karyawan) {
            // Cek apakah karyawan ini ada di outlet yang dipilih
            $query_check = "SELECT outlet FROM users WHERE id_karyawan = '$id_karyawan' AND outlet = '$target_outlet'";
            $result_check = mysqli_query($koneksi, $query_check);
            if (mysqli_num_rows($result_check) > 0) {
                $id_karyawan_filtered[] = $id_karyawan;
            }
        }
        $id_karyawan_values = $id_karyawan_filtered;
        
        // Debug log untuk karyawan yang difilter
        error_log("Filtered karyawan IDs for outlet $target_outlet: " . json_encode($id_karyawan_values));
        
        // Jika tidak ada karyawan yang diproses, tampilkan pesan
        if (empty($id_karyawan_values)) {
            $alert = 'Tidak ada jadwal yang diperbarui untuk outlet ' . $target_outlet . '!';
            $alert_type = 'warning';
        } else {
            // Membuat string ID karyawan untuk query
            $id_karyawan_list = "'" . implode("','", $id_karyawan_values) . "'";
            
            // Validasi data jadwal
            $invalid_data = false;
            foreach ($id_karyawan_values as $id_karyawan) {
                if (isset($_POST['jadwal'][$id_karyawan])) {
                    foreach ($_POST['jadwal'][$id_karyawan] as $day => $shift_data) {
                        if (empty($shift_data['status'])) {
                            $invalid_data = true;
                            break 2;
                        }
                    }
                }
            }
            
            if ($invalid_data) {
                $alert = 'Ada data jadwal yang belum lengkap di outlet ' . $target_outlet . '!';
                $alert_type = 'danger';
            } else {
                // Hapus jadwal lama untuk karyawan yang dipilih
                $query_delete = "DELETE FROM jadwal WHERE tanggal BETWEEN '$weekStartStr' AND '$weekEndStr' AND id_karyawan IN ($id_karyawan_list)";
                mysqli_query($koneksi, $query_delete);
                
                // Loop untuk semua hari dalam seminggu dan semua karyawan yang dipilih
                foreach ($id_karyawan_values as $id_karyawan) {
                    if (isset($_POST['jadwal'][$id_karyawan])) {
                        foreach ($_POST['jadwal'][$id_karyawan] as $day => $shift_data) {
                            // Pastikan menggunakan day_index jika tersedia
                            $day_index = isset($shift_data['day_index']) ? (int)$shift_data['day_index'] : (int)$day;
                            
                            // Hari ke-0 = Senin, hari ke-6 = Minggu
                            // Konversi ke tanggal dengan menambahkan jumlah hari dari tanggal awal minggu
                            $tanggal = date('Y-m-d', strtotime($weekStartStr . ' + ' . $day_index . ' days'));
                            
                            // Double check format tanggal
                            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
                                error_log("Invalid date format: $tanggal for day $day_index from $weekStartStr");
                                continue; // Skip this iteration
                            }
                            
                            $status = $shift_data['status'];
                            
                            // Perbaikan: hanya gunakan id_shift jika status "masuk"
                            if ($status === 'masuk') {
                                // Jika shift tidak dipilih, gunakan default berdasarkan hari
                                if (empty($shift_data['shift'])) {
                                    // Tentukan waktu sekarang untuk bandingkan
                                    $waktu_sekarang = date('H:i:s');
                                    
                                    // Default: jika pagi (07:00-15:00) gunakan shift 1, 
                                    // jika siang (15:00-23:00) gunakan shift 2, 
                                    // jika malam (23:00-07:00) gunakan shift 3
                                    if ($waktu_sekarang >= '07:00:00' && $waktu_sekarang < '15:00:00') {
                                        $id_shift = 1; // Shift pagi
                                    } elseif ($waktu_sekarang >= '15:00:00' && $waktu_sekarang < '23:00:00') {
                                        $id_shift = 2; // Shift siang
                                    } else {
                                        $id_shift = 3; // Shift malam
                                    }
                                } else {
                                    $id_shift = (int)$shift_data['shift'];
                                }
                            } else {
                                $id_shift = NULL;
                            }
                            
                            $query_insert = "INSERT INTO jadwal (id_karyawan, tanggal, id_shift, status) 
                                           VALUES ('$id_karyawan', '$tanggal', " . ($id_shift ? $id_shift : "NULL") . ", '$status')";
                            
                            // Debug log untuk query
                            error_log("Inserting jadwal: ID Karyawan=$id_karyawan, Tanggal=$tanggal, Day=$day_index, Status=$status");
                            
                            mysqli_query($koneksi, $query_insert);
                            
                            // Jika terjadi error pada query, catat dalam alert
                            if (mysqli_error($koneksi)) {
                                error_log("Error inserting schedule: " . mysqli_error($koneksi) . " for query: " . $query_insert);
                            }
                        }
                    }
                }
                
                // Simpan informasi admin yang mengupdate jadwal
                $admin_id = $_SESSION['user_id'] ?? 0;
                $admin_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : (isset($_SESSION['username']) ? $_SESSION['username'] : 'Admin');
                $update_time = date('Y-m-d H:i:s');
                
                // Debug informasi session
                error_log("SESSION data for admin update: " . json_encode($_SESSION));
                error_log("Admin ID: $admin_id, Admin Name: $admin_name");
                
                // Update atau insert informasi terakhir update
                $query_update_info = "INSERT INTO jadwal_updates (outlet, admin_id, admin_name, update_time, week_start, week_end) 
                                    VALUES ('$target_outlet', '$admin_id', '$admin_name', '$update_time', '$weekStartStr', '$weekEndStr')
                                    ON DUPLICATE KEY UPDATE 
                                    admin_id = '$admin_id', 
                                    admin_name = '$admin_name', 
                                    update_time = '$update_time'";
                
                $result_update_info = mysqli_query($koneksi, $query_update_info);
                if (!$result_update_info) {
                    error_log("Error updating jadwal_updates: " . mysqli_error($koneksi) . " for query: " . $query_update_info);
                }
                
                $alert = "Jadwal outlet $target_outlet berhasil disimpan!";
                $alert_type = 'success';
            }
        }
    }
}

// Ambil jadwal karyawan untuk minggu ini
$jadwal_karyawan = [];
$query_jadwal = "SELECT j.*, s.nama_shift, s.jam_mulai, s.jam_selesai 
                FROM jadwal j 
                LEFT JOIN shift s ON j.id_shift = s.id 
                WHERE j.tanggal BETWEEN '$weekStartStr' AND '$weekEndStr'";

// Jika ada filter karyawan berdasarkan outlet
if (!empty($selectedOutlet)) {
    $query_jadwal .= " AND j.id_karyawan IN (SELECT id_karyawan FROM users WHERE outlet = '$selectedOutlet')";
}

$result_jadwal = mysqli_query($koneksi, $query_jadwal);

if ($result_jadwal) {
    while ($row = mysqli_fetch_assoc($result_jadwal)) {
        // Hitung nomor hari (index 0-6 untuk Senin-Minggu)
        $dayOfWeek = (int)date('N', strtotime($row['tanggal'])) - 1; // N = 1 (Senin) s/d 7 (Minggu)
        
        $jadwal_karyawan[$row['id_karyawan']][$dayOfWeek] = [
            'id_shift' => $row['id_shift'],
            'nama_shift' => $row['nama_shift'],
            'jam_mulai' => $row['jam_mulai'],
            'jam_selesai' => $row['jam_selesai'],
            'status' => $row['status']
        ];
    }
}

// Ambil informasi update terakhir untuk setiap outlet
$last_updates = [];
$query_last_updates = "SELECT outlet, admin_name, update_time FROM jadwal_updates WHERE week_start = '$weekStartStr' AND week_end = '$weekEndStr'";
$result_last_updates = mysqli_query($koneksi, $query_last_updates);

// Debug informasi query
error_log("Last updates query: " . $query_last_updates);
if (!$result_last_updates) {
    error_log("Error querying last updates: " . mysqli_error($koneksi));
}

if ($result_last_updates && mysqli_num_rows($result_last_updates) > 0) {
    while ($row = mysqli_fetch_assoc($result_last_updates)) {
        $last_updates[$row['outlet']] = [
            'admin_name' => $row['admin_name'],
            'update_time' => $row['update_time']
        ];
    }
    // Debug isi last_updates
    error_log("Last updates data: " . json_encode($last_updates));
} else {
    error_log("No last updates found for week: $weekStartStr to $weekEndStr");
}

// Mendapatkan minggu sebelumnya dan minggu berikutnya
$prevWeek = clone $weekStart;
$prevWeek->modify('-1 week');
$nextWeek = clone $weekStart;
$nextWeek->modify('+1 week');

// Nama-nama hari
$dayNames = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Wakacao Admin - Kelola Jadwal</title>
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
  <style>
    .schedule-table th, .schedule-table td {
      text-align: center;
      vertical-align: middle;
    }
    .shift-cell {
      position: relative;
    }
    .shift-badge {
      font-size: 0.7rem;
      padding: 0.2rem 0.5rem;
      margin-top: 3px;
    }
    .week-navigator {
      font-size: 1.2rem;
      font-weight: 500;
    }
    .week-navigator a {
      padding: 0 10px;
    }
    
    /* Responsif untuk mobile */
    @media (max-width: 767px) {
      .schedule-table {
        display: block;
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
      }
      
      .schedule-table th, 
      .schedule-table td {
        min-width: 100px;
        white-space: nowrap;
      }
      
      .shift-cell {
        padding: 10px 5px !important;
      }
      
      .shift-cell select {
        width: 100%;
        max-width: 150px; /* Perbesar lebar dropdown */
        font-size: 13px; /* Perbesar ukuran font */
        padding: 0.35rem 0.5rem;
        height: auto;
        font-weight: normal;
        color: #000;
        background-color: #fff;
        margin-bottom: 8px !important; /* Tambah jarak antar dropdown */
      }
      
      /* Membuat text dalam dropdown terlihat lebih jelas */
      .shift-cell select option {
        font-size: 14px;
        padding: 8px;
      }
      
      /* Ganti background dropdown untuk kontras lebih baik */
      .shift-cell select:focus {
        background-color: #f8f9fa; 
        border-color: #4B49AC;
      }
      
      /* Styling ketika dropdown aktif/focus di mobile */
      .dropdown-active, select.dropdown-active {
        border: 2px solid #4B49AC !important;
        box-shadow: 0 0 5px rgba(75, 73, 172, 0.5) !important;
        background-color: #f0f0ff !important;
        z-index: 9999;
        position: relative;
      }
      
      /* Penanda visual validasi */
      .is-invalid {
        border: 2px solid #dc3545 !important;
        background-color: #fff8f8 !important;
      }
      
      /* Highlight outlet yang sedang disimpan */
      .saving-outlet {
        background-color: #e8f4ff;
        border-left: 4px solid #4B49AC;
        padding-left: 10px;
      }
      
      /* Style form outlet */
      .outlet-form {
        margin-bottom: 30px;
        padding: 0;
        position: relative;
      }
      
      .outlet-form.saving-outlet {
        padding: 10px;
        border-radius: 5px;
        transition: all 0.3s ease;
      }
      
      /* Khusus perangkat mobile */
      .mobile-device .mobile-select {
        -webkit-appearance: menulist; /* Tampilkan seperti dropdown native */
        appearance: menulist;
        font-size: 16px !important; /* Font di atas 16px mencegah zoom otomatis di iOS */
      }
      
      .table-responsive {
        overflow-y: hidden;
        padding-bottom: 15px;
      }
      
      /* Tombol simpan jadwal responsif */
      .text-right.mb-5 {
        margin-bottom: 3rem !important;
      }
      
      .btn-lg {
        padding: 0.5rem 1rem;
        font-size: 1rem;
      }
      
      /* Membuat tombol full width di mobile */
      .btn-block {
        display: block;
        width: 100%;
      }
      
      /* Penyesuaian dropdown untuk layar kecil */
      select.form-control-sm {
        height: auto;
        padding-top: 0.4rem;
        padding-bottom: 0.4rem;
        line-height: 1.4;
      }
      
      /* Tambahkan jarak pada elemen td untuk text yang lebih jelas */
      .schedule-table td:first-child {
        font-weight: bold;
        min-width: 120px;
        background-color: #f8f8f8;
      }
    }
    
    /* Untuk tampilan tablet ke atas */
    @media (min-width: 768px) {
      .btn-md-inline-block {
        display: inline-block;
        width: auto;
      }
    }
  </style>
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
                  <h3 class="font-weight-bold">Kelola Jadwal Shift Karyawan</h3>
                  <h6 class="font-weight-normal mb-0">Atur jadwal shift mingguan untuk seluruh karyawan</h6>
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
          
          <!-- Filter Outlet dan Navigasi Tanggal -->
          <div class="row mb-4">
            <div class="col-md-6 mb-3 mb-md-0">
              <div class="card">
                <div class="card-body">
                  <h4 class="card-title">Filter Outlet</h4>
                  <form method="GET" action="" class="d-flex align-items-center flex-wrap">
                    <input type="hidden" name="week_start" value="<?php echo $weekStartStr; ?>">
                    <div class="form-group mb-2 mb-md-0 mr-3 flex-grow-1">
                      <select class="form-control" name="outlet" id="outlet-filter">
                        <option value="">Semua Outlet</option>
                        <?php foreach ($outlets as $outlet): ?>
                        <option value="<?php echo $outlet; ?>" <?php echo ($selectedOutlet === $outlet) ? 'selected' : ''; ?>>
                          <?php echo $outlet; ?>
                        </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <button type="submit" class="btn btn-primary">
                      <i class="ti-filter mr-1"></i> Filter
                    </button>
                  </form>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="card">
                <div class="card-body">
                  <h4 class="card-title">Periode Jadwal</h4>
                  <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <a href="?week_start=<?php echo $prevWeek->format('Y-m-d'); ?><?php echo !empty($selectedOutlet) ? '&outlet=' . $selectedOutlet : ''; ?>" class="btn btn-outline-secondary mb-2 mb-md-0">
                      <i class="ti-arrow-left mr-1"></i> <span class="d-none d-sm-inline">Minggu Sebelumnya</span><span class="d-sm-none">Sebelumnya</span>
                    </a>
                    <div class="text-center font-weight-bold my-2">
                      <?php echo date('d M Y', strtotime($weekStartStr)); ?> - <?php echo date('d M Y', strtotime($weekEndStr)); ?>
                    </div>
                    <a href="?week_start=<?php echo $nextWeek->format('Y-m-d'); ?><?php echo !empty($selectedOutlet) ? '&outlet=' . $selectedOutlet : ''; ?>" class="btn btn-outline-secondary">
                      <span class="d-none d-sm-inline">Minggu Berikutnya</span><span class="d-sm-none">Berikutnya</span> <i class="ti-arrow-right ml-1"></i>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
              <div class="card">
                <div class="card-body">
                  <h4 class="card-title">Jadwal Shift Mingguan <?php echo !empty($selectedOutlet) ? 'Outlet ' . $selectedOutlet : 'Semua Outlet'; ?></h4>
                  
                  <div class="alert alert-info mb-4">
                    <i class="ti-info-alt mr-2"></i> Perubahan jadwal <strong>hanya akan disimpan</strong> setelah Anda mengklik tombol <strong>Simpan Jadwal</strong> di bawah tabel masing-masing outlet.
                    <br><small class="mt-1 d-block">Anda dapat menyimpan jadwal untuk satu outlet saja tanpa perlu mengisi semua jadwal di outlet lain.</small>
                  </div>
                  
                  <?php if (count($karyawan_list) > 0): ?>
                  
                  <?php
                  // Kelompokkan karyawan berdasarkan outlet
                  $karyawan_by_outlet = [];
                  foreach ($karyawan_list as $karyawan) {
                      $outlet = $karyawan['outlet'];
                      if (!isset($karyawan_by_outlet[$outlet])) {
                          $karyawan_by_outlet[$outlet] = [];
                      }
                      $karyawan_by_outlet[$outlet][] = $karyawan;
                  }

                  // Tampilkan tabel untuk setiap outlet
                  foreach ($karyawan_by_outlet as $outlet_name => $outlet_karyawan):
                  ?>
                  
                  <h5 class="mt-4 mb-3 font-weight-bold">Outlet: <?php echo $outlet_name; ?></h5>
                  
                  <?php if (isset($last_updates[$outlet_name])): ?>
                  <div class="mb-3">
                    <div class="p-2 bg-light border-left border-primary" style="border-left-width: 3px !important;">
                      <small>
                        <i class="ti-reload mr-1"></i> Terakhir diperbarui oleh: <strong><?php echo htmlspecialchars($last_updates[$outlet_name]['admin_name']); ?></strong> 
                        pada <strong><?php echo date('d/m/Y H:i', strtotime($last_updates[$outlet_name]['update_time'])); ?></strong>
                      </small>
                    </div>
                  </div>
                  <?php else: ?>
                  <div class="mb-3">
                    <div class="p-2 bg-light border-left border-warning" style="border-left-width: 3px !important;">
                      <small><i class="ti-alert mr-1"></i> Belum ada pembaruan jadwal untuk minggu ini</small>
                    </div>
                  </div>
                  <?php endif; ?>
                  
                  <!-- Form terpisah untuk setiap outlet -->
                  <form method="POST" action="" class="outlet-form">
                    <input type="hidden" name="action" value="save_schedule">
                    <input type="hidden" name="save_outlet" value="<?php echo $outlet_name; ?>">
                    
                    <div class="table-responsive mb-4">
                      <table class="table table-bordered schedule-table">
                        <thead>
                          <tr>
                            <th>Karyawan</th>
                            <?php
                            for ($i = 0; $i < 7; $i++) {
                                $currentDate = clone $weekStart;
                                $currentDate->modify("+$i days");
                                echo '<th class="d-sm-table-cell">' . $dayNames[$i] . '<br><small class="d-none d-sm-inline">' . $currentDate->format('d/m') . '</small><small class="d-sm-none">' . $currentDate->format('d/m') . '</small></th>';
                            }
                            ?>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach ($outlet_karyawan as $karyawan): ?>
                          <tr>
                            <td class="font-weight-bold">
                              <?php echo $karyawan['nama']; ?>
                            </td>
                            <?php for ($i = 0; $i < 7; $i++): ?>
                            <td class="shift-cell">
                              <select name="jadwal[<?php echo $karyawan['id_karyawan']; ?>][<?php echo $i; ?>][shift]" class="form-control form-control-sm select-shift mb-2">
                                <option value="">Pilih Shift</option>
                                <?php foreach ($shift_list as $shift): ?>
                                <option value="<?php echo $shift['id']; ?>" <?php 
                                  echo isset($jadwal_karyawan[$karyawan['id_karyawan']][$i]) && 
                                       $jadwal_karyawan[$karyawan['id_karyawan']][$i]['id_shift'] == $shift['id'] ? 'selected' : ''; 
                                ?>>
                                  <?php echo $shift['nama_shift']; ?> (<?php echo substr($shift['jam_mulai'], 0, 5); ?>-<?php echo substr($shift['jam_selesai'], 0, 5); ?>)
                                </option>
                                <?php endforeach; ?>
                              </select>
                              <select name="jadwal[<?php echo $karyawan['id_karyawan']; ?>][<?php echo $i; ?>][status]" class="form-control form-control-sm select-status">
                                <option value="">Pilih Status</option>
                                <option value="masuk" <?php 
                                  echo isset($jadwal_karyawan[$karyawan['id_karyawan']][$i]) && 
                                       $jadwal_karyawan[$karyawan['id_karyawan']][$i]['status'] == 'masuk' ? 'selected' : ''; 
                                ?>>Masuk</option>
                                <option value="libur" <?php 
                                  echo isset($jadwal_karyawan[$karyawan['id_karyawan']][$i]) && 
                                       $jadwal_karyawan[$karyawan['id_karyawan']][$i]['status'] == 'libur' ? 'selected' : ''; 
                                ?>>Libur</option>
                                <option value="izin" <?php 
                                  echo isset($jadwal_karyawan[$karyawan['id_karyawan']][$i]) && 
                                       $jadwal_karyawan[$karyawan['id_karyawan']][$i]['status'] == 'izin' ? 'selected' : ''; 
                                ?>>Izin</option>
                                <option value="sakit" <?php 
                                  echo isset($jadwal_karyawan[$karyawan['id_karyawan']][$i]) && 
                                       $jadwal_karyawan[$karyawan['id_karyawan']][$i]['status'] == 'sakit' ? 'selected' : ''; 
                                ?>>Sakit</option>
                                <option value="cuti" <?php 
                                  echo isset($jadwal_karyawan[$karyawan['id_karyawan']][$i]) && 
                                       $jadwal_karyawan[$karyawan['id_karyawan']][$i]['status'] == 'cuti' ? 'selected' : ''; 
                                ?>>Cuti</option>
                              </select>
                              <!-- Tambahkan input hidden untuk memastikan index hari konsisten -->
                              <input type="hidden" name="jadwal[<?php echo $karyawan['id_karyawan']; ?>][<?php echo $i; ?>][day_index]" value="<?php echo $i; ?>">
                            </td>
                            <?php endfor; ?>
                          </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
                    
                    <div class="text-right mb-5">
                      <button type="submit" class="btn btn-primary btn-lg btn-block btn-md-inline-block">
                        <i class="ti-save mr-1"></i> Simpan Jadwal <?php echo $outlet_name; ?>
                      </button>
                      <div class="text-muted mt-2">
                        <small><i class="ti-info-alt mr-1"></i> Jadwal outlet <?php echo $outlet_name; ?> akan diperbarui saat Anda menekan tombol ini</small>
                      </div>
                    </div>
                  </form>
                  <?php endforeach; ?>
                  
                  <?php else: ?>
                  <div class="alert alert-info">
                    <?php echo !empty($selectedOutlet) ? 'Tidak ada karyawan di outlet ' . $selectedOutlet : 'Tidak ada karyawan yang tersedia'; ?>
                  </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
          
          <div class="row mt-4">
            <div class="col-md-12">
              <div class="card">
                <div class="card-body">
                  <h4 class="card-title">Panduan Status</h4>
                  <div class="row">
                    <div class="col-md-2 col-sm-4 mb-3">
                      <div class="p-2 bg-white border">
                        <strong>Masuk</strong>: Karyawan masuk sesuai jadwal shift
                      </div>
                    </div>
                    <div class="col-md-2 col-sm-4 mb-3">
                      <div class="p-2 bg-light border">
                        <strong>Libur</strong>: Karyawan libur kerja
                      </div>
                    </div>
                    <div class="col-md-2 col-sm-4 mb-3">
                      <div class="p-2 bg-info text-white border">
                        <strong>Izin</strong>: Karyawan izin tidak masuk
                      </div>
                    </div>
                    <div class="col-md-2 col-sm-4 mb-3">
                      <div class="p-2 bg-warning text-dark border">
                        <strong>Sakit</strong>: Karyawan sakit
                      </div>
                    </div>
                    <div class="col-md-2 col-sm-4 mb-3">
                      <div class="p-2 bg-success text-white border">
                        <strong>Cuti</strong>: Karyawan cuti
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
  <script src="../js/dashboard.js"></script>
  <!-- End custom js for this page-->
  
  <script>
    $(document).ready(function() {
      // Script untuk menangani perubahan status jadwal
      $('select[name$="[status]"]').on('change', function() {
        const status = $(this).val();
        const shiftSelect = $(this).prev('select');
        
        // Hanya mengubah state enabled/disabled pada shift dropdown
        // tanpa langsung memperbarui database
        if (status !== 'masuk' || status === '') {
          shiftSelect.prop('disabled', true);
          shiftSelect.val('');
        } else {
          shiftSelect.prop('disabled', false);
        }
      });
      
      // Set initial state
      $('select[name$="[status]"]').trigger('change');
      
      // Tambahan: validasi form saat disubmit untuk masing-masing outlet
      $('.outlet-form').on('submit', function(e) {
        // Reset class invalid dari semua dropdown dalam form ini
        $(this).find('select.is-invalid').removeClass('is-invalid');
        
        // Highlight container outlet yang sedang disimpan
        $(this).addClass('saving-outlet');
        
        let isValid = true;
        const outletName = $(this).find('input[name="save_outlet"]').val();
        
        // Validasi seluruh dropdown status dalam form outlet ini
        $(this).find('select[name$="[status]"]').each(function() {
          if ($(this).val() === '') {
            isValid = false;
            $(this).addClass('is-invalid');
          }
        });
        
        if (!isValid) {
          e.preventDefault();
          alert('Harap pilih status untuk semua jadwal karyawan di outlet ' + outletName + '!');
          
          // Scroll ke dropdown invalid pertama
          $('html, body').animate({
            scrollTop: $(this).find('select.is-invalid').first().offset().top - 100
          }, 500);
          
          // Hapus highlight saving
          $(this).removeClass('saving-outlet');
          return false;
        }
        
        // Pastikan select status dan shift tidak disabled saat submit
        $(this).find('select:disabled').prop('disabled', false);
        
        // Tambahkan konfirmasi sebelum submit
        const confirmMessage = `Apakah Anda yakin ingin menyimpan jadwal outlet ${outletName}?`;
        
        if (!confirm(confirmMessage)) {
          e.preventDefault();
          $(this).removeClass('saving-outlet');
          return false;
        }
        
        // Tambahkan debug log ke console
        console.log(`Submitting form for outlet: ${outletName}`);
        return true;
      });
      
      // Perbaikan untuk mobile: menambahkan class active saat dropdown dibuka
      $('.select-shift, .select-status').on('focus', function() {
        $(this).addClass('dropdown-active');
      }).on('blur', function() {
        $(this).removeClass('dropdown-active');
      });
      
      // Deteksi jika perangkat mobile, tambahkan class untuk style khusus
      if(/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
        $('body').addClass('mobile-device');
        $('.select-shift, .select-status').addClass('mobile-select');
      }
    });
  </script>
</body>

</html> 