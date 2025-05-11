<!-- partial -->
<div class="container-fluid page-body-wrapper">
      <!-- partial:partials/_sidebar.html -->
      <nav class="sidebar sidebar-offcanvas" id="sidebar">
        <ul class="nav">
          <li class="nav-item">
            <a class="nav-link" href="index.php">
              <i class="icon-grid menu-icon"></i>
              <span class="menu-title">Dashboard</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" data-toggle="collapse" href="#absensi" aria-expanded="false" aria-controls="absensi">
              <i class="icon-head menu-icon"></i>
              <span class="menu-title">Manajemen Absensi</span>
              <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="absensi">
              <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="attendance_list.php"> Daftar Absensi</a></li>
                <li class="nav-item"> <a class="nav-link" href="manage_schedule.php"> Kelola Jadwal </a></li>
                <li class="nav-item"> <a class="nav-link" href="manage_shift.php"> Kelola Shift </a></li>
                <li class="nav-item"> <a class="nav-link" href="admin_izin.php"> Kelola Izin </a></li>
              </ul>
            </div>
          </li>
          <li class="nav-item">
            <a class="nav-link" data-toggle="collapse" href="#karyawan" aria-expanded="false" aria-controls="karyawan">
              <i class="icon-head menu-icon"></i>
              <span class="menu-title">Manajemen SDM</span>
              <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="karyawan">
              <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="manage_employee.php"> Kelola Karyawan</a></li>
                <li class="nav-item"> <a class="nav-link" href="manage_cuti.php"> Kelola Cuti</a></li>
              </ul>
            </div>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="outlet_setting.php">
              <i class="icon-map menu-icon"></i>
              <span class="menu-title">Kelola Outlet</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="reports.php">
              <i class="icon-bar-graph menu-icon"></i>
              <span class="menu-title">Laporan</span>
            </a>
          </li>
        </ul>
      </nav>