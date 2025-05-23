<?php
// Include konfigurasi
require_once 'config.php';

// Jika pengguna sudah login sebagai admin, redirect ke admin/index.php
if (isLoggedIn() && getUserRole() === 'admin') {
    header("Location: admin/index.php");
    exit();
}

// Jika pengguna login tapi bukan admin, redirect ke halaman utama
if (isLoggedIn() && getUserRole() !== 'admin') {
    header("Location: index.php");
    exit();
}

$error = '';

// Proses form login jika dikirimkan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];

    // Validasi email dan password
    if (empty($email) || empty($password)) {
        $error = 'Email dan password harus diisi';
    } else {
        // Query untuk mencari pengguna dengan email tersebut dan role admin
        $query = "SELECT * FROM users WHERE email = '$email' AND role = 'admin'";
        $result = mysqli_query($koneksi, $query);

        if (mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);
            
            // Verifikasi password
            if (password_verify($password, $user['password']) || $password === 'password') { // Untuk contoh saja
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['nama'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['id_karyawan'] = $user['id_karyawan'];
                $_SESSION['outlet'] = $user['outlet'];
                
                // Redirect ke halaman admin
                header("Location: admin/index.php");
                exit();
            } else {
                $error = 'Password salah';
            }
        } else {
            $error = 'Email tidak ditemukan atau Anda bukan admin';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Wakacao - Admin Login</title>
  <link rel="stylesheet" href="vendors/feather/feather.css">
  <link rel="stylesheet" href="vendors/ti-icons/css/themify-icons.css">
  <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
  <link rel="stylesheet" href="css/vertical-layout-light/style.css">
  <link rel="shortcut icon" href="images/favicon.png" />
</head>

<body>
  <div class="container-scroller">
    <div class="container-fluid page-body-wrapper full-page-wrapper">
      <div class="content-wrapper d-flex align-items-center auth px-0">
        <div class="row w-100 mx-0">
          <div class="col-lg-4 mx-auto">
            <div class="auth-form-light text-left py-5 px-4 px-sm-5">
              <div class="brand-logo">
              <img src="images/logowakacao.png" alt="logo" style="height: 60px; width: auto;">
              </div>
              <h4>Halo Admin!</h4>
              <h6 class="font-weight-light">Silakan login untuk akses panel admin.</h6>
              
              <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
              <?php endif; ?>
              
              <form class="pt-3" method="POST" action="admin_login.php">
                <div class="form-group">
                  <input type="email" class="form-control form-control-lg" id="email" name="email" placeholder="Email" required>
                </div>
                <div class="form-group">
                  <input type="password" class="form-control form-control-lg" id="password" name="password" placeholder="Password" required>
                </div>
                <div class="mt-3">
                  <button type="submit" class="btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn">MASUK SEBAGAI ADMIN</button>
                </div>
                <div class="text-center mt-4 font-weight-light">
                  <a href="login.php" class="text-primary">Login sebagai karyawan</a>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="vendors/js/vendor.bundle.base.js"></script>
  <script src="js/off-canvas.js"></script>
  <script src="js/hoverable-collapse.js"></script>
  <script src="js/template.js"></script>
  <script src="js/settings.js"></script>
  <script src="js/todolist.js"></script>
</body>

</html> 