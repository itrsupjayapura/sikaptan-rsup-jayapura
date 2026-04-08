<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include '../config.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
  header('Location: ../index.php');
  exit;
}

$observer = isset($_SESSION['user']['username']) ? $_SESSION['user']['nama'] : 'Admin Tidak Dikenal';

$fields_query = mysqli_query($conn, "SELECT field_name, display_label FROM apd_fields ORDER BY id ASC");
$fields = [];
while ($row = mysqli_fetch_assoc($fields_query)) {
  $fields[$row['field_name']] = $row['display_label'];
}

function format_tanggal_indo($tanggal)
{
  if (!$tanggal) return '-';
  $bulanIndo = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
  return date('d', strtotime($tanggal)) . ' ' . $bulanIndo[(int)date('m', strtotime($tanggal))] . ' ' . date('Y', strtotime($tanggal));
}

$tahun_list = mysqli_query($conn, "SELECT DISTINCT YEAR(STR_TO_DATE(CONCAT(bulan,'-01'),'%Y-%m-%d')) AS tahun FROM data_observasi ORDER BY tahun DESC");
$bulan_list = mysqli_query($conn, "SELECT DISTINCT RIGHT(bulan,2) AS bulan FROM data_observasi ORDER BY bulan ASC");
$ruangan_list = mysqli_query($conn, "SELECT nama AS ruangan FROM ruangan ORDER BY nama ASC");

$filter_tahun = $_GET['tahun'] ?? '';
$filter_bulan = $_GET['bulan'] ?? '';
$filter_ruangan = $_GET['ruangan'] ?? '';

$filter_tahun_cuci = $_GET['tahun_cuci'] ?? '';
$filter_bulan_cuci = $_GET['bulan_cuci'] ?? '';
$filter_ruangan_cuci = $_GET['ruangan_cuci'] ?? '';

$where = "1=1";

if ($filter_tahun != '' && $filter_bulan != '') {
  $periode = $filter_tahun . '-' . $filter_bulan;
  $where .= " AND do.bulan='$periode'";
}

if ($filter_ruangan != '') {
  $where .= " AND do.ruangan='$filter_ruangan'";
}

$sql = "SELECT oa.id_observasi, do.bulan, do.ruangan, oa.tanggal, oa.petugas,
        oa.nama_rekan_dilaporkan, oa.numerator, oa.denumerator";

foreach ($fields as $key => $label) {
  $sql .= ", oa.`$key`";
}

$sql .= ", do.observer
        FROM observasi_apd oa
        LEFT JOIN data_observasi do ON oa.id_observasi = do.id
        WHERE $where
        ORDER BY oa.tanggal DESC";
$data = mysqli_query($conn, $sql);
$rows = [];
while ($r = mysqli_fetch_assoc($data)) {
  $rows[] = $r;
}

$nama_rekan_apd = [];


$total_num = 0;
$total_den = 0;
$stat = [];
foreach ($fields as $key => $label) {
  $stat[$key] = ['Ya' => 0, 'Tidak' => 0, 'Tidak Dinilai' => 0];
}
$total_ya = 0;
$total_tidak = 0;
$total_tidak_dinilai = 0;
foreach ($rows as $row) {
  $nama = trim($row['nama_rekan_dilaporkan'] ?? '');
  if ($nama !== '') {
    $nama_rekan_apd[$nama] = true;
  }
  $total_num += $row['numerator'];
  $total_den += $row['denumerator'];
  foreach ($fields as $k => $v) {
    $val = strtolower(trim($row[$k] ?? 'tidak dinilai'));
    if ($val == 'ya') {
      $stat[$k]['Ya']++;
      $total_ya++;
    } elseif ($val == 'tidak') {
      $stat[$k]['Tidak']++;
    } else {
      $stat[$k]['Tidak Dinilai']++;
      $total_tidak_dinilai++;
    }
  }
}

// ================= FIX TOTAL APD =================
$total_ya = 0;
$total_tidak = 0;

foreach ($stat as $item) {
  $total_ya += $item['Ya'];
  $total_tidak += $item['Tidak'];
}

/* ================= HITUNG PERSENTASE (FIX SINKRON CHART) ================= */

// 🔹 hanya gunakan data valid (Ya + Tidak)
$total_valid = $total_ya + $total_tidak;

// 🔹 persen Ya (sama seperti chart)
$persen_ya = $total_valid > 0
  ? round(($total_ya / $total_valid) * 100, 1)
  : 0;

// 🔹 persen Tidak (sama seperti chart)
$persen_tidak = $total_valid > 0
  ? round(($total_tidak / $total_valid) * 100, 1)
  : 0;

// 🔹 tetap hitung Tidak Dinilai (opsional, tidak dipakai di chart utama)
$total_semua = $total_ya + $total_tidak + $total_tidak_dinilai;

$persen_tdk = $total_semua > 0
  ? round(($total_tidak_dinilai / $total_semua) * 100, 1)
  : 0;

// ================= RATA-RATA KEPATUHAN =================
$total_valid = $total_ya + $total_tidak;

$total_persen = ($total_valid > 0)
  ? round(($total_ya / $total_valid) * 100, 2)
  : 0;

/* ================= STATUS KEPATUHAN ================= */
if ($total_persen >= 85) {
  $status_kepatuhan = "Tercapai";
  $icon_status = "fa-check-circle";
  $warna_status = "bg-green";
} else {

  $status_kepatuhan = "Tidak Tercapai";
  $icon_status = "fa-times-circle";
  $warna_status = "bg-red";
}




/* ================= DATA CUCI TANGAN ================= */

$cuci_query = mysqli_query($conn, "
SELECT 
SUM(numerator) as total_num,
SUM(denumerator) as total_den
FROM observasi_cuci_tangan
");

$cuci = mysqli_fetch_assoc($cuci_query);
$total_num_cuci = $cuci['total_num'] ?? 0;
$total_den_cuci = $cuci['total_den'] ?? 0;
$rata_cuci = $total_den_cuci > 0
  ? ($total_num_cuci / $total_den_cuci) * 100
  : 0;

/* ================= STAT CUCI TANGAN ================= */

$fields_cuci = [
  "sebelum_kontak_dengan_pasien" => "Sebelum Kontak Pasien",
  "sebelum_melakukan_tindakan_aseptik" => "Tindakan Aseptik",
  "setelah_terpapar_cairan_tubuh_pasien" => "Cairan Tubuh",
  "setelah_kontak_dengan_pasien" => "Setelah Kontak Pasien",
  "setelah_kontak_dengan_lingkungan_pasien" => "Lingkungan Pasien"
];

$stat_cuci = [];

foreach ($fields_cuci as $k => $v) {
  $stat_cuci[$k] = ['Ya' => 0, 'Tidak' => 0, 'Tidak Dinilai' => 0];
}

/* ================= AMBIL DATA ================= */

$where_cuci = "1=1";

$nama_ruangan_cuci = $filter_ruangan_cuci != ''
  ? $filter_ruangan_cuci
  : 'Semua Ruangan';

// filter bulan + tahun
if ($filter_tahun_cuci != '' && $filter_bulan_cuci != '') {
  $periode_cuci = $filter_tahun_cuci . '-' . $filter_bulan_cuci;
  $where_cuci .= " AND bulan='$periode_cuci'";
}

// filter ruangan
if ($filter_ruangan_cuci != '') {
  $where_cuci .= " AND ruangan='$filter_ruangan_cuci'";
}

// query pakai filter
$query_cuci_detail = mysqli_query($conn, "
SELECT * FROM observasi_cuci_tangan
WHERE $where_cuci
");

// ================= TAMBAHAN =================
$nama_rekan_cuci = [];
$ruangan_cuci_terpakai = [];

/* ================= JUMLAH REKAN CUCI ================= */
$jumlah_rekan_cuci = 0;

$query_rekan_cuci = mysqli_query($conn, "
SELECT COUNT(*) as total 
FROM observasi_cuci_tangan
WHERE $where_cuci
");

$data_rekan = mysqli_fetch_assoc($query_rekan_cuci);
$jumlah_rekan_cuci = $data_rekan['total'] ?? 0;

$total_num_cuci = 0;
$total_den_cuci = 0;

$total_ya_cuci = 0;
$total_tidak_cuci = 0;
$total_tidak_dinilai_cuci = 0;

while ($row = mysqli_fetch_assoc($query_cuci_detail)) {

  // ================= TAMBAHAN =================

  // 🔥 HITUNG NAMA REKAN (UNIK)
  $nama = trim($row['nama_rekan_dilaporkan'] ?? '');
  if ($nama !== '') {
    $nama_rekan_cuci[$nama] = true;
  }

  // 🔥 SIMPAN RUANGAN YANG TERPAKAI
  if (!empty($row['ruangan'])) {
    $ruangan_cuci_terpakai[$row['ruangan']] = true;
  }


  // 🔹 ambil numerator & denominator langsung dari loop

  $total_num_cuci += $row['numerator'];
  $total_den_cuci += $row['denumerator'];


  foreach ($fields_cuci as $k => $v) {

    $val = strtolower(trim($row[$k] ?? ''));

    if ($val === 'ya') {
      $stat_cuci[$k]['Ya']++;
      $total_ya_cuci++;
    } elseif ($val === 'tidak') {
      $stat_cuci[$k]['Tidak']++;
      $total_tidak_cuci++;
    } else {
      $stat_cuci[$k]['Tidak Dinilai']++;
      $total_tidak_dinilai_cuci++;
    }
  }
}


/* ================= FIX TOTAL CUCI (SINKRON) ================= */

$total_ya_cuci = 0;
$total_tidak_cuci = 0;

foreach ($stat_cuci as $item) {
  $total_ya_cuci += $item['Ya'];
  $total_tidak_cuci += $item['Tidak'];
}


$total_valid_cuci = $total_ya_cuci + $total_tidak_cuci;

$persen_ya_cuci = $total_valid_cuci > 0
  ? round(($total_ya_cuci / $total_valid_cuci) * 100, 1)
  : 0;

$persen_tidak_cuci = $total_valid_cuci > 0
  ? round(($total_tidak_cuci / $total_valid_cuci) * 100, 1)
  : 0;


/* ================= PERSENTASE (SAMA DENGAN CHART) ================= */

$total_valid_cuci = $total_ya_cuci + $total_tidak_cuci;

$persen_ya_cuci = $total_valid_cuci > 0
  ? round(($total_ya_cuci / $total_valid_cuci) * 100, 1)
  : 0;

$persen_tidak_cuci = $total_valid_cuci > 0
  ? round(($total_tidak_cuci / $total_valid_cuci) * 100, 1)
  : 0;

/* ================= RATA-RATA CUCI TANGAN (BENAR) ================= */

$total_valid_cuci = $total_ya_cuci + $total_tidak_cuci;

$rata_cuci = ($total_den_cuci > 0)
  ? ($total_num_cuci / $total_den_cuci) * 100
  : 0;



/* ================= STATUS ================= */

if ($rata_cuci >= 85) {
  $status_kepatuhan_cuci = "Tercapai";
  $warna_status_cuci = "bg-green";
  $icon_status_cuci = "fa-check-circle";
} else {
  $status_kepatuhan_cuci = "Tidak Tercapai";
  $warna_status_cuci = "bg-red";
  $icon_status_cuci = "fa-times-circle";
}


/* ================= HANDWASH vs HANDRUB ================= */

$handwash = 0;
$handrub = 0;


$jenis_query = mysqli_query($conn, "
SELECT cuci_tangan_menggunakan 
FROM observasi_cuci_tangan
WHERE $where_cuci
");

while ($j = mysqli_fetch_assoc($jenis_query)) {

  $jenis = strtolower(trim($j['cuci_tangan_menggunakan']));

  if ($jenis === 'handwash') {
    $handwash++;
  } elseif ($jenis === 'handrub') {
    $handrub++;
  }
}


// ================= TAMBAHAN =================

// 🔥 TOTAL REKAN (UNIK)
$jumlah_rekan_cuci = count($nama_rekan_cuci);

// 🔥 NAMA RUANGAN (SAMA SEPERTI LAPORAN)
if ($filter_ruangan_cuci == '') {
  if (!empty($ruangan_cuci_terpakai)) {
    $nama_ruangan_cuci = 'Semua Ruangan';
  } else {
    $nama_ruangan_cuci = '-';
  }
} else {
  $nama_ruangan_cuci = $filter_ruangan_cuci;
}







/* ================= TOTAL FIX (SAMA PERSIS CHART) ================= */

// 🔹 hanya valid data
$total_valid_cuci = $total_ya_cuci + $total_tidak_cuci;

// 🔹 persen (harus sama chart)
$persen_ya_cuci = $total_valid_cuci > 0
  ? round(($total_ya_cuci / $total_valid_cuci) * 100, 1)
  : 0;

$persen_tidak_cuci = $total_valid_cuci > 0
  ? round(($total_tidak_cuci / $total_valid_cuci) * 100, 1)
  : 0;



/* ================= STATUS ================= */

if ($rata_cuci >= 85) {
  $status_kepatuhan_cuci = "Tercapai";
  $warna_status_cuci = "bg-green";
  $icon_status_cuci = "fa-check-circle";
} else {
  $status_kepatuhan_cuci = "Tidak Tercapai";
  $warna_status_cuci = "bg-red";
  $icon_status_cuci = "fa-times-circle";
}

/* ================= TAMBAHAN ================= */

// jumlah rekan unik
$jumlah_rekan_cuci = count($nama_rekan_cuci);

// nama ruangan
if ($filter_ruangan_cuci == '') {
  $nama_ruangan_cuci = !empty($ruangan_cuci_terpakai)
    ? 'Semua Ruangan'
    : '-';
} else {
  $nama_ruangan_cuci = $filter_ruangan_cuci;
}




/* ================= AMBIL DATA CUCI ================= */

$query_cuci_detail = mysqli_query($conn, "
SELECT * FROM observasi_cuci_tangan
WHERE $where_cuci
");

$nama_rekan_cuci = [];
$ruangan_cuci_terpakai = [];

$total_ya_cuci = 0;
$total_tidak_cuci = 0;
$total_tidak_dinilai_cuci = 0;

$total_num_cuci = 0;
$total_den_cuci = 0;

while ($row = mysqli_fetch_assoc($query_cuci_detail)) {

  // 🔥 nama rekan unik
  $nama = trim($row['nama_rekan_dilaporkan'] ?? '');
  if ($nama !== '') {
    $nama_rekan_cuci[$nama] = true;
  }

  // 🔥 ruangan terpakai
  if (!empty($row['ruangan'])) {
    $ruangan_cuci_terpakai[$row['ruangan']] = true;
  }

  foreach ($fields_cuci as $k => $v) {

    $val = strtolower(trim($row[$k] ?? ''));

    if ($val === 'ya') {
      $stat_cuci[$k]['Ya']++;
      $total_ya_cuci++;
    } elseif ($val === 'tidak') {
      $stat_cuci[$k]['Tidak']++;
      $total_tidak_cuci++;
    } else {
      $stat_cuci[$k]['Tidak Dinilai']++;
      $total_tidak_dinilai_cuci++;
    }
  }
}

/* ================= TOTAL FIX (SAMA PERSIS CHART) ================= */

// 🔹 hanya valid data
$total_valid_cuci = $total_ya_cuci + $total_tidak_cuci;

// 🔹 persen (harus sama chart)
$persen_ya_cuci = $total_valid_cuci > 0
  ? round(($total_ya_cuci / $total_valid_cuci) * 100, 1)
  : 0;

$persen_tidak_cuci = $total_valid_cuci > 0
  ? round(($total_tidak_cuci / $total_valid_cuci) * 100, 1)
  : 0;

// 🔹 numerator & denominator (SAMA LOGIKA CHART)
$total_num_cuci = $total_ya_cuci;
$total_den_cuci = $total_valid_cuci;

/* ================= RATA-RATA ================= */

$rata_cuci = $total_valid_cuci > 0
  ? round(($total_ya_cuci / $total_valid_cuci) * 100, 2)
  : 0;

/* ================= STATUS ================= */

if ($rata_cuci >= 85) {
  $status_kepatuhan_cuci = "Tercapai";
  $warna_status_cuci = "bg-green";
  $icon_status_cuci = "fa-check-circle";
} else {
  $status_kepatuhan_cuci = "Tidak Tercapai";
  $warna_status_cuci = "bg-red";
  $icon_status_cuci = "fa-times-circle";
}

/* ================= TAMBAHAN ================= */

// jumlah rekan unik
$jumlah_rekan_cuci = count($nama_rekan_cuci);

// nama ruangan
if ($filter_ruangan_cuci == '') {
  $nama_ruangan_cuci = !empty($ruangan_cuci_terpakai)
    ? 'Semua Ruangan'
    : '-';
} else {
  $nama_ruangan_cuci = $filter_ruangan_cuci;
}


?>

<!DOCTYPE html>
<html>

<head>

  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1"></script>
  <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

  <style>
    /* ================= GLOBAL ================= */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    /* ================= HEADER ================= */
    .page-title {
      text-align: center;
      margin-bottom: 25px;
      padding: 15px;
    }

    .page-title h2 {
      font-size: 26px;
      font-weight: 600;
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 10px;
      margin-bottom: 6px;
    }

    .page-title h2 i {
      color: #009879;
      font-size: 34px;
      transition: .3s;
    }

    .page-title h2:hover i {
      transform: scale(1.15) rotate(6deg);
      filter: drop-shadow(0 4px 8px rgba(0, 0, 0, .2));
    }

    .page-title p {
      color: #0d3f7d;
      font-size: 14px;
      max-width: 520px;
      margin: auto;
      line-height: 1.5;
    }

    /* ================= TAB MENU ================= */
    .tab {
      display: flex;
      justify-content: center;
      gap: 14px;
      flex-wrap: wrap;
      margin-bottom: 20px;
    }

    .tab button {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 12px 20px;
      background: #ffffff;
      border: none;
      border-radius: 12px;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      box-shadow: 0 6px 16px rgba(0, 0, 0, .08);
      transition: .25s;
    }

    .tab button i {
      font-size: 16px;
      transition: .3s;
    }

    .tab button:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 24px #007f69;
    }

    .tab button:hover i {
      transform: scale(1.15) rotate(-6deg);
    }

    .tab button.active {
      background: linear-gradient(135deg, #009879, #007f69);
      color: #fff;
      box-shadow: 0 8px 22px rgba(0, 0, 0, .18);
    }


    /* ================= TAB CONTENT ================= */
    .tabcontent {
      display: none;
      background: #007f6a3b;
      padding: 35px;
      border-radius: 16px;
      box-shadow: 0 12px 35px rgba(0, 0, 0, .08);
      max-width: 1300px;
      margin: 20px auto;
      animation: fadeSlide .5s ease;
      transition: all .35s ease;
    }

    .tabcontent:hover {
      transform: translateY(-6px) scale(1.01);
      box-shadow: 0 18px 45px rgba(0, 0, 0, .12);
      background: #007f6a45;
    }

    @keyframes fadeSlide {
      from {
        opacity: 0;
        transform: translateY(20px) scale(.97);
      }

      to {
        opacity: 1;
        transform: translateY(0) scale(1);
      }
    }

    .tabcontent h2 {
      font-size: 24px;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 25px;
      color: #007f69;
      transition: all .3s ease;
    }

    .tabcontent:hover h2 {
      letter-spacing: .5px;
    }

    .tabcontent h2 i {
      font-size: 36px;
      color: #007f69;
      transition: all .35s ease;
    }

    .tabcontent:hover h2 i {
      transform: rotate(-8deg) scale(1.1);
      color: #134E8E;
    }


    /* ================= FILTER Form ================= */
    .filter-form {
      display: flex;
      gap: 20px;
      flex-wrap: wrap;
      align-items: end;
      justify-content: center;
      background: #f9fbff;
      padding: 18px;
      border-radius: 12px;
      margin-bottom: 30px;
      border: 1px solid #e5e7eb;
      animation: fadeSlideUp .6s ease;
      transition: all .3s ease;
    }

    .filter-form:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
    }

    .filter-group {
      display: flex;
      flex-direction: column;
      font-size: 12px;
      gap: 6px;
      transition: all .25s ease;
    }

    .filter-group:hover {
      transform: translateY(-2px);
    }

    .filter-group label {
      display: flex;
      align-items: center;
      gap: 6px;
      font-weight: 600;
      color: #2c3e50;
      transition: color .25s ease;
    }

    .filter-group:hover label {
      color: #007f69;
    }

    .filter-select {
      padding: 9px 12px;
      border-radius: 8px;
      border: 1px solid #d1d5db;
      min-width: 150px;
      background: white;
      font-size: 12px;
      transition: all .25s ease;
      cursor: pointer;
    }

    .filter-select:hover {
      border-color: #2563eb;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
      transform: translateY(-1px);
    }

    .filter-select:focus {
      outline: none;
      border-color: #2563eb;
      box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
      transform: scale(1.02);
    }


    @keyframes fadeSlideUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }






    /* ================= BUTTON ================= */
    .btn-primary {
      background: linear-gradient(135deg, #009879, #007f69);
      color: #fff;
      border: none;
      padding: 10px 18px;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
      transition: .2s;
    }

    .btn-primary:hover {
      transform: translateY(-2px);
    }

    /* ================= GRID STATS================= */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap: 20px;
      justify-content: center;
      margin-top: 25px;
    }

    /* .stat-card{
padding:24px;
border-radius:16px;
text-align:center;
position:relative;
overflow:hidden;
background:#fff;
box-shadow:0 10px 25px rgba(0,0,0,.08);
transition:all .45s cubic-bezier(.22,.61,.36,1);
opacity:0;
transform:translateY(30px) scale(.94);
animation:cardFade .8s ease forwards;
}

.stat-card:nth-child(1){animation-delay:.1s;}
.stat-card:nth-child(2){animation-delay:.2s;}
.stat-card:nth-child(3){animation-delay:.3s;}
.stat-card:nth-child(4){animation-delay:.4s;}
.stat-card:nth-child(5){animation-delay:.5s;}

.stat-card:hover{
transform:translateY(-12px) scale(1.04);
box-shadow:0 25px 45px rgba(0,0,0,.18);
} */


    /* ================= CARD STATS ================= */

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap: 18px;
      margin-top: 25px;
    }

    /* CARD */
    .stat-card {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      padding: 18px 16px;
      border-radius: 14px;
      background: linear-gradient(135deg, #e6f4f1, #cfe9e2);
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
      transition: all .25s ease;
      position: relative;
      overflow: hidden;
      min-height: 110px;
    }

    /* hover */
    .stat-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
    }

    /* ICON BOX */
    .stat-card .icon-box {
      width: 36px;
      height: 36px;
      border-radius: 10px;
      background: #ffffff;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 3px 8px rgba(0, 0, 0, 0.12);
      margin-bottom: 8px;
    }

    /* ICON */
    .stat-card i {
      font-size: 16px;
      color: #22c55e;
    }

    /* ANGKA */
    .stat-card h3 {
      font-size: 22px;
      font-weight: 700;
      margin: 3px 0;
      color: #0f172a;
    }

    /* LABEL */
    .stat-card p {
      font-size: 13px;
      color: #475569;
      margin: 0;
    }

    /* STATUS CARD */
    .bg-green {
      background: linear-gradient(135deg, #d8f5e9, #bdebd7);
    }

    .bg-red {
      background: linear-gradient(135deg, #fde2e2, #f9caca);
    }


    /* ================= SHINE EFFECT ================= */
    .stat-card::after {
      content: "";
      position: absolute;
      top: -120%;
      left: -60%;
      width: 60%;
      height: 300%;
      background: linear-gradient(120deg,
          transparent,
          rgba(255, 255, 255, .6),
          transparent);
      transform: rotate(25deg);
      transition: .6s;
    }

    .stat-card:hover::after {
      left: 120%;
    }

    /* ================= GLOW EFFECT ================= */
    .stat-card::before {
      content: "";
      position: absolute;
      inset: 0;
      border-radius: 16px;
      opacity: 0;
      transition: .4s;
    }

    .stat-card:hover::before {
      opacity: .15;
    }

    /* ================= ICON ================= */
    .stat-card i {
      font-size: 28px;
      margin-bottom: 10px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 50px;
      height: 50px;
      border-radius: 14px;
      background: #fff;
      box-shadow: 0 6px 14px rgba(0, 0, 0, .12);
      transition: all .4s ease;
    }

    /* icon animasi hover */
    .stat-card:hover i {
      transform: scale(1.18) rotate(6deg);
      box-shadow: 0 10px 22px rgba(0, 0, 0, .18);
    }


    /* ================= TEXT ================= */
    .stat-card h3 {
      font-size: 28px;
      margin: 8px 0;
      font-weight: 500;
      letter-spacing: .5px;
    }

    .stat-card p {
      font-size: 13px;
      color: #6b7280;
    }



    /* ================= COLORS ================= */
    .bg-green {
      background: linear-gradient(145deg, #e7f8f1, #dff5eb);
    }

    .bg-green i {
      color: #10b981;
    }

    .bg-green::before {
      background: #10b981;
    }


    .bg-red {
      background: linear-gradient(145deg, #ffe7e7, #ffdede);
    }

    .bg-red i {
      color: #ef4444;
    }

    .bg-red::before {
      background: #ef4444;
    }

    .bg-yellow {
      background: linear-gradient(145deg, #fff6d8, #ffefc4);
    }

    .bg-yellow i {
      color: #f59e0b;
    }

    .bg-yellow::before {
      background: #f59e0b;
    }


    .bg-blue {
      background: linear-gradient(145deg, #e7f1ff, #dbeafe);
    }

    .bg-blue i {
      color: #3b82f6;
    }

    .bg-blue::before {
      background: #3b82f6;
    }

    .bg-gray {
      background: linear-gradient(145deg, #f1f5f9, #e8edf2);
    }

    .bg-gray i {
      color: #64748b;
    }

    .bg-gray::before {
      background: #64748b;
    }

    /* ================= ANIMATION ================= */
    @keyframes cardFade {
      to {
        opacity: 1;
        transform: translateY(0) scale(1);
      }
    }


    /* ================= ICON ================= */
    .stat-card i {
      font-size: 28px;
      margin-bottom: 10px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 48px;
      height: 48px;
      border-radius: 12px;
      background: #fff;
      box-shadow: 0 4px 10px rgba(0, 0, 0, .08);
      transition: .35s;
    }


    /* icon hover */
    .stat-card:hover i {
      transform: scale(1.15) rotate(4deg);
      box-shadow: 0 8px 18px rgba(0, 0, 0, .18);
    }

    /* ================= TEXT ================= */
    .stat-card h3 {
      font-size: 28px;
      margin: 8px 0;
      font-weight: 700;
      letter-spacing: .5px;
    }

    .stat-card p {
      font-size: 13px;
      color: #6b7280;
      margin-top: 2px;
    }


    /* ================= COLORS ================= */
    .bg-green {
      background: linear-gradient(145deg, #e7f8f1, #dff5eb);
    }

    .bg-green i {
      color: #10b981;
    }

    .bg-red {
      background: linear-gradient(145deg, #ffe7e7, #ffdede);
    }

    .bg-red i {
      color: #ef4444;
    }

    .bg-yellow {
      background: linear-gradient(145deg, #fff6d8, #ffefc4);
    }

    .bg-yellow i {
      color: #f59e0b;
    }

    .bg-blue {
      background: linear-gradient(145deg, #e7f1ff, #dbeafe);
    }

    .bg-blue i {
      color: #3b82f6;
    }

    .bg-gray {
      background: linear-gradient(145deg, #f1f5f9, #e8edf2);
    }

    .bg-gray i {
      color: #64748b;
    }


    /* ================= ANIMATION ================= */
    @keyframes cardFade {
      to {
        opacity: 1;
        transform: translateY(0) scale(1);
      }
    }


    /* ================= CHART ================= */
    .chart-container {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      justify-content: center;
      margin-top: 25px;
    }

    .chart-box {
      background: #ffffff;
      padding: 18px;
      border-radius: 14px;
      border: 1px solid rgba(0, 0, 0, .05);
      box-shadow:
        0 10px 28px rgba(0, 0, 0, .08),
        0 2px 6px rgba(0, 0, 0, .05);
      transition:
        transform .35s ease,
        box-shadow .35s ease,
        filter .35s ease;
      width: 300px;
      transform: translateY(0) scale(1);
    }

    .chart-box:hover {
      transform: translateY(-8px) scale(1.02);
      box-shadow:
        0 20px 45px rgba(0, 0, 0, .18),
        0 8px 20px rgba(0, 0, 0, .12),
        0 2px 6px rgba(0, 0, 0, .08);
      filter: brightness(1.02);
    }

    .chart-box h3 {
      font-size: 15px;
      font-weight: 600;
      text-align: center;
      margin-bottom: 12px;
      transition: all .3s ease;
    }

    .chart-box:hover h3 {
      transform: translateY(-1px);
      letter-spacing: .3px;
    }

    .chart-box p {
      font-size: 13px;
      color: #6b7280;
      text-align: center;
      transition: all .3s ease;
    }

    .chart-box:hover p {
      color: #4b5563;
    }

    canvas {
      width: 100% !important;
      height: 240px !important;
      transition: transform .35s ease;
    }

    .chart-box:hover canvas {
      transform: scale(1.03);
    }


    /* ================= RESPONSIVE ================= */
    /* ===== Laptop kecil ===== */
    @media (max-width:1200px) {

      .tabcontent {
        max-width: 95%;
        padding: 30px;
      }

      .chart-box {
        width: 280px;
      }

      .stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
      }

    }


    /* ===== Tablet ===== */
    @media (max-width:992px) {

      .page-title h2 {
        font-size: 24px;
      }

      .tab button {
        padding: 10px 16px;
        font-size: 14px;
      }

      .filter-form {
        gap: 15px;
      }

      .filter-select {
        min-width: 130px;
      }

      .chart-container {
        justify-content: center;
      }

      .chart-box {
        width: 260px;
      }

      .stats-grid {
        grid-template-columns: repeat(2, 1fr);
      }

    }


    /* ===== HP besar ===== */
    @media (max-width:768px) {

      .page-title h2 {
        font-size: 22px;
        flex-direction: column;
        gap: 6px;
      }

      .page-title h2 i {
        font-size: 28px;
      }

      .tab {
        gap: 10px;
      }

      .tab button {
        padding: 9px 14px;
        font-size: 13px;
      }

      .tabcontent {
        padding: 25px;
      }

      .filter-form {
        flex-direction: column;
        align-items: stretch;
      }

      .filter-group {
        width: 100%;
      }

      .filter-select {
        width: 100%;
      }

      .btn-primary {
        width: 100%;
      }

      .chart-container {
        flex-direction: column;
        align-items: center;
      }

      .chart-box {
        width: 100%;
        max-width: 420px;
      }

      .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
      }

      .stat-card {
        padding: 20px;
      }
    }


    /* ===== HP kecil ===== */
    @media (max-width:480px) {

      body {
        padding: 15px;
      }

      .page-title h2 {
        font-size: 20px;
      }

      .page-title p {
        font-size: 12px;
      }

      .tab button {
        font-size: 12px;
        padding: 8px 12px;
      }

      .tabcontent {
        padding: 18px;
      }

      .stats-grid {
        grid-template-columns: 1fr;
      }

      .chart-box {
        max-width: 100%;
      }

      .stat-card h3 {
        font-size: 24px;
      }

      .stat-card p {
        font-size: 12px;
      }

      canvas {
        height: 220px !important;
      }

    }


    /* ===== HP sangat kecil ===== */
    @media (max-width:360px) {
      .page-title h2 {
        font-size: 18px;
      }

      .tab button {
        font-size: 11px;
        padding: 7px 10px;
      }

      .stat-card {
        padding: 18px;
      }

      .stat-card h3 {
        font-size: 22px;
      }

    }

    .chart-top {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 25px;
      margin-bottom: 20px;
    }

    .chart-top canvas {
      max-width: 280px !important;
      max-height: 280px !important;
    }

    .chart-box {
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .chart-label {
      margin-top: 8px;
      font-size: 14px;
      color: #333;
      text-align: center;
    }

    .glass {
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(8px);
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .chart-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
      gap: 25px;
      margin-top: 25px;
    }

    .chart-card {
      background: #ffffff;
      border-radius: 16px;
      padding: 20px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, .08);
      transition: 0.3s;
      text-align: center;
    }

    .chart-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 15px 35px rgba(0, 0, 0, .15);
    }

    .chart-card h3 {
      font-size: 15px;
      margin-bottom: 10px;
      font-weight: 600;
    }

    .chart-canvas {
      position: relative;
      width: 100%;
      height: 260px;
    }

    .chart-canvas canvas {
      width: 100% !important;
      height: 100% !important;
    }

    .chart-info {
      margin-top: 10px;
      font-size: 13px;
      color: #555;
      display: flex;
      flex-direction: column;
      gap: 3px;
    }
  </style>



</head>

<body>

  <div class="page-title">
    <h2>
      <i class="fa-solid fa-chart-column">
      </i> Dashboard Kepatuhan APD & Cuci Tangan
    </h2>
    <p>
      Silakan pilih tab APD atau Cuci Tangan di bawah ini untuk melihat dashboard pemantauan masing-masing.
    </p>
  </div>


  <div class="tab">
    <button class="tablinks" onclick="openTab(event,'apd')" id="defaultOpen">
      <i class="fa-solid fa-user-shield"></i>
      Kepatuhan APD
    </button>

    <button class="tablinks" onclick="openTab(event,'cuci')">
      <i class="fa-solid fa-hands-bubbles"></i>
      Cuci Tangan
    </button>
  </div>









  <!-- ================= APD TAB ================= -->
  <div id="apd" class="tabcontent">
    <h2>
      <i class="fa-solid fa-file-lines"></i>
      Laporan Kepatuhan APD Bulanan
    </h2>

    <form method="GET" class="filter-form">
      <input type="hidden" name="tab" value="apd">
      <div class="filter-group">
        <label> <i class="fa-solid fa-calendar"></i>Bulan</label>
        <select name="bulan" class="filter-select">
          <option value="">Semua Bulan</option>

          <?php
          $nama_bulan = ['01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Agu', '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des'];
          while ($b = mysqli_fetch_assoc($bulan_list)) {
          ?>

            <option value="<?= $b['bulan'] ?>" <?= ($filter_bulan == $b['bulan']) ? 'selected' : '' ?>>
              <?= $nama_bulan[$b['bulan']] ?>
            </option>
          <?php } ?>
        </select>
      </div>

      <div class="filter-group">
        <label> <i class="fa-solid fa-calendar-days"></i>Tahun</label>
        <select name="tahun" class="filter-select">
          <option value="">Semua Tahun</option>
          <?php while ($t = mysqli_fetch_assoc($tahun_list)) { ?>
            <option value="<?= $t['tahun'] ?>" <?= ($filter_tahun == $t['tahun']) ? 'selected' : '' ?>>
              <?= $t['tahun'] ?>
            </option>
          <?php } ?>
        </select>
      </div>

      <div class="filter-group">
        <label> <i class="fa-solid fa-door-open"></i> Ruangan</label>
        <select name="ruangan" class="filter-select">
          <option value="">Semua Rangan</option>
          <?php while ($r = mysqli_fetch_assoc($ruangan_list)) { ?>
            <option value="<?= $r['ruangan'] ?>" <?= ($filter_ruangan == $r['ruangan']) ? 'selected' : '' ?>>
              <?= $r['ruangan'] ?>
            </option>
          <?php } ?>
        </select>
      </div>

      <div style="display:flex;align-items:flex-end">
        <button class="btn-primary">
          <i class="fa-solid fa-filter"></i>
          Tampilkan
        </button>
      </div>

    </form>



    <!-- ====== Grid/Box = = = = = -->
    <div class="stats-grid">

      <div class="stat-card bg-yellow">
        <i class="fa-regular fa-square-check"></i>
        <h3><?= $total_ya ?></h3>
        <p>Total "Ya"</p>
        <small><?= $persen_ya ?>%</small>
      </div>

      <div class="stat-card bg-red">
        <i class="fa-solid fa-xmark"></i>
        <h3><?= $total_tidak ?></h3>
        <p>Total "Tidak"</p>
        <small><?= $persen_tidak ?>%</small>
      </div>

      <div class="stat-card bg-green">
        <div class="stat-icon">
          <i class="fa-solid fa-chart-simple"></i>
        </div>
        <h2><?= $total_persen ?>%</h2>
        <p>Rata-rata Kepatuhan</p>
      </div>


      <div class="stat-card <?= $warna_status ?>">
        <div class="stat-icon">
          <i class="fa-solid <?= $icon_status ?>"></i>
        </div>
        <h2><?= $status_kepatuhan ?></h2>
        <p>Status Kepatuhan</p>
      </div>

      <!-- <div class="stat-card bg-green">
<i class="fa-solid fa-circle-check"></i>
<h3><?= $total_persen ?>%</h3>
<p>Rata-rata Kepatuhan</p>
</div>

<div class="stat-card <?= $warna_status ?>">
<i class="fa-solid fa-ranking-star"></i>
<h3><?= $total_persen ?>%</h3>
<p><?= $status_kepatuhan ?></p>
<small>Status Kepatuhan</small>
</div> -->

      <div class="stat-card bg-blue">
        <i class="fa-solid fa-user-pen"></i>
        <h3><?= count($nama_rekan_apd) ?></h3>
        <p>Nama Rekan Dilaporkan</p>
      </div>
    </div>


    <!-- = = = = = Char /Grafik = = = = = -->
    <div class="chart-container">
      <div class="chart-box">
        <h3>Distribusi Jawaban Observasi</h3>
        <canvas id="chartPieApd"></canvas>
      </div>

      <div class="chart-box">
        <h3>Perbandingan Tiap Jenis APD</h3>
        <canvas id="chartBarApd"></canvas>
      </div>

      <div class="chart-box">

        <h3>Perbandingan Numerator VS Denumerator </h3>
        <canvas id="chartNDApd"></canvas>
        <p>Total Numerator : <?= $total_num ?></p>
        <p>Total Denominator : <?= $total_den ?></p>
      </div>
    </div>
  </div>



























  <!-- ================= CUCI TANGAN TAB ================= -->
  <div id="cuci" class="tabcontent">
    <h2>
      <i class="fa-solid fa-hands-bubbles"></i>
      Dashboard Kepatuhan Cuci Tangan
    </h2>

    <?php
    /* ================= FILTER DATA CUCI TANGAN ================= */

    $tahun_list2 = mysqli_query($conn, "
SELECT DISTINCT YEAR(STR_TO_DATE(CONCAT(bulan,'-01'),'%Y-%m-%d')) AS tahun 
FROM data_observasi 
ORDER BY tahun DESC
");

    $bulan_list2 = mysqli_query($conn, "
SELECT DISTINCT RIGHT(bulan,2) AS bulan 
FROM data_observasi 
ORDER BY bulan ASC
");

    $ruangan_list2 = mysqli_query($conn, "
SELECT nama AS ruangan 
FROM ruangan 
ORDER BY nama ASC
");
    ?>

    <form method="GET" class="filter-form">

      <!-- 🔥 SIMPAN TAB -->
      <input type="hidden" name="tab" value="cuci">

      <!-- ================= BULAN ================= -->
      <div class="filter-group">
        <label><i class="fa-solid fa-calendar"></i> Bulan</label>

        <select name="bulan_cuci" class="filter-select">
          <option value="">Semua Bulan</option>

          <?php
          $nama_bulan = [
            '01' => 'Jan',
            '02' => 'Feb',
            '03' => 'Mar',
            '04' => 'Apr',
            '05' => 'Mei',
            '06' => 'Jun',
            '07' => 'Jul',
            '08' => 'Agu',
            '09' => 'Sep',
            '10' => 'Okt',
            '11' => 'Nov',
            '12' => 'Des'
          ];

          while ($b = mysqli_fetch_assoc($bulan_list2)) {
          ?>
            <option value="<?= $b['bulan'] ?>"
              <?= ($filter_bulan_cuci == $b['bulan']) ? 'selected' : '' ?>>

              <?= $nama_bulan[$b['bulan']] ?>

            </option>
          <?php } ?>
        </select>
      </div>


      <!-- ================= TAHUN ================= -->
      <div class="filter-group">
        <label><i class="fa-solid fa-calendar-days"></i> Tahun</label>

        <select name="tahun_cuci" class="filter-select">
          <option value="">Semua Tahun</option>

          <?php while ($t = mysqli_fetch_assoc($tahun_list2)) { ?>
            <option value="<?= $t['tahun'] ?>"
              <?= ($filter_tahun_cuci == $t['tahun']) ? 'selected' : '' ?>>

              <?= $t['tahun'] ?>

            </option>
          <?php } ?>
        </select>
      </div>


      <!-- ================= RUANGAN ================= -->
      <div class="filter-group">
        <label><i class="fa-solid fa-door-open"></i> Ruangan</label>

        <select name="ruangan_cuci" class="filter-select">
          <option value="">Semua Ruangan</option>

          <?php while ($r = mysqli_fetch_assoc($ruangan_list2)) { ?>
            <option value="<?= $r['ruangan'] ?>"
              <?= ($filter_ruangan_cuci == $r['ruangan']) ? 'selected' : '' ?>>

              <?= $r['ruangan'] ?>

            </option>
          <?php } ?>
        </select>
      </div>


      <!-- ================= BUTTON ================= -->
      <div style="display:flex;align-items:flex-end">
        <button class="btn-primary">
          <i class="fa-solid fa-filter"></i> Tampilkan
        </button>
      </div>

    </form>



    <div class="stats-grid">
      <div class="stat-card bg-yellow">
        <i class="fa-solid fa-check"></i>
        <h3><?= $total_num_cuci ?></h3>
        <p>Total "Ya"</p>
        <small><?= $persen_ya_cuci ?>%</small>
      </div>

      <div class="stat-card bg-red">
        <i class="fa-solid fa-xmark"></i>
        <h3><?= $total_tidak_cuci ?></h3>
        <p>Total "Tidak"</p>
        <small><?= $persen_tidak_cuci ?>%</small>
      </div>

      <div class="stat-card">
        <div class="icon-box">
          <i class="fa-solid fa-chart-simple"></i>
        </div>
        <h3><?= number_format($rata_cuci, 2) ?>%</h3>
        <p>Rata-rata Kepatuhan</p>
      </div>

      <div class="stat-card <?= $warna_status_cuci ?>">
        <div class="icon-box">
          <i class="fa-solid <?= $icon_status_cuci ?>"></i>
        </div>
        <h3><?= $status_kepatuhan_cuci ?></h3>
        <p>Status Kepatuhan</p>
      </div>

      <div class="stat-card bg-blue">
        <i class="fa-solid fa-door-open"></i>
        <h3><?= $nama_ruangan_cuci ?></h3>
        <p>Nama Ruangan</p>
      </div>

      <div class="stat-card bg-blue">
        <i class="fa-solid fa-user-pen"></i>
        <h3><?= $jumlah_rekan_cuci ?></h3>
        <p>Nama Rekan Dilaporkan</p>
      </div>
    </div>


    <div class="chart-grid">
      <!-- PIE OBSERVASI -->
      <div class="chart-card">
        <h3>Distribusi Jawaban Observasi Cuci Tangan</h3>
        <div class="chart-canvas">
          <canvas id="chartPieCuci"></canvas>
        </div>
        <div class="chart-info">
          <span>Patuh : <?= $total_num_cuci ?></span>
          <span>Tidak Patuh : <?= $total_tidak_cuci ?></span>
        </div>
      </div>



      <!-- BAR -->
      <div class="chart-card">
        <h3>Perbandingan Tiap Komponen Cuci Tangan</h3>
        <div class="chart-canvas">
          <canvas id="chartBarCuci"></canvas>
        </div>
      </div>



      <!-- DOUGHNUT -->
      <div class="chart-card">
        <h3>Numerator vs Denominator</h3>
        <div class="chart-canvas">
          <canvas id="chartNumDenCuci"></canvas>
        </div>
        <div class="chart-info">
          <p>Total Numerator : <?= $total_num_cuci ?></p>
          <p>Total Denominator : <?= $total_den_cuci ?></p>
        </div>
      </div>



      <!-- HANDWASH -->
      <div class="chart-card">
        <h3>Handwash vs Handrub</h3>
        <div class="chart-canvas">
          <canvas id="chartHandCuci"></canvas>
        </div>
        <div class="chart-info">
          <p>Total Handwash : <?= $handwash ?></p>
          <p>Total Handrub : <?= $handrub ?></p>
        </div>
      </div>
    </div>
  </div>




  <script>
    /* ================= TAB SYSTEM ================= */
    function openTab(evt, name) {
      let i, tabcontent, tablinks;
      tabcontent = document.getElementsByClassName("tabcontent");
      for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
      }
      tablinks = document.getElementsByClassName("tablinks");
      for (i = 0; i < tablinks.length; i++) {
        tablinks[i].classList.remove("active");
      }
      document.getElementById(name).style.display = "block";
      evt.currentTarget.classList.add("active");
    }

    /* buka tab pertama otomatis */
    // document.addEventListener("DOMContentLoaded", function() {
    //   document.getElementById("defaultOpen").click();
    // });

    document.addEventListener("DOMContentLoaded", function() {
      const urlParams = new URLSearchParams(window.location.search);
      const tab = urlParams.get('tab');

      if (tab === 'cuci') {
        document.querySelector("button[onclick*='cuci']").click();
      } else {
        document.getElementById("defaultOpen").click();
      }
    });



    /* ================= DATA APD ================= */
    const statData = <?= json_encode($stat) ?>;
    const labels = Object.keys(statData);
    const totalYa = Object.values(statData).reduce((a, b) => a + b['Ya'], 0);
    const totalTidak = Object.values(statData).reduce((a, b) => a + b['Tidak'], 0);
    const totalTdk = Object.values(statData).reduce((a, b) => a + b['Tidak Dinilai'], 0);

    /* ================= APD PIE ================= */
    const pieApdCanvas = document.getElementById('chartPieApd');
    if (pieApdCanvas) {
      new Chart(pieApdCanvas, {
        type: 'pie',
        data: {
          labels: ['Ya', 'Tidak'],
          datasets: [{
            data: [totalYa, totalTidak],
            backgroundColor: ['#2E7D32', '#C62828']
          }]
        },

        options: {
          responsive: true,
          plugins: {
            legend: {
              position: 'bottom'
            }
          }
        }
      });
    }



    /* ================= APD BAR ================= */
    const barApdCanvas = document.getElementById('chartBarApd');
    if (barApdCanvas) {
      new Chart(barApdCanvas, {
        type: 'bar',
        data: {
          labels: labels,
          datasets: [{
              label: 'Ya',
              data: labels.map(k => statData[k]['Ya']),
              backgroundColor: '#2E7D32'
            },

            {
              label: 'Tidak',
              data: labels.map(k => statData[k]['Tidak']),
              backgroundColor: '#C62828'
            }
          ]
        },
        options: {
          responsive: true,
          plugins: {
            legend: {
              position: 'bottom'
            }
          }
        }
      });
    }


    /* ================= APD DOUGHNUT ================= */
    const ndApdCanvas = document.getElementById('chartNDApd');
    if (ndApdCanvas) {
      new Chart(ndApdCanvas, {
        type: 'doughnut',
        data: {
          labels: ['Numerator', 'Denominator'],
          datasets: [{
            data: [<?= $total_num ?>, <?= $total_den ?>],
            backgroundColor: ['#1976D2', '#9E9E9E']
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: {
              position: 'bottom'
            }
          }
        }
      });
    }

    /* ================= DATA CUCI TANGAN ================= */
    const totalPatuh = <?= $total_num_cuci ?>;
    const totalTidakPatuh = <?= $total_tidak_cuci ?>;
    const totalHandwash = <?= $handwash ?: 0 ?>;
    const totalHandrub = <?= $handrub ?: 0 ?>;
    const totalDen = <?= $total_den_cuci ?>;
    const statCuci = <?= json_encode($stat_cuci) ?>;
    const labelsCuci = Object.keys(statCuci);
    const totalYaCuci = <?= $total_ya_cuci ?>;
    const totalTidakCuci = <?= $total_tidak_cuci ?>;
    const totalTdkCuci = Object.values(statCuci).reduce((a, b) => a + b['Tidak Dinilai'], 0);

    /* ================= PIE OBSERVASI ================= */
    const pieCuciCanvas = document.getElementById("chartPieCuci");
    if (pieCuciCanvas) {
      new Chart(pieCuciCanvas, {
        type: "pie",

        data: {
          labels: ["Ya", "Tidak"],

          datasets: [{
            data: [totalYaCuci, totalTidakCuci],
            backgroundColor: ["#2E7D32", "#C62828"],
            borderColor: "#fff",
            borderWidth: 2
          }]
        },

        options: {
          responsive: true,
          plugins: {
            legend: {
              position: "bottom"
            }
          }
        }
      });
    }

    /* ================= BAR CUCI TANGAN ================= */
    const labelMap = {
      "sebelum_kontak_dengan_pasien": "Sebelum Kontak Pasien",
      "sebelum_melakukan_tindakan_aseptik": "Tindakan Aseptik",
      "setelah_terpapar_cairan_tubuh_pasien": "Cairan Tubuh",
      "setelah_kontak_dengan_pasien": "Setelah Kontak Pasien",
      "setelah_kontak_dengan_lingkungan_pasien": "Lingkungan Pasien"
    };
    const barCuciCanvas = document.getElementById("chartBarCuci");
    if (barCuciCanvas) {
      const keys = Object.keys(statCuci);
      new Chart(barCuciCanvas, {
        type: "bar",
        data: {
          labels: keys.map(k => labelMap[k] || k),

          datasets: [{
              label: "Ya",
              data: keys.map(k => statCuci[k]['Ya']),
              backgroundColor: "#2E7D32"
            },

            {
              label: "Tidak",
              data: keys.map(k => statCuci[k]['Tidak']),
              backgroundColor: "#C62828"
            },
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: "bottom"
            }
          },
          scales: {
            x: {
              ticks: {
                autoSkip: false,
                maxRotation: 30,
                minRotation: 30,
                font: {
                  size: 11
                }
              }
            },

            y: {
              beginAtZero: true
            }
          }
        }
      });

    }



    /* ================= CUCI TANGAN DOUGHNUT ================= */
    const ndCuciCanvas = document.getElementById('chartNumDenCuci');
    if (ndCuciCanvas) {
      new Chart(ndCuciCanvas, {
        type: 'doughnut',
        data: {
          labels: ['Numerator', 'Denominator'],
          datasets: [{
            data: [<?= $total_num_cuci ?>, <?= $total_den_cuci ?>],
            backgroundColor: ['#1976D2', '#9E9E9E']
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: {
              position: 'bottom'
            }
          }
        }
      });

    }


    /* ================= HANDWASH VS HANDRUB ================= */
    const handCanvas = document.getElementById('chartHandCuci');
    if (handCanvas) {
      new Chart(handCanvas, {
        type: 'pie',
        data: {
          labels: ['Handwash', 'Handrub'],
          datasets: [{
            data: [totalHandwash, totalHandrub],
            backgroundColor: ['#42A5F5', '#66BB6A'],
            borderColor: '#fff',
            borderWidth: 2
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: {
              position: 'bottom'
            },
            tooltip: {
              callbacks: {
                label: function(context) {
                  let total = totalHandwash + totalHandrub;
                  let value = context.raw;
                  let percent = total ? ((value / total) * 100).toFixed(1) : 0;
                  return context.label + " : " + value + " (" + percent + "%)";
                }
              }
            }
          }
        }
      });
    }
  </script>

</body>

</html>