<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: index.php");
    exit;
}

include 'config.php';

// Mengambil jumlah nota dinas
$result_nota_dinas = $conn->query("SELECT COUNT(*) as total FROM nota_dinas");
$row_nota_dinas = $result_nota_dinas->fetch_assoc();
$total_nota_dinas = $row_nota_dinas['total'];

// Mengambil jumlah undangan internal
$result_undangan_internal = $conn->query("SELECT COUNT(*) as total FROM undangan_internal");
$row_undangan_internal = $result_undangan_internal->fetch_assoc();
$total_undangan_internal = $row_undangan_internal['total'];

// Mengambil jumlah surat penugasan
$result_surat_penugasan = $conn->query("SELECT COUNT(*) as total FROM surat_penugasan");
$row_surat_penugasan = $result_surat_penugasan->fetch_assoc();
$total_surat_penugasan = $row_surat_penugasan['total'];

// Mengambil data nota dinas per bulan
$result_monthly_nota = $conn->query("SELECT MONTH(tanggal) AS month, COUNT(*) AS count FROM nota_dinas GROUP BY MONTH(tanggal)");
$monthly_counts_nota = [];
while ($row = $result_monthly_nota->fetch_assoc()) {
    $monthly_counts_nota[$row['month']] = $row['count'];
}

// Mengambil data undangan internal per bulan
$result_monthly_undangan = $conn->query("SELECT MONTH(tanggal) AS month, COUNT(*) AS count FROM undangan_internal GROUP BY MONTH(tanggal)");
$monthly_counts_undangan = [];
while ($row = $result_monthly_undangan->fetch_assoc()) {
    $monthly_counts_undangan[$row['month']] = $row['count'];
}

// Mengambil data surat penugasan per bulan
$result_monthly_surat = $conn->query("SELECT MONTH(tanggal) AS month, COUNT(*) AS count FROM surat_penugasan GROUP BY MONTH(tanggal)");
$monthly_counts_surat = [];
while ($row = $result_monthly_surat->fetch_assoc()) {
    $monthly_counts_surat[$row['month']] = $row['count'];
}

// Siapkan data untuk chart
$months = range(1, 12);

// Data untuk Nota Dinas
$counts_nota = array_map(function($month) use ($monthly_counts_nota) {
    return isset($monthly_counts_nota[$month]) ? $monthly_counts_nota[$month] : 0;
}, $months);

// Data untuk Undangan Internal
$counts_undangan = array_map(function($month) use ($monthly_counts_undangan) {
    return isset($monthly_counts_undangan[$month]) ? $monthly_counts_undangan[$month] : 0;
}, $months);

// Data untuk Surat Penugasan
$counts_surat = array_map(function($month) use ($monthly_counts_surat) {
    return isset($monthly_counts_surat[$month]) ? $monthly_counts_surat[$month] : 0;
}, $months);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Halaman Utama</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .card {
            margin: 20px 0;
        }
        .navbar-nav {
            margin-left: auto;
        }
        .logout-btn {
            margin-right: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">Halaman Utama</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Keluar <i class="fas fa-sign-out-alt"></i></a>
                </li>
            </ul>
        </div>
    </nav>

    <h2 class="mt-4">Halaman Utama</h2>
    <ul class="nav nav-tabs">
        <li class="nav-item"><a class="nav-link" href="nota_dinas.php">Nota Dinas</a></li>
        <li class="nav-item"><a class="nav-link" href="undangan_internal.php">Undangan Internal</a></li>
        <li class="nav-item"><a class="nav-link" href="surat_penugasan.php">Surat Penugasan</a></li>
        <li class="nav-item"><a class="nav-link" href="dokumen_lain_lain.php">Dokumen Lain-lain</a></li>
    </ul>

    <div class="row">
        <div class="col-md-4">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title">Total Nota Dinas</h5>
                    <h2 class="card-text"><?= $total_nota_dinas ?></h2>
                    <a href="nota_dinas.php" class="btn btn-light">Lihat Detail</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Total Undangan Internal</h5>
                    <h2 class="card-text"><?= $total_undangan_internal ?></h2>
                    <a href="undangan_internal.php" class="btn btn-light">Lihat Detail</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title">Total Surat Penugasan</h5>
                    <h2 class="card-text"><?= $total_surat_penugasan ?></h2>
                    <a href="surat_penugasan.php" class="btn btn-light">Lihat Detail</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Grafik Nota Dinas -->
    <div class="mt-4">
        <h3>Grafik Nota Dinas per Bulan</h3>
        <canvas id="notaDinasChart" width="200" height="100"></canvas>
    </div>

    <!-- Grafik Undangan Internal -->
    <div class="mt-4">
        <h3>Grafik Undangan Internal per Bulan</h3>
        <canvas id="undanganInternalChart" width="200" height="100"></canvas>
    </div>

    <!-- Grafik Surat Penugasan -->
    <div class="mt-4">
        <h3>Grafik Surat Penugasan per Bulan</h3>
        <canvas id="suratPenugasanChart" width="200" height="100"></canvas>
    </div>
</div>

<script>
    // Chart untuk Nota Dinas
    const ctxNotaDinas = document.getElementById('notaDinasChart').getContext('2d');
    const notaDinasChart = new Chart(ctxNotaDinas, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Jumlah Nota Dinas',
                data: <?= json_encode($counts_nota) ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Chart untuk Undangan Internal
    const ctxUndanganInternal = document.getElementById('undanganInternalChart').getContext('2d');
    const undanganInternalChart = new Chart(ctxUndanganInternal, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Jumlah Undangan Internal',
                data: <?= json_encode($counts_undangan) ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Chart untuk Surat Penugasan
    const ctxSuratPenugasan = document.getElementById('suratPenugasanChart').getContext('2d');
    const suratPenugasanChart = new Chart(ctxSuratPenugasan, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Jumlah Surat Penugasan',
                data: <?= json_encode($counts_surat) ?>,
                backgroundColor: 'rgba(255, 206, 86, 0.6)',
                borderColor: 'rgba(255, 206, 86, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>

</body>
</html>
