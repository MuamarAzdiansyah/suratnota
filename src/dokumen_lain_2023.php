<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: index.php");
    exit;
}

include 'config.php';

// Proses hapus data
if (isset($_GET['hapus_id'])) {
    $hapus_id = $_GET['hapus_id'];

    // Pastikan untuk menggunakan prepared statement untuk keamanan
    $stmt = $conn->prepare("DELETE FROM dokumen_lain WHERE id = ?");
    $stmt->bind_param("i", $hapus_id);

    if ($stmt->execute()) {
        $success_message = "Data berhasil dihapus.";
    } else {
        $error_message = "Gagal menghapus data: " . $stmt->error;
    }

    $stmt->close();
}

// Proses ketika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $nomor = $_POST['nomor'];
    $tanggal = $_POST['tanggal'];
    $perihal = $_POST['perihal'];

    // Proses upload dokumen
    if (isset($_FILES['dokumen']) && $_FILES['dokumen']['error'] == UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['dokumen']['tmp_name'];
        $file_name = $_FILES['dokumen']['name'];
        $file_size = $_FILES['dokumen']['size'];
        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);

        // Validasi ukuran file (maksimal 5MB)
        if ($file_size > 5 * 1024 * 1024) {
            $error_message = "File terlalu besar. Maksimal 5MB.";
        } elseif (!in_array($file_ext, ['pdf', 'doc', 'docx'])) {
            $error_message = "Tipe file tidak valid. Hanya diperbolehkan PDF, DOC, atau DOCX.";
        } else {
            // Tentukan path untuk menyimpan file
            $upload_dir = 'uploads/'; // Pastikan direktori ini ada dan writable
            $file_path = $upload_dir . basename($file_name);

            // Pindahkan file ke direktori tujuan
            if (move_uploaded_file($file_tmp, $file_path)) {
                // Simpan informasi dokumen ke database
                $stmt = $conn->prepare("INSERT INTO dokumen_lain (nomor_memo, tanggal, perihal, dokumen) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $nomor, $tanggal, $perihal, $file_path);

                if ($stmt->execute()) {
                    $success_message = "Dokumen berhasil diunggah.";
                } else {
                    $error_message = "Gagal menyimpan data: " . $stmt->error;
                }

                $stmt->close();
            } else {
                $error_message = "Gagal mengunggah file.";
            }
        }
    } else {
        $error_message = "Dokumen tidak diupload.";
    }
}

// Proses pencarian berdasarkan perihal
$search_query = '';
if (isset($_GET['search'])) {
    $search_query = $_GET['search'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NOMOR DOKUMEN LAIN TAHUN 2023</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f0f4f8;
            overflow-x: hidden; /* Mencegah scroll horizontal */
        }
        .sidebar {
            width: 250px;
            height: 100%;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #343a40;
            padding-top: 60px;
            z-index: 1000;
            transition: all 0.3s;
        }
        .sidebar.closed {
            left: -250px; /* Menutup sidebar ke kiri */
        }
        .sidebar a {
            padding: 15px 20px;
            text-decoration: none;
            font-size: 18px;
            color: #f1f1f1;
            display: block;
        }
        .sidebar a:hover {
            background-color: #575757;
        }
        .content {
            margin-left: 250px; /* Memberikan ruang untuk sidebar */
            padding: 20px;
            transition: margin-left 0.3s;
        }
        .content.expanded {
            margin-left: 10px; /* Mengurangi ruang saat sidebar ditutup */
        }
        h2, h3 {
            color: #343a40;
        }
        .alert {
            margin-top: 20px;
        }
        .navbar {
            margin-left: 250px; /* Memberikan ruang untuk sidebar */
            margin-bottom: 20px;
            transition: margin-left 0.3s;
        }
        .navbar.expanded {
            margin-left: 10px; /* Mengurangi ruang saat sidebar ditutup */
        }
        .toggle-sidebar {
            cursor: pointer;
            margin-left: 15px;
            color: #007bff;
            font-size: 24px;
        }
        .toggle-sidebar:hover {
            color: #0056b3;
        }
        .form-control, .form-control-file {
            border-radius: 0.25rem; /* Bulatkan sudut */
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        .table {
            margin-top: 30px;
            border-radius: 0.25rem;
            overflow: hidden; /* Untuk border-radius pada tabel */
        }
        .table th, .table td {
            vertical-align: middle; /* Rata tengah vertikal */
        }
    </style>
</head>
<body>

<!-- Navbar di bagian atas -->
<nav class="navbar navbar-expand-lg navbar-light bg-light" id="navbar">
    <a class="navbar-brand" href="#">Manajemen Dokumen</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav">
            <li class="nav-item active">
                <a class="nav-link" href="laporan_2024.php">Laporan 2024 <span class="sr-only">(current)</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="surat_keterangan.php">Surat Keterangan</a>
            </li>
        </ul>
        <span class="toggle-sidebar" onclick="toggleSidebar()">☰</span>
    </div>
</nav>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <a href="dashboard.php">Home</a>
    <a href="nota_dinas.php">Nota Dinas</a>
    <a href="undangan_internal.php">Undangan Internal</a>
    <a href="surat_penugasan.php">Surat Penugasan</a>
    <a href="dokumen_lain_lain.php">Dokumen Lain-lain</a>
    <a href="logout.php" class="btn btn-danger btn-block mt-4">Keluar</a>
</div>

<!-- Konten Utama -->
<div class="content" id="content">
    <div class="container">
        <h2 class="text-center">NOMOR DOKUMEN LAIN TAHUN 2023</h2>

        <!-- Pesan sukses/gagal -->
        <?php if (isset($success_message)) : ?>
            <div class="alert alert-success" role="alert">
                <?php echo $success_message; ?>
            </div>
        <?php elseif (isset($error_message)) : ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <!-- Form pencarian berdasarkan perihal -->
        <form action="" method="GET">
            <div class="form-group">
                <label for="search">Cari Berdasarkan Perihal</label>
                <input type="text" class="form-control" id="search" name="search" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Masukkan kata kunci perihal">
                <button type="submit" class="btn btn-primary mt-2">Cari</button>
            </div>
        </form>

        <!-- Form Upload Dokumen -->
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="nomor">Nomor Memo</label>
                    <input type="text" class="form-control" id="nomor" name="nomor" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-3">
                    <label for="tanggal">Tanggal</label>
                    <input type="date" class="form-control" id="tanggal" name="tanggal" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-12">
                    <label for="perihal">Perihal</label>
                    <textarea class="form-control" id="perihal" name="perihal" rows="2" required></textarea>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-12">
                    <label for="dokumen">Unggah Dokumen</label>
                    <input type="file" class="form-control-file" id="dokumen" name="dokumen" accept=".pdf,.doc,.docx" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Simpan</button>
        </form>

        <!-- Tabel Data Dokumen -->
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Nomor Memo</th>
                    <th>Tanggal</th>
                    <th>Perihal</th>
                    <th>Dokumen</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Pencarian berdasarkan perihal
                if ($search_query) {
                    $stmt = $conn->prepare("SELECT * FROM dokumen_lain WHERE perihal LIKE ?");
                    $search_param = "%$search_query%";
                    $stmt->bind_param("s", $search_param);
                } else {
                    $stmt = $conn->prepare("SELECT * FROM dokumen_lain");
                }

                $stmt->execute();
                $result = $stmt->get_result();

                $no = 1;
                while ($row = $result->fetch_assoc()) : ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($row['nomor_memo']); ?></td>
                        <td><?php echo htmlspecialchars($row['tanggal']); ?></td>
                        <td><?php echo htmlspecialchars($row['perihal']); ?></td>
                        <td><a href="<?php echo htmlspecialchars($row['dokumen']); ?>" target="_blank">Lihat Dokumen</a></td>
                        <td>
                            <a href="?hapus_id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus dokumen ini?');">Hapus</a>
                            <a href="edit_dokumen_lain_2023.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                        </td>
                    </tr>
                <?php endwhile; ?>

                <?php $stmt->close(); ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('closed');
        document.getElementById('content').classList.toggle('expanded');
        document.getElementById('navbar').classList.toggle('expanded');
    }
</script>

<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>

</body>
</html>
