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
            background-image: url('ocean-blue-copy-space-abstract-paper-waves_23-2148319152.avif'); /* Ganti dengan path gambar Anda */
            background-size: cover; /* Mengatur gambar agar menutupi seluruh halaman */
            background-repeat: no-repeat;
            background-position: center;
        }
        body {
            background-color: #f8f9fa;
        }
        .card {
            margin-bottom: 20px;
        }
        #klasifikasi {
            max-width: 250px; /* Sesuaikan dengan kebutuhan Anda */
            width: 100%; /* Pastikan lebar dropdown responsif */
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
                        <select class="select2" id="klasifikasi" name="klasifikasi" required>
        <option value="<?= htmlspecialchars($nota_dinas['klasifikasi']) ?>"><?= htmlspecialchars($nota_dinas['klasifikasi']) ?></option>
        <option value="AI.01">AI.01 AUDIT</option>
        <option value="AI.02">AI.02 REVIU</option>
        <option value="AI.03">AI.03 EVALUASI</option>
        <option value="AI.04">AI.04 PENGADUAN MASYARAKAT</option>
        <option value="AI.05">AI.05 PENGAWASAN UNTUK TUJUAN TERTENTU</option>
        <option value="AI.06">AI.06 LAPORAN HASIL PENGAWASAN/PEMERIKSAAN</option>
        <option value="AI.07">AI.07 PEMANTAUAN</option>
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
    <option value="KEPALA">KEPALA</option>
<option value="WAKIL KEPALA">WAKIL KEPALA</option>
<option value="SEKRETARIAT UTAMA">SEKRETARIAT UTAMA</option>
<option value="DEPUTI BIDANG PEMBINAAN MANAJEMEN KEPEGAWAIAN">DEPUTI BIDANG PEMBINAAN MANAJEMEN KEPEGAWAIAN</option>
<option value="DEPUTI BIDANG MUTASI KEPEGAWAIAN">DEPUTI BIDANG MUTASI KEPEGAWAIAN</option>
<option value="DEPUTI BIDANG SISTEM INFORMASI KEPEGAWAIAN">DEPUTI BIDANG SISTEM INFORMASI KEPEGAWAIAN</option>
<option value="DEPUTI BIDANG PENGAWASAN DAN PENGENDALIAN">DEPUTI BIDANG PENGAWASAN DAN PENGENDALIAN</option>
<option value="BIRO UMUM">BIRO UMUM</option>
<option value="BIRO SDM">BIRO SDM</option>
<option value="BIRO KEUANGAN">BIRO KEUANGAN</option>
<option value="BIRO PERENCANAAN DAN ORGANISASI">BIRO PERENCANAAN DAN ORGANISASI</option>
<option value="BIRO HUMAS, HUKUM, DAN KERJA SAMA">BIRO HUMAS, HUKUM, DAN KERJA SAMA</option>
<option value="DIREKTORAT PERATURAN DAN PERUNDANG-UNDANGAN">DIREKTORAT PERATURAN DAN PERUNDANG-UNDANGAN</option>
<option value="DIREKTORAT JABATAN ASN">DIREKTORAT JABATAN ASN</option>
<option value="DIREKTORAT KINERJA ASN">DIREKTORAT KINERJA ASN</option>
<option value="DIREKTORAT KOMPENSASI ASN">DIREKTORAT KOMPENSASI ASN</option>
<option value="DIREKTORAT PENGADAAN DAN KEPANGKATAN">DIREKTORAT PENGADAAN DAN KEPANGKATAN</option>
<option value="DIREKTORAT PENSIUN PNS DAN PEJABAT NEGARA">DIREKTORAT PENSIUN PNS DAN PEJABAT NEGARA</option>
<option value="DIREKTORAT STATUS DAN KEDUDUKAN KEPEGAWAIAN">DIREKTORAT STATUS DAN KEDUDUKAN KEPEGAWAIAN</option>
<option value="DIREKTORAT ARSIP KEPEGAWAIAN">DIREKTORAT ARSIP KEPEGAWAIAN</option>
<option value="DIREKTORAT PPSI ASN">DIREKTORAT PPSI ASN</option>
<option value="DIREKTORAT PDPIK">DIREKTORAT PDPIK</option>
<option value="DIREKTORAT INTI">DIREKTORAT INTI</option>
<option value="DIREKTORAT PENGAWASAN DAN PENGENDALIAN I">DIREKTORAT PENGAWASAN DAN PENGENDALIAN I</option>
<option value="DIREKTORAT PENGAWASAN DAN PENGENDALIAN II">DIREKTORAT PENGAWASAN DAN PENGENDALIAN II</option>
<option value="DIREKTORAT PENGAWASAN DAN PENGENDALIAN III">DIREKTORAT PENGAWASAN DAN PENGENDALIAN III</option>
<option value="DIREKTORAT PENGAWASAN DAN PENGENDALIAN IV">DIREKTORAT PENGAWASAN DAN PENGENDALIAN IV</option>
<option value="PUSAT PERENCANAAN KEBUTUHAN ASN">PUSAT PERENCANAAN KEBUTUHAN ASN</option>
<option value="PUSAT PENGEMBANGAN KEPEGAWAIAN ASN">PUSAT PENGEMBANGAN KEPEGAWAIAN ASN</option>
<option value="PUSAT PENGKAJIAN MANAJEMEN ASN">PUSAT PENGKAJIAN MANAJEMEN ASN</option>
<option value="PUSAT PEMBINAAN JABATAN FUNGSIONAL KEPEGAWAIAN">PUSAT PEMBINAAN JABATAN FUNGSIONAL KEPEGAWAIAN</option>
<option value="PUSAT PENGEMBANGAN SISTEM SELEKSI ASN">PUSAT PENGEMBANGAN SISTEM SELEKSI ASN</option>
<option value="PUSAT PENILAIAN KOMPETENSI ASN">PUSAT PENILAIAN KOMPETENSI ASN</option>
<option value="PUSAT KONSULTASI DAN BANTUAN HUKUM">PUSAT KONSULTASI DAN BANTUAN HUKUM</option>
<option value="INSPEKTORAT">INSPEKTORAT</option>
<option value="BADAN PERTIMBANGAN ASN">BADAN PERTIMBANGAN ASN</option>
<option value="KANTOR REGIONAL I YOGYAKARTA">KANTOR REGIONAL I YOGYAKARTA</option>
<option value="KANTOR REGIONAL II SURABAYA">KANTOR REGIONAL II SURABAYA</option>
<option value="KANTOR REGIONAL III BANDUNG">KANTOR REGIONAL III BANDUNG</option>
<option value="KANTOR REGIONAL IV MAKASSAR">KANTOR REGIONAL IV MAKASSAR</option>
<option value="KANTOR REGIONAL V JAKARTA">KANTOR REGIONAL V JAKARTA</option>
<option value="KANTOR REGIONAL VI MEDAN">KANTOR REGIONAL VI MEDAN</option>
<option value="KANTOR REGIONAL VII PALEMBANG">KANTOR REGIONAL VII PALEMBANG</option>
<option value="KANTOR REGIONAL VIII BANJARMASIN">KANTOR REGIONAL VIII BANJARMASIN</option>
<option value="KANTOR REGIONAL IX JAYAPURA">KANTOR REGIONAL IX JAYAPURA</option>
<option value="KANTOR REGIONAL X DENPASAR">KANTOR REGIONAL X DENPASAR</option>
<option value="KANTOR REGIONAL XI MANADO">KANTOR REGIONAL XI MANADO</option>
<option value="KANTOR REGIONAL XII PEKANBARU">KANTOR REGIONAL XII PEKANBARU</option>
<option value="KANTOR REGIONAL XIII BANDA ACEH">KANTOR REGIONAL XIII BANDA ACEH</option>
<option value="KANTOR REGIONAL XIV MANOKWARI">KANTOR REGIONAL XIV MANOKWARI</option>

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
