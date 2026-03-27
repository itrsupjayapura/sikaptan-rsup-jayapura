<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include __DIR__ . '/../../config.php';

// ✅ Batasi akses hanya untuk admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: {$base_url}index.php");
    exit;
}

// ✅ URL redirect kembali ke halaman ini
$redirect_url = "{$base_url}dashboard.php?page=pengisian_form_cuci";

// ================= TAMBAH FIELD =================
if (isset($_POST['add'])) {
    $field_name = strtolower(trim($_POST['field_name']));
$field_name = preg_replace('/[^a-z0-9_]+/', '_', $field_name); // ubah spasi & karakter aneh jadi _
$field_name = trim($field_name, '_'); // hapus _ di awal/akhir

    $display_label = trim($_POST['display_label']);

    if ($field_name !== '' && $display_label !== '') {
        // Tambah field baru ke tabel daftar field
        mysqli_query($conn, "INSERT INTO cuci_tangan_fields (field_name, display_label) VALUES ('$field_name', '$display_label')");

        // Tambahkan kolom baru ke tabel observasi_cuci_tangan
        mysqli_query($conn, "ALTER TABLE observasi_cuci_tangan 
            ADD COLUMN `$field_name` ENUM('Ya', 'Tidak', 'Tidak Dinilai') DEFAULT 'Tidak Dinilai'");
    }

    // ✅ Redirect aman via JavaScript
    echo "<script>window.location.href='{$redirect_url}&success=add';</script>";
    exit;
}

// ================= EDIT FIELD =================
if (isset($_POST['edit'])) {
    $id = intval($_POST['id']);
    $display_label = trim($_POST['display_label']);

    if ($display_label !== '') {
        mysqli_query($conn, "UPDATE cuci_tangan_fields SET display_label='$display_label' WHERE id='$id'");
    }

    echo "<script>window.location.href='{$redirect_url}&success=edit';</script>";
    exit;
}

// ================= HAPUS FIELD =================
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // Ambil nama kolom sebelum dihapus
    $q = mysqli_query($conn, "SELECT field_name FROM cuci_tangan_fields WHERE id='$id'");
    if ($r = mysqli_fetch_assoc($q)) {
        $field_name = $r['field_name'];

        // Hapus kolom dari tabel observasi_cuci_tangan
        mysqli_query($conn, "ALTER TABLE observasi_cuci_tangan DROP COLUMN `$field_name`");
    }

    // Hapus dari tabel daftar field
    mysqli_query($conn, "DELETE FROM cuci_tangan_fields WHERE id='$id'");

    echo "<script>window.location.href='{$redirect_url}&success=delete';</script>";
    exit;
}

// ================= AMBIL SEMUA DATA =================
$fields = mysqli_query($conn, "SELECT * FROM cuci_tangan_fields ORDER BY id ASC");
?>


<style>
/* ====== GLOBAL STYLE ====== */
body {
  font-family: "Segoe UI", Arial, sans-serif;
  margin: 0;
  padding: 0;
  background: #f7fdfc;
  color: #333;
}

/* ====== WRAPPER ====== */
.container-apd {
  max-width: 100%;
  margin: auto;
  padding: 0px;
}

/* ====== HEADER ====== */
.apd-header h3 {
  color: #007A64;
  font-weight: 700;
  margin-bottom: 10px;
}
.apd-header p {
  color: #555;
  margin-bottom: 20px;
}

/* ====== ALERT ====== */
.alert-success {
  background: #d4edda;
  color: #155724;
  padding: 12px;
  border-radius: 6px;
  margin-bottom: 20px;
  font-size: 15px;
}

/* ====== CARD TAMBAH ====== */
.card {
  border: 1px solid #007A64;
  border-radius: 10px;
  margin-bottom: 25px;
  background: white;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}
.card h4 {
  background: #007A64;
  color: white;
  padding: 10px 15px;
  border-radius: 10px 10px 0 0;
  font-size: 16px;
  margin: 0;
}
.card form {
  padding: 15px;
}
.card input {
  flex: 1;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 14px;
}
.card button {
  background: #007A64;
  color: white;
  border: none;
  padding: 10px 18px;
  border-radius: 6px;
  cursor: pointer;
  transition: 0.3s;
}
.card button:hover {
  background: #026652;
}

/* ====== TABEL ====== */
.table-wrapper {
  border: 1px solid #007A64;
  border-radius: 10px;
  overflow: hidden;
  background: white;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}
.table-wrapper h4 {
  background: #007A64;
  color: white;
  padding: 10px 15px;
  margin: 0;
  font-size: 16px;
}
.table-wrapper table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}
.table-wrapper th, .table-wrapper td {
  padding: 10px;
  border: 1px solid #ddd;
  text-align: center;
}
.table-wrapper th {
  background: #c9f2e9;
  font-weight: 600;
}
.table-wrapper code {
  background: #f3f3f3;
  padding: 3px 6px;
  border-radius: 4px;
}

/* ====== BUTTON AKSI ====== */
.btn-edit {
  background: #ffc107;
  color: #000;
  border: none;
  padding: 6px 10px;
  border-radius: 6px;
  cursor: pointer;
}
.btn-delete {
  background: #dc3545;
  color: #fff;
  padding: 6px 10px;
  border-radius: 6px;
  text-decoration: none;
}
.btn-edit:hover { opacity: 0.8; }
.btn-delete:hover { opacity: 0.8; }

/* ====== MODAL ====== */
#editModal {
  display: none;
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(0,0,0,0.4);
  justify-content: center;
  align-items: center;
  z-index: 1000;
}
.modal-content {
  background: white;
  padding: 20px;
  border-radius: 8px;
  width: 90%;
  max-width: 350px;
}
.modal-content h4 {
  margin-bottom: 15px;
  color: #007A64;
}
.modal-content input {
  width: 100%;
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 6px;
  margin-bottom: 12px;
}
.modal-content button {
  padding: 8px 12px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}
.btn-cancel {
  background: #6c757d;
  color: white;
}
.btn-save {
  background: #007A64;
  color: white;
}

/* ====== RESPONSIVE ====== */
@media (max-width: 768px) {
  .card form {
    display: flex;
    flex-direction: column;
  }
  .card input, .card button {
    width: 100%;
  }
  .table-wrapper table {
    font-size: 13px;
  }
  .table-wrapper th, .table-wrapper td {
    padding: 8px;
  }
}
</style>

<div class="container-apd">
    <div class="apd-header">
        <h3>🧴 Pengaturan Form Observasi Cuci Tangan</h3>
        <p>Atur field (kolom) yang muncul di halaman <b>Form Observasi Cuci Tangan</b>. Anda dapat menambah, mengedit, atau menghapus.</p>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert-success">
            ✅ Data berhasil <?= htmlspecialchars($_GET['success']) ?>!
        </div>
    <?php endif; ?>

    <!-- Form Tambah -->
    <div class="card">
        <h4>Tambah Field Baru</h4>
        <form method="POST">
            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <input type="text" name="field_name" placeholder="Nama Field (misal: sebelum_menyentuh_pasien)" required>
                <input type="text" name="display_label" placeholder="Teks Tampilan (misal: Sebelum menyentuh pasien)" required>
                <button type="submit" name="add">Tambah</button>
            </div>
        </form>
    </div>

    <!-- Tabel Data -->
    <div class="table-wrapper">
        <h4>Daftar Field Observasi Cuci Tangan</h4>
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Field</th>
                        <th>Label Tampilan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no=1; while($f = mysqli_fetch_assoc($fields)): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><code><?= htmlspecialchars($f['field_name']) ?></code></td>
                        <td><?= htmlspecialchars($f['display_label']) ?></td>
                        <td>
                            <button class="btn-edit" onclick="openEdit(<?= $f['id'] ?>, '<?= htmlspecialchars(addslashes($f['display_label'])) ?>')">Edit</button>
                            <a class="btn-delete" href="dashboard.php?page=pengisian_form_cuci&delete=<?= $f['id'] ?>" onclick="return confirm('Hapus field ini?')">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div id="editModal">
    <div class="modal-content">
        <h4>Edit Field</h4>
        <form method="POST">
            <input type="hidden" name="id" id="edit_id">
            <label>Label Tampilan:</label>
            <input type="text" name="display_label" id="edit_label" required>
            <div style="text-align:right;">
                <button type="button" class="btn-cancel" onclick="closeEdit()">Batal</button>
                <button type="submit" name="edit" class="btn-save">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEdit(id, label) {
    document.getElementById('editModal').style.display = 'flex';
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_label').value = label;
}
function closeEdit() {
    document.getElementById('editModal').style.display = 'none';
}
</script>
