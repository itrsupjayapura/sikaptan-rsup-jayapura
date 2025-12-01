<?php
session_start();
include '../config.php';

// Jika belum login, arahkan ke halaman login
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit;
}

// Jika bukan user atau admin, tolak akses
if ($_SESSION['user']['role'] !== 'user' && $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$nama_user = $_SESSION['user']['nama'];
$username = $_SESSION['user']['username'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard User - RSUP Jayapura</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../kemenkess.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        * { font-family: 'Poppins', sans-serif; box-sizing: border-box; }
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
    background: linear-gradient(to bottom right, rgba(46, 139, 87, 0.6), rgba(0, 0, 0, 0.4));
    backdrop-filter: blur(5px);
    z-index: 0;
    pointer-events: none; /* ✅ Tambahkan ini */
}

        .container {
            position: relative;
            z-index: 1;
            padding: 60px 20px;
            color: #033b1e;
            text-align: center;
        }

        .logout-box {
            position: absolute;
            top: 20px;
            right: 30px;
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(255, 255, 255, 0.9);
            /* border-radius: 4px; */
            padding: 8px 14px;
            font-weight: 600;
            font-size: 14px;
            color: #000;
            backdrop-filter: blur(5px);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        }
        .logout-box a {
            color: #f01111;
            font-weight: 600;
            text-decoration: none;
        }
        .logout-box a:hover { text-decoration: underline; }
        @media (max-width: 768px) {
            .logout-box {
                position: static;
                justify-content: center;
                flex-wrap: wrap;
                text-align: center;
            }
        }

        .header-box img {
            width: 80px;
            margin-bottom: 10px;
        }
        .header-box h2 {
            font-size: 24px;
            font-weight: 600;
            color: #fff;
            margin-bottom: 40px;
        }

        .form-card {
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(46, 139, 87, 0.2);
            border-radius: 12px;
            padding: 25px 20px;
            text-align: center;
            color: #064b2f;
            transition: all 0.3s ease;
            height: 100%;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .form-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 8px 18px rgba(0, 0, 0, 0.25);
        }

        .form-card h5 {
            font-size: 18px;
            font-weight: 700;
            color: #06613b;
            margin-bottom: 10px;
        }
        .form-card p {
            font-size: 14px;
            color: #3e6655;
            min-height: 40px;
        }
        .form-card .btn {
            background-color: #2e8b57;
            border: none;
            border-radius: 6px;
            color: white;
            font-weight: 600;
            padding: 10px 20px;
            transition: 0.3s;
        }
        .form-card .btn:hover {
            background-color: #256d47;
            transform: scale(1.05);
        }

        footer {
            margin-top: 60px;
            text-align: center;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.85);
        }
    </style>
</head>

<body>
<!-- 🔹 Kotak Logout -->
<div class="logout-box">
  👤 <?= htmlspecialchars($nama_user); ?>
 <?php if ($_SESSION['user']['role'] === 'admin'): ?>
 <a href="<?= $base_url ?>user_admin.php" class="btn btn-warning btn-sm fw-semibold px-3 py-1">← Admin</a>

  <?php endif; ?>

  <a href="../logout.php" class="btn btn-outline-danger btn-sm fw-semibold">Logout</a>
</div>

<!-- 🔹 Konten Utama -->
<div class="container">
    <div class="header-box">
        <img src="../kemenkess.png" alt="Logo Kemenkes">
        <h2>Pilih Jenis Formulir Observasi</h2>
    </div>

    <div class="row justify-content-center g-4">
        <!-- 🧤 Form APD -->
        <div class="col-lg-3 col-md-4 col-sm-6">
            <div class="form-card">
                <h5>🧤 Form APD</h5>
                <p>Formulir untuk observasi penggunaan Alat Pelindung Diri (APD).</p>
                <a href="form_input.php" class="btn w-100">Masuk</a>
            </div>
        </div>

        <!-- 🧼 Form Cuci Tangan -->
        <div class="col-lg-3 col-md-4 col-sm-6">
            <div class="form-card">
                <h5>🧼 Form Hand Hygiene</h5>
                <p>Formulir untuk observasi kebersihan tangan (Hand Hygiene).</p>
                <a href="form_input_cuci.php" class="btn w-100">Masuk</a>
            </div>
        </div>
    </div>

    <footer>© <span id="tahun"></span> RSUP Jayapura – Kementerian Kesehatan Republik Indonesia</footer>
</div>

<script>
document.getElementById("tahun").textContent = new Date().getFullYear();
</script>
</body>
</html>
