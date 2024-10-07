<?php
include 'config.php';

// Menangani penghapusan data
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $conn->query("DELETE FROM nota_dinas WHERE id = $delete_id");
    header("Location: nota_dinas.php"); // Redirect setelah delete
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
            $sql = "INSERT INTO nota_dinas (nomor_memo, tanggal, perihal, tujuan, dokumen) 
                    VALUES ('$nomor_memo', '$tanggal', '$perihal', '$tujuan', '" . basename($target_file) . "')";
            if ($conn->query($sql) === TRUE) {
                echo "Nota Dinas berhasil disimpan!";
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

// Menangani filter urutan
$order_by = 'tanggal'; // Default order
$order_type = 'ASC'; // Default order type
if (isset($_GET['order'])) {
    $order_type = $_GET['order'] == 'newest' ? 'DESC' : 'ASC';
}

// Pagination
$limit = 10; // Jumlah data per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Mengambil data dari database untuk ditampilkan dengan filter dan urutan
$result = $conn->query("SELECT * FROM nota_dinas WHERE perihal LIKE '%$search%' ORDER BY $order_by $order_type LIMIT $start, $limit");
$total_result = $conn->query("SELECT COUNT(*) as total FROM nota_dinas WHERE perihal LIKE '%$search%'");
$total_row = $total_result->fetch_assoc();
$total_pages = ceil($total_row['total'] / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Nota Dinas</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border: 1px solid #ddd;
        }
        td {
            word-wrap: break-word; /* Allow text to wrap */
            max-width: 200px; /* Set a max width for the cell */
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
    <h2>Nota Dinas</h2>
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
                    </select>
                </div>
                <div class="form-group">
                    <label>Dokumen</label>
                    <input type="file" name="dokumen" class="form-control" accept=".pdf,.doc,.docx" required>
                </div>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </form>
        </div>
    </div>

    <form method="POST" action="">
        <div class="input-group mb-3">
            <input type="text" name="search" class="form-control" placeholder="Pencarian berdasarkan perihal" value="<?= htmlspecialchars($search) ?>">
            <div class="input-group-append">
                <button class="btn btn-outline-secondary" type="submit">Cari</button>
            </div>
        </div>
    </form>

    <div class="mb-2">
        <a href="?order=oldest" class="btn btn-secondary">Terlama</a>
        <a href="?order=newest" class="btn btn-secondary">Terbaru</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Nomor Memo</th>
                <th>Tanggal</th>
                <th>Perihal</th>
                <th>Tujuan</th>
                <th>Dokumen</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?= $row['nomor_memo'] ?></td>
                    <td><?= $row['tanggal'] ?></td>
                    <td><?= $row['perihal'] ?></td>
                    <td><?= $row['tujuan'] ?></td>
                    <td><a href="uploads/<?= $row['dokumen'] ?>" target="_blank">Lihat Dokumen</a></td>
                    <td>
                        <a href="?delete_id=<?= $row['id'] ?>" class="btn btn-danger btn-sm">Hapus</a>
                        <a href="edit_nota_dinas.php?id=<?= $row['id'] ?>" class="btn btn-warning">Ubah</a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <nav aria-label="Page navigation">
        <ul class="pagination">
            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $page - 1 ?>" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
            <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php } ?>
            <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $page + 1 ?>" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>
    </nav>
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
