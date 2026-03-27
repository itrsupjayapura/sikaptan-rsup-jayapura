<?php
// ======================================================
// ⚙️ BASE URL OTOMATIS (BISA DI LOCALHOST & HOSTING)
// ======================================================
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$hostName = $_SERVER['HTTP_HOST'];

// ambil path folder project (otomatis)
$folder = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
if ($folder === '' || $folder === '/') {
    $folder = '';
} else {
    $folder .= '/';
}

$base_url = $protocol . $hostName . $folder;
// Contoh hasil otomatis:
//   localhost  → http://localhost/rsup/
//   hosting    → https://namadomainmu.com/

// ======================================================
// ⚙️ KONFIGURASI DATABASE
// ======================================================
$host = "localhost";
$user = "admin";
$pass = "rsvp@satu23";
$db   = "sikaptan";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("❌ Koneksi database gagal: " . mysqli_connect_error());
}

// ======================================================
// ⚙️ OPTIONAL: Set zona waktu ke Indonesia
// ======================================================
date_default_timezone_set('Asia/Jayapura');
?>
