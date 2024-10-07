<?php
include 'config.php';

// Menangani penghapusan data
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $conn->query("DELETE FROM surat_penugasan WHERE id = $delete_id");
    header("Location: surat_penugasan.php");
}

// Menangani penyimpanan data untuk edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $nomor = $_POST['nomor'];
    $klasifikasi = $_POST['klasifikasi'];
    $kategori = $_POST['kategori'];
    $kode_unit = $_POST['kode_unit'];
    $tahun = $_POST['tahun'];
    $tanggal = $_POST['tanggal'];
    $perihal = $_POST['perihal'];
    
    $tujuan = !empty($_POST['tujuan_select']) ? implode(", ", $_POST['tujuan_select']) : '';
    $nomor_memo = "$nomor/$klasifikasi/$kategori/$kode_unit/$tahun";
    
    // Proses upload dokumen
    if (isset($_FILES['dokumen']) && $_FILES['dokumen']['error'] == 0) {
        $file_name = $_FILES['dokumen']['name'];
        $file_tmp = $_FILES['dokumen']['tmp_name'];
        $file_size = $_FILES['dokumen']['size'];
        $file_error = $_FILES['dokumen']['error'];

        $allowed_extensions = ['pdf', 'doc', 'docx'];
        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
        if ($file_size <= 5 * 1024 * 1024 && in_array($file_ext, $allowed_extensions)) {
            $upload_dir = 'uploads/';
            $file_path = $upload_dir . basename($file_name);

            if (move_uploaded_file($file_tmp, $file_path)) {
                $sql = "UPDATE surat_penugasan SET nomor_memo='$nomor_memo', tanggal='$tanggal', perihal='$perihal', tujuan='$tujuan', dokumen='$file_name' WHERE id=$id";
                if ($conn->query($sql) === TRUE) {
                    echo "<div class='alert alert-success'>Surat penugasan berhasil diperbarui!</div>";
                } else {
                    echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
                }
            } else {
                echo "<div class='alert alert-danger'>Maaf, terjadi kesalahan saat mengupload file.</div>";
            }
        } else {
            echo "<div class='alert alert-danger'>File terlalu besar atau tipe file tidak diizinkan.</div>";
        }
    } else {
        // Jika tidak ada file yang di-upload, update tanpa mengganti dokumen
        $sql = "UPDATE surat_penugasan SET nomor_memo='$nomor_memo', tanggal='$tanggal', perihal='$perihal', tujuan='$tujuan' WHERE id=$id";
        if ($conn->query($sql) === TRUE) {
            echo "<div class='alert alert-success'>Surat penugasan berhasil diperbarui!</div>";
        } else {
            echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
        }
    }
}

// Mendapatkan data surat penugasan untuk diedit
$id = $_GET['id'];
$result = $conn->query("SELECT * FROM surat_penugasan WHERE id = $id");
$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Surat Penugasan</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f8f9fa;
        }
        h2 {
            color: #343a40;
        }
        .container {
            margin-top: 20px;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
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

<div class="container">
    <h2>Edit Surat Penugasan</h2>
    <form method="POST" action="" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
        <div class="form-group">
            <label>Nomor Memo</label>
            <div class="input-group mb-3">
                <input type="number" name="nomor" class="form-control" value="<?php echo htmlspecialchars($row['nomor_memo']); ?>" required>
                <select name="klasifikasi" class="form-control" required>
                    <option value="">Klasifikasi</option>
                    <option value="AI.01" <?= ($row['klasifikasi'] == 'AI.01') ? 'selected' : ''; ?>>AI.01 AUDIT</option>
                    <option value="AI.02" <?= ($row['klasifikasi'] == 'AI.02') ? 'selected' : ''; ?>>AI.02 REVIU</option>
                    <option value="AK" <?= ($row['klasifikasi'] == 'AK') ? 'selected' : ''; ?>>AK PENGAWASAN</option>
                    <option value="KP.09.02" <?= ($row['klasifikasi'] == 'KP.09.02') ? 'selected' : ''; ?>>KP.09.02 PROGRAM SARJANA ATAU DIPLOMA IV</option>
                </select>
                <select name="kategori" class="form-control" required>
                    <option value="">Kategori</option>
                    <option value="ND" <?= ($row['kategori'] == 'ND') ? 'selected' : ''; ?>>Nota Dinas (ND)</option>
                    <option value="UI" <?= ($row['kategori'] == 'UI') ? 'selected' : ''; ?>>Undangan Internal (UI)</option>
                    <option value="SP" <?= ($row['kategori'] == 'SP') ? 'selected' : ''; ?>>Surat Penugasan (SP)</option>
                    <option value="DLL" <?= ($row['kategori'] == 'DLL') ? 'selected' : ''; ?>>Dokumen Lain (DLL)</option>
                </select>
                <select name="kode_unit" class="form-control" required>
                    <option value="E.IV" <?= ($row['kode_unit'] == 'E.IV') ? 'selected' : ''; ?>>E.IV</option>
                    <!-- Tambahkan kode unit lainnya di sini -->
                </select>
                <select name="tahun" class="form-control" required>
                    <option value="2024" <?= ($row['tahun'] == '2024') ? 'selected' : ''; ?>>2024</option>
                    <option value="2023" <?= ($row['tahun'] == '2023') ? 'selected' : ''; ?>>2023</option>
                </select>
            </div>
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
    </select>
</div>

            </select>
        </div>
        <div class="form-group">
            <label>Tanggal</label>
            <input type="date" name="tanggal" class="form-control" value="<?php echo htmlspecialchars($row['tanggal']); ?>" required>
        </div>
        <div class="form-group">
            <label>Perihal</label>
            <textarea name="perihal" class="form-control" required><?php echo htmlspecialchars($row['perihal']); ?></textarea>
        </div>
        <div class="form-group">
            <label>Dokumen (opsional, maksimal 5MB)</label>
            <input type="file" name="dokumen" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        <a href="surat_penugasan.php" class="btn btn-secondary">Kembali</a>
    </form>
</div>

<script>
    $(document).ready(function() {
        $('.select2').select2();
    });
</script>
</body>
</html>
