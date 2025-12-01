<?php
include '../config.php';

// ✅ Gunakan base_url dari config
$redirect_page = $base_url . "dashboard.php?page=akun";

// 🔒 Validasi ID
if (!isset($_GET['id'])) {
    header("Location: $redirect_page");
    exit;
}

$id = $_GET['id'];

// 🔧 Proses hapus user
if (mysqli_query($conn, "DELETE FROM users WHERE id='$id'")) {
    $status = "success";
    $message = "🗑️ Data user berhasil dihapus!";
} else {
    $status = "error";
    $message = "❌ Gagal menghapus data user.";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Hapus User</title>
<style>
body {
  font-family: 'Segoe UI', sans-serif;
  background: #f1f5f9;
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100vh;
}
.popup {
  background: #fff;
  padding: 25px 35px;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  text-align: center;
  animation: pop 0.4s ease;
}
.popup.success { border-left: 6px solid #009879; }
.popup.error { border-left: 6px solid #e74c3c; }
.popup h3 { margin: 0 0 10px; font-size: 18px; color: #333; }
.popup p { color: #555; margin-bottom: 20px; }
.btn-ok {
  background: #009879;
  color: white;
  padding: 8px 18px;
  border-radius: 6px;
  text-decoration: none;
  transition: 0.3s;
}
.btn-ok:hover { background: #007a63; }
@keyframes pop {
  from { transform: scale(0.8); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}
</style>
<script>
// ✅ Arahkan otomatis setelah 2 detik
setTimeout(() => {
  window.location.href = "<?= $redirect_page ?>";
}, 2000);
</script>
</head>
<body>
<div class="popup <?= $status ?>">
  <h3><?= $message ?></h3>
  <p>Mengalihkan kembali ke halaman akun...</p>
  <a href="<?= $redirect_page ?>" class="btn-ok">Kembali Sekarang</a>
</div>
</body>
</html>
