<?php
include '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header('Location: ../index.php');
    exit;
}

$redirect_page = $base_url . "dashboard.php?page=observer";






// === AKTIF/NONAKTIF (AMAN) ===
if (isset($_GET['aksi']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $aksi = $_GET['aksi'];

    if ($aksi == 'aktifkan') {
        mysqli_query($conn, "UPDATE data_observasi SET status='aktif' WHERE id=$id");
        echo "<script>
            if (typeof Swal !== 'undefined') {
                Swal.fire('♻️ Diaktifkan', 'Periode ini telah diaktifkan kembali.', 'success')
                .then(()=> window.location.href = '{$redirect_page}');
            } else {
                window.location.href = '{$redirect_page}';
            }
        </script>";
        exit;
    } elseif ($aksi == 'nonaktifkan') {
        mysqli_query($conn, "UPDATE data_observasi SET status='selesai' WHERE id=$id");
        echo "<script>
            if (typeof Swal !== 'undefined') {
                Swal.fire('✅ Dinonaktifkan', 'Periode telah ditandai selesai.', 'success')
                .then(()=> window.location.href = '{$redirect_page}');
            } else {
                window.location.href = '{$redirect_page}';
            }
        </script>";
        exit;
    }
}






$username_admin = $_SESSION['user']['username'];
$q_admin = mysqli_query($conn, "SELECT nama FROM users WHERE username='$username_admin' LIMIT 1");
$observer_nama = ($r = mysqli_fetch_assoc($q_admin)) ? $r['nama'] : $username_admin;

$cek_aktif = mysqli_query($conn, "SELECT COUNT(*) as jml FROM data_observasi WHERE status='aktif'");
$aktif_count = mysqli_fetch_assoc($cek_aktif)['jml'] ?? 0;

// === CRUD RUANGAN (versi lancar seperti cuci tangan) ===
if (isset($_POST['aksi'])) {
    $aksi = $_POST['aksi'];
    if ($aksi == 'tambah') {
        $nama = trim($_POST['nama']);
        if ($nama != '') mysqli_query($conn, "INSERT INTO ruangan (nama) VALUES ('$nama')");
    } elseif ($aksi == 'hapus') {
        $id = (int)$_POST['id'];
        mysqli_query($conn, "DELETE FROM ruangan WHERE id=$id");
    } elseif ($aksi == 'edit') {
        $id = (int)$_POST['id'];
        $nama = trim($_POST['nama']);
        if ($nama != '') mysqli_query($conn, "UPDATE ruangan SET nama='$nama' WHERE id=$id");
    }
    echo "<script>window.location.href='$redirect_page';</script>";
    exit;
}


// === SIMPAN / UPDATE STATUS PERIODE ===
if (isset($_POST['simpan'])) {
    $bulan = $_POST['bulan'];
    $tahun = $_POST['tahun'];
    $periode = "$tahun-$bulan";
    $tanggal = date('Y-m-d');
    $ruangan_list = $_POST['ruangan'] ?? [];

    if (!empty($ruangan_list)) {
        if (isset($_POST['forceClose']) && $_POST['forceClose'] === 'yes') {
            mysqli_query($conn, "UPDATE data_observasi SET status='selesai' WHERE status='aktif'");
        }

        $sukses = 0;
        foreach ($ruangan_list as $r) {
            $insert = "INSERT INTO data_observasi (bulan, ruangan, observer, status, tanggal_input)
                       VALUES ('$periode', '$r', '$observer_nama', 'aktif', '$tanggal')";
            if (mysqli_query($conn, $insert)) $sukses++;
        }

        echo "<script>
            alert('✅ Berhasil membuat $sukses periode baru!');
            window.location.href='$redirect_page';
        </script>";
        exit;
    } else {
        echo "<script>
            alert('❌ Pilih minimal satu ruangan!');
            window.location.href='$redirect_page';
        </script>";
        exit;
    }
}





// ============== HAPUS SEMUA DATA DALAM 1 BULAN ==============
if (isset($_GET['hapus_bulan'])) {
    $bulan_hapus = mysqli_real_escape_string($conn, $_GET['hapus_bulan']);

    // 🔹 Hapus data di tabel observasi_apd yang punya bulan sama
    mysqli_query($conn, "DELETE FROM observasi_apd WHERE bulan='$bulan_hapus'");

    // 🔹 Hapus juga data di tabel data_observasi seperti semula
    mysqli_query($conn, "DELETE FROM data_observasi WHERE bulan='$bulan_hapus'");

echo "<script>
  alert('🗑️ Semua data pada bulan $bulan_hapus (termasuk observasi APD) berhasil dihapus!');
  window.location.href='{$redirect_page}';
</script>";
exit;
}




// === AMBIL DATA ===
$data = mysqli_query($conn, "SELECT * FROM data_observasi ORDER BY id DESC");
$grouped = [];
while ($d = mysqli_fetch_assoc($data)) $grouped[$d['bulan']][] = $d;

function formatBulanTeks($bulan_angka) {
    $nama_bulan = [
        '01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni',
        '07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'
    ];
    $tahun = substr($bulan_angka, 0, 4);
    $bulan = substr($bulan_angka, 5, 2);
    return ($nama_bulan[$bulan] ?? $bulan) . " " . $tahun;
}


?>

<!-- ==== LINK EKSTERNAL ==== -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">







<div class="container">
  <h3 class="title">📋 Data Periode Observasi APD</h3>

  <!-- FORM TAMBAH PERIODE -->
  <form id="formPeriode" method="POST" class="periode-form">
    <input type="hidden" name="forceClose" id="forceClose" value="no">
    <input type="hidden" name="simpan" value="1">

    <div class="form-grid">
      <!-- PILIH BULAN -->
      <div class="form-group">
        <label for="bulan">Bulan</label>
        <div class="input-group">
          <select name="bulan" id="bulan" required>
            <option value="">-- Bulan --</option>
            <?php
            $bulan_arr = ['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni',
                '07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'];
            foreach ($bulan_arr as $num=>$nama) echo "<option value='$num'>$nama</option>";
            ?>
          </select>
          <input type="number" name="tahun" id="tahun" min="2024" max="2100" value="<?= date('Y') ?>" required>
        </div>
      </div>

      <!-- PILIH RUANGAN -->
      <div class="form-group">
        <label>Pilih Ruangan</label>
        <div id="ruanganContainer" class="checkbox-grid">
          <?php
          $q_ruang = mysqli_query($conn, "SELECT * FROM ruangan ORDER BY nama ASC");
          $no = 1;
          while ($r = mysqli_fetch_assoc($q_ruang)) {
            echo "
              <label class='checkbox-item'>
                <input type='checkbox' name='ruangan[]' value='{$r['nama']}' id='check{$r['id']}'>
                <span><b>{$no}.</b> {$r['nama']}</span>
              </label>";
            $no++;
          }
          ?>
        </div>
        <small class="kelola-link">
          <a href='#' data-bs-toggle='modal' data-bs-target='#modalRuangan'>⚙️ Kelola Ruangan</a>
        </small>
      </div>
    </div>

    <div class="form-actions">
      <button type="button" id="btnTambah" class="btn btn-success">Tambah Periode Baru</button>
    </div>
  </form>
</div>

<hr>
<br>  



<style>
    .table {
    width: 100%;
    margin-top: 10px;
    border-collapse: collapse;
    border-radius: 10px;
    overflow: hidden;
}

.table th {
    background: linear-gradient(180deg, #009879 0%, #007f69 100%);
    color: #fff;
    text-align: center;
    font-weight: 600;
    border: 1px solid #cbd5e1;
}

.table td {
    text-align: center;
    vertical-align: middle;
    border: 1px solid #dee2e6;
}

.table-hover tbody tr:hover {
    background-color: #e8f5ee;
    transition: 0.2s;
}

.badge.bg-success {
    background-color: #3c7a57 !important;
}

.badge.bg-secondary {
    background-color: #999 !important;
}

.accordion-item {
    border: none;
    border-radius: 5px;
    /* box-shadow: 0 3px 10px rgba(0,0,0,0.08); */
    overflow: hidden;
}

.accordion-button {
    background-color: #eaf5ef;
    color: #1e3d34;
    font-weight: 500;
    transition: all 0.3s ease;
}

.accordion-button:hover {
    background-color: #d7efe0;
}

.accordion-button:not(.collapsed) {
    background-color: #cbe6d6;
    color: #1e3d34;
}



/* === TITLE === */
.title {
  font-size: 1.8rem;
  font-weight: 600;
  color: #1e3d34;
  border-left: 6px solid #2e8b57;
  padding-left: 12px;
  margin-bottom: 30px;
}

/* === FORM === */
.periode-form {
  display: flex;
  flex-direction: column;
  gap: 30px;
  width: 100%;
}

/* === GRID FORM === */
.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 25px;
  width: 100%;
}

@media (max-width: 900px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
}

/* === FORM GROUP === */
.form-group label {
  font-weight: 600;
  margin-bottom: 8px;
  display: block;
  color: #34495e;
}

/* === INPUT GROUP === */
.input-group {
  display: flex;
  gap: 10px;
  width: 100%;
}

select,
input[type="number"] {
  flex: 1;
  padding: 10px 12px;
  border: 1.5px solid #cbd5e1;
  border-radius: 8px;
  font-size: 0.95rem;
  background: #f9fafb;
  transition: 0.3s;
}

select:focus,
input[type="number"]:focus {
  outline: none;
  border-color: #2e8b57;
  background: #ffffff;
  box-shadow: 0 0 6px rgba(46, 139, 87, 0.25);
}

/* === CHECKBOX GRID === */
.checkbox-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 10px 15px;
  background: #f8faf9;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 15px;
}

.checkbox-item {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.95rem;
  color: #1e3d34;
}

.checkbox-item input[type="checkbox"] {
  accent-color: #2e8b57;
  transform: scale(1.15);
  cursor: pointer;
}

/* === LINK KECIL === */
.kelola-link {
  display: block;
  margin-top: 8px;
}

.kelola-link a {
  text-decoration: none;
  color: #2e8b57;
  font-weight: 500;
}

.kelola-link a:hover {
  text-decoration: underline;
}

/* === TOMBOL (biarkan tombol aslinya tetap tapi disinkronkan gaya) === */
.btn-success {
  background-color: #006e5c !important;
  color: #fff !important;
  border: none !important;
  padding: 10px 20px !important;
  border-radius: 4px !important;
  font-size: 14px;
  font-weight: 600 !important;
  cursor: pointer;
  transition: 0.3s ease;

}

.btn-success:hover {
  background-color: #256d47 !important;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

/* === ALIGN BUTTON === */
.form-actions {
  text-align: right;
  width: 100%;
  
}

  

/* === PERBAIKAN TATA LETAK FORM PERIODE === */
.periode-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

/* Bungkus bulan, tahun, dan tombol jadi sejajar */
.form-grid {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 15px;
}

/* Grup bulan dan tahun di sisi kiri */
.form-group:first-child {
  display: flex;
  align-items: flex-end;
  gap: 10px;
  flex-wrap: wrap;
}

/* Input bulan dan tahun sejajar */
.form-group:first-child .input-group {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: nowrap;
}

/* Tombol tambah periode rata kanan */
.form-actions {
  margin-left: auto;
  margin-bottom: 5px;
}

/* Area pilih ruangan rapi di bawah */
.form-group:nth-child(2) {
  width: 100%;
}

.checkbox-grid {
  margin-top: 10px;
  background: #f9fafb;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 15px;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 10px 20px;
}

.checkbox-item {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.95rem;
  background: #fff;
  padding: 6px 10px;
  border-radius: 6px;
  transition: background 0.2s;
  border: 1px solid #e2e8f0;
}

.checkbox-item:hover {
  background: #e8f5ee;
}

/* Responsif */
@media (max-width: 768px) {
  .form-grid {
    flex-direction: column;
    align-items: stretch;
  }
  .form-actions {
    width: 100%;
    text-align: right;
    margin-top: 10px;
  }
}



.pagination-container {
  margin-top: 25px;
}

.pagination {
  display: inline-flex;
  gap: 6px;
  list-style: none;
  padding: 0;
  font-size: 13px; /* 🔹 ukuran font pagination */
}

.page-item .page-link {
  border: 1px solid #2e8b57;
  color: #2e8b57;
  padding: 4px 10px; /* 🔹 sedikit lebih kecil agar proporsional */
  border-radius: 4px;
  font-weight: 500;
  text-decoration: none;
  transition: 0.2s;
}

.page-item.active .page-link {
  background-color: #2e8b57;
  color: #fff;
  border-color: #2e8b57;
}

.page-item .page-link:hover {
  background-color: #e8f5ee;
  color: #256d47;
}

.page-item.disabled .page-link {
  color: #ccc;
  border-color: #ddd;
}

.popup {
  display: none;
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(0, 0, 0, 0.4);
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

.popup-content {
  background: #fff;
  padding: 25px 35px;
  border-radius: 15px;
  text-align: center;
  box-shadow: 0 5px 20px rgba(0,0,0,0.25);
  animation: fadeInUp 0.3s ease;
  font-family: "Poppins", sans-serif;
}

.popup-icon {
  font-size: 40px;
  color: #16a34a;
  display: block;
  margin-bottom: 10px;
}

@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(25px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>








<!-- TABEL DATA -->
<?php






?>

<?php
// === PAGINATION UNTUK BULAN ===
$bulan_keys = array_keys($grouped);
$total_bulan = count($bulan_keys);
$per_page = 12; // tampil 4 bulan per halaman
$total_pages = ceil($total_bulan / $per_page);

$current_page = isset($_GET['hal']) ? max(1, min((int)$_GET['hal'], $total_pages)) : 1;
$start_index = ($current_page - 1) * $per_page;
$bulan_keys_page = array_slice($bulan_keys, $start_index, $per_page, true);
?>

<div class="accordion" id="accordionBulan">
  <?php if (empty($grouped)): ?>
    <p class="text-muted text-center">Belum ada data observasi.</p>
  <?php else: $i=1; foreach($bulan_keys_page as $bulan): $rows=$grouped[$bulan]; ?>
    <div class="accordion-item mb-3">
      <h2 class="accordion-header" id="heading<?= $i ?>">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $i ?>">
          📅 <?= formatBulanTeks($bulan) ?>
        </button>
      </h2>
      <div id="collapse<?= $i ?>" class="accordion-collapse collapse">
        <div class="accordion-body">
          
          <!-- Tombol hapus bulan -->
          <div class="text-end mb-2">
            <button class="btn btn-outline-danger btn-sm" onclick="hapusBulan('<?= $bulan ?>')">
              🗑️ Hapus Bulan Ini
            </button>
          </div>

          <table class="table table-bordered table-hover text-center align-middle">
            <thead class="table-success">
              <tr>
                <th>No</th>
                <th>Ruangan</th>
                <th>Observer</th>
                <th>Status</th>
                <th>Tanggal Input</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php $n=1; foreach($rows as $d): ?>
              <tr>
                <td><?= $n++ ?></td>
                <td><?= htmlspecialchars($d['ruangan']) ?></td>
                <td><?= htmlspecialchars($d['observer']) ?></td>
                <td>
                  <span class="badge bg-<?= $d['status']=='aktif'?'success':'secondary' ?>">
                    <?= ucfirst($d['status']) ?>
                  </span>
                </td>
                <td><?= htmlspecialchars($d['tanggal_input']) ?></td>
                <td>
                  <?php if ($d['status'] == 'aktif'): ?>
                    <button class="btn btn-danger btn-sm" onclick="nonaktifkanPeriode(<?= $d['id'] ?>)">
                      <i class="bi bi-x-circle"></i> Nonaktifkan
                    </button>
                  <?php else: ?>
                    <button class="btn btn-success btn-sm" onclick="aktifkanPeriode(<?= $d['id'] ?>)">
                      <i class="bi bi-check-circle"></i> Aktifkan Kembali
                    </button>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  <?php $i++; endforeach; endif; ?>
</div>


<!-- === PAGINATION LINK === -->
<?php if ($total_pages > 1): ?>
  <div class="pagination-container text-center mt-4">
    <ul class="pagination justify-content-center">
      <?php if ($current_page > 1): ?>
        <li class="page-item"><a class="page-link" href="?page=observer&hal=1">First</a></li>
        <li class="page-item"><a class="page-link" href="?page=observer&hal=<?= $current_page - 1 ?>">«</a></li>
      <?php endif; ?>

      <?php
      $start_page = max(1, $current_page - 2);
      $end_page = min($total_pages, $start_page + 4);
      for ($p = $start_page; $p <= $end_page; $p++):
      ?>
        <li class="page-item <?= $p == $current_page ? 'active' : '' ?>">
          <a class="page-link" href="?page=observer&hal=<?= $p ?>"><?= $p ?></a>
        </li>
      <?php endfor; ?>

      <?php if ($current_page < $total_pages): ?>
        <li class="page-item"><a class="page-link" href="?page=observer&hal=<?= $current_page + 1 ?>">»</a></li>
        <li class="page-item"><a class="page-link" href="?page=observer&hal=<?= $total_pages ?>">Last</a></li>
      <?php endif; ?>
    </ul>
  </div>
<?php endif; ?>


<script>
// === SWEETALERT KONFIRMASI AKTIF/NONAKTIF ===
function nonaktifkanPeriode(id) {
  Swal.fire({
    title: 'Nonaktifkan Periode?',
    text: 'Periode ini akan ditandai sebagai selesai.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#aaa',
    confirmButtonText: 'Ya, Nonaktifkan'
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = "<?= $base_url ?>dashboard.php?page=observer&aksi=nonaktifkan&id=" + id;

    }
  });
}

function aktifkanPeriode(id) {
  Swal.fire({
    title: 'Aktifkan Kembali?',
    text: 'Periode ini akan menjadi periode aktif, dan yang lain akan otomatis ditutup.',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#2e8b57',
    cancelButtonColor: '#aaa',
    confirmButtonText: 'Ya, Aktifkan'
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = "<?= $base_url ?>dashboard.php?page=observer&aksi=aktifkan&id=" + id;

    }
  });
}
</script>












<!-- MODAL KELOLA RUANGAN -->
<div class="modal fade" id="modalRuangan" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content p-3">
      <div class="modal-header">
        <h5 class="modal-title">🏥 Kelola Ruangan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered text-center">
          <thead class="table-light"><tr><th>No</th><th>Nama Ruangan</th><th>Aksi</th></tr></thead>
          <tbody>
          <?php
          $q = mysqli_query($conn, "SELECT * FROM ruangan ORDER BY nama ASC");
          $no=1; while($r=mysqli_fetch_assoc($q)): ?>
            <tr>
              <td><?= $no++ ?></td>
              <td><?= htmlspecialchars($r['nama']) ?></td>
              <td>
                <form method="POST" class="d-inline">
                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                    <input type="hidden" name="aksi" value="hapus">
                    <button class="btn btn-danger btn-sm" onclick="return confirm('Hapus ruangan ini?')"><i class="bi bi-trash"></i></button>
                </form>
                <button class="btn btn-warning btn-sm" onclick="editRuang(<?= $r['id'] ?>, '<?= htmlspecialchars($r['nama']) ?>')">
                    <i class="bi bi-pencil"></i>
                </button>
              </td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>

    <!-- FORM TAMBAH RUANGAN -->
<form id="formTambah" method="POST" class="mt-3 d-flex gap-2">
  <input type="hidden" name="aksi" value="tambah">
  <input type="text" name="nama" id="namaRuang" class="form-control" placeholder="Nama Ruangan Baru" required>
  <button type="submit" class="btn btn-success">Tambah</button>
</form>

<!-- POPUP KONFIRMASI -->
<div id="popupBerhasil" class="popup">
  <div class="popup-content">
    <span class="popup-icon">✅</span>
    <p id="popupText"></p>
  </div>
</div>


      </div>
    </div>
  </div>
</div>

<!-- MODAL EDIT -->
<div class="modal fade" id="modalEdit" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <form method="POST" class="modal-content p-3">
      <input type="hidden" name="aksi" value="edit">
      <input type="hidden" name="id" id="editId">
      <div class="modal-header">
        <h5 class="modal-title">✏️ Edit Ruangan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="text" name="nama" id="editNama" class="form-control" required>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- ==== JS ==== -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="assets/observasi.js"></script>



<script>
document.addEventListener("DOMContentLoaded", () => {
  const checkboxes = document.querySelectorAll("#ruanganContainer .checkbox-item span");
  checkboxes.forEach((span, i) => {
    const text = span.textContent.replace(/^\d+\.\s*/, ""); // hapus nomor lama
    span.innerHTML = `<b>${i + 1}.</b> ${text}`;
  });
});
</script>





<script>
document.getElementById('btnTambah').addEventListener('click', function() {
  const bulan = document.getElementById('bulan').value;
  const tahun = document.getElementById('tahun').value;
  const ruanganDipilih = document.querySelectorAll('input[name="ruangan[]"]:checked').length;

  if (!bulan || !tahun) {
    Swal.fire('⚠️ Lengkapi Data', 'Pilih bulan dan tahun terlebih dahulu.', 'warning');
    return;
  }
  if (ruanganDipilih === 0) {
    Swal.fire('⚠️ Belum Memilih Ruangan', 'Pilih minimal satu ruangan.', 'warning');
    return;
  }

  <?php if ($aktif_count > 0): ?>
  Swal.fire({
    title: 'Tutup Periode Aktif?',
    text: 'Masih ada periode observasi aktif. Tutup dan buat periode baru?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Ya, lanjutkan',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      document.getElementById('forceClose').value = 'yes';
      document.getElementById('formPeriode').submit();
    }
  });
  <?php else: ?>
  document.getElementById('formPeriode').submit();
  <?php endif; ?>
});
</script>


<script>
// === FUNGSI BUKA MODAL EDIT ===
function editRuang(id, nama) {
  const modal = new bootstrap.Modal(document.getElementById('modalEdit'));
  document.getElementById('editId').value = id;
  document.getElementById('editNama').value = nama;
  modal.show();
}

// === NOTIFIKASI JIKA DATA BERHASIL DIHAPUS ===
<?php if (isset($_GET['deleted']) && $_GET['deleted'] == 'true'): ?>
Swal.fire({
  icon: 'success',
  title: '🗑️ Data ruangan berhasil dihapus!',
  showConfirmButton: false,
  timer: 2000,
  toast: true,
  position: 'top-end'
});
<?php endif; ?>

// === NOTIFIKASI JIKA DATA BERHASIL DIUPDATE ===
<?php if (isset($_GET['updated']) && $_GET['updated'] == 'true'): ?>
Swal.fire({
  icon: 'success',
  title: '✏️ Data ruangan berhasil diperbarui!',
  showConfirmButton: false,
  timer: 2000,
  toast: true,
  position: 'top-end'
});
<?php endif; ?>





</script>



<script>
// === FUNGSI HAPUS SEMUA DATA DALAM SATU BULAN ===
function hapusBulan(bulan) {
  Swal.fire({
    title: 'Hapus Semua Data?',
    text: `Semua data observasi pada bulan ${bulan} akan dihapus permanen!`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Ya, Hapus Sekarang',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {

      // 🔹 Pop-up kedua untuk konfirmasi terakhir
      Swal.fire({
        title: 'Yakin Betul?',
        text: 'Data akan benar-benar terhapus dan tidak dapat dikembalikan lagi!',
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#b91c1c',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus Permanen',
        cancelButtonText: 'Tidak Jadi'
      }).then((finalConfirm) => {
        if (finalConfirm.isConfirmed) {
          // arahkan ke URL hapus bulan sesuai parameter
          window.location.href = "<?= $base_url ?>dashboard.php?page=observer&hapus_bulan=" + bulan;


        }
      });
    }
  });
}
</script>








<!-- ===== SCRIPT POPUP ===== -->
<script>
document.getElementById("formTambah").addEventListener("submit", function(e) {
  e.preventDefault(); // cegah reload otomatis
  const nama = document.getElementById("namaRuang").value.trim();
  if (nama === "") return;

  // kirim form ke server
  fetch("", {
    method: "POST",
    body: new FormData(this)
  })
  .then(res => res.text())
  .then(data => {
    // tampilkan popup
    const popup = document.getElementById("popupBerhasil");
    const popupText = document.getElementById("popupText");
    popupText.innerHTML = `Berhasil menambahkan ruangan <b>${nama}</b>`;
    popup.style.display = "flex";

    // tunggu 2 detik lalu redirect ke halaman observer
    setTimeout(() => {
      window.location.href = "<?= $base_url ?>dashboard.php?page=observer";

    }, 500);
  })
  .catch(err => {
    alert("Terjadi kesalahan saat menambahkan ruangan!");
    console.error(err);
  });
});
</script>