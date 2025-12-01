<?php
session_start();
include '../config.php';

// 🔹 Cek login
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit;
}

// 🔹 Hanya user dan admin yang boleh
if ($_SESSION['user']['role'] !== 'user' && $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

// 🔹 Ambil ID observasi
$id_observasi = $_GET['id'] ?? null;
if (!$id_observasi) {
    header('Location: form_input_cuci.php');
    exit;
}

// 🔹 Ambil data periode observasi cuci tangan
$q = mysqli_query($conn, "SELECT * FROM data_observasi_cuci_tangan WHERE id='$id_observasi' LIMIT 1");
$data_observer = mysqli_fetch_assoc($q);
if (!$data_observer) {
    echo "<p style='color:red;'>Data observasi tidak ditemukan.</p>";
    exit;
}

// 🔹 Ambil nama petugas dari user login
$username_login = $_SESSION['user']['username'];
$query_nama = mysqli_query($conn, "SELECT nama FROM users WHERE username='$username_login' LIMIT 1");
if ($row_nama = mysqli_fetch_assoc($query_nama)) {
    $_SESSION['user']['nama'] = $row_nama['nama'];
    $petugas = $row_nama['nama'];
} else {
    $petugas = $_SESSION['user']['username'];
}

// 🔹 Fungsi ubah tanggal
function tanggal_indo($tanggal) {
    $hari = [
        'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
    ];
    $bulan = [
        '01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni',
        '07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'
    ];
    $ts = strtotime($tanggal);
    return $hari[date('l',$ts)] . ", " . date('d',$ts) . " " . $bulan[date('m',$ts)] . " " . date('Y',$ts);
}

$tampilkan_popup = false;

// 🔹 Saat form dikirim
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tanggal = $_POST['tanggal'];
    $nama_rekan_dilaporkan = trim($_POST['nama_rekan_dilaporkan']) ?: null;
    $tindakan = trim($_POST['tindakan']) ?: null;
    $cuci_tangan_menggunakan = $_POST['cuci_tangan_menggunakan'] ?? null;

    // 🔸 Otomatis nilai 50 untuk masing-masing jenis
    $nilai_cuci_tangan = ($cuci_tangan_menggunakan == 'Handwash' || $cuci_tangan_menggunakan == 'Handrub') ? 50 : 0;

    // 🔹 Ambil field dinamis
    $fields_query = mysqli_query($conn, "SELECT field_name FROM cuci_tangan_fields ORDER BY id ASC");
    $fields = [];
    while ($row = mysqli_fetch_assoc($fields_query)) {
        $fields[] = $row['field_name'];
    }

    $ya = 0;
    $ya_tidak = 0;
    $field_values = [];

    foreach ($fields as $f) {
        $val = $_POST[$f] ?? 'Tidak Dinilai';
        $field_values[$f] = mysqli_real_escape_string($conn, $val);
        if ($val == 'Ya') $ya++;
        if ($val == 'Ya' || $val == 'Tidak') $ya_tidak++;
    }

    $field_names = implode(", ", array_map(fn($f) => "`$f`", $fields));
    $field_data  = "'" . implode("','", $field_values) . "'";

    // 🔹 Simpan ke DB (sinkron versi baru)
    $sql = "INSERT INTO observasi_cuci_tangan 
        (id_observasi, bulan, ruangan, tanggal, petugas, nama_rekan_dilaporkan, tindakan, 
         $field_names, numerator, denumerator, cuci_tangan_menggunakan, nilai_cuci_tangan)
    VALUES (
        '$id_observasi',
        '{$data_observer['bulan']}',
        '{$data_observer['ruangan']}',
        '$tanggal',
        '$petugas',
        '$nama_rekan_dilaporkan',
        '$tindakan',
        $field_data,
        '$ya',
        '$ya_tidak',
        '$cuci_tangan_menggunakan',
        '$nilai_cuci_tangan'
    )";

    if (mysqli_query($conn, $sql)) {
        $tampilkan_popup = true;
    } else {
        echo "<div class='alert alert-danger text-center mt-3'>❌ Gagal menyimpan data: ".mysqli_error($conn)."</div>";
    }
}

$tanggal_hari_ini = date('Y-m-d');
$tanggal_tampil = tanggal_indo($tanggal_hari_ini);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Form Observasi Cuci Tangan - <?= htmlspecialchars($data_observer['ruangan']) ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="../styles/style.css"> <!-- gunakan css lama -->
</head>

<style>
body {
    background: url('../rsupp.webp') no-repeat center center fixed;
    background-size: cover;
    font-family: "Poppins", sans-serif;
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
     background: linear-gradient(to bottom right, rgba(218, 215, 48, 0.51), rgba(0,0,0,0.4));

    backdrop-filter: blur(5px);
    z-index: 0;
}
.form-container {
    position: relative;
    z-index: 2;
    max-width: 420px;
    width: 100%;
    background: white;
    box-shadow: 0 4px 16px rgba(0,107,94,0.3);
    padding: 25px;
}
h3 { color: #006b5e; font-weight: 700; text-align: center; }
.btn-primary { background-color: #009688; border: none; }
.btn-primary:hover { background-color: #00796b; }
.btn-secondary {
    background-color: #e0f2f1;
    color: #006b5e;
    border: none;
    font-weight: 500;
}
.btn-secondary:hover { background-color: #b2dfdb; }

/* 🔹 Popup Konfirmasi Simpan */
.popup-konfirmasi {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
  z-index: 9999;
  justify-content: center;
  align-items: center;
}
.popup-content {
  background: #fff;
  border-radius: 12px;
  padding: 25px 35px;
  text-align: center;
  box-shadow: 0 5px 20px rgba(0,0,0,0.2);
  animation: fadeInScale 0.3s ease forwards;
  margin: 20px;
}
.popup-content h5 {
  color: #006b5e;
  font-weight: 600;
  margin-bottom: 10px;
}
.popup-buttons {
  display: flex;
  justify-content: center;
  gap: 15px;
  margin-top: 15px;
}
@keyframes fadeInScale {
  from { opacity: 0; transform: scale(0.8); }
  to { opacity: 1; transform: scale(1); }
}

/* 🔹 Popup Success */
.popup-success {
    position: fixed;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%) scale(0.8);
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.2);
    padding: 25px 35px;
    text-align: center;
    z-index: 10000;
    opacity: 0;
    animation: popupShow 0.5s forwards;
}
.popup-success img { width: 40px; margin-bottom: 10px; }
.popup-success h4 { color: #007a64; margin-bottom: 10px; }
@keyframes popupShow {
    to { opacity: 1; transform: translate(-50%, -50%) scale(1); }
}

/* Select coloring */
select.form-select { transition: all 0.3s ease; }
select[value="Ya"] { background-color: #c8e6c9 !important; }
select[value="Tidak"] { background-color: #ffcdd2 !important; }
select[value="Tidak Dinilai"] { background-color: #f0f0f0 !important; }

/* 🔹 Logout box */
.logout-box {
    position: absolute;
    top: 20px;
    right: 30px;
    display: flex;
    align-items: center;
    gap: 8px;
    background: rgba(255,255,255,0.92);
    padding: 8px 14px;
    font-weight: 600;
    font-size: 14px;
    color: #000;
    backdrop-filter: blur(5px);
}
.logout-box .btn {
    font-size: 13px;
    padding: 4px 10px;
    border-radius: 5px;
}


/* 🔹 Style untuk input readonly (tidak bisa diketik) */
input[readonly] {
  background-color: #e9ecef !important;  /* abu-abu khas Bootstrap */
  color: #555;                           /* teks sedikit lebih gelap */
  cursor: not-allowed;                   /* tanda dilarang saat hover */
  pointer-events: none;                  /* tidak bisa diklik */
  user-select: none;                     /* tidak bisa disorot */
  border: 1px solid #ced4da;
}




label span[style*="color:red"] {
    font-weight: bold;
    margin-left: 2px;
}

</style>




<body>

<div class="logout-box">
  👤 <?= htmlspecialchars($_SESSION['user']['nama']); ?>
  <?php if ($_SESSION['user']['role'] === 'admin'): ?>
    <a href="../user_admin.php" class="btn btn-warning btn-sm fw-semibold px-3 py-1">← Admin</a>
  <?php endif; ?>
  <a href="../logout.php" class="btn btn-outline-danger btn-sm fw-semibold px-3 py-1">Logout</a>
</div>

<!-- Popup Konfirmasi -->
<div class="popup-konfirmasi" id="popupKonfirmasi">
  <div class="popup-content">
    <h5>Konfirmasi Simpan Data</h5>
    <p>Apakah Anda yakin ingin menyimpan data observasi ini?</p>
    <div class="popup-buttons">
      <button type="button" id="btnYa" class="btn btn-success">Ya, Simpan</button>
      <button type="button" id="btnBatal" class="btn btn-secondary">Kembali</button>
    </div>
  </div>
</div>

<div class="form-container">
    <div class="text-center mb-3">
        <img src="kemenkess.png" alt="Logo Kemenkes" style="width:70px;">
        <h5>RSUP Jayapura</h5>
        <p style="color:#388e3c;">Form Observasi Cuci Tangan</p>
    </div>

    <a href="form_input_cuci.php" class="btn btn-secondary w-100 mb-3">← Kembali ke Daftar Ruangan</a>

    <h3>🧾 Observasi: <?= htmlspecialchars($data_observer['ruangan']) ?></h3>
    <p>
       <strong>Bulan:</strong> <?= htmlspecialchars($data_observer['bulan']) ?><br>
       <strong>Observer:</strong> <?= htmlspecialchars($data_observer['observer']) ?>
    </p>

    <form method="POST">
        <input type="hidden" name="tanggal" value="<?= $tanggal_hari_ini ?>">
        <label>Tanggal Observasi:</label>
        <input type="text" class="form-control mb-2" value="<?= $tanggal_tampil ?>" readonly>

        <label>Petugas:</label>
        <input type="text" class="form-control mb-2" value="<?= htmlspecialchars($petugas) ?>" readonly>

        <label>Nama Rekan yang Dilaporkan (opsional):</label>
        <input type="text" name="nama_rekan_dilaporkan" class="form-control mb-3" placeholder="Boleh dikosongkan jika menilai diri sendiri">

        <!-- 🔹 Tambahan baru -->
       <label>Tindakan: <span style="color:red;">*</span></label>
<input type="text" name="tindakan" class="form-control mb-3" placeholder="Contoh: Pemasangan infus" required>

        <?php
        $fields_query = mysqli_query($conn, "SELECT field_name, display_label FROM cuci_tangan_fields ORDER BY id ASC");
        while($f = mysqli_fetch_assoc($fields_query)): ?>
            <div class="mb-3">
                <label><?= htmlspecialchars($f['display_label']) ?></label><br>
                <select name="<?= htmlspecialchars($f['field_name']) ?>" class="form-select" onchange="updateSelectColor(this)">
                    <option value="Tidak Dinilai">Tidak Dinilai</option>
                    <option value="Ya">Ya</option>
                    <option value="Tidak">Tidak</option>
                </select>
            </div>
        <?php endwhile; ?>

        <!-- 🔹 Tambahan dropdown baru -->
   <label>Cuci tangan menggunakan apa: <span style="color:red;">*</span></label>
<select name="cuci_tangan_menggunakan" class="form-select mb-3" required>
    <option value="">Pilih jenis</option>
    <option value="Handwash">Handwash - (Pakai Sabun)</option>
    <option value="Handrub">Handrub - (Antiseptik)</option>
</select>


        <button type="submit" class="btn btn-primary w-100 mt-3">💾 Simpan Data</button>
    </form>
</div>

<?php if ($tampilkan_popup): ?>
<div class="popup-success" id="popupSuccess">
    <img src="https://cdn-icons-png.flaticon.com/512/845/845646.png" alt="Berhasil">
    <h4>Data Berhasil Dikirim!</h4>
    <p>Data observasi cuci tangan telah tersimpan.</p>
</div>
<script>
setTimeout(() => {
    document.getElementById('popupSuccess').style.display = 'none';
    window.location.href = 'form_input_cuci.php';
}, 3000);
</script>
<?php endif; ?>

<script>
// JS lama tetap dipertahankan
function updateSelectColor(select) {
    select.style.backgroundColor =
        select.value === "Ya" ? "#c8e6c9" :
        select.value === "Tidak" ? "#ffcdd2" :
        "#f0f0f0";
}
document.querySelectorAll('select').forEach(sel => updateSelectColor(sel));

const form = document.querySelector("form");
const popup = document.getElementById("popupKonfirmasi");
const btnYa = document.getElementById("btnYa");
const btnBatal = document.getElementById("btnBatal");

form.addEventListener("submit", e => {
    e.preventDefault();
    popup.style.display = "flex";
});
btnYa.addEventListener("click", () => {
    popup.style.display = "none";
    form.submit();
});
btnBatal.addEventListener("click", () => {
    popup.style.display = "none";
});
</script>

</body>
</html>
