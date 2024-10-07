<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: index.php");
    exit;
}

include 'config.php';

// Proses upload dokumen dan penyimpanan ke database
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomor = $_POST['nomor'];
    $tanggal = $_POST['tanggal'];
    $perihal = $_POST['perihal'];
    $nomor_memo = htmlspecialchars($nomor);

    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["dokumen"]["name"]);
    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $upload_ok = 1;

    // Validasi file
    if ($_FILES["dokumen"]["size"] > 5000000) {
        $upload_ok = 0;
        $error_message = "File terlalu besar.";
    }

    if (!in_array($file_type, ["pdf", "doc", "docx"])) {
        $upload_ok = 0;
        $error_message = "Hanya file PDF, DOC, dan DOCX yang diizinkan.";
    }

    if ($upload_ok && move_uploaded_file($_FILES["dokumen"]["tmp_name"], $target_file)) {
        $stmt = $conn->prepare("INSERT INTO laporan_2024 (nomor_memo, dokumen, tanggal, perihal) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nomor_memo, $target_file, $tanggal, $perihal);
        if ($stmt->execute()) {
            $success_message = "Data berhasil disimpan.";
        } else {
            $error_message = "Terjadi kesalahan saat menyimpan data.";
        }
    } else {
        $error_message = "Terjadi kesalahan saat mengupload file.";
    }
}

// Proses hapus dokumen
if (isset($_GET['hapus_id'])) {
    $id = $_GET['hapus_id'];
    $result = $conn->query("SELECT dokumen FROM laporan_2024 WHERE id = '$id'");
    $row = $result->fetch_assoc();
    if (file_exists($row['dokumen'])) {
        unlink($row['dokumen']);
    }
    $stmt = $conn->prepare("DELETE FROM laporan_2024 WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $success_message = "Data berhasil dihapus.";
    } else {
        $error_message = "Terjadi kesalahan saat menghapus data.";
    }
}

// Proses pencarian berdasarkan perihal
$search_perihal = '';
if (isset($_GET['search_perihal'])) {
    $search_perihal = $_GET['search_perihal'];
    $query = "SELECT * FROM laporan_2024 WHERE perihal LIKE '%$search_perihal%'";
} else {
    $query = "SELECT * FROM laporan_2024";
}
$result = $conn->query($query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NOMOR LAPORAN TAHUN 2024</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            height: 100vh;
            width: 250px;
            position: fixed;
            top: 0;
            left: -250px;
            background-color: #343a40;
            transition: 0.3s;
            padding-top: 60px;
            z-index: 100;
        }
        .sidebar a {
            color: white;
            padding: 15px 20px;
            text-decoration: none;
            display: block;
            transition: 0.3s;
            font-size: 16px;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .content {
            transition: margin-left 0.3s;
            padding: 20px;
            margin-left: 0;
        }
        .navbar-top {
            background-color: #007bff;
            color: white;
            position: fixed;
            width: 100%;
            top: 0;
            left: 0;
            padding: 10px 0;
            z-index: 200;
            transition: margin-top 0.3s;
        }
        .navbar-top a {
            color: white;
            padding: 10px;
            text-decoration: none;
            font-weight: bold;
        }
        .toggle-btn {
            cursor: pointer;
            padding: 10px 20px;
            font-size: 18px;
            color: white;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .toggle-btn:hover {
            background-color: #0056b3;
        }
        .sidebar.open {
            left: 0;
        }
        .content.sidebar-open {
            margin-left: 250px;
        }
        .navbar-top.collapsed {
            margin-top: -60px;
        }
    </style>
</head>
<body>

<div class="navbar-top" id="navbar">
    <button class="toggle-btn" id="toggleSidebar">&#9776; Menu</button>
    <a href="dokumen_lain_2023.php" class="float-right">Dokumen Lain 2023</a>
    <a href="surat_keterangan.php" class="float-right">Surat Keterangan Tahun</a>
</div>

<div id="sidebar" class="sidebar">
    <a href="#" id="closeBtn">&times; Tutup</a>
    <a href="dashboard.php">Home</a>
    <a href="nota_dinas.php">Nota Dinas</a>
    <a href="undangan_internal.php">Undangan Internal</a>
    <a href="surat_penugasan.php">Surat Penugasan</a>
    <a href="dokumen_lain_lain.php">Dokumen Lain Lain</a>
    <a href="logout.php" style="background-color: #dc3545; font-weight: bold;">Keluar</a>
</div>

<div id="content" class="content">
    <h2 class="mt-5">NOMOR DOKUMEN TAHUN 2024</h2>

    <?php if (isset($success_message)) : ?>
        <div class="alert alert-success" role="alert">
            <?php echo $success_message; ?>
        </div>
    <?php elseif (isset($error_message)) : ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <form action="laporan_2024.php" method="POST" enctype="multipart/form-data">
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
            <div class="form-group col-md-6">
                <label for="perihal">Perihal</label>
                <input type="text" class="form-control" id="perihal" name="perihal" required>
            </div>
        </div>
        <div class="form-group">
            <label for="dokumen">Upload Dokumen (PDF/DOC/DOCX - Maksimal 5MB)</label>
            <input type="file" class="form-control-file" id="dokumen" name="dokumen" required>
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>

    <!-- Form Pencarian -->
    <form action="laporan_2024.php" method="GET" class="mt-3">
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="search_perihal">Cari Berdasarkan Perihal</label>
                <input type="text" class="form-control" id="search_perihal" name="search_perihal" placeholder="Masukkan Perihal" value="<?php echo htmlspecialchars($search_perihal); ?>">
            </div>
            <div class="form-group col-md-2 align-self-end">
                <button type="submit" class="btn btn-info">Cari</button>
            </div>
        </div>
    </form>

    <h3 class="mt-5">Data Dokumen Lain</h3>
    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>No</th>
                <th>Nomor Memo</th>
                <th>Tanggal</th>
                <th>Perihal</th>
                <th>Dokumen</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            while ($row = $result->fetch_assoc()) :
            ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo htmlspecialchars($row['nomor_memo']); ?></td>
                    <td><?php echo htmlspecialchars($row['tanggal']); ?></td>
                    <td><?php echo htmlspecialchars($row['perihal']); ?></td>
                    <td><a href="<?php echo htmlspecialchars($row['dokumen']); ?>" target="_blank">Lihat Surat</a></td>
                    <td><a href="laporan_2024.php?hapus_id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm">Hapus</a></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script>
    document.getElementById('toggleSidebar').onclick = function () {
        document.getElementById('sidebar').classList.toggle('open');
        document.getElementById('content').classList.toggle('sidebar-open');
    };
    document.getElementById('closeBtn').onclick = function () {
        document.getElementById('sidebar').classList.remove('open');
        document.getElementById('content').classList.remove('sidebar-open');
    };
</script>

</body>
</html>
