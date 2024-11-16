<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: index.php");
    exit;
}

include 'config.php';

// Mendapatkan ID dokumen dari URL
if (isset($_GET['id'])) {
    $dokumen_id = $_GET['id'];

    // Mendapatkan data dokumen dari database
    $stmt = $conn->prepare("SELECT * FROM dokumen_lain WHERE id = ?");
    $stmt->bind_param("i", $dokumen_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $dokumen = $result->fetch_assoc();
    } else {
        $error_message = "Dokumen tidak ditemukan.";
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
                $stmt = $conn->prepare("UPDATE dokumen_lain SET nomor_memo = ?, tanggal = ?, perihal = ?, dokumen = ? WHERE id = ?");
                $stmt->bind_param("ssssi", $nomor, $tanggal, $perihal, $file_path, $dokumen_id);

                if ($stmt->execute()) {
                    $success_message = "Dokumen berhasil diperbarui.";
                } else {
                    $error_message = "Gagal menyimpan data: " . $stmt->error;
                }

                $stmt->close();
            } else {
                $error_message = "Gagal mengunggah file.";
            }
        }
    } else {
        // Jika dokumen tidak diupload, hanya update tanpa file
        $stmt = $conn->prepare("UPDATE dokumen_lain SET nomor_memo = ?, tanggal = ?, perihal = ? WHERE id = ?");
        $stmt->bind_param("sssi", $nomor, $tanggal, $perihal, $dokumen_id);

        if ($stmt->execute()) {
            $success_message = "Dokumen berhasil diperbarui.";
        } else {
            $error_message = "Gagal menyimpan data: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Dokumen Lain</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <style>
        body {
            background-image: url('ocean-blue-copy-space-abstract-paper-waves_23-2148319152.avif'); /* Ganti dengan path gambar Anda */
            background-size: cover; /* Mengatur gambar agar menutupi seluruh halaman */
            background-repeat: no-repeat;
            background-position: center;
        }
        body {
            background-color: #f0f4f8;
        }
        h2 {
            color: #343a40;
            margin-bottom: 30px;
            text-align: center;
        }
        .alert {
            margin-top: 20px;
        }
        .container {
            max-width: 600px;
            margin: auto;
            margin-top: 50px;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .btn-back {
            margin-top: 20px; /* Tempatkan tombol kembali di bawah form */
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Edit Dokumen Lain</h2>

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

    <!-- Form Edit Dokumen -->
    <form action="" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="nomor">Nomor Memo</label>
            <input type="text" class="form-control" id="nomor" name="nomor" value="<?php echo isset($dokumen['nomor_memo']) ? htmlspecialchars($dokumen['nomor_memo']) : ''; ?>" required>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="tanggal">Tanggal</label>
                <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?php echo isset($dokumen['tanggal']) ? htmlspecialchars($dokumen['tanggal']) : ''; ?>" required>
            </div>

            <div class="form-group col-md-6">
                <label for="perihal">Perihal</label>
                <input type="text" class="form-control" id="perihal" name="perihal" value="<?php echo isset($dokumen['perihal']) ? htmlspecialchars($dokumen['perihal']) : ''; ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label for="dokumen">Upload Dokumen Baru (PDF/DOC/DOCX - Maksimal 5MB)</label>
            <input type="file" class="form-control-file" id="dokumen" name="dokumen">
            <small class="form-text text-muted">Biarkan kosong jika tidak ingin mengubah dokumen.</small>
        </div>

        <button type="submit" class="btn btn-primary">Perbarui Dokumen</button>
    </form>

    <!-- Tombol Kembali -->
    <a href="dokumen_lain_2023.php" class="btn btn-secondary btn-back">Kembali</a>
</div>

<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>

</body>
</html>
