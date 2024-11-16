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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Utama</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        <style>
    body {
        background-color: #f8f9fa;
        transition: background-color 0.5s;
    }
    .card {
        margin: 20px 0;
        transition: transform 0.3s;
    }
    .card:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    }
    .navbar-nav {
        margin-left: auto;
    }
    .logout-btn {
        margin-right: 20px;
    }
    .chart-container {
        position: relative;
        margin: auto; /* Center the chart */
        height: 40vh;
        width: 60vw; /* Adjust width to 60% */
    }
    .alert {
        margin: 20px 0;
    }
    .header-image {
        width: 100%;
        height: 200px;
        object-fit: cover;
        margin-bottom: 20px;
    }
    .card-icon {
        font-size: 30px;
        margin-right: 10px;
    }
</style>

    </style>
</head>
<body>
<div class="container">
  
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#"></a>
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

    <div class="alert alert-info" role="alert">
        Selamat datang di dashboard! Di sini Anda dapat mengelola semua dokumen yang diperlukan.
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card text-white bg-info">
                <div class="card-body d-flex align-items-center">
                    <i class="fas fa-file-alt card-icon"></i>
                    <div>
                        <h5 class="card-title">Total Nota Dinas</h5>
                        <h2 class="card-text"><?= $total_nota_dinas ?></h2>
                        <a href="nota_dinas.php" class="btn btn-light">Lihat Detail</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-white bg-success">
                <div class="card-body d-flex align-items-center">
                    <i class="fas fa-envelope card-icon"></i>
                    <div>
                        <h5 class="card-title">Total Undangan Internal</h5>
                        <h2 class="card-text"><?= $total_undangan_internal ?></h2>
                        <a href="undangan_internal.php" class="btn btn-light">Lihat Detail</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-white bg-warning">
                <div class="card-body d-flex align-items-center">
                    <i class="fas fa-tasks card-icon"></i>
                    <div>
                        <h5 class="card-title">Total Surat Penugasan</h5>
                        <h2 class="card-text"><?= $total_surat_penugasan ?></h2>
                        <a href="surat_penugasan.php" class="btn btn-light">Lihat Detail</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Grafik Nota Dinas -->
    <div class="mt-4 text-center"> <!-- Added text-center class for centering -->
    <h3>Grafik Nota Dinas per Bulan</h3>
    <div class="chart-container">
        <canvas id="notaDinasChart"></canvas>
    </div>
</div>

<div class="mt-4 text-center"> <!-- Added text-center class for centering -->
    <h3>Grafik Undangan Internal per Bulan</h3>
    <div class="chart-container">
        <canvas id="undanganInternalChart"></canvas>
    </div>
</div>

<div class="mt-4 text-center"> <!-- Added text-center class for centering -->
    <h3>Grafik Surat Penugasan per Bulan</h3>
    <div class="chart-container">
        <canvas id="suratPenugasanChart"></canvas>
    </div>
</div>

<div class="mt-4 text-center"> <!-- Added text-center class for centering -->
    <h3>Distribusi Total Dokumen</h3>
    <div class="chart-container">
        <canvas id="totalDokumenChart"></canvas>
    </div>
</div>


<script>
    // Grafik Nota Dinas
    var ctxNotaDinas = document.getElementById('notaDinasChart').getContext('2d');
    var notaDinasChart = new Chart(ctxNotaDinas, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Nota Dinas',
                data: <?= json_encode($counts_nota) ?>,
                backgroundColor: 'rgba(23, 162, 184, 0.7)',
                borderColor: 'rgba(23, 162, 184, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Grafik Undangan Internal
    var ctxUndanganInternal = document.getElementById('undanganInternalChart').getContext('2d');
    var undanganInternalChart = new Chart(ctxUndanganInternal, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Undangan Internal',
                data: <?= json_encode($counts_undangan) ?>,
                backgroundColor: 'rgba(40, 167, 69, 0.7)',
                borderColor: 'rgba(40, 167, 69, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Grafik Surat Penugasan
    var ctxSuratPenugasan = document.getElementById('suratPenugasanChart').getContext('2d');
    var suratPenugasanChart = new Chart(ctxSuratPenugasan, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Surat Penugasan',
                data: <?= json_encode($counts_surat) ?>,
                backgroundColor: 'rgba(255, 193, 7, 0.7)',
                borderColor: 'rgba(255, 193, 7, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Grafik Total Semua Jenis Dokumen
    var ctxTotalDokumen = document.getElementById('totalDokumenChart').getContext('2d');
    var totalDokumenChart = new Chart(ctxTotalDokumen, {
        type: 'pie',
        data: {
            labels: ['Nota Dinas', 'Undangan Internal', 'Surat Penugasan'],
            datasets: [{
                label: 'Total Dokumen',
                data: [<?= $total_nota_dinas ?>, <?= $total_undangan_internal ?>, <?= $total_surat_penugasan ?>],
                backgroundColor: [
                    'rgba(23, 162, 184, 0.7)',
                    'rgba(40, 167, 69, 0.7)',
                    'rgba(255, 193, 7, 0.7)'
                ],
            }]
        },
        options: {
            responsive: true
        }
    });
</script>
</body>
</html>
