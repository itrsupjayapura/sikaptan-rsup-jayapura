<?php
include '../config.php';

// ✅ Ambil BASE URL otomatis dari config.php
$redirect_page = $base_url . "dashboard.php?page=akun";

// 🔒 Cek ID
if (!isset($_GET['id'])) {
  header("Location: $redirect_page");
  exit;
}

$id = $_GET['id'];
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id='$id'"));

if (!$user) {
  echo "<script>
    alert('Data user tidak ditemukan!');
    window.location.href='$redirect_page';
  </script>";
  exit;
}

$status = '';
$message = '';

// 🔧 Proses Update
if (isset($_POST['update'])) {
  $nama = mysqli_real_escape_string($conn, $_POST['nama']);
  $username = mysqli_real_escape_string($conn, $_POST['username']);
  $ruangan = mysqli_real_escape_string($conn, $_POST['ruangan']);
  $jabatan = mysqli_real_escape_string($conn, $_POST['jabatan']);
  $role = mysqli_real_escape_string($conn, $_POST['role']);
  $password = $_POST['password'];

  if (!empty($password)) {
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $query = "UPDATE users SET nama='$nama', username='$username', ruangan='$ruangan', jabatan='$jabatan', role='$role', password='$hashed' WHERE id='$id'";
  } else {
    $query = "UPDATE users SET nama='$nama', username='$username', ruangan='$ruangan', jabatan='$jabatan', role='$role' WHERE id='$id'";
  }

  if (mysqli_query($conn, $query)) {
    $status = "success";
    $message = "✅ Data berhasil diperbarui!";
  } else {
    $status = "error";
    $message = "❌ Gagal memperbarui data!";
  }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Edit Akun</title>
<style>
body {
  font-family: 'Segoe UI', sans-serif;
  background: #f1f5f9;
  margin: 0;
  padding: 40px;
}

/* Form Styling */
form {
  max-width: 480px;
  margin: auto;
  background: #fff;
  padding: 25px 30px;
  border-radius: 10px;
  box-shadow: 0 3px 10px rgba(0,0,0,0.1);
}
h2 {
  text-align: center;
  color: #007f69;
  margin-bottom: 20px;
}
label {
  font-weight: 600;
  color: #1e3d34;
  display: block;
  margin-bottom: 6px;
}
input, select {
  width: 100%;
  padding: 8px 10px;
  margin-bottom: 15px;
  border-radius: 6px;
  border: 1px solid #cbd5e1;
  font-size: 14px;
}
input:focus, select:focus {
  outline: none;
  border-color: #009879;
  box-shadow: 0 0 4px rgba(0,152,121,0.3);
}
button, a.btn-back {
  display: inline-block;
  padding: 8px 14px;
  border-radius: 6px;
  text-decoration: none;
  font-size: 14px;
  cursor: pointer;
  border: none;
  transition: 0.2s ease;
}
button {
  background: #009879;
  color: #fff;
}
button:hover {
  background: #007a63;
}
a.btn-back {
  background: #ccc;
  color: #333;
  margin-left: 10px;
}
a.btn-back:hover {
  background: #b3b3b3;
}

/* Popup */
.popup {
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  background: rgba(0,0,0,0.4);
  visibility: hidden;
  opacity: 0;
  transition: 0.3s ease;
}
.popup.show {
  visibility: visible;
  opacity: 1;
}
.popup-content {
  background: #fff;
  padding: 25px 35px;
  border-radius: 10px;
  text-align: center;
  box-shadow: 0 4px 15px rgba(0,0,0,0.2);
  animation: pop 0.3s ease;
}
.popup.success .popup-content { border-left: 6px solid #009879; }
.popup.error .popup-content { border-left: 6px solid #e74c3c; }
.popup-content h3 { margin: 0 0 10px; color: #333; }
.popup-content p { margin: 0 0 15px; color: #555; }
.popup-content button {
  background: #009879;
  color: white;
  border: none;
  padding: 8px 15px;
  border-radius: 6px;
  cursor: pointer;
}
.popup-content button:hover { background: #007a63; }
@keyframes pop {
  from { transform: scale(0.8); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}
</style>
</head>
<body>

<form method="POST" onsubmit="return confirmUpdate(event)">
  <h2>Edit Data Akun</h2>

  <label>Nama Lengkap</label>
  <input type="text" name="nama" value="<?= htmlspecialchars($user['nama']) ?>" required>

  <label>Username</label>
  <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>

  <label>Ruangan</label>
  <input type="text" name="ruangan" value="<?= htmlspecialchars($user['ruangan']) ?>">

  <label>Jabatan</label>
  <input type="text" name="jabatan" value="<?= htmlspecialchars($user['jabatan']) ?>">

  <label>Role</label>
  <select name="role" required>
    <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
    <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>Pegawai</option>
  </select>

  <label>Password Baru (Opsional)</label>
  <input type="password" name="password" placeholder="Kosongkan jika tidak diubah">

  <div style="text-align:center;">
    <button type="submit" name="update">💾 Simpan Perubahan</button>
    <a href="<?= $redirect_page ?>" class="btn-back">⬅ Kembali</a>
  </div>
</form>

<!-- Popup -->
<?php if ($status): ?>
<div class="popup <?= $status ?> show" id="notifPopup">
  <div class="popup-content">
    <h3><?= $message ?></h3>
    <p>Mengalihkan kembali ke halaman akun...</p>
    <button onclick="redirect()">OK</button>
  </div>
</div>
<script>
setTimeout(() => { redirect(); }, 2000);
function redirect() {
  window.location.href = "<?= $redirect_page ?>";
}
</script>
<?php endif; ?>

<script>
function confirmUpdate(e) {
  if (!confirm("Yakin ingin memperbarui data user ini?")) {
    e.preventDefault();
    return false;
  }
  return true;
}
</script>

</body>
</html>
