<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header('Location: index.php');
    exit;
}

$nama_admin = $_SESSION['user']['nama'] ?? $_SESSION['user']['username'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Menu Admin & User - RSUP Jayapura</title>
<link rel="icon" type="image/png" href="kemenkess.png">
<style>
body {
    margin: 0;
    font-family: 'Poppins', sans-serif;
    background: url('rsupp.webp') no-repeat center center fixed;
    background-size: cover;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    position: relative;
}
body::before {
    content: "";
    position: absolute;
    inset: 0;
    /* background: rgba(46, 139, 87, 0.6);
    backdrop-filter: blur(5px); */
    background: linear-gradient(to bottom right, rgba(46, 139, 87, 0.6), rgba(0, 0, 0, 0.4));
    backdrop-filter: blur(5px);
    z-index: 0;
}
.container {
    position: relative;
    z-index: 2;
    background: rgba(255,255,255,0.9);
    padding: 30px 40px;
    border-radius: 14px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.2);
    text-align: center;
    max-width: 400px;
    width: 90%;
}
h2 {
    color: #006b5e;
    font-weight: 700;
    margin-bottom: 10px;
}
p {
    color: #333;
    font-size: 14px;
    margin-bottom: 25px;
}
button {
    display: block;
    width: 100%;
    padding: 12px;
    margin: 10px 0;
    border: none;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.3s;
}
.btn-admin {
    background-color: #00796b;
    color: white;
}
.btn-admin:hover {
    background-color: #004d40;
}
.btn-user {
    background-color: #4caf50;
    color: white;
}
.btn-user:hover {
    background-color: #388e3c;
}
.btn-logout {
    background-color: #e53935;
    color: white;
}
.btn-logout:hover {
    background-color: #b71c1c;
}
footer {
    margin-top: 20px;
    color: #666;
    font-size: 12px;
}
</style>
</head>


<body>
<div class="container">
    <h2>Selamat Datang, <?= htmlspecialchars($nama_admin) ?></h2>
    <p>Pilih menu di bawah untuk melanjutkan:</p>

    <button class="btn-admin" onclick="location.href='admin/dashboard.php'">📊 Dashboard Admin</button>
    <button class="btn-user" onclick="location.href='user/dashboard_user.php'">🧾 Isi Form Observasi (Mode User)</button>
    <button class="btn-logout" onclick="location.href='logout.php'">🚪 Logout</button>

    <footer>© <span id="tahun"></span> RSUP Jayapura – Kementerian Kesehatan RI</footer>
</div>

<script>
document.getElementById("tahun").textContent = new Date().getFullYear();
</script>
</body>
</html>
