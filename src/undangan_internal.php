<?php
include 'config.php';

// Menangani penghapusan data
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $conn->query("DELETE FROM undangan_internal WHERE id = $delete_id");
    header("Location: undangan_internal.php"); // Redirect setelah delete
    exit; // Pastikan untuk keluar setelah redirect
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
            // Menyimpan data ke database
            $sql = "INSERT INTO undangan_internal (nomor_memo, tanggal, perihal, tujuan, dokumen) 
                    VALUES ('$nomor_memo', '$tanggal', '$perihal', '$tujuan', '" . basename($target_file) . "')";
            if ($conn->query($sql) === TRUE) {
                echo "Undangan internal berhasil disimpan!";
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        } else {
            echo "Maaf, terjadi kesalahan saat mengunggah berkas.";
        }
    }
}

// Menangani pencarian
$search = '';
if (isset($_POST['search'])) {
    $search = $_POST['search'];
}

// Menangani urutan
$order = isset($_GET['order']) ? $_GET['order'] : 'ASC'; // Default urutan ASC
$order = $order === 'DESC' ? 'DESC' : 'ASC'; // Validasi urutan

// Pagination
$limit = 10; // Jumlah baris per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Halaman saat ini
$offset = ($page - 1) * $limit; // Offset untuk query SQL

// Mengambil data dari database untuk ditampilkan dengan pagination
$result = $conn->query("SELECT * FROM undangan_internal WHERE perihal LIKE '%$search%' ORDER BY tanggal $order LIMIT $limit OFFSET $offset");

// Menghitung jumlah total data
$total_result = $conn->query("SELECT COUNT(*) AS total FROM undangan_internal WHERE perihal LIKE '%$search%'");
$total_rows = $total_result->fetch_assoc()['total']; // Total baris
$total_pages = ceil($total_rows / $limit); // Total halaman
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Undangan Internal</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            margin-bottom: 20px;
        }
        .table th, .table td {
            vertical-align: middle; /* Center vertically */
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
    <h2>Undangan Internal</h2>
    <div class="card">
        <div class="card-body">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Nomor Memo</label>
                    <div class="input-group">
                        <input type="number" name="nomor" class="form-control" placeholder="Nomor" required>
                        <select name="klasifikasi" class="form-control" required>
                            <option value="">Klasifikasi</option>
                            <option value="AI.01">AI.01 AUDIT</option>
                            <option value="AI.02">AI.02 REVIU</option>
                            <option value="AK">AK PENGAWASAN</option>
                            <option value="KP.09.02">KP.09.02 PROGRAM SARJANA ATAU DIPLOMA IV</option>
                        </select>
                        <select name="kategori" class="form-control" required>
                            <option value="">Kategori</option>
                            <option value="ND">Nota Dinas (ND)</option>
                            <option value="UI">Undangan Internal (UI)</option>
                            <option value="SP">Surat Penugasan (SP)</option>
                            <option value="DLL">Dokumen Lain (DLL)</option>
                        </select>
                        <select name="kode_unit" class="form-control" required>
                            <option value="E.IV">E.IV</option>
                        </select>
                        <select name="tahun" class="form-control" required>
                            <option value="">Pilih Tahun</option>
                            <?php for ($year = 2020; $year <= 2030; $year++) { ?>
                                <option value="<?= $year ?>"><?= $year ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Perihal</label>
                    <input type="text" name="perihal" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Tujuan</label>
                    <select name="tujuan_select[]" class="form-control select2" multiple required>
                        <option value="WAKIL KEPALA">WAKIL KEPALA</option>
                        <option value="SEKRETARIAT UTAMA">SEKRETARIAT UTAMA</option>
                        <option value="DEPUTI BIDANG PEMBINAAN MANAJEMEN KEPEGAWAIAN">DEPUTI BIDANG PEMBINAAN MANAJEMEN KEPEGAWAIAN</option>
                        <option value="DEPUTI BIDANG MUTASI KEPEGAWAIAN">DEPUTI BIDANG MUTASI KEPEGAWAIAN</option>
                        <option value="DEPUTI BIDANG SISTEM INFORMASI KEPEGAWAIAN">DEPUTI BIDANG SISTEM INFORMASI KEPEGAWAIAN</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Dokumen</label>
                    <input type="file" name="dokumen" class="form-control-file" accept=".pdf,.doc,.docx" required>
                </div>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </form>
        </div>
    </div>

    <form method="POST" action="" class="mb-3">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Cari berdasarkan perihal" value="<?= htmlspecialchars($search) ?>">
            <div class="input-group-append">
                <button class="btn btn-outline-secondary" type="submit">Cari</button>
            </div>
        </div>
    </form>

    <table class="table table-bordered table-hover">
    <thead class="thead-light">
        <tr>
            <th scope="col">No</th>
            <th scope="col"><a href="?order=<?= $order === 'ASC' ? 'DESC' : 'ASC' ?>">Tanggal <?= $order === 'ASC' ? '↑' : '↓' ?></a></th>
            <th scope="col">Perihal</th>
            <th scope="col">Tujuan</th>
            <th scope="col">Dokumen</th>
            <th scope="col">Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $no = $offset + 1; // Menentukan nomor urut
        while ($row = $result->fetch_assoc()) {
        ?>
            <tr>
                <td><?= $no++; ?></td>
                <td><?= htmlspecialchars($row['tanggal']); ?></td>
                <td><?= htmlspecialchars($row['perihal']); ?></td>
                <td><?= htmlspecialchars($row['tujuan']); ?></td>
                <td>
                    <a href="uploads/<?= htmlspecialchars($row['dokumen']); ?>" class="btn btn-info btn-sm" target="_blank">Lihat</a>
                </td>
                <td class="text-center">
                    <div class="btn-group" role="group" aria-label="Basic example">
                        <a href="?delete_id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                            <i class="fas fa-trash-alt"></i> Hapus
                        </a>
                        <a href="edit_undangan_internal.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Ubah
                        </a>
                    </div>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>


    <!-- Pagination -->
    <nav>
        <ul class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                <li class="page-item <?= ($i == $page) ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?= $i; ?>&order=<?= $order; ?>"><?= $i; ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>

<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2();
    });
</script>
</body>
</html>
