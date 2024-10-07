<?php
include 'config.php';

// Menangani penghapusan data
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $conn->query("DELETE FROM surat_penugasan WHERE id = $delete_id");
    header("Location: surat_penugasan.php");
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

    $tujuan = !empty($_POST['tujuan_select']) ? implode(", ", $_POST['tujuan_select']) : '';
    $nomor_memo = "$nomor/$klasifikasi/$kategori/$kode_unit/$tahun";

    // Proses upload dokumen
    if (isset($_FILES['dokumen'])) {
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
                $sql = "INSERT INTO surat_penugasan (nomor_memo, tanggal, perihal, tujuan, dokumen) 
                        VALUES ('$nomor_memo', '$tanggal', '$perihal', '$tujuan', '$file_name')";
                if ($conn->query($sql) === TRUE) {
                    echo "<div class='alert alert-success'>Surat penugasan berhasil disimpan!</div>";
                } else {
                    echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
                }
            } else {
                echo "<div class='alert alert-danger'>Maaf, terjadi kesalahan saat mengupload file.</div>";
            }
        } else {
            echo "<div class='alert alert-danger'>File terlalu besar atau tipe file tidak diizinkan.</div>";
        }
    }
}

// Fitur pencarian, pagination, dan sortasi
$search_query = '';
if (isset($_GET['search'])) {
    $search_query = $_GET['search'];
}

$sort_order = 'desc'; // Default sortasi
if (isset($_GET['sort']) && in_array($_GET['sort'], ['asc', 'desc'])) {
    $sort_order = $_GET['sort'];
}

$limit = 10; // Batas data per halaman
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$sql_total = "SELECT COUNT(*) as total FROM surat_penugasan WHERE perihal LIKE '%$search_query%'";
$result_total = $conn->query($sql_total);
$row_total = $result_total->fetch_assoc();
$total_records = $row_total['total'];
$total_pages = ceil($total_records / $limit);

$sql = "SELECT * FROM surat_penugasan WHERE perihal LIKE '%$search_query%' ORDER BY tanggal $sort_order LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Surat Penugasan</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f8f9fa;
        }
        h2, h3 {
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
    <h2>Surat Penugasan</h2>
    <form method="POST" action="" enctype="multipart/form-data">
        <div class="form-group">
            <label>Nomor Memo</label>
            <div class="input-group mb-3">
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
                    <!-- Tambahkan kode unit lainnya di sini -->
                </select>
                <select name="tahun" class="form-control" required>
                    <option value="2020">2020</option>
                    <option value="2021">2021</option>
                    <option value="2022">2022</option>
                    <option value="2023">2023</option>
                    <option value="2024">2024</option>
                    <option value="2025">2025</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label>Tujuan</label>
            <select name="tujuan_select[]" class="form-control select2" multiple="multiple" required>
                <option value="KEPALA">KEPALA</option>
                <option value="SEKRETARIAT UTAMA">SEKRETARIAT UTAMA</option>
                <option value="DEPUTI BIDANG MUTASI KEPEGAWAIAN">DEPUTI BIDANG MUTASI KEPEGAWAIAN</option>
            </select>
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
            <label>Upload Dokumen (PDF/DOC/DOCX, Max: 5MB)</label>
            <input type="file" name="dokumen" class="form-control-file" required>
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>

    <h3 class="mt-5">Daftar Surat Penugasan</h3>
    <form method="GET" action="">
        <div class="input-group mb-3">
            <input type="text" name="search" class="form-control" placeholder="Cari perihal" value="<?= htmlspecialchars($search_query); ?>">
            <select name="sort" class="form-control">
                <option value="">Urutkan Berdasarkan</option>
                <option value="asc" <?= (isset($_GET['sort']) && $_GET['sort'] == 'asc') ? 'selected' : ''; ?>>Tanggal Terlama (ASC)</option>
                <option value="desc" <?= (isset($_GET['sort']) && $_GET['sort'] == 'desc') ? 'selected' : ''; ?>>Tanggal Terbaru (DESC)</option>
            </select>
            <div class="input-group-append">
                <button class="btn btn-outline-secondary" type="submit">Cari</button>
            </div>
        </div>
    </form>

    <table class="table table-bordered table-striped table-hover">
    <thead>
        <tr>
            <th>No</th>
            <th>Nomor Memo</th>
            <th>Tanggal</th>
            <th>Perihal</th>
            <th>Tujuan</th>
            <th>Dokumen</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($result->num_rows > 0) {
            $no = $offset + 1;
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>{$no}</td>";
                echo "<td>{$row['nomor_memo']}</td>";
                echo "<td>{$row['tanggal']}</td>";
                echo "<td>{$row['perihal']}</td>";
                echo "<td>{$row['tujuan']}</td>";
                echo "<td><a href='uploads/{$row['dokumen']}' target='_blank'>{$row['dokumen']}</a></td>";
                echo "<td>
                        <a href='edit_surat_penugasan.php?id={$row['id']}' class='btn btn-warning btn-sm'>Edit</a>
                        <a href='surat_penugasan.php?delete_id={$row['id']}' class='btn btn-danger btn-sm' onclick=\"return confirm('Yakin ingin menghapus?');\">Hapus</a>
                      </td>";
                echo "</tr>";
                $no++;
            }
        } else {
            echo "<tr><td colspan='7' class='text-center'>Tidak ada data ditemukan</td></tr>";
        }
        ?>
    </tbody>
</table>


    <nav>
        <ul class="pagination">
            <?php
            for ($i = 1; $i <= $total_pages; $i++) {
                $active = ($i == $page) ? 'active' : '';
                echo "<li class='page-item $active'><a class='page-link' href='?page=$i&search=$search_query&sort=$sort_order'>$i</a></li>";
            }
            ?>
        </ul>
    </nav>
</div>

<script>
    $(document).ready(function() {
        $('.select2').select2();
    });
</script>
</body>
</html>
