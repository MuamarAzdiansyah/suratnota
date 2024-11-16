<?php
include 'config.php';

// Menangani pengambilan data untuk form edit
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $result = $conn->query("SELECT * FROM undangan_internal WHERE id = $edit_id");
    $data = $result->fetch_assoc();

    if (!$data) {
        echo "Data tidak ditemukan.";
        exit;
    }
}

// Mengambil data kode unit dari database
$kode_unit_result = $conn->query("SELECT * FROM kode_unit_table"); // Ganti dengan nama tabel kode unit yang sesuai
$kode_unit_options = [];
if ($kode_unit_result->num_rows > 0) {
    while ($row = $kode_unit_result->fetch_assoc()) {
        $kode_unit_options[] = $row['kode_unit']; // Ganti dengan nama kolom yang sesuai
    }
}

// Menangani penyimpanan data setelah edit
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

    // Mengatur upload file jika ada
    if (isset($_FILES['dokumen']) && $_FILES['dokumen']['name'] != '') {
        $dokumen = $_FILES['dokumen']['name'];
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($dokumen);
        $uploadOk = 1;

        // Check if file already exists and rename if necessary
        $file_extension = pathinfo($dokumen, PATHINFO_EXTENSION);
        $base_name = pathinfo($dokumen, PATHINFO_FILENAME);
        $counter = 1;

        while (file_exists($target_file)) {
            $target_file = $target_dir . $base_name . "_{$counter}." . $file_extension;
            $counter++;
        }

        // Check file size (misalnya: maksimal 5MB)
        if ($_FILES['dokumen']['size'] > 5000000) {
            echo "Maaf, berkas Anda terlalu besar.";
            $uploadOk = 0;
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            echo "Maaf, berkas Anda tidak dapat diunggah.";
        } else {
            // Upload file
            if (move_uploaded_file($_FILES['dokumen']['tmp_name'], $target_file)) {
                // Update data ke database
                $sql = "UPDATE undangan_internal SET nomor_memo = '$nomor_memo', tanggal = '$tanggal', perihal = '$perihal', tujuan = '$tujuan', dokumen = '" . basename($target_file) . "' WHERE id = $edit_id";
                if ($conn->query($sql) === TRUE) {
                    echo "Undangan internal berhasil diperbarui!";
                    header("Location: undangan_internal.php"); // Redirect setelah update
                    exit;
                } else {
                    echo "Error: " . $sql . "<br>" . $conn->error;
                }
            } else {
                echo "Maaf, terjadi kesalahan saat mengunggah berkas.";
            }
        }
    } else {
        // Jika tidak ada file yang diunggah, hanya update data lainnya
        $sql = "UPDATE undangan_internal SET nomor_memo = '$nomor_memo', tanggal = '$tanggal', perihal = '$perihal', tujuan = '$tujuan' WHERE id = $edit_id";
        if ($conn->query($sql) === TRUE) {
            echo "Undangan internal berhasil diperbarui!";
            header("Location: undangan_internal.php"); // Redirect setelah update
            exit;
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Undangan Internal</title>
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
            <li class="nav-item"><a class="nav-link" href="dashboard.php">Halaman Utama</a></li>
            <li class="nav-item"><a class="nav-link" href="nota_dinas.php">Nota Dinas</a></li>
            <li class="nav-item"><a class="nav-link" href="undangan_internal.php">Undangan Internal</a></li>
            <li class="nav-item"><a class="nav-link" href="surat_penugasan.php">Surat Penugasan</a></li>
            <li class="nav-item"><a class="nav-link" href="dokumen_lain_lain.php">Dokumen Lain</a></li>
        </ul>
    </div>
</nav>

<div class="container mt-4">
    <h2>Edit Undangan Internal</h2>
    <div class="card">
        <div class="card-body">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Nomor Memo</label>
                    <div class="input-group">
                        <input type="number" name="nomor" class="form-control" placeholder="Nomor" value="<?= htmlspecialchars($data['nomor_memo']) ?>" required>
                        <select class="select2" id="klasifikasi" name="klasifikasi" required>
        <option value="<?= htmlspecialchars($undangan_internal['klasifikasi']) ?>"><?= htmlspecialchars($undangan_internal['klasifikasi']) ?></option>
        <option value="AI.01">AI.01 AUDIT</option>
        <option value="AI.02">AI.02 REVIU</option>
        <option value="AI.03">AI.03 EVALUASI</option>
        <option value="AI.04">AI.04 PENGADUAN MASYARAKAT</option>
        <option value="AI.05">AI.05 PENGAWASAN UNTUK TUJUAN TERTENTU</option>
        <option value="AI.06">AI.06 LAPORAN HASIL PENGAWASAN/PEMERIKSAAN</option>
        <option value="AI.07">AI.07 PEMANTAUAN</option>
    </select>
                        <select name="kategori" class="form-control" required>
                            <option value="<?= htmlspecialchars($data['kategori']) ?>"><?= htmlspecialchars($data['kategori']) ?></option>
                            <option value="ND">Nota Dinas (ND)</option>
                            <option value="UI">Undangan Internal (UI)</option>
                            <option value="SP">Surat Penugasan (SP)</option>
                            <option value="DLL">Dokumen Lain (DLL)</option>
                        </select>
                        <select name="kode_unit" class="form-control" required>
                            <option value="<?= htmlspecialchars($data['kode_unit']) ?>"><?= htmlspecialchars($data['kode_unit']) ?></option>
                            <?php foreach ($kode_unit_options as $kode): ?>
                                <option value="<?= htmlspecialchars($kode) ?>"><?= htmlspecialchars($kode) ?></option>
                            <?php endforeach; ?>
                            <option value="E.IV">E.IV</option> <!-- Menambahkan opsi E.IV -->
                        </select>
                        <select name="tahun" class="form-control" required>
                            <option value="<?= htmlspecialchars($data['tahun']) ?>"><?= htmlspecialchars($data['tahun']) ?></option>
                            <?php for ($year = 2020; $year <= 2030; $year++) { ?>
                                <option value="<?= $year ?>"><?= $year ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" value="<?= htmlspecialchars($data['tanggal']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Perihal</label>
                    <input type="text" name="perihal" class="form-control" value="<?= htmlspecialchars($data['perihal']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Tujuan</label>
                    <select name="tujuan_select[]" class="form-control select2" multiple required>
                        <?php
                        $tujuan_array = explode(", ", $data['tujuan']);
                        $options = [
                            "KEPALA",
                            "WAKIL KEPALA",
                            "SEKRETARIAT UTAMA",
                            "DEPUTI BIDANG PEMBINAAN MANAJEMEN KEPEGAWAIAN",
                            "DEPUTI BIDANG MUTASI KEPEGAWAIAN",
                            "DEPUTI BIDANG SISTEM INFORMASI KEPEGAWAIAN",
                            "DEPUTI BIDANG PENGAWASAN DAN PENGENDALIAN",
                            "BIRO UMUM",
                            "BIRO SDM",
                            "BIRO KEUANGAN",
                            "BIRO PERENCANAAN DAN ORGANISASI",
                            "BIRO HUMAS, HUKUM, DAN KERJA SAMA",
                            "DIREKTORAT PERATURAN DAN PERUNDANG-UNDANGAN",
                            "DIREKTORAT JABATAN ASN",
                            "DIREKTORAT KINERJA ASN",
                            "DIREKTORAT KOMPENSASI ASN",
                            "DIREKTORAT PENGADAAN DAN KEPANGKATAN",
                            "DIREKTORAT PENSIUN PNS DAN PEJABAT NEGARA",
                            "DIREKTORAT STATUS DAN KEDUDUKAN KEPEGAWAIAN",
                            "DIREKTORAT ARSIP KEPEGAWAIAN",
                            "DIREKTORAT PPSI ASN",
                            "DIREKTORAT PDPIK",
                            "DIREKTORAT INTI",
                            "DIREKTORAT PENGAWASAN DAN PENGENDALIAN I",
                            "DIREKTORAT PENGAWASAN DAN PENGENDALIAN II",
                            "DIREKTORAT PENGAWASAN DAN PENGENDALIAN III",
                            "DIREKTORAT PENGAWASAN DAN PENGENDALIAN IV",
                            "PUSAT PERENCANAAN KEBUTUHAN ASN",
                            "PUSAT PENGEMBANGAN KEPEGAWAIAN ASN",
                            "PUSAT PENGKAJIAN MANAJEMEN ASN",
                            "PUSAT PEMBINAAN JABATAN FUNGSIONAL KEPEGAWAIAN",
                            "PUSAT PENGEMBANGAN SISTEM SELEKSI ASN",
                            "PUSAT PENILAIAN KOMPETENSI ASN",
                            "PUSAT KONSULTASI DAN BANTUAN HUKUM",
                            "INSPEKTORAT",
                            "BADAN PERTIMBANGAN ASN",
                            "KANTOR REGIONAL I YOGYAKARTA",
                            "KANTOR REGIONAL II SURABAYA",
                            "KANTOR REGIONAL III BANDUNG",
                            "KANTOR REGIONAL IV MAKASSAR",
                            "KANTOR REGIONAL V JAKARTA",
                            "KANTOR REGIONAL VI MEDAN",
                            "KANTOR REGIONAL VII PALEMBANG",
                            "KANTOR REGIONAL VIII BANJARMASIN",
                            "KANTOR REGIONAL IX JAYAPURA",
                            "KANTOR REGIONAL X DENPASAR",
                            "KANTOR REGIONAL XI MANADO",
                            "KANTOR REGIONAL XII PEKANBARU",
                            "KANTOR REGIONAL XIII BANDA ACEH",
                            "KANTOR REGIONAL XIV MANOKWARI",
                            "LAINNYA....",
                        ];
                        foreach ($options as $option) {
                            $selected = in_array($option, $tujuan_array) ? "selected" : "";
                            echo "<option value=\"$option\" $selected>$option</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Dokumen (jika ada)</label>
                    <input type="file" name="dokumen" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="undangan_internal.php" class="btn btn-secondary">Batal</a>
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
