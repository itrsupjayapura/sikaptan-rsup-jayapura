<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include __DIR__ . '/../../config.php';


// ✅ Hanya admin yang bisa akses
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header('Location: ../index.php');
    exit;
}

// ============================================================
// 🔹 Ambil data user login (observer/admin)
// ============================================================
$observer = isset($_SESSION['user']['nama']) ? $_SESSION['user']['nama'] : 'Admin Tidak Dikenal';

// ============================================================
// 🔹 Ambil daftar field dinamis dari tabel cuci_tangan_fields
// ============================================================
$fields_query = mysqli_query($conn, "SELECT field_name, display_label FROM cuci_tangan_fields ORDER BY id ASC");
$fields = [];
while ($row = mysqli_fetch_assoc($fields_query)) {
    $fields[$row['field_name']] = $row['display_label'];
}

// ============================================================
// 🔹 Fungsi Format Tanggal (Indonesia)
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
// 🔹 Filter Bulan, Tahun, dan Ruangan
// ============================================================
$tahun_list = mysqli_query($conn, "SELECT DISTINCT YEAR(STR_TO_DATE(CONCAT(bulan, '-01'), '%Y-%m-%d')) AS tahun FROM data_observasi_cuci_tangan ORDER BY tahun DESC");
$bulan_list = mysqli_query($conn, "SELECT DISTINCT RIGHT(bulan, 2) AS bulan FROM data_observasi_cuci_tangan ORDER BY bulan ASC");
$ruangan_list = mysqli_query($conn, "SELECT nama AS ruangan FROM ruangan_cuci_tangan ORDER BY nama ASC");

$filter_tahun = $_GET['tahun'] ?? 'semua';
$filter_bulan = $_GET['bulan'] ?? 'semua';
$filter_ruangan = $_GET['ruangan'] ?? 'semua';

$where = "1=1";
if ($filter_tahun != 'semua' && $filter_bulan != 'semua') {
    $periode = $filter_tahun . '-' . $filter_bulan;
    $where .= " AND do.bulan='$periode'";
}
if ($filter_ruangan != 'semua') {
    $where .= " AND do.ruangan='$filter_ruangan'";
}

$show_data = ($filter_tahun != '' && $filter_bulan != '' && $filter_ruangan != '');

// ============================================================
// 🔹 Query Utama — Gabungkan data_observasi_cuci_tangan & observasi_cuci_tangan
// ============================================================
if ($show_data) {
   


 $sql = "SELECT oa.id_observasi, do.bulan, do.ruangan, oa.tanggal, oa.petugas, 
       oa.nama_rekan_dilaporkan, oa.tindakan, oa.cuci_tangan_menggunakan, oa.numerator, oa.denumerator, oa.nilai_cuci_tangan";



    foreach ($fields as $key => $label) {
        $sql .= ", oa.`$key`";
    }

    $sql .= ", do.observer
              FROM observasi_cuci_tangan oa
              LEFT JOIN data_observasi_cuci_tangan do ON oa.id_observasi = do.id
              WHERE $where
              ORDER BY oa.tanggal DESC";


              

    $data = mysqli_query($conn, $sql);
    if (!$data) die("❌ Query error: " . mysqli_error($conn) . "<br>SQL: " . $sql);

    $rows = [];
    while ($r = mysqli_fetch_assoc($data)) {
        $rows[] = $r;
    }



    // === Hitung total penggunaan Handwash dan Handrub ===
$handwash = 0;
$handrub = 0;
foreach ($rows as $r) {
    $jenis = strtolower(trim($r['cuci_tangan_menggunakan'] ?? ''));
    if ($jenis == 'handwash') $handwash++;
    elseif ($jenis == 'handrub') $handrub++;
}



} else {
    $rows = [];
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








<h2>🧼 Laporan Kepatuhan Cuci Tangan Bulanan</h2>

<!-- ===== FILTER ===== -->
<form method="GET" class="filter-form">
    <input type="hidden" name="page" value="laporan_cuci">

    <div class="filter-group">
        <label for="bulan">🗓️ Bulan</label>
        <select name="bulan" id="bulan" class="filter-select">
            <option value="semua" <?= $filter_bulan=='semua'?'selected':'' ?>>Semua Bulan</option>
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
            <option value="semua" <?= $filter_tahun=='semua'?'selected':'' ?>>Semua Tahun</option>
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
            <option value="semua" <?= $filter_ruangan=='semua'?'selected':'' ?>>Semua Ruangan</option>
            <?php while($r=mysqli_fetch_assoc($ruangan_list)): ?>
                <option value="<?= $r['ruangan'] ?>" <?= $r['ruangan']==$filter_ruangan?'selected':'' ?>>
                    <?= $r['ruangan'] ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="filter-buttons">
        <button type="submit" class="btn-primary">🔍 Tampilkan</button>
        <a href="?page=laporan_cuci" class="btn-reset">♻️ Reset</a>
    </div>
</form>

<!-- ===== EXPORT ===== -->
<?php if($show_data): ?>
<form method="POST" action="cuci_tangan/page_laporan_cuci_export.php" target="_blank" class="export-form">
    <input type="hidden" name="tahun" value="<?= $filter_tahun ?>">
    <input type="hidden" name="bulan" value="<?= $filter_bulan ?>">
    <input type="hidden" name="ruangan" value="<?= $filter_ruangan ?>">
    <button type="submit" class="btn-export">📥 Export ke Excel</button>
</form>

<?php endif; ?>


<?php
if(!$show_data):
    echo "<p><em>Belum ada data untuk periode ini.</em></p>";
else:

// ============================================================
// 🔹 Statistik & Hitung
// ============================================================
$total_num = $total_den = 0;
$stat = [];
foreach ($fields as $key => $label) {
    $stat[$key] = ['Ya'=>0,'Tidak'=>0,'Tidak Dinilai'=>0];
}

$nama_dilaporkan = []; // 🧩 Tambahan untuk menghitung nama rekan dilaporkan


$total_ya=$total_tidak=$total_tidak_dinilai=0;
$ruangan_terobservasi = [];

foreach ($rows as $row):
    $total_num += $row['numerator'];
    $total_den += $row['denumerator'];

    foreach ($fields as $k => $v) {
       

      $val = ucfirst(strtolower(trim($row[$k] ?? 'Tidak dinilai')));
if (isset($stat[$k][$val])) {
    $stat[$k][$val]++;
} else {
    // Jika ada nilai aneh, masukkan ke "Tidak Dinilai"
    $stat[$k]['Tidak Dinilai']++;
}



        if ($val == 'ya') $total_ya++;
        elseif ($val == 'tidak') $total_tidak++;
        else $total_tidak_dinilai++;
    }



    // 🧩 Catat nama rekan dilaporkan
$nama_rekan = trim($row['nama_rekan_dilaporkan']);
if ($nama_rekan !== '') {
    $nama_dilaporkan[$nama_rekan] = true;
}



    if (!empty($row['ruangan'])) $ruangan_terobservasi[$row['ruangan']] = true;
endforeach;

$total_semua = $total_ya + $total_tidak + $total_tidak_dinilai;
$total_persen = ($total_den > 0) ? round(($total_num / $total_den) * 100, 2) : 0;
$persen_ya = ($total_semua > 0) ? round(($total_ya / $total_semua) * 100, 1) : 0;
$persen_tidak = ($total_semua > 0) ? round(($total_tidak / $total_semua) * 100, 1) : 0;
$persen_tdk = ($total_semua > 0) ? round(($total_tidak_dinilai / $total_semua) * 100, 1) : 0;

$status_kepatuhan = ($total_persen >= 100) ? 'Tercapai' : 'Tidak Tercapai';
$warna_status = ($status_kepatuhan == 'Tercapai') ? 'bg-green' : 'bg-red';
?>



<?php
// === Hitung Handwash vs Handrub ===
$handwash = 0;
$handrub = 0;

foreach ($rows as $r) {
    $jenis = strtolower(trim($r['cuci_tangan_menggunakan'] ?? ''));
    if ($jenis == 'handwash') $handwash++;
    elseif ($jenis == 'handrub') $handrub++;
}

?>


<!-- ===== CHARTS + INFO ===== -->
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

<!-- 🔹 Tambahan Chart Handwash vs Handrub -->
<div class="chart-box mt-3">
    <canvas id="chartHand"></canvas>
    <div class="chart-label">
        <strong>Total Handwash:</strong> <?= $handwash ?><br>
        <strong>Total Handrub:</strong> <?= $handrub ?><br>
    </div>
</div>
</div>









<div class="stats-grid">
    <div class="stat-card bg-yellow">🟩<h3><?= $total_ya ?></h3><p>Total "Ya"</p><small><?= $persen_ya ?>%</small></div>
    <div class="stat-card bg-red">❌<h3><?= $total_tidak ?></h3><p>Total "Tidak"</p><small><?= $persen_tidak ?>%</small></div>
    <div class="stat-card bg-gray">⚪<h3><?= $total_tidak_dinilai ?></h3><p>Tidak Dinilai</p><small><?= $persen_tdk ?>%</small></div>
    <div class="stat-card bg-green">✅<h3><?= $total_persen ?>%</h3><p>Rata-rata Kepatuhan</p></div>
    <div class="stat-card <?= $warna_status ?>">🏆<h3><?= $status_kepatuhan ?></h3><p>Status Kepatuhan</p></div>


<?php
// 🧩 logika baru untuk menampilkan ruangan
if ($filter_ruangan == 'semua') {
    if (!empty($ruangan_terobservasi)) {
        $nama_ruangan_tampil = 'Semua Ruangan';
    } else {
        $nama_ruangan_tampil = '-';
    }
} else {
    $nama_ruangan_tampil = $filter_ruangan ?: '-';
}
?>
<div class="stat-card bg-purple">
    🏥<h3><?= htmlspecialchars($nama_ruangan_tampil) ?></h3>
    <p>Nama Ruangan</p>
</div>



    <div class="stat-card bg-blue">
    👩‍⚕️<h3><?= count($nama_dilaporkan) ?></h3>
    <p>Nama Rekan Dilaporkan</p>
</div>

</div>

<br>
<div class="info-box">
  <p><strong>Bulan:</strong> <?= ($filter_bulan=='semua' && $filter_tahun=='semua') ? 'Semua Bulan & Tahun' : ($nama_bulan[$filter_bulan] ?? $filter_bulan) . ' ' . $filter_tahun ?></p>
  <p><strong>Ruangan:</strong> <?= $filter_ruangan ?: 'Semua Ruangan' ?></p>
  <p><strong>Observer:</strong> <?= $observer ?: '-' ?></p>
</div>

<!-- ===== TABEL DATA ===== -->
<div class="table-container">
<table border="1" cellspacing="0" cellpadding="5">
<thead>
<tr>
    <th>No</th>
    <th>Auditor</th>
    <th>Pegawai</th>
    <th>Tindakan</th>
    <th>Ruangan</th>

    <?php foreach ($fields as $label): ?>
        <th><?= htmlspecialchars($label) ?></th>
    <?php endforeach; ?>
    <th>Numerator</th>
    <th>Denumerator</th>
<th>Jenis Cuci Tangan</th>

 <th>Persentase (%)</th>
<th>Tanggal</th>

</tr>
</thead>
<tbody>
<?php $no=1; foreach($rows as $row): ?>
<tr>
    <td><?= $no++ ?></td>

<td><?= htmlspecialchars($row['petugas']) ?></td>
<td><?= htmlspecialchars($row['nama_rekan_dilaporkan']) ?></td>
<td><?= htmlspecialchars($row['tindakan'] ?: '-') ?></td>
<td><?= htmlspecialchars($row['ruangan']) ?></td>




    <?php foreach ($fields as $key => $label): 
        $val = strtolower(trim($row[$key] ?? ''));
        $bg_color = ($val == 'ya') ? 'background:#d4edda;color:#155724;' : (($val=='tidak') ? 'background:#f8d7da;color:#721c24;' : 'background:#fff3cd;color:#856404;');
    ?>
        <td style="<?= $bg_color ?>"><?= htmlspecialchars(ucwords($val)) ?></td>
    <?php endforeach; ?>
    <td><?= $row['numerator'] ?></td>
    <td><?= $row['denumerator'] ?></td>
<td><?= htmlspecialchars($row['cuci_tangan_menggunakan'] ?: '-') ?></td>

 <td><?= ($row['denumerator']>0)? round(($row['numerator']/$row['denumerator'])*100,2):0 ?>%</td>


<td><?= format_tanggal_indo($row['tanggal']) ?></td>

</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script>
const statData = <?= json_encode($stat) ?>;
const labels = Object.keys(statData);
const totalYa = Object.values(statData).reduce((a,b)=>a+b['Ya'],0);
const totalTidak = Object.values(statData).reduce((a,b)=>a+b['Tidak'],0);
const totalTdk = Object.values(statData).reduce((a,b)=>a+b['Tidak Dinilai'],0);
const totalSemua = totalYa+totalTidak+totalTdk;

new Chart(document.getElementById('chartPie'), {
  type:'pie',
  data:{labels:['Ya','Tidak','Tidak Dinilai'],
  datasets:[{data:[totalYa,totalTidak,totalTdk],backgroundColor:['#2E7D32','#C62828','#FFB300'],borderColor:'#fff',borderWidth:2}]},
  options:{responsive:true,plugins:{legend:{position:'bottom'},title:{display:true,text:'Distribusi Jawaban Observasi Cuci Tangan'},datalabels:{color:'#fff',formatter:(v)=>((v/totalSemua)*100).toFixed(1)+'%'}}},
  plugins:[ChartDataLabels]
});

new Chart(document.getElementById('chartBar'), {
  type:'bar',
  data:{labels:labels,
  datasets:[
    {label:'Ya',data:labels.map(k=>statData[k]['Ya']),backgroundColor:'#2E7D32'},
    {label:'Tidak',data:labels.map(k=>statData[k]['Tidak']),backgroundColor:'#C62828'},
    {label:'Tidak Dinilai',data:labels.map(k=>statData[k]['Tidak Dinilai']),backgroundColor:'#FFB300'}
  ]},
  options:{responsive:true,plugins:{legend:{position:'bottom'},title:{display:true,text:'Perbandingan Tiap Komponen Cuci Tangan'}}}
});

new Chart(document.getElementById('chartNumDen'), {
  type:'doughnut',
  data:{labels:['Numerator','Denumerator'],datasets:[{data:[<?= $total_num ?>,<?= $total_den ?>],backgroundColor:['#1976D2','#9E9E9E'],borderColor:'#fff'}]},
  options:{plugins:{legend:{position:'bottom'},title:{display:true,text:'Numerator vs Denumerator'}}}
});
</script>




<script>
// === PIE CHART Handwash vs Handrub ===
new Chart(document.getElementById('chartHand'), {
  type: 'pie',
  data: {
    labels: ['Handwash', 'Handrub'],
    datasets: [{
      data: [<?= $handwash ?>, <?= $handrub ?>],
      backgroundColor: ['#42A5F5', '#66BB6A'],
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
        text: 'Distribusi Penggunaan Handwash vs Handrub',
        font: { size: 16, weight: 'bold' }
      },
      datalabels: {
        color: '#fff',
        font: { weight: 'bold', size: 14 },
        formatter: (val, ctx) => {
          const total = <?= $handwash + $handrub ?>;
          const persen = total > 0 ? ((val / total) * 100).toFixed(1) + '%' : '0%';
          return persen;
        }
      }
    }
  },
  plugins: [ChartDataLabels]
});


</script>
<?php endif; ?>
