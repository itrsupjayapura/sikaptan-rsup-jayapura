<?php
session_start();
include 'config.php';

// === LOGIN PROSES ===
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if ($user) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;
            if ($user['role'] == 'admin') {
                header('Location: user_admin.php');
            } else {
                header('Location: user/dashboard_user.php');
            }
            exit;
        } else {
            $error = "❌ Password salah!";
        }
    } else {
        $error = "❌ Username tidak ditemukan!";
    }
    mysqli_stmt_close($stmt);
}

// === KONFIGURASI STATUS PENDAFTARAN ===
$configPath = __DIR__ . '/includes/config_settings.json';
if (file_exists($configPath)) {
    $config = json_decode(file_get_contents($configPath), true);
    $registerUser = $config['registerUser'] ?? 'aktif';
    $registerAdmin = $config['registerAdmin'] ?? 'aktif';
} else {
    $registerUser = 'aktif';
    $registerAdmin = 'aktif';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SIKAPTAN - RSUP Jayapura</title>
<link rel="icon" type="image/png" href="kemenkess.png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    * { font-family: 'Poppins', sans-serif; box-sizing: border-box; }

    body {
        margin: 0;
        padding: 0;
        background: url('rsupp.webp') no-repeat center center fixed;
        background-size: cover;
        position: relative;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    body::before {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(to bottom right, rgba(46,139,87,0.6), rgba(0,0,0,0.4));
        backdrop-filter: blur(8px);
        z-index: 0;
    }

    .login-container {
        position: relative;
        z-index: 1;
        background: rgba(255, 255, 255, 0.18);
        border: 1px solid rgba(255, 255, 255, 0.35);
        border-radius: 20px;
        padding: 45px 40px;
        width: 100%;
        max-width: 420px;
        text-align: center;
        color: #fff;
        box-shadow: 0 8px 30px rgba(0,0,0,0.25);
        backdrop-filter: blur(12px);
        animation: fadeIn 0.9s ease;
    }

    .login-container img {
        width: 85px;
        margin-bottom: 10px;
    }

    .login-container h1 {
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 3px;
        color: #eafaf0;
            margin-bottom: 20px;
    }

    .login-container h2 {
        font-size: 19px;
        margin-bottom: 25px;
        color: #ffffff;
        font-weight: 500;
        letter-spacing: 0.5px;
    }

    label {
        display: block;
        margin: 10px 0 5px;
        text-align: left;
        font-size: 13px;
        color: #e8f5ee;
    }

    input[type="text"], input[type="password"] {
        width: 100%;
        padding: 10px 14px;
        border: none;
        border-radius: 8px;
        background: rgba(255,255,255,0.9);
        outline: none;
        font-size: 14px;
        color: #333;
    }

    button {
        margin-top: 18px;
        width: 100%;
        padding: 10px;
        border: none;
        border-radius: 8px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        background-color: #2e8b57;
        color: white;
        transition: all 0.3s ease;
    }

    button:hover {
        background-color: #256d47;
        transform: translateY(-2px);
    }

    .register-btn {
        background-color: rgba(255,255,255,0.2);
        border: 1px solid rgba(255,255,255,0.3);
        color: #fff;
        margin-top: 12px;
        border-radius: 8px;
    }

    .register-btn:hover {
        background-color: rgba(255,255,255,0.35);
        color: #0b3d22;
        transform: translateY(-2px);
    }

    .error {
        margin-top: 10px;
        color: #ffb3b3;
        font-size: 14px;
    }

    footer {
        margin-top: 25px;
        font-size: 12px;
        color: rgba(255,255,255,0.85);
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }

</style>
</head>
<body>
<div class="login-container">
    <img src="kemenkess.png" alt="Logo Kemenkes">
    <h1>KEMENKES RSUP Jayapura</h1>
    <h2><b>SIKAPTAN</b><br><small>Sistem Kepatuhan APD & Hand Hygiene</small></h2>

    <?php if (!empty($error)): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>Username</label>
        <input type="text" name="username" placeholder="Masukkan username" required>

        <label>Password</label>
        <input type="password" name="password" placeholder="Masukkan password" required>

        <button type="submit">🔐 Login</button>
    </form>

    <?php if ($registerUser === 'aktif' || $registerAdmin === 'aktif'): ?>
        <?php if ($registerUser === 'aktif'): ?>
            <form action="register.php" method="get">
                <button type="submit" class="register-btn">🧾 Daftar User</button>
            </form>
        <?php endif; ?>
        <?php if ($registerAdmin === 'aktif'): ?>
            <form action="register_admin.php" method="get">
                <button type="submit" class="register-btn">⚙️ Daftar Admin</button>
            </form>
        <?php endif; ?>
    <?php else: ?>
        <p style="color: #ffd6d6; font-size: 13px; margin-top: 10px;">
            ⚠️ Semua pendaftaran akun dinonaktifkan oleh admin.
        </p>
    <?php endif; ?>

    <footer>© <span id="tahun"></span> RSUP Jayapura – Kementerian Kesehatan RI</footer>
</div>

<script>
document.getElementById("tahun").textContent = new Date().getFullYear();
</script>
</body>
</html>
