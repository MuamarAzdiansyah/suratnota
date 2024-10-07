<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: index.php");
    exit;
}

include 'config.php';

// Proses edit dokumen
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
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

    // Update tanpa file baru
    if (empty($_FILES["dokumen"]["name"])) {
        $stmt = $conn->prepare("UPDATE laporan_2024 SET nomor_memo = ?, tanggal = ?, perihal = ? WHERE id = ?");
        $stmt->bind_param("sssi", $nomor_memo, $tanggal, $perihal, $id);
        if ($stmt->execute()) {
            $success_message = "Data berhasil diperbarui.";
        } else {
            $error_message = "Terjadi kesalahan saat memperbarui data.";
        }
    } elseif ($upload_ok && move_uploaded_file($_FILES["dokumen"]["tmp_name"], $target_file)) {
        // Update dengan file baru
        $stmt = $conn->prepare("UPDATE laporan_2024 SET nomor_memo = ?, dokumen = ?, tanggal = ?, perihal = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $nomor_memo, $target_file, $tanggal, $perihal, $id);
        if ($stmt->execute()) {
            $success_message = "Data berhasil diperbarui.";
        } else {
            $error_message = "Terjadi kesalahan saat memperbarui data.";
        }
    } else {
        $error_message = "Terjadi kesalahan saat mengupload file.";
    }
}

// Ambil data dokumen yang akan diedit
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $result = $conn->query("SELECT * FROM laporan_2024 WHERE id = '$id'");
    $row = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Laporan Tahun 2024</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .content {
            padding: 20px;
            max-width: 600px; /* Membatasi lebar form */
            margin: auto; /* Memusatkan form */
        }
        .form-control, .btn {
            margin-bottom: 15px; /* Menambahkan jarak antara elemen */
        }
    </style>
</head>
<body>

<div class="content">
    <h2>Edit Laporan Tahun 2024</h2>
    
    <?php if (isset($success_message)) : ?>
        <div class="alert alert-success" role="alert">
            <?php echo $success_message; ?>
        </div>
    <?php elseif (isset($error_message)) : ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <form action="edit_laporan_2024.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
        
        <div class="form-group">
            <label for="nomor">Nomor Memo</label>
            <input type="text" class="form-control" id="nomor" name="nomor" value="<?php echo $row['nomor_memo']; ?>" required>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="tanggal">Tanggal</label>
                <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?php echo $row['tanggal']; ?>" required>
            </div>
            <div class="form-group col-md-6">
                <label for="perihal">Perihal</label>
                <input type="text" class="form-control" id="perihal" name="perihal" value="<?php echo $row['perihal']; ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label for="dokumen">Upload Dokumen Baru (PDF/DOC/DOCX - Maksimal 5MB)</label>
            <input type="file" class="form-control-file" id="dokumen" name="dokumen">
            <small class="form-text text-muted">Jika tidak ingin mengganti dokumen, biarkan kosong.</small>
        </div>
        
        <button type="submit" class="btn btn-primary">Perbarui</button>
        <a href="laporan_2024.php" class="btn btn-secondary">Kembali</a>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</body>
</html>
