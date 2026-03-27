<?php
$configPath = '../includes/config_settings.json';

// === Simpan Status ke File JSON ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $registerUser = $_POST['registerUser'] ?? 'aktif';
    $registerAdmin = $_POST['registerAdmin'] ?? 'aktif';
    
    $config = [
        'registerUser' => $registerUser,
        'registerAdmin' => $registerAdmin
    ];
    
    file_put_contents($configPath, json_encode($config, JSON_PRETTY_PRINT));
    echo "<script>alert('✅ Status pendaftaran berhasil diperbarui!');</script>";
}

if (file_exists($configPath)) {
    $config = json_decode(file_get_contents($configPath), true);
    $registerUser = $config['registerUser'] ?? 'aktif';
    $registerAdmin = $config['registerAdmin'] ?? 'aktif';
} else {
    $registerUser = 'aktif';
    $registerAdmin = 'aktif';
}
?>


<style>
:root {
  --main-color: #007A64;
  --accent-color: #009879;
  --danger-color: #c0392b;
  --bg-light: #f5f7fa;
  --text-dark: #333;
  --radius: 14px;
  --shadow: 0 6px 20px rgba(0,0,0,0.08);
}

body {
  background: var(--bg-light);
  color: var(--text-dark);
  font-family: "Poppins", sans-serif;
}

.container {
  max-width: 880px;
  margin: 40px auto;
}

h3 {
  font-weight: 600;
  color: var(--main-color);
  margin-bottom: 24px;
}

.card {
  background: #fff;
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  border: none;
  padding: 28px 32px;
  margin-bottom: 24px;
  transition: all 0.3s ease;
}
.card:hover { transform: translateY(-2px); }

.form-section-title {
  font-size: 16px;
  font-weight: 600;
  color: var(--main-color);
  margin-bottom: 12px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.form-check-label {
  font-weight: 500;
  margin-left: 6px;
}

.btn-main {
  background: linear-gradient(135deg, var(--main-color), var(--accent-color));
  color: #fff;
  border: none;
  border-radius: var(--radius);
  padding: 10px 20px;
  font-weight: 500;
  transition: all 0.3s ease;
}
.btn-main:hover { opacity: 0.9; transform: translateY(-2px); }

.btn-danger-custom {
  background: linear-gradient(135deg, var(--danger-color), #e74c3c);
  color: #fff;
  border: none;
  border-radius: var(--radius);
  padding: 10px 20px;
  font-weight: 500;
  transition: all 0.3s ease;
}
.btn-danger-custom:hover { opacity: 0.9; transform: translateY(-2px); }

.btn-toggle {
  flex: 1;
  border: 2px solid var(--main-color);
  border-radius: var(--radius);
  padding: 10px;
  background: transparent;
  color: var(--main-color);
  font-weight: 500;
  transition: all 0.3s ease;
}
.btn-toggle:hover {
  background: var(--main-color);
  color: #fff;
}
.btn-toggle.active {
  background: linear-gradient(135deg, var(--main-color), var(--accent-color));
  color: #fff;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.text-muted {
  font-size: 13px;
  margin-top: 6px;
  color: #777;
}

/* Responsif */
@media (max-width: 768px) {
  .card { padding: 20px; }
  .btn-main, .btn-danger-custom { width: 100%; }
}
</style>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container">
  <h3><i class="ph ph-gear-six"></i> Pengaturan Tampilan</h3>

<div class="card">
  <h5 class="form-section-title">🧍 Kontrol Tombol Pendaftaran</h5>
  <p>Atur apakah tombol "Daftar User" dan "Daftar Admin" tampil di halaman login utama.</p>

  <!-- Kontrol Daftar User -->
  <div class="mb-3">
    <label class="form-section-title">👤 Pendaftaran User</label>
    <div class="d-flex gap-3 flex-wrap mb-3">
      <button id="btnUserAktif" class="btn-toggle <?= $registerUser === 'aktif' ? 'active' : '' ?>">✅ Aktif</button>
      <button id="btnUserNonaktif" class="btn-toggle <?= $registerUser === 'nonaktif' ? 'active' : '' ?>">🚫 Nonaktif</button>
    </div>
  </div>

  <!-- Kontrol Daftar Admin -->
  <div class="mb-3">
    <label class="form-section-title">🛡️ Pendaftaran Admin</label>
    <div class="d-flex gap-3 flex-wrap mb-3">
      <button id="btnAdminAktif" class="btn-toggle <?= $registerAdmin === 'aktif' ? 'active' : '' ?>">✅ Aktif</button>
      <button id="btnAdminNonaktif" class="btn-toggle <?= $registerAdmin === 'nonaktif' ? 'active' : '' ?>">🚫 Nonaktif</button>
    </div>
  </div>

  <form method="POST" id="registerStatusForm">
    <input type="hidden" name="registerUser" id="registerUserInput" value="<?= htmlspecialchars($registerUser) ?>">
    <input type="hidden" name="registerAdmin" id="registerAdminInput" value="<?= htmlspecialchars($registerAdmin) ?>">
    <button type="submit" class="btn-main w-100">💾 Simpan Status</button>
  </form>

  <p class="text-muted">Perubahan akan langsung diterapkan di halaman login.</p>
</div>

</div>


<script>
document.addEventListener("DOMContentLoaded", function() {
  const colorInput = document.getElementById("themeColor");
  const saveButton = document.getElementById("saveSettings");
  const resetButton = document.getElementById("resetSettings");
  const btnAktif = document.getElementById("btnAktif");
  const btnNonaktif = document.getElementById("btnNonaktif");
  const statusInput = document.getElementById("registerStatusInput");

  // === Fungsi Tema ===
  function setThemeColor(color) {
    document.documentElement.style.setProperty('--main-color', color);
    localStorage.setItem('themeColor', color);
  }
  function setMode(mode) {
    if (mode === 'dark') document.body.classList.add('dark-mode');
    else document.body.classList.remove('dark-mode');
    localStorage.setItem('mode', mode);
  }
  function setSidebarStyle(style) {
    const sidebar = document.querySelector(".sidebar");
    if (sidebar) {
      sidebar.classList.remove("gradient-light","gradient-dark","solid");
      sidebar.classList.add(style);
    }
    localStorage.setItem('sidebarStyle', style);
  }

  // === Load ===
  const savedColor = localStorage.getItem('themeColor') || '#007A64';
  const savedMode = localStorage.getItem('mode') || 'light';
  const savedSidebar = localStorage.getItem('sidebarStyle') || 'gradient-light';
  setThemeColor(savedColor);
  setMode(savedMode);
  setSidebarStyle(savedSidebar);
  colorInput.value = savedColor;
  document.getElementById(savedMode + 'Mode').checked = true;
  document.querySelector(`input[name='sidebarStyle'][value='${savedSidebar}']`).checked = true;

  // === Simpan Tema ===
  saveButton.addEventListener("click", () => {
    const color = colorInput.value;
    const mode = document.querySelector("input[name='mode']:checked").value;
    const sidebarStyle = document.querySelector("input[name='sidebarStyle']:checked").value;
    setThemeColor(color);
    setMode(mode);
    setSidebarStyle(sidebarStyle);

    Swal.fire({
      icon: 'success',
      title: 'Pengaturan Disimpan!',
      text: 'Tampilan berhasil diperbarui.',
      showConfirmButton: false,
      timer: 1600,
      timerProgressBar: true
    });
  });

  // === Reset Tema ===
  resetButton.addEventListener("click", () => {
    localStorage.clear();
    setThemeColor('#007A64');
    setMode('light');
    setSidebarStyle('gradient-light');
    colorInput.value = '#007A64';
    document.getElementById('lightMode').checked = true;
    document.getElementById('gradientLight').checked = true;

    Swal.fire({
      icon: 'info',
      title: 'Reset ke Default',
      text: 'Tema dan warna berhasil dikembalikan.',
      showConfirmButton: false,
      timer: 1600
    });
  });

  // === Tombol Aktif/Nonaktif ===
  btnAktif.addEventListener("click", function(){
    btnAktif.classList.add('active');
    btnNonaktif.classList.remove('active');
    statusInput.value = 'aktif';
  });
  btnNonaktif.addEventListener("click", function(){
    btnNonaktif.classList.add('active');
    btnAktif.classList.remove('active');
    statusInput.value = 'nonaktif';
  });

  // === Sinkronisasi ke index.php ===
  document.getElementById("registerStatusForm").addEventListener("submit", function(e){
    e.preventDefault();
    const status = statusInput.value;
    localStorage.setItem("registerStatus", status);

    fetch("", {
      method: "POST",
      body: new FormData(this)
    }).then(() => {
      Swal.fire({
        icon: 'success',
        title: 'Status Diperbarui!',
        text: `Tombol daftar akun sekarang: ${status.toUpperCase()}.`,
        showConfirmButton: false,
        timer: 2000
      });
    });
  });
});




const btnUserAktif = document.getElementById("btnUserAktif");
const btnUserNonaktif = document.getElementById("btnUserNonaktif");
const btnAdminAktif = document.getElementById("btnAdminAktif");
const btnAdminNonaktif = document.getElementById("btnAdminNonaktif");
const inputUser = document.getElementById("registerUserInput");
const inputAdmin = document.getElementById("registerAdminInput");

// === Tombol User ===
btnUserAktif.addEventListener("click", () => {
  btnUserAktif.classList.add("active");
  btnUserNonaktif.classList.remove("active");
  inputUser.value = "aktif";
});
btnUserNonaktif.addEventListener("click", () => {
  btnUserNonaktif.classList.add("active");
  btnUserAktif.classList.remove("active");
  inputUser.value = "nonaktif";
});

// === Tombol Admin ===
btnAdminAktif.addEventListener("click", () => {
  btnAdminAktif.classList.add("active");
  btnAdminNonaktif.classList.remove("active");
  inputAdmin.value = "aktif";
});
btnAdminNonaktif.addEventListener("click", () => {
  btnAdminNonaktif.classList.add("active");
  btnAdminAktif.classList.remove("active");
  inputAdmin.value = "nonaktif";
});

// === Simpan Status ===
document.getElementById("registerStatusForm").addEventListener("submit", function(e){
  e.preventDefault();
  const userStatus = inputUser.value;
  const adminStatus = inputAdmin.value;
  localStorage.setItem("registerUser", userStatus);
  localStorage.setItem("registerAdmin", adminStatus);

  fetch("", {
    method: "POST",
    body: new FormData(this)
  }).then(() => {
    Swal.fire({
      icon: 'success',
      title: 'Status Diperbarui!',
      text: `User: ${userStatus.toUpperCase()} | Admin: ${adminStatus.toUpperCase()}`,
      showConfirmButton: false,
      timer: 2000
    });
  });
});

</script>


<script>
document.addEventListener("DOMContentLoaded", function() {

  // elemen
  const colorInput = document.getElementById("themeColor");
  const saveButton = document.getElementById("saveSettings");
  const resetButton = document.getElementById("resetSettings");
  const btnAktif = document.getElementById("btnAktif");
  const btnNonaktif = document.getElementById("btnNonaktif");
  const statusInput = document.getElementById("registerStatusInput");
  const regForm = document.getElementById("registerStatusForm");
  const sidebarEl = document.querySelector(".sidebar"); // pastikan ada .sidebar di layout Anda

  // helper: hex <-> rgb & shade mixer
  function hexToRgb(hex) {
    hex = (hex || "").replace("#", "");
    if (hex.length === 3) hex = hex.split("").map(h => h+h).join("");
    const int = parseInt(hex, 16);
    return { r: (int >> 16) & 255, g: (int >> 8) & 255, b: int & 255 };
  }
  function rgbToHex(r,g,b){
    return "#" + [r,g,b].map(v => {
      const s = Math.max(0, Math.min(255, Math.round(v))).toString(16);
      return s.length === 1 ? "0"+s : s;
    }).join("");
  }
  // percent positive => lighten toward white; negative => darken
  function mixPercent(hex, percent) {
    const c = hexToRgb(hex);
    const p = percent/100;
    function mix(v){
      if (p >= 0) return Math.round(v + (255 - v) * p);
      return Math.round(v * (1 + p));
    }
    return rgbToHex(mix(c.r), mix(c.g), mix(c.b));
  }

  // === applyColorLive: update :root var + sidebar background (live) ===
  function applyColorLive(hex) {
    if (!hex) return;
    // set CSS variable untuk digunakan di sisa UI
    document.documentElement.style.setProperty('--main-color', hex);
    // simpan ke localStorage untuk persist antar halaman
    localStorage.setItem('themeColor', hex);

    // companion colors untuk gradient
    const lighter = mixPercent(hex, 22);  // 22% lebih terang
    const darker  = mixPercent(hex, -18); // 18% lebih gelap

    // ambil pilihan style sidebar saat ini
    const sidebarStyle = document.querySelector("input[name='sidebarStyle']:checked")?.value || 'gradient-light';

    if (sidebarEl) {
      if (sidebarStyle === 'solid') {
        sidebarEl.style.background = hex;
      } else if (sidebarStyle === 'gradient-dark') {
        sidebarEl.style.background = `linear-gradient(180deg, ${darker} 0%, ${hex} 100%)`;
      } else {
        // gradient-light (default)
        sidebarEl.style.background = `linear-gradient(180deg, ${hex} 0%, ${lighter} 100%)`;
      }
    }
  }

  // jika ada perubahan pada radio sidebarStyle => update preview sesuai warna saat ini
  document.querySelectorAll("input[name='sidebarStyle']").forEach(r => {
    r.addEventListener('change', function(){
      const cur = colorInput?.value || getComputedStyle(document.documentElement).getPropertyValue('--main-color').trim() || '#007A64';
      applyColorLive(cur);
      localStorage.setItem('sidebarStyle', this.value);
    });
  });

  // live input color: langsung preview saat slider digeser
  if (colorInput) {
    colorInput.addEventListener('input', function(e){
      applyColorLive(e.target.value);
    });
  }

  // === inisialisasi dari localStorage saat load halaman (persist) ===
  (function initFromStorage(){
    const savedColor = localStorage.getItem('themeColor') || '#007A64';
    const savedMode  = localStorage.getItem('mode') || 'light';
    const savedSidebar = localStorage.getItem('sidebarStyle') || document.querySelector("input[name='sidebarStyle']:checked")?.value || 'gradient-light';

    if (colorInput) colorInput.value = savedColor;
    const sideRadio = document.querySelector(`input[name='sidebarStyle'][value='${savedSidebar}']`);
    if (sideRadio) sideRadio.checked = true;

    applyColorLive(savedColor);

    // set mode radio / body class sesuai savedMode
    const modeRadio = document.querySelector(`input[name='mode'][value='${savedMode}']`);
    if (modeRadio) modeRadio.checked = true;
    document.body.classList.toggle('dark-mode', savedMode === 'dark');
  })();

  // === Save theme button (localStorage) ===
  saveButton && saveButton.addEventListener('click', function(){
    const color = colorInput?.value || '#007A64';
    const mode = document.querySelector("input[name='mode']:checked")?.value || 'light';
    const sidebarStyle = document.querySelector("input[name='sidebarStyle']:checked")?.value || 'gradient-light';

    localStorage.setItem('themeColor', color);
    localStorage.setItem('mode', mode);
    localStorage.setItem('sidebarStyle', sidebarStyle);

    // nice popup
    if (window.Swal) {
      Swal.fire({ icon: 'success', title: 'Pengaturan Disimpan', text: 'Tampilan berhasil diperbarui.', timer:1500, showConfirmButton:false });
    } else alert('Pengaturan Disimpan');
  });

  // === Reset button ===
  resetButton && resetButton.addEventListener('click', function(){
    localStorage.removeItem('themeColor');
    localStorage.removeItem('mode');
    localStorage.removeItem('sidebarStyle');

    // kembalikan defaults & apply
    if (colorInput) colorInput.value = '#007A64';
    document.getElementById('lightMode')?.checked = true;
    document.getElementById('gradientLight')?.checked = true;
    applyColorLive('#007A64');

    if (window.Swal) Swal.fire({ icon:'info', title:'Reset ke Default', timer:1400, showConfirmButton:false });
    else alert('Reset ke default');
  });

  // === Aktivasi tombol Aktif / Nonaktif di UI admin ===
  btnAktif && btnAktif.addEventListener('click', function(){
    btnAktif.classList.add('active'); btnNonaktif.classList.remove('active');
    statusInput.value = 'aktif';
  });
  btnNonaktif && btnNonaktif.addEventListener('click', function(){
    btnNonaktif.classList.add('active'); btnAktif.classList.remove('active');
    statusInput.value = 'nonaktif';
  });

  // === Submit registerStatus form: simpan file (server) + set localStorage untuk sinkron index.php ===
  if (regForm) {
    regForm.addEventListener('submit', function(e){
      e.preventDefault();
      const status = statusInput.value || 'aktif';

      // simpan di localStorage dulu supaya tab index.php yang terbuka segera bereaksi (storage event)
      localStorage.setItem('registerStatus', status);

      // kirim POST ke server (menggunakan form data), server akan update JSON file
      fetch("", { method: "POST", body: new FormData(this) })
        .then(resp => {
          if (window.Swal) {
            Swal.fire({ icon:'success', title:'Status Diperbarui', text: `Daftar: ${status.toUpperCase()}`, timer:1500, showConfirmButton:false });
          } else alert('Status diperbarui: ' + status);
        })
        .catch(err => {
          console.error(err);
          if (window.Swal) Swal.fire({ icon:'error', title:'Gagal menyimpan' });
          else alert('Gagal menyimpan status');
        });
    });
  }

}); // DOMContentLoaded
</script>

