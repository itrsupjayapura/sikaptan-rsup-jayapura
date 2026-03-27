<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../config.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header('Location: ../index.php');
    exit;
}

$result = mysqli_query($conn, "SELECT * FROM observasi_apd ORDER BY tanggal DESC");

// Inisialisasi total
$total_numerator = 0;
$total_denumerator = 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - Kepatuhan APD</title>
    <link rel="stylesheet" href="style.css"> <!-- gunakan CSS utama kamu -->
</head>
<body>

<div class="container">
    <h2 class="judul-halaman">📊 Dashboard Kepatuhan APD</h2>

    <div class="card">
        <h3>📅 Data Observasi</h3>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Petugas</th>
                        <th>Haircap</th>
                        <th>Faceshield</th>
                        <th>Masker</th>
                        <th>Gown</th>
                        <th>Sarung Tangan</th>
                        <th>Boot</th>
                        <th>Numerator</th>
                        <th>Denumerator</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['tanggal']) ?></td>
                            <td><?= htmlspecialchars($row['petugas']) ?></td>
                            <td><?= htmlspecialchars($row['haircap']) ?></td>
                            <td><?= htmlspecialchars($row['faceshield']) ?></td>
                            <td><?= htmlspecialchars($row['masker']) ?></td>
                            <td><?= htmlspecialchars($row['gown']) ?></td>
                            <td><?= htmlspecialchars($row['sarung_tangan']) ?></td>
                            <td><?= htmlspecialchars($row['boot']) ?></td>
                            <td align="center"><?= $row['numerator'] ?></td>
                            <td align="center"><?= $row['denumerator'] ?></td>
                        </tr>

                        <?php
                            $total_numerator += $row['numerator'];
                            $total_denumerator += $row['denumerator'];
                        ?>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <br>

    <?php
    // Hitung persentase
    if ($total_denumerator > 0) {
        $persentase = ($total_numerator / $total_denumerator) * 100;
    } else {
        $persentase = 0;
    }

    // Tercapai atau tidak
    $status = ($persentase >= 100) ? "TERCAPAI" : "TIDAK TERCAPAI";

    // Format persen dua angka di belakang koma
    $persen_format = number_format($persentase, 2);
    ?>

    <div class="card summary-card">
        <h3>📈 Rekapitulasi Kepatuhan APD</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Total Numerator</th>
                    <th>Total Denumerator</th>
                    <th>Persentase Kepatuhan</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td align="center"><?= $total_numerator ?></td>
                    <td align="center"><?= $total_denumerator ?></td>
                    <td align="center"><?= $persen_format ?>%</td>
                    <td align="center">
                        <span class="status <?= ($status == 'TERCAPAI') ? 'aktif' : 'selesai' ?>">
                            <strong><?= $status ?></strong>
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
