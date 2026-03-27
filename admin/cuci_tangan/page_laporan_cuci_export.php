<?php
include __DIR__ . '/../../config.php';

// ============================
// 🔹 Ambil filter dari form
// ============================
$tahun = $_POST['tahun'] ?? 'semua';
$bulan = $_POST['bulan'] ?? 'semua';
$ruangan = $_POST['ruangan'] ?? 'semua';

$where = "1=1";
if ($tahun != 'semua' && $bulan != 'semua') {
    $periode = $tahun . '-' . $bulan;
    $where .= " AND do.bulan = '$periode'";
}
if ($ruangan != 'semua') {
    $where .= " AND do.ruangan = '$ruangan'";
}

// ============================
// 🔹 Fungsi Format Tanggal & Bulan Indonesia
// ============================
function format_tanggal_indo($tanggal) {
    if (!$tanggal) return '-';
    $bulanIndo = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
                  7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
    return date('j', strtotime($tanggal)) . ' ' . $bulanIndo[(int)date('m', strtotime($tanggal))] . ' ' . date('Y', strtotime($tanggal));
}
function format_bulan_tahun_indo($bulanData) {
    $bulanIndo = ['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni',
                  '07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'];
    if (preg_match('/^(\d{4})-(\d{2})$/', $bulanData, $m)) return $bulanIndo[$m[2]] . ' ' . $m[1];
    return $bulanData ?: '-';
}

// ============================
// 🔹 Ambil field dinamis
// ============================
$fields_query = mysqli_query($conn, "SELECT field_name, display_label FROM cuci_tangan_fields ORDER BY id ASC");
$fields = [];
while ($row = mysqli_fetch_assoc($fields_query)) {
    $fields[$row['field_name']] = $row['display_label'];
}

// ============================
// 🔹 Format nama file export
// ============================
$namaRuangan = ($ruangan != 'semua' && $ruangan != '') ? ucfirst($ruangan) : 'Semua Ruangan';
$namaBulan = ($bulan != 'semua' && $tahun != 'semua') ? format_bulan_tahun_indo("$tahun-$bulan") : 'Semua Bulan';
$namaFile = "Laporan Observasi Cuci Tangan ($namaRuangan) Bulan $namaBulan.xls";

header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=\"$namaFile\"");

// ============================
// 🔹 Query data
// ============================
$sql = "
SELECT 
    oa.id_observasi,
    do.bulan,
    do.ruangan,
    oa.tanggal,
    oa.petugas,
    oa.nama_rekan_dilaporkan,
    oa.tindakan,
    oa.cuci_tangan_menggunakan,
    oa.numerator,
    oa.denumerator,
    oa.nilai_cuci_tangan,
    do.observer
";
foreach ($fields as $key => $label) {
    $sql .= ", oa.`$key`";
}
$sql .= "
FROM observasi_cuci_tangan oa
LEFT JOIN data_observasi_cuci_tangan do ON oa.id_observasi = do.id
WHERE $where
ORDER BY oa.tanggal DESC
";

$data = mysqli_query($conn, $sql);
if (!$data) die('Query Error: '.mysqli_error($conn));

// 🔹 Hitung total Handwash & Handrub
$handwash_total = 0;
$handrub_total = 0;
$temp_data = [];

while ($r = mysqli_fetch_assoc($data)) {
    $jenis = strtolower(trim($r['cuci_tangan_menggunakan'] ?? ''));
    if ($jenis == 'handwash') $handwash_total++;
    elseif ($jenis == 'handrub') $handrub_total++;
    $temp_data[] = $r;
}
$rows = $temp_data;

// 🔹 Tentukan dominasi
if ($handwash_total > $handrub_total) {
    $dominasi = "Handwash (lebih sering digunakan)";
} elseif ($handrub_total > $handwash_total) {
    $dominasi = "Handrub (lebih sering digunakan)";
} else {
    $dominasi = "Seimbang antara Handwash dan Handrub";
}
?>

<!-- ============================ -->
<!--        TABEL EXPORT          -->
<!-- ============================ -->
<table border="1" cellspacing="0" cellpadding="6" style="border-collapse:collapse; font-family:Segoe UI, sans-serif; font-size:13px;">

<tr style="background:#009879; color:#fff; font-weight:bold; text-align:center;">
    <th>No</th>
    <th>Tanggal</th>
    <th>Auditor</th>
    <th>Nama Rekan</th>
    <th>Tindakan</th>
    <th>Jenis Cuci Tangan</th>
    <th>Total Penggunaan Jenis</th>
    <th>Ruangan</th>

    <?php foreach ($fields as $label): ?>
        <th><?= htmlspecialchars($label) ?></th>
    <?php endforeach; ?>

    <th>Numerator</th>
    <th>Denumerator</th>
    <th>Persentase Kepatuhan</th>
    <th>Status</th>
    <th>Bulan</th>
</tr>

<?php
$no=1;
$total_num=0;
$total_den=0;
foreach($rows as $row):
    $persen = ($row['denumerator']>0)? round(($row['numerator']/$row['denumerator'])*100,2):0;
    $status = ($persen >= 100)? 'Tercapai':'Tidak Tercapai';
    $total_num += $row['numerator'];
    $total_den += $row['denumerator'];

    $total_jenis = ($row['cuci_tangan_menggunakan'] == 'Handwash') ? $handwash_total :
                   (($row['cuci_tangan_menggunakan'] == 'Handrub') ? $handrub_total : 0);
?>
<tr>
    <td><?= $no++ ?></td>
    <td><?= format_tanggal_indo($row['tanggal']) ?></td>
    <td><?= htmlspecialchars($row['observer']) ?></td>
    <td><?= htmlspecialchars($row['nama_rekan_dilaporkan'] ?: '-') ?></td>
    <td><?= htmlspecialchars($row['tindakan'] ?: '-') ?></td>
    <td><?= htmlspecialchars($row['cuci_tangan_menggunakan'] ?: '-') ?></td>
    <td align="center"><?= $total_jenis ?></td>
    <td><?= htmlspecialchars($row['ruangan']) ?></td>

    <?php foreach ($fields as $key => $label): ?>
        <td><?= htmlspecialchars($row[$key] ?? '-') ?></td>
    <?php endforeach; ?>

    <td align="center"><?= $row['numerator'] ?></td>
    <td align="center"><?= $row['denumerator'] ?></td>
    <td align="center"><?= $persen ?>%</td>
    <td><?= $status ?></td>
    <td><?= format_bulan_tahun_indo($row['bulan']) ?></td>
</tr>
<?php endforeach; ?>

<!-- TOTAL -->
<tr style="background:#d9fdd3; font-weight:bold;">
    <td colspan="<?= 8 + count($fields) ?>" align="right">TOTAL</td>
    <td><?= $total_num ?></td>
    <td><?= $total_den ?></td>
    <td colspan="3"></td>
</tr>

<!-- RATA-RATA -->
<?php $rata = ($total_den>0)? round(($total_num/$total_den)*100,2):0; ?>
<tr style="background:#d7e9ff; font-weight:bold;">
    <td colspan="<?= 8 + count($fields) ?>" align="right">RATA-RATA KEPATUHAN</td>
    <td colspan="4"><?= $rata ?>%</td>
</tr>

<!-- TOTAL HANDWASH -->
<tr style="background:#fff7c2; font-weight:bold;">
    <td colspan="<?= 8 + count($fields) + 1 ?>" align="right">TOTAL HANDWASH</td>
    <td colspan="4"><?= $handwash_total ?></td>
</tr>

<!-- TOTAL HANDRUB -->
<tr style="background:#ffd8d8; font-weight:bold;">
    <td colspan="<?= 8 + count($fields) + 1 ?>" align="right">TOTAL HANDRUB</td>
    <td colspan="4"><?= $handrub_total ?></td>
</tr>

<!-- DOMINASI -->
<tr style="background:#e9d7ff; font-weight:bold;">
    <td colspan="<?= 8 + count($fields) + 1 ?>" align="right">JENIS DOMINAN</td>
    <td colspan="4"><?= $dominasi ?></td>
</tr>

</table>
