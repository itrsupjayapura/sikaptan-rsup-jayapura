<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include '../config.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header('Location: ../index.php');
    exit;
}

// ============================================================
// 🔹 Ambil data user login (observer/admin)
// ============================================================
$observer = isset($_SESSION['user']['username']) ? $_SESSION['user']['nama'] : 'Admin Tidak Dikenal';


// ============================================================
// 🔹 Ambil daftar field dinamis dari tabel apd_fields
// ============================================================
$fields_query = mysqli_query($conn, "SELECT field_name, display_label FROM apd_fields ORDER BY id ASC");
$fields = [];
while ($row = mysqli_fetch_assoc($fields_query)) {
    $fields[$row['field_name']] = $row['display_label'];
}


// ============================================================
// 🔹 Fungsi Format Tanggal
// ============================================================
function format_tanggal_indo($tanggal) {
    if (!$tanggal) return '-';
    $bulanIndo = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
    return date('d', strtotime($tanggal)) . ' ' . $bulanIndo[(int)date('m', strtotime($tanggal))] . ' ' . date('Y', strtotime($tanggal));
}
function format_bulan_tahun_indo($bulanData) {
    $bulanIndo = ['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'];
    if (preg_match('/^(\d{4})-(\d{2})$/', $bulanData, $m)) return $bulanIndo[$m[2]] . ' ' . $m[1];
    return $bulanData ?: '-';
}

// ============================================================
// 🔹 Filter Bulan & Ruangan
// ============================================================
// Ambil daftar tahun unik
$tahun_list = mysqli_query($conn, "SELECT DISTINCT YEAR(STR_TO_DATE(CONCAT(bulan, '-01'), '%Y-%m-%d')) AS tahun FROM data_observasi ORDER BY tahun DESC");

// Ambil daftar bulan unik (hanya bagian MM)
$bulan_list = mysqli_query($conn, "SELECT DISTINCT RIGHT(bulan, 2) AS bulan FROM data_observasi ORDER BY bulan ASC");

// Ambil daftar ruangan
$ruangan_list = mysqli_query($conn, "SELECT nama AS ruangan FROM ruangan ORDER BY nama ASC");


$filter_tahun = $_GET['tahun'] ?? '';
$filter_bulan = $_GET['bulan'] ?? '';
$filter_ruangan = $_GET['ruangan'] ?? '';


// Tambahkan ini
$show_data = ($filter_bulan != '' && $filter_ruangan != '');


$where = "1=1";
if ($filter_tahun != '' && $filter_bulan != '') {
    $periode = $filter_tahun . '-' . $filter_bulan;
    $where .= " AND do.bulan='$periode'";
}
if ($filter_ruangan != '') {
    $where .= " AND do.ruangan='$filter_ruangan'";
}


// 🔸 Tambahan: Jika belum pilih bulan atau ruangan, hentikan query agar tidak tampil data
$show_data = true; // tampilkan semua data dulu




// ============================================================
// 🔹 Query Utama — Gabungan data_observasi dan observasi_apd
// ============================================================
if ($show_data) {
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

    if (!$data) {
        die("❌ Query error: " . mysqli_error($conn) . "<br>SQL: " . $sql);
    }

    $rows = [];
    while ($r = mysqli_fetch_assoc($data)) {
        $rows[] = $r;
    }
} else {
    $rows = []; // kosongkan agar tidak ada data muncul
}

?>















<style>
body{font-family:'Segoe UI',sans-serif;background:#f5f6f9;}
h2{}
.glass{background:rgba(255,255,255,0.9);backdrop-filter:blur(8px);border-radius:10px;box-shadow:0 4px 8px rgba(0,0,0,0.1);padding:15px;}
.chart-top{display:flex;flex-wrap:wrap;justify-content:center;gap:25px;margin-bottom:20px;}
.chart-box{display:flex;flex-direction:column;align-items:center;}
.chart-label{margin-top:8px;font-size:14px;color:#333;text-align:center;}
.chart-top canvas{max-width:280px!important;max-height:280px!important;}

.stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;margin-top:25px;}
.stat-card{padding:15px;border-radius:12px;text-align:center;box-shadow:0 3px 6px rgba(0,0,0,0.1);}
.bg-green{background:#d9fdd3;} .bg-red{background:#ffd6d6;} .bg-gray{background:#eee;}
.bg-blue{background:#d7e9ff;} .bg-purple{background:#e9d7ff;} .bg-yellow{background:#fff3c4;}
.stat-card h3{margin:6px 0;font-size:22px;} .stat-card p{margin:0;font-size:14px;}
.stat-card small{display:block;margin-top:3px;font-size:13px;color:#555;}
.info-box{margin:15px 0;padding:10px 15px;border-left:4px solid #4a90e2;background:#f0f6ff;border-radius:8px;}
.info-box strong{color:#1a1a1a;}











/* ===================== MAIN CONTENT ===================== */
.content {
  flex: 1;
  margin-left: 260px;
  transition: all 0.3s ease;
  overflow-y: auto;
}

.sidebar.active + .content {
  margin-left: 0;
}

.topbar {
  height: 65px;
  background: #fff;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 25px;
  border-bottom: 1px solid #dee2e6;
}

.topbar-left button {
  background: none;
  border: none;
  font-size: 1.5rem;
  color: #009879;
  cursor: pointer;
}

.greeting {
  font-weight: 500;
  color: #333;
}

/* ===================== MAIN CONTENT AREA ===================== */
.main-content {
  padding: 25px;
}

.page-wrapper {
  background: #fff;
  padding: 25px;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  min-height: calc(100vh - 120px);
  animation: fadeIn 0.6s ease-in-out;
  overflow-x: auto;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

/* ===================== RESPONSIVE ===================== */
@media (max-width: 992px) {
  .sidebar {
    position: fixed;
    transform: translateX(-100%);
    z-index: 999;
  }

  .sidebar.active {
    transform: translateX(0);
  }

  .content {
    margin-left: 0;
  }

  .toggle-btn {
    display: block;
  }
}



/* ===================== TABLE STYLING ===================== */
.table-container {
  width: 100%;
  overflow-x: auto;
  margin-top: 25px;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  background: #fff;
  border: 1px solid #cbd5e1; /* ⬅ Border luar tabel */
}

/* === Table utama === */
table {
  width: 100%;
  border-collapse: collapse;
  white-space: nowrap;
  min-width: 1100px;
  border: 1px solid #cbd5e1; /* ⬅ Border keliling tabel */
}

/* === Sel tabel === */
th, td {
  padding: 10px 14px;
  text-align: center;
  border: 1px solid #cbd5e1; /* ⬅ Border antar kolom & baris */
  transition: background 0.2s ease;
}

/* === Header tabel === */
th {
  background: linear-gradient(180deg, #009879 0%, #007f69 100%);
  color: #fff;
  font-weight: 600;
  text-transform: uppercase;
  font-size: 13px;
  border: 1px solid #007f69; /* ⬅ Garis header agar tegas */
}

/* === Baris data === */
tr:nth-child(even) {
  background: #f9fafb;
}

tr:hover td {
  background: #e8f5f1;
}

td {
  font-size: 14px;
  color: #2f3e46;
}

/* === Scrollbar === */
.table-container::-webkit-scrollbar {
  height: 8px;
}
.table-container::-webkit-scrollbar-thumb {
  background: #009879;
  border-radius: 10px;
}
.table-container::-webkit-scrollbar-track {
  background: #e6f4f1;
}










/* ===================== FILTER FORM ===================== */
.filter-form {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  background: #fff;
  border-radius: 10px;
  padding: 15px 20px;
  margin-bottom: 25px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.05);
}

.filter-group {
  display: flex;
  flex-direction: column;
}

.filter-group label {
  font-weight: 500;
  color: #006e5c;
  margin-bottom: 5px;
}

.filter-select {
  padding: 8px 10px;
  border-radius: 6px;
  border: 1px solid #ccc;
  font-size: 14px;
}

.btn-primary, .btn-reset {
  padding: 8px 14px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.3s ease;
}

.btn-primary {
  background: #009879;
  color: #fff;
}

.btn-primary:hover {
  background: #007f69;
}

.btn-reset {
  background: #f0f0f0;
  color: #333;
}

.btn-reset:hover {
  background: #ddd;
}



canvas {
    background: #fff;
    border-radius: 10px;
    padding: 10px;
    /* box-shadow: 0 3px 6px rgba(0,0,0,0.1); */
}





</style>









<h2>📊 Laporan Kepatuhan APD Bulanan</h2>

<!-- ===== FORM FILTER ===== -->
<form method="GET" class="filter-form">
    <input type="hidden" name="page" value="dashboard">

   <div class="filter-group">
    <label for="bulan">🗓️ Bulan</label>
    <select name="bulan" id="bulan" class="filter-select">
        <option value="">Pilih Bulan</option>
        <?php 
        $nama_bulan = ['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'];
        while($b=mysqli_fetch_assoc($bulan_list)): ?>
            <option value="<?= $b['bulan'] ?>" <?= $b['bulan']==$filter_bulan?'selected':'' ?>>
                <?= $nama_bulan[$b['bulan']] ?>
            </option>
        <?php endwhile; ?>
    </select>
</div>

<div class="filter-group">
    <label for="tahun">📆 Tahun</label>
    <select name="tahun" id="tahun" class="filter-select">
        <option value="">Pilih Tahun</option>
        <?php while($t=mysqli_fetch_assoc($tahun_list)): ?>
            <option value="<?= $t['tahun'] ?>" <?= $t['tahun']==$filter_tahun?'selected':'' ?>>
                <?= $t['tahun'] ?>
            </option>
        <?php endwhile; ?>
    </select>
</div>

<div class="filter-group">
    <label for="ruangan">🏥 Ruangan</label>
    <select name="ruangan" id="ruangan" class="filter-select">
        <option value="">Pilih Ruangan</option>
        <?php while($r=mysqli_fetch_assoc($ruangan_list)): ?>
            <option value="<?= $r['ruangan'] ?>" <?= $r['ruangan']==$filter_ruangan?'selected':'' ?>>
                <?= $r['ruangan'] ?>
            </option>
        <?php endwhile; ?>
    </select>
</div>

    <div class="filter-buttons">
        <button type="submit" class="btn-primary">🔍 Tampilkan</button>
        <?php if($filter_bulan||$filter_ruangan): ?>
            <a href="dashboard.php" class="btn-reset">♻️ Reset</a>
        <?php endif; ?>
    </div>
</form>


<?php
if(!$show_data): ?>
    <div class="info-box">
        <p><strong>Silakan pilih Bulan dan Ruangan terlebih dahulu</strong> untuk menampilkan laporan.</p>
    </div>
<?php else:


// ============================================================
// 🔹 Statistik & Hitung Data
// ============================================================
$no=1;$total_num=0;$total_den=0;
$stat = [];
foreach ($fields as $key => $label) {
    $stat[$key] = ['Ya'=>0,'Tidak'=>0,'Tidak Dinilai'=>0];
}
$total_ya=$total_tidak=$total_tidak_dinilai=0;
$nama_dilaporkan=[]; 
$petugas_terobservasi=[];

foreach ($rows as $row):
    $persen = ($row['denumerator'] > 0) ? round(($row['numerator'] / $row['denumerator']) * 100, 2) : 0;
    $total_num += $row['numerator'];
    $total_den += $row['denumerator'];

    // 🧩 Tambahan agar "Petugas Terobservasi" tampil
    $petugas_nama = trim($row['petugas']);
    if ($petugas_nama !== '') {
        if(!empty($row['ruangan'])) $petugas_terobservasi[$row['ruangan']] = true;

    }

    foreach ($fields as $k => $v) {
        $val = trim(strtolower($row[$k] ?? 'tidak dinilai'));

        if ($val == 'ya') {
            $stat[$k]['Ya']++;
            $total_ya++;
        } elseif ($val == 'tidak') {
            $stat[$k]['Tidak']++;
            $total_tidak++;
        } else {
            $stat[$k]['Tidak Dinilai']++;
            $total_tidak_dinilai++;
        }
    }
endforeach;


//$total_semua=$total_ya+$total_tidak+$total_tidak_dinilai;
$total_semua = $total_ya+$total_tidak;
if($total_semua>0):
$total_persen=round(($total_num/$total_den)*100,2);
$persen_ya=round(($total_ya/$total_semua)*100,1);
$persen_tidak=round(($total_tidak/$total_semua)*100,1);
//$persen_tdk=round(($total_tidak_dinilai/$total_semua)*100,1);
$persen_tdk=0;

?>



<div class="stats-grid">
    <div class="stat-card bg-yellow">
        🟩<h3><?= $total_ya ?></h3>
        <p>Total "Ya"</p>
        <small><?= $persen_ya ?>%</small>
    </div>

    <div class="stat-card bg-red">
        ❌<h3><?= $total_tidak ?></h3>
        <p>Total "Tidak"</p>
        <small><?= $persen_tidak ?>%</small>
    </div>

    <div class="stat-card bg-gray">
        ⚪<h3><?= $total_tidak_dinilai ?></h3>
        <p>Tidak Dinilai</p>
        <small><?= $persen_tdk ?>%</small>
    </div>

    <div class="stat-card bg-green">
        ✅<h3><?= $total_persen ?>%</h3>
        <p>Rata-rata Kepatuhan</p>
    </div>

    <!-- 🔹 Tambahan baru: Status Kepatuhan -->
    <?php 
    $status_kepatuhan = ($total_persen >= 100) ? 'Tercapai' : 'Tidak Tercapai';
    $warna_status = ($status_kepatuhan == 'Tercapai') ? 'bg-green' : 'bg-red';
    ?>
    <div class="stat-card <?= $warna_status ?>">
        🏆<h3><?= $status_kepatuhan ?></h3>
        <p>Status Kepatuhan</p>
        <small>berdasarkan rata-rata <?= $total_persen ?>%</small>
    </div>

    <!-- <div class="stat-card bg-purple">
        🏥<h3><?= implode(', ', array_keys($petugas_terobservasi)) ?: '-' ?></h3>
        <p>Nama Ruangan</p>
    </div> -->

    <div class="stat-card bg-blue">
        👩‍⚕️<h3><?= count($nama_dilaporkan) ?></h3>
        <p>Nama Rekan Dilaporkan</p>
    </div>
</div>


<br>


<!-- ===== CHART SECTION ===== -->
<div class="glass chart-top">
    <canvas id="chartPie"></canvas>
    <canvas id="chartBar"></canvas>
    <div class="chart-box">
        <canvas id="chartNumDen"></canvas>
        <div class="chart-label">
            <strong>Total Numerator:</strong> <?= $total_num ?><br>
            <strong>Total Denumerator:</strong> <?= $total_den ?><br>
        </div>
    </div>
</div>




<!-- <br>
<div class="info-box">
    <p><strong>Periode:</strong> <?= $filter_bulan && $filter_tahun ? $nama_bulan[$filter_bulan] . ' ' . $filter_tahun : '-' ?></p>

    <p><strong>Ruangan:</strong> <?= $filter_ruangan ?: 'Semua Ruangan' ?></p>
    <p><strong>Observer:</strong> <?= $observer ?: '-' ?></p>
</div> -->

<!-- ===== TABEL DATA ===== -->
<!-- <div class="table-container">
<table border="1" cellspacing="0" cellpadding="5">
<thead>
<tr>
    <th>No</th>
    <th>Bulan</th>
    <th>Ruangan</th>
    <th>Tanggal</th>
    <th>Petugas</th>
    <th>Nama Rekan</th>
    <?php foreach ($fields as $label): ?>
        <th><?= htmlspecialchars($label) ?></th>
    <?php endforeach; ?>
    <th>Numerator</th>
    <th>Denumerator</th>
    <th>Persentase (%)</th>
</tr>
</thead>
<tbody>
<?php $no=1; foreach($rows as $row): ?>
<tr>
    <td><?= $no++ ?></td>
    <td><?= htmlspecialchars($row['bulan']) ?></td>
    <td><?= htmlspecialchars($row['ruangan']) ?></td>
    <td><?= htmlspecialchars($row['tanggal']) ?></td>
    <td><?= htmlspecialchars($row['petugas']) ?></td>
    <td><?= htmlspecialchars($row['nama_rekan_dilaporkan']) ?></td>
    <?php foreach ($fields as $key => $label): ?>
        <td><?= htmlspecialchars($row[$key]) ?></td>
    <?php endforeach; ?>
    <td><?= htmlspecialchars($row['numerator']) ?></td>
    <td><?= htmlspecialchars($row['denumerator']) ?></td>
    <td>
        <?php
        $persen = ($row['denumerator'] > 0)
            ? round(($row['numerator'] / $row['denumerator']) * 100, 2)
            : 0;
        echo $persen . '%';
        ?>
    </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div> -->


<!-- === CHART BAGUS (Versi Lama tapi Disinkronkan dengan Data Baru) === -->
<!-- === CHART DENGAN PERSENTASE TAMPIL LANGSUNG === -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

<script>
// === Data dari PHP ===
const statData = <?= json_encode($stat) ?>;
const labels = Object.keys(statData);

// Warna khas Kemenkes
const warnaYa = '#2E7D32';
const warnaTidak = '#C62828';
const warnaTdk = '#FFB300';
const warnaNum = '#1976D2';
const warnaDen = '#9E9E9E';

// Hitung total global
const totalYa = Object.values(statData).reduce((a,b)=>a+b['Ya'],0);
const totalTidak = Object.values(statData).reduce((a,b)=>a+b['Tidak'],0);
const totalTdk = Object.values(statData).reduce((a,b)=>a+b['Tidak Dinilai'],0);
//const totalSemua = totalYa + totalTidak + totalTdk;
const totalSemua = totalYa + totalTidak;
// === Pie Chart ===
new Chart(document.getElementById('chartPie'), {
    type: 'pie',
    data: {
        labels: ['Ya', 'Tidak'],
        datasets: [{
            data: [totalYa, totalTidak],
            backgroundColor: [warnaYa, warnaTidak],
            borderColor: '#fff',
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom' },
            title: {
                display: true,
                text: 'Distribusi Jawaban Observasi',
                font: { size: 16, weight: 'bold' }
            },
            tooltip: {
                callbacks: {
                    label: function(ctx) {
                        const val = ctx.parsed;
                        const persen = ((val / totalSemua) * 100).toFixed(1);
                        return `${ctx.label}: ${val} (${persen}%)`;
                    }
                }
            },
            datalabels: {
                color: '#fff',
                font: { weight: 'bold', size: 14 },
                formatter: (val, ctx) => {
                    const persen = ((val / totalSemua) * 100).toFixed(1);
                    return persen + '%';
                }
            }
        }
    },
    plugins: [ChartDataLabels]
});

// === Bar Chart ===
const ctxBar = document.getElementById('chartBar').getContext('2d');
new Chart(ctxBar, {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [
            { label: 'Ya', data: labels.map(k=>statData[k]['Ya']), backgroundColor: warnaYa },
            { label: 'Tidak', data: labels.map(k=>statData[k]['Tidak']), backgroundColor: warnaTidak }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom' },
            title: {
                display: true,
                text: 'Perbandingan Tiap Jenis APD',
                font: { size: 16, weight: 'bold' }
            }
        },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 } },
            x: { ticks: { font: { size: 13 } } }
        }
    }
});

// === Doughnut Numerator vs Denumerator ===
const totalNum = <?= $total_num ?>;
const totalDen = <?= $total_den ?>;
const totalND = totalNum + totalDen;

new Chart(document.getElementById('chartNumDen'), {
    type: 'doughnut',
    data: {
        labels: ['Numerator', 'Denumerator'],
        datasets: [{
            data: [totalNum, totalDen],
            backgroundColor: [warnaNum, warnaDen],
            borderColor: '#fff',
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom' },
            title: {
                display: true,
                text: 'Perbandingan Numerator vs Denumerator',
                font: { size: 16, weight: 'bold' }
            },
            tooltip: {
                callbacks: {
                    label: function(ctx) {
                        const val = ctx.parsed;
                        const persen = ((val / totalND) * 100).toFixed(1);
                        return `${ctx.label}: ${val} (${persen}%)`;
                    }
                }
            },
            datalabels: {
                color: '#fff',
                font: { weight: 'bold', size: 14 },
                formatter: (val) => ((val / totalND) * 100).toFixed(1) + '%'
            }
        }
    },
    plugins: [ChartDataLabels]
});
</script>



<?php else: ?>
<p><em>Belum ada data untuk periode ini.</em></p>
<?php endif; ?>
<?php endif; ?>
