<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dokumen Lain-lain</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <style>
        body {
            background-image: url('ocean-blue-copy-space-abstract-paper-waves_23-2148319152.avif'); /* Ganti dengan path gambar Anda */
            background-size: cover; /* Mengatur gambar agar menutupi seluruh halaman */
            background-repeat: no-repeat;
            background-position: center;
        }
        body {
            background-color: #f8f9fa;
        }
        .header-title {
            text-align: center;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        .header-description {
            text-align: center;
            color: #6c757d;
            margin-bottom: 30px;
        }
        .card {
            margin-top: 20px;
            transition: transform 0.2s;
        }
        .card:hover {
            transform: scale(1.05);
        }
        .navbar-brand {
            font-weight: bold;
        }
        .navbar {
            margin-bottom: 30px;
        }
        .container {
            margin-top: 20px;
        }
        .card-title {
            font-size: 18px;
            font-weight: 500;
        }
        .card-body {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        footer {
            margin-top: 30px;
            padding: 20px;
            background-color: #343a40;
            color: white;
            text-align: center;
        }
        .icon {
            font-size: 40px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="#">Manajemen Persuratan</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item"><a class="nav-link" href="dashboard.php">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="nota_dinas.php">Nota Dinas</a></li>
            <li class="nav-item"><a class="nav-link" href="undangan_internal.php">Undangan Internal</a></li>
            <li class="nav-item"><a class="nav-link" href="surat_penugasan.php">Surat Penugasan</a></li>
            <li class="nav-item active"><a class="nav-link" href="dokumen_lain_lain.php">Dokumen Lain <span class="sr-only">(current)</span></a></li>
        </ul>
    </div>
</nav>

<!-- Header Title and Description -->
<div class="header-title">
    <h2>Dokumen Lain-lain</h2>
    <p class="header-description">Pilih salah satu kategori dokumen di bawah ini untuk membuat atau mengelola dokumen terkait.</p>
</div>

<!-- Halaman Konten -->
<div class="container">
    <div class="row justify-content-center">
        <!-- Kategori 1: Nomor Laporan Tahun 2024 -->
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <i class="icon fas fa-file-alt"></i>
                    <h5 class="card-title">Nomor Laporan Tahun 2024</h5>
                    <button class="btn btn-primary" data-toggle="modal" data-target="#laporanModal">Buat Surat</button>
                </div>
            </div>
        </div>

        <!-- Kategori 2: Nomor Dokumen Lain Tahun 2023 -->
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <i class="icon fas fa-file"></i>
                    <h5 class="card-title">Nomor Dokumen Lain Tahun 2023</h5>
                    <button class="btn btn-primary" data-toggle="modal" data-target="#dokumenModal">Buat Surat</button>
                </div>
            </div>
        </div>

        <!-- Kategori 3: Nomor Surat Keterangan Tahun -->
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <i class="icon fas fa-file-signature"></i>
                    <h5 class="card-title">Nomor Surat Keterangan Tahun</h5>
                    <button class="btn btn-primary" data-toggle="modal" data-target="#suratKeteranganModal">Buat Surat</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<div class="modal fade" id="laporanModal" tabindex="-1" role="dialog" aria-labelledby="laporanModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="laporanModalLabel">Nomor Laporan Tahun 2024</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Informasi dan langkah untuk membuat surat dengan nomor laporan tahun 2024.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                <a href="laporan_2024.php" class="btn btn-primary">Buat Surat</a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="dokumenModal" tabindex="-1" role="dialog" aria-labelledby="dokumenModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dokumenModalLabel">Nomor Dokumen Lain Tahun 2023</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Informasi dan langkah untuk membuat surat dengan nomor dokumen lain tahun 2023.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                <a href="dokumen_lain_2023.php" class="btn btn-primary">Buat Surat</a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="suratKeteranganModal" tabindex="-1" role="dialog" aria-labelledby="suratKeteranganModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="suratKeteranganModalLabel">Nomor Surat Keterangan Tahun</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Informasi dan langkah untuk membuat surat keterangan.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                <a href="surat_keterangan.php" class="btn btn-primary">Buat Surat</a>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<footer>
    <p>&copy; Direktorat Infrastruktur Teknologi Informasi</p>
</footer>

<!-- Bootstrap and jQuery -->
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
<!-- Font Awesome for icons -->
<script src="https://kit.fontawesome.com/a076d05399.js"></script>
</body>
</html>
