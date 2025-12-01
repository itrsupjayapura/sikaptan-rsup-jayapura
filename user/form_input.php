<?php
session_start();
include '../config.php';

// Jika belum login, kembalikan ke halaman login
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit;
}

// Jika role bukan user ATAU admin, tolak akses
if ($_SESSION['user']['role'] !== 'user' && $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$data_observasi = mysqli_query($conn, "SELECT * FROM data_observasi WHERE status='aktif' ORDER BY ruangan ASC");

function bulan_tahun_indo($periode) {
    $bulan = [
        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
        '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
        '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
    ];
    $parts = explode('-', $periode);
    if (count($parts) == 2) {
        return ($bulan[$parts[1]] ?? $parts[1]) . " " . $parts[0];
    }
    return $periode;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
<meta charset="UTF-8">
<title>Pilih Ruangan Observasi - RSUP Jayapura</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" href="../kemenkess.png" type="image/png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
* {
  font-family: 'Poppins', sans-serif;
  box-sizing: border-box;
}

body {
  margin: 0;
  min-height: 100vh;
  background: url('../rsupp.webp') no-repeat center center fixed;
  background-size: cover;
  position: relative;
}

body::before {
  content: "";
  position: absolute;
  inset: 0;
  background: linear-gradient(to bottom right, rgba(2, 91, 175, 0.44), rgba(0,0,0,0.4));
  backdrop-filter: blur(5px);
  z-index: 0;
}

.container {
  position: relative;
  z-index: 1;
  padding: 60px 20px;
  color: #033b1e;
}

.header-box {
  text-align: center;
  margin-bottom: 40px;
  color: #fff;
}

.header-box img {
  width: 80px;
  margin-bottom: 10px;
}

.header-box h2 {
  font-size: 22px;
  font-weight: 600;
  color: #ffffff;
}

/* 🔹 Tombol kembali (desktop kiri atas) */
.back-box {
  position: absolute;
  top: 20px;
  left: 30px;
  background: rgba(255, 255, 255, 0.92);
  /* border-radius: 4px; */
  padding: 8px 14px;
  font-weight: 600;
  font-size: 14px;
  color: #2e8b57;
  backdrop-filter: blur(5px);
  box-shadow: 0 2px 6px rgba(0,0,0,0.15);
  z-index: 10;
}

.back-box a {
  text-decoration: none;
  color: #2e8b57;
}

.back-box a:hover {
  text-decoration: underline;
  color: #256d47;
}

/* 🔹 Box Logout */
.logout-box {
  position: absolute;
  top: 20px;
  right: 30px;
  display: flex;
  align-items: center;
  gap: 10px;
  background: rgba(255, 255, 255, 0.92);
  /* border-radius: 4px; */
  padding: 8px 14px;
  font-weight: 600;
  font-size: 14px;
  color: #000;
  backdrop-filter: blur(5px);
  box-shadow: 0 2px 6px rgba(0,0,0,0.15);
}

.logout-box a {
  color: #f01111ff;
  font-weight: 600;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.logout-box a:hover {
  text-decoration: underline;
}

/* ======================== */
/* 🔹 Mobile (tanpa ubah logout style) */
@media (max-width: 768px) {
  .logout-box {
    position: static;
    justify-content: center;
    flex-wrap: wrap;
    text-align: center;
  }

  /* Tombol kembali pindah ke bawah */
  .back-box {
    position: static;
    display: block;
    /* margin: 15px auto 0; */
    text-align: center;
  }
}

/* ======================== */
.ruangan-card {
  background: rgba(255,255,255,0.92);
  border: 1px solid rgba(46,139,87,0.2);
  border-radius: 10px;
  padding: 25px 20px;
  text-align: center;
  color: #064b2f;
  transition: 0.3s;
  height: 100%;
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}

.ruangan-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 8px 18px rgba(0,0,0,0.25);
}

.ruangan-card h5 {
  font-size: 18px;
  font-weight: 600;
  color: #06613b;
  margin-bottom: 8px;
}

.ruangan-card p {
  font-size: 14px;
  color: #3e6655;
}

.ruangan-card .btn {
  background-color: #2e8b57;
  border: none;
  border-radius: 5px;
  color: white;
  font-weight: 600;
  transition: 0.3s;
}

.ruangan-card .btn:hover {
  background-color: #256d47;
  transform: scale(1.05);
}

.no-data {
  margin-top: 40px;
  font-size: 16px;
  color: #fff7f7;
  text-shadow: 1px 1px 3px rgba(0,0,0,0.4);
}

footer {
  margin-top: 60px;
  text-align: center;
  font-size: 13px;
  color: rgba(255,255,255,0.85);
}
</style>
</head>

<body>

<!-- 🔹 Tombol kembali kiri atas -->
<div class="back-box">
  <a href="dashboard_user.php">⬅️ Kembali ke Dashboard</a>
</div>

<!-- 🔹 Box Logout kanan atas -->
<div class="logout-box d-flex align-items-center">
  👤 <span><?= htmlspecialchars($_SESSION['user']['nama']); ?></span>
  
  <?php if ($_SESSION['user']['role'] === 'admin'): ?>
<a href="../user_admin.php" class="btn btn-warning btn-sm fw-semibold px-3 py-1">← Admin</a>
  <?php endif; ?>
  
  <a href="../logout.php" class="btn btn-outline-danger btn-sm fw-semibold">Logout</a>
</div>

<!-- 🔹 Konten utama -->
<div class="container">
  <div class="header-box">
    <img src="../kemenkess.png" alt="Logo Kemenkes">
    <h2>📋 Pilih Ruangan untuk Observasi APD</h2>
  </div>

  <div class="row g-4 justify-content-center">
    <?php while ($r = mysqli_fetch_assoc($data_observasi)): ?>
      <div class="col-lg-3 col-md-4 col-sm-6">
        <div class="ruangan-card">
          <h5><?= htmlspecialchars($r['ruangan']) ?></h5>
          <p><small><?= htmlspecialchars(bulan_tahun_indo($r['bulan'])) ?></small></p>
          <a href="form_observasi.php?id=<?= $r['id'] ?>" class="btn w-100">Buka Form</a>
        </div>
      </div>
    <?php endwhile; ?>

    <?php if (mysqli_num_rows($data_observasi) == 0): ?>
      <p class="no-data text-center">⚠️ Belum ada data observasi aktif. Silakan hubungi admin.</p>
    <?php endif; ?>
  </div>

  <footer>© <span id="tahun"></span> RSUP Jayapura – Kementerian Kesehatan Republik Indonesia</footer>
</div>

<script>
document.getElementById("tahun").textContent = new Date().getFullYear();
</script>

</body>
</html>
