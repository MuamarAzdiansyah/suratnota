<?php
ob_start(); // Memulai output buffering
include 'config.php';

// Menangani pengambilan data untuk edit
$id = intval($_GET['id']); // Menghindari serangan XSS
$result = $conn->query("SELECT * FROM nota_dinas WHERE id = $id");

if (!$result) {
    die("Query Error: " . $conn->error);
}

$nota_dinas = $result->fetch_assoc();
if (!$nota_dinas) {
    die("Nota Dinas tidak ditemukan.");
}

// Menangani penyimpanan data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nomor = $_POST['nomor'];
    $klasifikasi = $_POST['klasifikasi'];
    $kategori = $_POST['kategori'];
    $kode_unit = $_POST['kode_unit'];
    $tahun = $_POST['tahun'];
    $tanggal = $_POST['tanggal'];
    $perihal = $_POST['perihal'];

    // Mengambil nilai dari dropdown untuk tujuan
    $tujuan = isset($_POST['tujuan_select']) ? implode(", ", $_POST['tujuan_select']) : '';

    // Menggabungkan nomor memo
    $nomor_memo = "$nomor/$klasifikasi/$kategori/$kode_unit/$tahun";

    // Mengatur upload file
    $target_dir = "uploads/";
    $uploadOk = 1;
    $new_file_name = $nota_dinas['dokumen']; // Simpan nama dokumen lama

    if (!empty($_FILES['dokumen']['name'])) {
        $dokumen = $_FILES['dokumen']['name'];
        $target_file = $target_dir . basename($dokumen);

        // Check file size (misalnya: maksimal 5MB)
        if ($_FILES['dokumen']['size'] > 5000000) {
            echo "Maaf, berkas Anda terlalu besar.";
            $uploadOk = 0;
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk) {
            // Upload file
            if (move_uploaded_file($_FILES['dokumen']['tmp_name'], $target_file)) {
                $new_file_name = basename($target_file); // Simpan nama file baru
            } else {
                echo "Maaf, terjadi kesalahan saat mengunggah berkas.";
                $uploadOk = 0;
            }
        }
    }

    if ($uploadOk) {
        // Update data ke database
        $stmt = $conn->prepare("UPDATE nota_dinas SET nomor_memo = ?, tanggal = ?, perihal = ?, tujuan = ?, dokumen = ? WHERE id = ?");
        $stmt->bind_param("sssssi", $nomor_memo, $tanggal, $perihal, $tujuan, $new_file_name, $id);

        if ($stmt->execute()) {
            echo "Nota Dinas berhasil diperbarui!";
            header("Location: nota_dinas.php"); // Redirect setelah update
            exit; // Pastikan untuk keluar setelah redirect
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}
ob_end_flush(); // Mengakhiri output buffering
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Nota Dinas</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="#">Manajemen Persuratan</a>
    <div class="collapse navbar-collapse">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item"><a class="nav-link" href="dashboard.php">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="nota_dinas.php">Nota Dinas</a></li>
            <li class="nav-item"><a class="nav-link" href="undangan_internal.php">Undangan Internal</a></li>
            <li class="nav-item"><a class="nav-link" href="surat_penugasan.php">Surat Penugasan</a></li>
            <li class="nav-item"><a class="nav-link" href="dokumen_lain_lain.php">Dokumen Lain</a></li>
        </ul>
    </div>
</nav>

<div class="container mt-4">
    <h2>Edit Nota Dinas</h2>
    <div class="card">
        <div class="card-body">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Nomor Memo</label>
                    <div class="input-group">
                        <input type="number" name="nomor" class="form-control" placeholder="Nomor" value="<?= htmlspecialchars($nota_dinas['nomor']) ?>" required>
                        <select name="klasifikasi" class="form-control" required>
                            <option value="<?= htmlspecialchars($nota_dinas['klasifikasi']) ?>"><?= htmlspecialchars($nota_dinas['klasifikasi']) ?></option>
                            <option value="AI.01">AI.01 AUDIT</option>
                            <option value="AI.02">AI.02 REVIU</option>
                            <option value="AK">AK PENGAWASAN</option>
                            <option value="KP.09.02">KP.09.02 PROGRAM SARJANA ATAU DIPLOMA IV</option>
                        </select>
                        <select name="kategori" class="form-control" required>
                            <option value="<?= htmlspecialchars($nota_dinas['kategori']) ?>"><?= htmlspecialchars($nota_dinas['kategori']) ?></option>
                            <option value="ND">Nota Dinas (ND)</option>
                            <option value="UI">Undangan Internal (UI)</option>
                            <option value="SP">Surat Penugasan (SP)</option>
                            <option value="DLL">Dokumen Lain (DLL)</option>
                        </select>
                        <select name="kode_unit" class="form-control" required>
                            <option value="<?= htmlspecialchars($nota_dinas['kode_unit']) ?>"><?= htmlspecialchars($nota_dinas['kode_unit']) ?></option>
                            <option value="E.IV">E.IV</option>
                        </select>
                        <select name="tahun" class="form-control" required>
                            <option value="<?= htmlspecialchars($nota_dinas['tahun']) ?>"><?= htmlspecialchars($nota_dinas['tahun']) ?></option>
                            <?php for ($year = 2020; $year <= 2030; $year++) { ?>
                                <option value="<?= $year ?>"><?= $year ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" value="<?= htmlspecialchars($nota_dinas['tanggal']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Perihal</label>
                    <input type="text" name="perihal" class="form-control" value="<?= htmlspecialchars($nota_dinas['perihal']) ?>" required>
                </div>
                <div class="form-group">
    <label>Tujuan</label>
    <select name="tujuan_select[]" class="form-control select2" multiple required>
        <option value="KEPALA" <?= (strpos($nota_dinas['tujuan'], 'KEPALA') !== false) ? 'selected' : '' ?>>KEPALA</option>
        <option value="WAKIL KEPALA" <?= (strpos($nota_dinas['tujuan'], 'WAKIL KEPALA') !== false) ? 'selected' : '' ?>>WAKIL KEPALA</option>
        <option value="SEKRETARIAT UTAMA" <?= (strpos($nota_dinas['tujuan'], 'SEKRETARIAT UTAMA') !== false) ? 'selected' : '' ?>>SEKRETARIAT UTAMA</option>
        <option value="DEPUTI BIDANG PEMBINAAN MANAJEMEN KEPEGAWAIAN" <?= (strpos($nota_dinas['tujuan'], 'DEPUTI BIDANG PEMBINAAN MANAJEMEN KEPEGAWAIAN') !== false) ? 'selected' : '' ?>>DEPUTI BIDANG PEMBINAAN MANAJEMEN KEPEGAWAIAN</option>
        <option value="DEPUTI BIDANG MUTASI KEPEGAWAIAN" <?= (strpos($nota_dinas['tujuan'], 'DEPUTI BIDANG MUTASI KEPEGAWAIAN') !== false) ? 'selected' : '' ?>>DEPUTI BIDANG MUTASI KEPEGAWAIAN</option>
        <option value="DEPUTI BIDANG SISTEM INFORMASI KEPEGAWAIAN" <?= (strpos($nota_dinas['tujuan'], 'DEPUTI BIDANG SISTEM INFORMASI KEPEGAWAIAN') !== false) ? 'selected' : '' ?>>DEPUTI BIDANG SISTEM INFORMASI KEPEGAWAIAN</option>
        <option value="DEPUTI BIDANG PENGAWASAN DAN PENGENDALIAN" <?= (strpos($nota_dinas['tujuan'], 'DEPUTI BIDANG PENGAWASAN DAN PENGENDALIAN') !== false) ? 'selected' : '' ?>>DEPUTI BIDANG PENGAWASAN DAN PENGENDALIAN</option>
        <option value="BIRO UMUM" <?= (strpos($nota_dinas['tujuan'], 'BIRO UMUM') !== false) ? 'selected' : '' ?>>BIRO UMUM</option>
        <option value="BIRO SDM" <?= (strpos($nota_dinas['tujuan'], 'BIRO SDM') !== false) ? 'selected' : '' ?>>BIRO SDM</option>
        <option value="BIRO KEUANGAN" <?= (strpos($nota_dinas['tujuan'], 'BIRO KEUANGAN') !== false) ? 'selected' : '' ?>>BIRO KEUANGAN</option>
        <option value="BIRO PERENCANAAN DAN ORGANISASI" <?= (strpos($nota_dinas['tujuan'], 'BIRO PERENCANAAN DAN ORGANISASI') !== false) ? 'selected' : '' ?>>BIRO PERENCANAAN DAN ORGANISASI</option>
        <option value="BIRO HUMAS, HUKUM, DAN KERJA SAMA" <?= (strpos($nota_dinas['tujuan'], 'BIRO HUMAS, HUKUM, DAN KERJA SAMA') !== false) ? 'selected' : '' ?>>BIRO HUMAS, HUKUM, DAN KERJA SAMA</option>
        <option value="DIREKTORAT PERATURAN DAN PERUNDANG-UNDANGAN" <?= (strpos($nota_dinas['tujuan'], 'DIREKTORAT PERATURAN DAN PERUNDANG-UNDANGAN') !== false) ? 'selected' : '' ?>>DIREKTORAT PERATURAN DAN PERUNDANG-UNDANGAN</option>
        <option value="DIREKTORAT JABATAN ASN" <?= (strpos($nota_dinas['tujuan'], 'DIREKTORAT JABATAN ASN') !== false) ? 'selected' : '' ?>>DIREKTORAT JABATAN ASN</option>
        <option value="DIREKTORAT KINERJA ASN" <?= (strpos($nota_dinas['tujuan'], 'DIREKTORAT KINERJA ASN') !== false) ? 'selected' : '' ?>>DIREKTORAT KINERJA ASN</option>
        <option value="DIREKTORAT KOMPENSASI ASN" <?= (strpos($nota_dinas['tujuan'], 'DIREKTORAT KOMPENSASI ASN') !== false) ? 'selected' : '' ?>>DIREKTORAT KOMPENSASI ASN</option>
        <option value="DIREKTORAT PENGADAAN DAN KEPANGKATAN" <?= (strpos($nota_dinas['tujuan'], 'DIREKTORAT PENGADAAN DAN KEPANGKATAN') !== false) ? 'selected' : '' ?>>DIREKTORAT PENGADAAN DAN KEPANGKATAN</option>
        <option value="DIREKTORAT PENSIUN PNS DAN PEJABAT NEGARA" <?= (strpos($nota_dinas['tujuan'], 'DIREKTORAT PENSIUN PNS DAN PEJABAT NEGARA') !== false) ? 'selected' : '' ?>>DIREKTORAT PENSIUN PNS DAN PEJABAT NEGARA</option>
        <option value="DIREKTORAT STATUS DAN KEDUDUKAN KEPEGAWAIAN" <?= (strpos($nota_dinas['tujuan'], 'DIREKTORAT STATUS DAN KEDUDUKAN KEPEGAWAIAN') !== false) ? 'selected' : '' ?>>DIREKTORAT STATUS DAN KEDUDUKAN KEPEGAWAIAN</option>
        <option value="DIREKTORAT ARSIP KEPEGAWAIAN" <?= (strpos($nota_dinas['tujuan'], 'DIREKTORAT ARSIP KEPEGAWAIAN') !== false) ? 'selected' : '' ?>>DIREKTORAT ARSIP KEPEGAWAIAN</option>
        <option value="DIREKTORAT PPSI ASN" <?= (strpos($nota_dinas['tujuan'], 'DIREKTORAT PPSI ASN') !== false) ? 'selected' : '' ?>>DIREKTORAT PPSI ASN</option>
        <option value="DIREKTORAT PDPIK" <?= (strpos($nota_dinas['tujuan'], 'DIREKTORAT PDPIK') !== false) ? 'selected' : '' ?>>DIREKTORAT PDPIK</option>
        <option value="DIREKTORAT INTI" <?= (strpos($nota_dinas['tujuan'], 'DIREKTORAT INTI') !== false) ? 'selected' : '' ?>>DIREKTORAT INTI</option>
        <option value="DIREKTORAT PENGAWASAN DAN PENGENDALIAN I" <?= (strpos($nota_dinas['tujuan'], 'DIREKTORAT PENGAWASAN DAN PENGENDALIAN I') !== false) ? 'selected' : '' ?>>DIREKTORAT PENGAWASAN DAN PENGENDALIAN I</option>
        <option value="DIREKTORAT PENGAWASAN DAN PENGENDALIAN II" <?= (strpos($nota_dinas['tujuan'], 'DIREKTORAT PENGAWASAN DAN PENGENDALIAN II') !== false) ? 'selected' : '' ?>>DIREKTORAT PENGAWASAN DAN PENGENDALIAN II</option>
        <option value="DIREKTORAT PENGAWASAN DAN PENGENDALIAN III" <?= (strpos($nota_dinas['tujuan'], 'DIREKTORAT PENGAWASAN DAN PENGENDALIAN III') !== false) ? 'selected' : '' ?>>DIREKTORAT PENGAWASAN DAN PENGENDALIAN III</option>
        <option value="DIREKTORAT PENGAWASAN DAN PENGENDALIAN IV" <?= (strpos($nota_dinas['tujuan'], 'DIREKTORAT PENGAWASAN DAN PENGENDALIAN IV') !== false) ? 'selected' : '' ?>>DIREKTORAT PENGAWASAN DAN PENGENDALIAN IV</option>
        <option value="PUSAT PERENCANAAN KEBUTUHAN ASN" <?= (strpos($nota_dinas['tujuan'], 'PUSAT PERENCANAAN KEBUTUHAN ASN') !== false) ? 'selected' : '' ?>>PUSAT PERENCANAAN KEBUTUHAN ASN</option>
        <option value="PUSAT PENGEMBANGAN KEPEGAWAIAN ASN" <?= (strpos($nota_dinas['tujuan'], 'PUSAT PENGEMBANGAN KEPEGAWAIAN ASN') !== false) ? 'selected' : '' ?>>PUSAT PENGEMBANGAN KEPEGAWAIAN ASN</option>
        <option value="PUSAT PENGEMBANGAN SDM" <?= (strpos($nota_dinas['tujuan'], 'PUSAT PENGEMBANGAN SDM') !== false) ? 'selected' : '' ?>>PUSAT PENGEMBANGAN SDM</option>
        <option value="SELURUH PEGAWAI"<?= (strpos($nota_dinas['tujuan'], 'SELURUH PEGAWAI') !== false) ? 'selected' : '' ?>>SELURUH PEGAWAI</option>
    </select>
</div>

                <div class="form-group">
                    <label>Dokumen (file PDF maks. 5MB)</label>
                    <input type="file" name="dokumen" class="form-control" accept="application/pdf">
                </div>
                <button type="submit" class="btn btn-primary">Update</button>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2();
    });
</script>
</body>
</html>
