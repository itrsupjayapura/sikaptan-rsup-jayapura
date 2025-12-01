<?php
session_start();
include '../config.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header('Location: ../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PANEL ADMIN - SIKAPTAN</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://unpkg.com/phosphor-icons"></script>
<link rel="icon" href="kemenkess.png" type="image/png">

<style>
:root {
  --main-color: #007A64;
  --sidebar-bg: linear-gradient(180deg, #026956, #00493c);
  --sidebar-hover: rgba(255,255,255,0.1);
  --sidebar-active: rgba(255,255,255,0.2);
  --text-color: #e8f5f1;
}

/* === BASE === */
body {
  margin: 0;
  font-family: 'Poppins', sans-serif;
  background: #f4f5f7;
}

/* === SIDEBAR === */
.sidebar {
  width: 250px;
  height: 100vh;
  background: var(--sidebar-bg);
  position: fixed;
  left: 0;
  top: 0;
  color: var(--text-color);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  box-shadow: 2px 0 10px rgba(0,0,0,0.2);
  transition: transform 0.3s ease;
  z-index: 999;
}
.sidebar.active { transform: translateX(-100%); }




/* === REVISI HEADER LOGO DI ATAS TEKS === */
.sidebar-header {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 25px 15px;
  border-bottom: 1px solid rgba(255,255,255,0.2);
}

.sidebar-header .logo-top {
  width: 60px;
  height: 60px;
  border-radius: 10px;
  margin-bottom: 8px;
}

.sidebar-header h2 {
  font-size: 17px;
  font-weight: 700;
  color: #fff;
  margin: 5px 0 3px;
  letter-spacing: 0.5px;
}

.sidebar-header p {
  font-size: 12px;
  line-height: 1.4;
  color: rgba(255,255,255,0.8);
  margin: 0;
  margin-bottom: 20px;
}





/* === MENU === */
.sidebar-menu {
  list-style: none;
  margin: 0;
  padding: 15px 0;
  flex-grow: 1;
}
.sidebar-menu li { width: 100%; }
.sidebar-menu a {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 12px 22px;
  font-size: 15px;
  font-weight: 500;
  color: var(--text-color);
  text-decoration: none;
  border-left: 3px solid transparent;
  transition: all 0.2s ease;
}
.sidebar-menu a i.ph {
  width: 22px;
  min-width: 22px;
  text-align: center;
  font-size: 18px;
}
.sidebar-menu a:hover {
  background: var(--sidebar-hover);
  color: #fff;
}
.sidebar-menu a.active {
  background: var(--sidebar-active);
  border-left: 3px solid #fff;
  color: #fff;
  font-weight: 600;
}

/* === DROPDOWN === */
.sidebar-menu .dropdown > a {
  justify-content: space-between;
}
.sidebar-menu .dropdown > a .left {
  display: flex;
  align-items: center;
  gap: 14px;
}
.submenu {
  display: none;
  flex-direction: column;
  list-style: none;
  padding-left: 30px;
  margin: 5px 0;
}
.submenu li a {
  font-size: 14px;
  padding: 8px 20px;
  color: #d7ebe4;
}
.submenu li a:hover {
  background: rgba(255,255,255,0.08);
  color: #fff;
}
.dropdown.open > .submenu {
  display: flex;
}
.right-icon {
  font-size: 13px;
  transition: transform 0.3s ease;
}
.dropdown.open .right-icon {
  transform: rotate(180deg);
}
.dropdown-toggle::after { display: none !important; }

/* === LOGOUT === */
.logout-section {
  border-top: 1px solid rgba(255,255,255,0.2);
  padding: 15px 20px;
}
.logout-section a {
  display: flex;
  align-items: center;
  gap: 10px;
  color: #f9dcdc;
  font-weight: 600;
  text-decoration: none;
}
.logout-section a:hover { color: #fff; }

/* === TOPBAR === */
.topbar {
  height: 65px;
  background: #fff;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 25px;
  border-bottom: 1px solid #dee2e6;
  position: sticky;
  top: 0;
  z-index: 998;
}
.toggle-btn {
  border: none;
  background: none;
  font-size: 1.8rem;
  color: var(--main-color);
  cursor: pointer;
}
main.content {
  margin-left: 250px;
  transition: all 0.3s ease;
}
.sidebar.active ~ main.content { margin-left: 0; }

@media (max-width: 992px) {
  .sidebar { transform: translateX(-100%); }
  .sidebar.active { transform: translateX(0); }
  main.content { margin-left: 0; }
}
</style>
</head>
<body>


   <!-- STYLE -->
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/phosphor-icons"></script>
    <link rel="icon" type="image/png" href="kemenkess.png">

<!-- === SIDEBAR === -->
<aside class="sidebar" id="sidebar">
  <div>
<div class="sidebar-header text-center">
  <img src="kemenkess.png" alt="Logo Kemenkes" class="logo-top">
  <h2 class="mt-2">PANEL ADMIN</h2>
  <p>SIKAPTAN<br>Sistem APD & Hand Hygiene</p>
</div>

    </div>

    <ul class="sidebar-menu">
      <li><a href="?page=dashboard" class="<?= ($_GET['page'] ?? 'dashboard') == 'dashboard' ? 'active' : '' ?>"><i class="ph ph-gauge"></i>Dashboard</a></li>

      <li class="dropdown">
        <a href="#" class="dropdown-toggle">
          <div class="left"><i class="ph ph-clipboard-text"></i>Data Observasi</div>
          <i class="ph ph-caret-down right-icon"></i>
        </a>
        <ul class="submenu">
          <li><a href="?page=observer" class="<?= ($_GET['page'] ?? '') == 'observer' ? 'active' : '' ?>">Kepatuhan APD</a></li>
          <li><a href="?page=observer_cuci" class="<?= ($_GET['page'] ?? '') == 'observer_cuci' ? 'active' : '' ?>">Cuci Tangan</a></li>
        </ul>
      </li>

      <li class="dropdown">
        <a href="#" class="dropdown-toggle">
          <div class="left"><i class="ph ph-note-pencil"></i>Pengisian Form</div>
          <i class="ph ph-caret-down right-icon"></i>
        </a>
        <ul class="submenu">
          <li><a href="?page=pengisian_form" class="<?= ($_GET['page'] ?? '') == 'pengisian_form' ? 'active' : '' ?>">Form APD</a></li>
          <li><a href="?page=pengisian_form_cuci" class="<?= ($_GET['page'] ?? '') == 'pengisian_form_cuci' ? 'active' : '' ?>">Form Cuci Tangan</a></li>
        </ul>
      </li>

      <li class="dropdown">
        <a href="#" class="dropdown-toggle">
          <div class="left"><i class="ph ph-chart-bar"></i>Laporan Bulanan</div>
          <i class="ph ph-caret-down right-icon"></i>
        </a>
        <ul class="submenu">
          <li><a href="?page=laporan" class="<?= ($_GET['page'] ?? '') == 'laporan' ? 'active' : '' ?>">Laporan APD</a></li>
          <li><a href="?page=laporan_cuci" class="<?= ($_GET['page'] ?? '') == 'laporan_cuci' ? 'active' : '' ?>">Laporan Cuci Tangan</a></li>
        </ul>
      </li>

      <li><a href="?page=akun" class="<?= ($_GET['page'] ?? '') == 'akun' ? 'active' : '' ?>"><i class="ph ph-users-three"></i>Akun Terdaftar</a></li>
      <li><a href="?page=pengaturan" class="<?= ($_GET['page'] ?? '') == 'pengaturan' ? 'active' : '' ?>"><i class="ph ph-gear-six"></i>Pengaturan</a></li>
    </ul>
  </div>

  <div class="logout-section">
    <a href="../logout.php"><i class="ph ph-sign-out"></i>Logout</a>
  </div>
</aside>

<!-- === MAIN CONTENT === -->
<main class="content">
  <header class="topbar shadow-sm">
    <button class="toggle-btn" onclick="document.getElementById('sidebar').classList.toggle('active')">
      <i class="ph ph-list"></i>
    </button>
    <span>👋 Halo, <b><?= $_SESSION['user']['nama']; ?></b></span>
  </header>

  <section class="main-content p-4">
    <div class="page-wrapper">
      <?php
      $page = $_GET['page'] ?? 'dashboard';
      switch ($page) {
        case 'dashboard': include 'page_dashboard.php'; break;
        case 'akun': include 'page_akun.php'; break;
        case 'observer': include 'page_observer.php'; break;
        case 'laporan': include 'page_laporan.php'; break;
        case 'pengisian_form': include 'pengisian_form.php'; break;
        case 'pengaturan': include 'page_pengaturan.php'; break;
        case 'observer_cuci': include 'cuci_tangan/page_observer_cuci.php'; break;
        case 'pengisian_form_cuci': include 'cuci_tangan/pengisian_form_cuci.php'; break;
        case 'laporan_cuci': include 'cuci_tangan/page_laporan_cuci.php'; break;
        default: include 'page_dashboard.php'; break;
      }
      ?>
    </div>
  </section>
</main>

<script>
document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
  toggle.addEventListener('click', e => {
    e.preventDefault();
    toggle.parentElement.classList.toggle('open');
  });
});
</script>
</body>
</html>
