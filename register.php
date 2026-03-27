<?php
include 'config.php';



$configPath = __DIR__ . '/includes/config_settings.json';

if (file_exists($configPath)) {
    $config = json_decode(file_get_contents($configPath), true);
    $registerUser = $config['registerUser'] ?? 'aktif';
} else {
    $registerUser = 'aktif';
}

// Jika pendaftaran user nonaktif, hentikan akses
if ($registerUser !== 'aktif') {
    header("Location: blocked.php");
    exit;
}



if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password_raw = $_POST['password'];
    $password_hashed = password_hash($password_raw, PASSWORD_DEFAULT);
    $nama = trim($_POST['nama']);
    $ruangan = trim($_POST['ruangan']);
    $role = isset($_POST['role']) ? $_POST['role'] : 'user';
    $jabatan = !empty($_POST['jabatan']) ? trim($_POST['jabatan']) : null;

    // Cek username sudah dipakai atau belum
    $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        $error = "❌ Username sudah digunakan, silakan pilih yang lain.";
    } else {
        mysqli_stmt_close($stmt);

        // Simpan data pengguna baru
        $sql = "INSERT INTO users (username, password, nama, ruangan, role, jabatan)
                VALUES (?, ?, ?, ?, ?, ?)";
        $ins = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($ins, "ssssss", $username, $password_hashed, $nama, $ruangan, $role, $jabatan);

        if (mysqli_stmt_execute($ins)) {
            $success = "✅ Akun berhasil dibuat! Silakan <a href='index.php'>Login</a>.";
        } else {
            $error = "❌ Gagal menyimpan data: " . htmlspecialchars(mysqli_error($conn));
        }

        mysqli_stmt_close($ins);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daftar Akun - RSUP Jayapura</title>
<link rel="icon" type="image/png" href="kemenkess.png">
<style>
    * {
        box-sizing: border-box;
        font-family: 'Poppins', sans-serif;
    }

    html, body {
        margin: 0;
        padding: 0;
        height: 100%;
        width: 100%;
    }

    body {
        background: url('rsupp.webp') no-repeat center center fixed;
        background-size: cover;
        display: flex;
        justify-content: center;
        align-items: center;
        position: relative;
    }

    body::before {
        content: "";
        position: absolute;
        inset: 0;
       background: linear-gradient(to bottom right, rgba(46, 139, 87, 0.6), rgba(0, 0, 0, 0.4));
    backdrop-filter: blur(5px);
        z-index: 0;
    }

    .container {
        position: relative;
        z-index: 1;
        width: 100%;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 20px;
    }

    .register-box {
        background: rgba(255, 255, 255, 0.15);
        border: 1px solid rgba(255,255,255,0.3);
        padding: 35px 30px;
        border-radius: 18px;
        width: 100%;
        max-width: 420px;
        text-align: center;
        color: #fff;
        backdrop-filter: blur(10px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.25);
        animation: fadeIn 0.8s ease;
    }

    .register-box img {
        width: 80px;
        margin-bottom: 12px;
    }

    .register-box h1 {
        font-size: 20px;
        color: #eafaf0;
        margin-bottom: 6px;
    }

    .register-box h2 {
        font-size: 21px;
        color: #fff;
        margin-bottom: 25px;
        font-weight: 500;
    }

    label {
        display: block;
        text-align: left;
        margin: 10px 0 4px;
        font-size: 14px;
        color: #e8f5ee;
    }

    input, select {
        width: 100%;
        padding: 10px 14px;
        border: none;
        border-radius: 4px;
        background: rgba(255,255,255,0.9);
        font-size: 14px;
        color: #333;
        outline: none;
    }

    button {
        margin-top: 18px;
        width: 100%;
        padding: 10px;
        border: none;
        border-radius: 4px;
        font-size: 15px;
        font-weight: 600;
        background-color: #2e8b57;
        color: white;
        cursor: pointer;
        transition: 0.3s;
    }

    button:hover {
        background-color: #256d47;
        transform: translateY(-2px);
    }

    .message {
        margin-bottom: 15px;
        font-size: 14px;
        line-height: 1.5;
    }

    .message a {
        color: #fff;
        text-decoration: underline;
    }

    .back-link {
        display: inline-block;
        margin-top: 15px;
        font-size: 13px;
        color: #fff;
        text-decoration: none;
    }

    .back-link:hover {
        text-decoration: underline;
    }

    footer {
        margin-top: 25px;
        font-size: 12px;
        color: rgba(255,255,255,0.9);
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 768px) {
        .register-box {
            max-width: 90%;
            padding: 28px 22px;
            border-radius: 14px;
        }
        .register-box img { width: 70px; }
        .register-box h1 { font-size: 18px; }
        .register-box h2 { font-size: 19px; }
        input, select { font-size: 13px; }
        button { font-size: 14px; padding: 9px; }
        footer { font-size: 11px; }
    }
</style>
</head>
<body>
    <div class="container">
        <div class="register-box">
            <img src="kemenkess.png" alt="Logo Kemenkes">
            <h1>KEMENKES RSUP Jayapura</h1>
            <h2>Daftar Akun Baru</h2>

            <?php if (!empty($error)): ?>
                <p class="message" style="color:#ffb3b3;"><?= htmlspecialchars($error) ?></p>
            <?php elseif (!empty($success)): ?>
                <p class="message" style="color:#c3f7c0;"><?= $success ?></p>
            <?php endif; ?>

            <form method="POST">
                <label>Username</label>
                <input type="text" name="username" required placeholder="Masukkan username">

                <label>Password</label>
                <input type="password" name="password" required placeholder="Masukkan password">

                <label>Nama Lengkap (Gelar anda)</label>
                <input type="text" name="nama" required placeholder="Masukkan nama lengkap">

                <label>Ruangan</label>
                <input type="text" name="ruangan" required placeholder="Masukkan ruangan">

                <label>Role</label>
                <select name="role" id="role" required onchange="toggleJabatan()">
                    <option value="">-- Pilih Role --</option>
                    <option value="user">User</option>
                    <!-- <option value="admin">Admin</option> -->
                </select>

                <div id="jabatan_field" style="display:none;">
                    <label>Jabatan</label>
                    <input type="text" name="jabatan" placeholder="Masukkan jabatan admin">
                </div>

                <button type="submit">Daftar</button>
            </form>

            <a href="index.php" class="back-link">⬅️ Kembali ke Halaman Login</a>

               <footer>© <span id="tahun"></span> RSUP Jayapura – Kementerian Kesehatan RI</footer>
        </div>
    </div>

      <script>
     document.getElementById("tahun").textContent = new Date().getFullYear();
   </script>

    <script>
    function toggleJabatan() {
        const role = document.getElementById('role').value;
        const jabatanField = document.getElementById('jabatan_field');
        jabatanField.style.display = (role === 'admin') ? 'block' : 'none';
    }
    </script>

    
</body>
</html>
