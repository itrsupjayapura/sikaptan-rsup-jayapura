<?php
include '../config.php'; // pastikan sudah konek DB

// === Pagination Setup ===
$filter = isset($_GET['role']) ? $_GET['role'] : 'pegawai';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$limit = 10;
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$offset = ($page - 1) * $limit;

// === Query Data Berdasarkan Filter dan Pencarian ===
$where = ($filter === 'pegawai') ? "role='user'" : "role='admin'";
if ($search !== '') {
    $safeSearch = mysqli_real_escape_string($conn, $search);
    $where .= " AND (nama LIKE '%$safeSearch%' OR username LIKE '%$safeSearch%' OR jabatan LIKE '%$safeSearch%' OR ruangan LIKE '%$safeSearch%')";
}

$query = "SELECT id, username, nama, role, ruangan, jabatan FROM users WHERE $where LIMIT $limit OFFSET $offset";
$countQuery = "SELECT COUNT(*) AS total FROM users WHERE $where";

$result = mysqli_query($conn, $query);
$totalData = mysqli_fetch_assoc(mysqli_query($conn, $countQuery))['total'];
$totalPages = ceil($totalData / $limit);

// === Hitung Total Admin dan Pegawai ===
$countAdmin = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role='admin'"))['total'];
$countPegawai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role='user'"))['total'];
?>

<style>
/* ======== TABEL STYLE ======== */
.table-container {
  width: 100%;
  overflow-x: auto;
  margin-top: 25px;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  background: #fff;
  border: 1px solid #cbd5e1;
}
table {
  width: 100%;
  border-collapse: collapse;
  white-space: nowrap;
  min-width: 900px;
  border: 1px solid #cbd5e1;
}
th, td {
  padding: 10px 14px;
  text-align: center;
  border: 1px solid #cbd5e1;
  transition: background 0.2s ease, color 0.2s ease;
}
th {
  background: linear-gradient(180deg, #009879 0%, #007f69 100%);
  color: #fff;
  font-weight: 600;
  text-transform: uppercase;
  font-size: 13px;
}
tr:nth-child(even) { background: #f9fafb; }
tr:hover td { background: #e8f5f1; color: #000; }
td { font-size: 14px; color: #2f3e46; }

/* ======== ROLE BADGE ======== */
.role-badge {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  text-transform: capitalize;
}
.role-badge.admin { background: #c8f7dc; color: #146c43; border: 1px solid #9ed9b8; }
.role-badge.user { background: #d8ecff; color: #004085; border: 1px solid #a4d0ff; }

/* ======== BUTTONS ======== */
.action-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 6px 12px;
  font-size: 13px;
  border-radius: 6px;
  border: none;
  cursor: pointer;
  transition: all 0.3s ease;
  text-decoration: none;
  font-weight: 500;
}
.btn-edit { background-color: #009879; color: #fff; }
.btn-edit:hover { background-color: #007a63; transform: translateY(-2px); }
.btn-delete { background-color: #e74c3c; color: #fff; }
.btn-delete:hover { background-color: #c0392b; transform: translateY(-2px); }

/* ======== POPUP MODAL ======== */
.modal-overlay {
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(0,0,0,0.5);
  display: none;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  animation: fadeIn 0.3s ease;
}
@keyframes fadeIn { from {opacity:0;} to {opacity:1;} }

.modal-box {
  background: #fff;
  border-radius: 10px;
  padding: 25px 30px;
  max-width: 400px;
  width: 90%;
  box-shadow: 0 4px 20px rgba(0,0,0,0.2);
  text-align: center;
  animation: slideUp 0.3s ease;
}
@keyframes slideUp { from {transform:translateY(20px);opacity:0;} to {transform:translateY(0);opacity:1;} }

.modal-box h3 {
  margin-bottom: 10px;
  font-size: 20px;
  color: #009879;
}
.modal-box p {
  color: #444;
  font-size: 15px;
  margin-bottom: 20px;
}
.modal-actions {
  display: flex;
  justify-content: center;
  gap: 12px;
}
.modal-btn {
  padding: 8px 18px;
  border-radius: 6px;
  font-size: 14px;
  cursor: pointer;
  border: none;
  transition: 0.3s;
  font-weight: 600;
}
.btn-cancel { background: #e5e7eb; color: #333; }
.btn-cancel:hover { background: #d1d5db; }
.btn-confirm { background: #009879; color: #fff; }
.btn-confirm:hover { background: #007a63; }

/* ======== FILTER, SUMMARY, SEARCH ======== */
.filter-box { display: flex; align-items: center; gap: 10px; margin-bottom: 15px; }
.filter-box select {
  padding: 8px 12px; border-radius: 5px;
  border: 1px solid #cbd5e1; background: #fff; cursor: pointer;
  transition: 0.3s;
}
.filter-box select:hover { border-color: #009879; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }

.summary-wrapper { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; margin-bottom: 15px; }
.summary-box { display: flex; gap: 20px; flex-wrap: wrap; }
.summary-item {
  background: #fff; border: 1px solid #cbd5e1; border-radius: 5px;
  padding: 10px 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}
.summary-item span { font-weight: 600; color: #009879; }

.search-box input {
  padding: 8px 12px; border-radius: 5px; border: 1px solid #cbd5e1;
  width: 230px; transition: 0.3s;
}
.search-box input:focus {
  border-color: #009879; outline: none;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}

/* ======== PAGINATION ======== */
.pagination { margin-top: 20px; display: flex; justify-content: center; gap: 6px; flex-wrap: wrap; }
.pagination a {
  padding: 6px 12px; border-radius: 6px; text-decoration: none;
  font-weight: 500; color: #009879; border: 1px solid #009879; transition: all 0.2s ease;
}
.pagination a:hover { background: #009879; color: #fff; }
.pagination a.active { background: #009879; color: #fff; }
.pagination a.disabled { color: #aaa; border-color: #ccc; pointer-events: none; }

@media (max-width: 768px) {
  .summary-wrapper { flex-direction: column; align-items: stretch; gap: 10px; }
}




/* ======== SUMMARY WRAPPER ======== */
.summary-wrapper {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  margin-bottom: 15px;
}

/* ======== SUMMARY ITEM ======== */
.clickable-summary {
  cursor: pointer;
  border: 1px solid #ccc;
  background: #ffffff;
  color: #2f3e46;
  border-radius: 8px;
  padding: 10px 16px;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 6px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
  transition: 0.3s ease;
}

/* ======== SAAT AKTIF ======== */
.active-summary {
  background: #009879;       /* hijau lembut */
  border-color: #008466;
  color: #ffffff;             /* teks ikon tetap putih */
  box-shadow: 0 2px 6px rgba(0, 152, 121, 0.25);
}

/* ======== ANGKA TOTAL ======== */
.clickable-summary span {
  color: #000000ff !important;     /* angka selalu hitam */
  /* font-weight: bold; */
}


</style>

<div class="page-header">
  <h2>👤 Akun Terdaftar</h2>
  <p class="page-subtitle">Daftar akun berdasarkan kategori pengguna.</p>
</div>



<div class="summary-wrapper">
  <div class="summary-box">
 <div class="summary-item clickable-summary <?= $filter === 'pegawai' ? 'active-summary' : '' ?>" onclick="filterRole('pegawai')">
  👩‍🔧 Total Pegawai: <span><?= $countPegawai ?></span>
</div>
<div class="summary-item clickable-summary <?= $filter === 'admin' ? 'active-summary' : '' ?>" onclick="filterRole('admin')">
  👨‍💼 Total Admin: <span><?= $countAdmin ?></span>
</div>

  </div>

  <div class="search-box">
    <form method="GET" action="">
      <input type="hidden" name="page" value="akun">
      <input type="hidden" name="role" value="<?= $filter ?>">
      <input type="text" id="searchInput" name="search" placeholder="🔍 Cari nama, jabatan, ruangan..." value="<?= htmlspecialchars($search) ?>">
    </form>
  </div>
</div>


<div class="table-container">
  <table>
    <thead>
      <tr>
        <th>No</th>
        <th>Username</th>
        <th>Nama Lengkap</th>
        <th>Jabatan</th>
        <th>Ruangan</th>
        <th>Role</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php if (mysqli_num_rows($result) > 0): ?>
        <?php $no = $offset + 1; while ($row = mysqli_fetch_assoc($result)): ?>
          <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($row['username']) ?></td>
            <td><?= htmlspecialchars($row['nama']) ?></td>
            <td><?= htmlspecialchars($row['jabatan'] ?: '-') ?></td>
            <td><?= htmlspecialchars($row['ruangan']) ?></td>
            <td><span class="role-badge <?= htmlspecialchars($row['role']) ?>"><?= ucfirst(htmlspecialchars($row['role'])) ?></span></td>
            <td>
              <button class="action-btn btn-edit" onclick="showModal('edit', '<?= $row['id'] ?>')">✏️ Edit</button>
              <button class="action-btn btn-delete" onclick="showModal('delete', '<?= $row['id'] ?>')">🗑 Hapus</button>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="7" style="text-align:center; color:#888;">Tidak ada data akun.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- MODAL -->
<div class="modal-overlay" id="modalConfirm">
  <div class="modal-box">
    <h3 id="modalTitle">Konfirmasi</h3>
    <p id="modalMessage">Apakah Anda yakin?</p>
    <div class="modal-actions">
      <button class="modal-btn btn-cancel" onclick="closeModal()">Batal</button>
      <button class="modal-btn btn-confirm" id="btnYes">Ya</button>
    </div>
  </div>
</div>

<!-- PAGINATION -->
<?php if ($totalPages > 1): ?>
<div class="pagination">
  <?php if ($page > 1): ?>
    <a href="?page=akun&role=<?= $filter ?>&search=<?= urlencode($search) ?>&p=1">First</a>
    <a href="?page=akun&role=<?= $filter ?>&search=<?= urlencode($search) ?>&p=<?= $page - 1 ?>">&laquo;</a>
  <?php else: ?>
    <a class="disabled">First</a><a class="disabled">&laquo;</a>
  <?php endif; ?>

  <?php
  $start = max(1, $page - 4);
  $end = min($totalPages, $page + 5);
  for ($i = $start; $i <= $end; $i++): ?>
    <a href="?page=akun&role=<?= $filter ?>&search=<?= urlencode($search) ?>&p=<?= $i ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
  <?php endfor; ?>

  <?php if ($page < $totalPages): ?>
    <a href="?page=akun&role=<?= $filter ?>&search=<?= urlencode($search) ?>&p=<?= $page + 1 ?>">&raquo;</a>
    <a href="?page=akun&role=<?= $filter ?>&search=<?= urlencode($search) ?>&p=<?= $totalPages ?>">Last</a>
  <?php else: ?>
    <a class="disabled">&raquo;</a><a class="disabled">Last</a>
  <?php endif; ?>
</div>
<?php endif; ?>

<script>
function filterRole(role) {
  const url = new URL(window.location.href);
  url.searchParams.set('role', role);
  url.searchParams.set('page', 'akun');
  window.location.href = url.toString();
}

function showModal(type, id) {
  const modal = document.getElementById('modalConfirm');
  const title = document.getElementById('modalTitle');
  const message = document.getElementById('modalMessage');
  const btnYes = document.getElementById('btnYes');

  modal.style.display = 'flex';
  document.body.style.overflow = 'hidden';

  if (type === 'delete') {
    title.textContent = 'Hapus Akun';
    message.textContent = 'Apakah Anda yakin ingin menghapus akun ini?';
    btnYes.onclick = () => { window.location.href = `hapus_user.php?id=${id}`; };
  } else {
    title.textContent = 'Edit Akun';
    message.textContent = 'Anda akan mengedit data akun ini.';
    btnYes.onclick = () => { window.location.href = `edit_user.php?id=${id}`; };
  }
}

function closeModal() {
  const modal = document.getElementById('modalConfirm');
  modal.style.display = 'none';
  document.body.style.overflow = 'auto';
}

// === DELAYED SEARCH FORM SUBMIT ===
let typingTimer;
const searchInput = document.getElementById('searchInput');
if (searchInput) {
  searchInput.addEventListener('input', () => {
    clearTimeout(typingTimer);
    typingTimer = setTimeout(() => {
      searchInput.form.submit();
    }, 1000); // delay 700ms setelah berhenti mengetik
  });
}



</script>

</script>


