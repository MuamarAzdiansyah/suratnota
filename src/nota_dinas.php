<?php
include 'config.php';

session_start(); // Tambahkan ini di bagian atas file untuk memulai sesi

// Menangani penghapusan data
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $conn->query ("DELETE FROM nota_dinas WHERE id = $delete_id");
    
    // Simpan pesan dalam sesi
    $_SESSION['message'] = "Table berhasil dihapus!";
    
    header("Location: nota_dinas.php");
    exit;
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
    $tujuan = isset($_POST['tujuan_select']) ? implode(", ", $_POST['tujuan_select']) : '';
    $nomor_memo = "$nomor/$klasifikasi/$kategori/$kode_unit/$tahun";

    $dokumen = $_FILES['dokumen']['name'];
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($dokumen);
    $uploadOk = 1;

    $message = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); // Hapus pesan setelah ditampilkan
}

    // Validasi ukuran file
    if ($_FILES['dokumen']['size'] > 5000000) {
        echo "Maaf, berkas Anda terlalu besar.";
        $uploadOk = 0;
    }

    // Upload file dan simpan data ke database jika berhasil
    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES['dokumen']['tmp_name'], $target_file)) {
            $stmt = $conn->prepare("INSERT INTO nota_dinas (nomor_memo, tanggal, perihal, tujuan, dokumen) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $nomor_memo, $tanggal, $perihal, $tujuan, basename($target_file));
            if ($stmt->execute()) {
                echo "Nota Dinas berhasil disimpan!";
            } else {
                echo "Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo " ";
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
    $order_type = $_GET['order'] == 'desc' ? 'DESC' : 'ASC';
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <style>
        body {
            background-image: url('ocean-blue-copy-space-abstract-paper-waves_23-2148319152.avif'); /* Ganti dengan path gambar Anda */
            background-size: cover; /* Mengatur gambar agar menutupi seluruh halaman */
            background-repeat: no-repeat;
            background-position: center;
        }
    body {
        background-color: #f8f9fa;
        padding-top: 56px; /* Sesuaikan dengan tinggi navbar */
    }
    h2, h3 {
        color: #343a40;
    }
    .navbar {
        position: fixed;
        top: 0; /* Pastikan navbar di atas */
        width: 100%;
        z-index: 1030; /* Pastikan navbar berada di atas elemen lainnya */
        background-color: #ffffff; /* Warna latar belakang navbar */
    }
    .navbar a {
        color: #ffffff; /* Warna teks navbar */
    }
    .navbar a:hover {
        color: #ffe600; /* Warna teks saat hover */
    }
    
    .container {
        margin-top: 20px;
        padding: 20px;
        background-color: #ffffff;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
    #klasifikasi {
            max-width: 250px; /* Sesuaikan dengan kebutuhan Anda */
            width: 100%; /* Pastikan lebar dropdown responsif */
        }
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light">
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
        <select class="select2" id="klasifikasi" name="klasifikasi">
        <option value="AI.01">AI.01 AUDIT</option>
<option value="AI.02">AI.02 REVIU</option>
<option value="AI.03">AI.03 EVALUASI</option>
<option value="AI.04">AI.04 PENGADUAN MASYARAKAT</option>
<option value="AI.05">AI.05 PENGAWASAN UNTUK TUJUAN TERTENTU</option>
<option value="AI.06">AI.06 LAPORAN HASIL PENGAWASAN/PEMERIKSAAN</option>
<option value="AI.07">AI.07 PEMANTAUAN</option>
<option value="AI.07.01">AI.07.01 PEMANTAUAN PELAKSANAAN KEGIATAN/PROGRAM</option>
<option value="AI.07.02">AI.07.02 TUNTUTAN GANTI RUGI (TGR)</option>
<option value="AI.07.03">AI.07.03 PEMANTAUAN TINDAK LANJUT LAPORAN HASIL PENGAWASAN/PEMERIKSAAN</option>
<option value="AI.07.04">AI.07.04 PENERAPAN EARLY WARNING SYSTEM</option>
<option value="AI.07.05">AI.07.05 PEMANTAUAN DISIPLIN PEGAWAI</option>
<option value="AI.08">AI.08 KEGIATAN PENGAWASAN LAINNYA</option>
<option value="AK.01">AK.01 PENGADUAN MASALAH KEPEGAWAIAN</option>
<option value="AK.02">AK.02 PELAKSANAAN KEBIJAKAN TEKNIS</option>
<option value="AK.02.01">AK.02.01 KOORDINASI DAN MONITORING</option>
<option value="AK.02.02">AK.02.02 LAPORAN, REKOMENDASI, DAN EVALUASI MONITORING</option>
<option value="AK.03">AK.03 STANDAR PELAYANAN</option>
<option value="AK.04">AK.04 KEGIATAN PENGAWASAN LAINNYA</option>
<option value="AR.01">AR.01 PENETAPAN KEBIJAKAN</option>
<option value="AR.02">AR.02 PEMBINAAN</option>
<option value="AR.03">AR.03 PENGELOLAAN ARSIP DINAMIS</option>
<option value="AR.03.01">AR.03.01 PENCIPTAAN ARSIP</option>
<option value="AR.03.02">AR.03.02 PEMELIHARAAN ARSIP</option>
<option value="AR.03.03">AR.03.03 PENGGUNAAN ARSIP</option>
<option value="AR.05">AR.05 MONEV SISTEM KEARSIPAN</option>
<option value="AU.01">AU.01 PROGRAM LEGALASI</option>
<option value="AU.01.01">AU.01.01 PENGKAJIAN DAN PENGUSULAN BAHAN</option>
<option value="AU.01.02">AU.01.02 PENYIAPAN BAHAN</option>
<option value="AU.01.03">AU.01.03 PERUMUSAN KEBIJAKAN</option>
<option value="AU.01.04">AU.01.04 PEMBERIAN MASUKAN DAN DUKUNGAN DALAM PENYUSUNAN KEBIJAKAN</option>
<option value="AU.01.05">AU.01.05 PENETAPAN KEBIJAKAN</option>
<option value="AU.02">AU.02 PRODUK HUKUM</option>
<option value="AU.02.01">AU.02.01 PRODUK HUKUM YANG BERSIFAT PENGATURAN</option>
<option value="AU.02.02">AU.02.02 PRODUK HUKUM YANG BERSIFAT PENETAPAN</option>
<option value="AU.02.03">AU.02.03 PRODUK HUKUM YANG BERSIFAT</option>
<option value="AU.03">AU.03 DOKUMENTASI PRODUK HUKUM</option>
<option value="PR">PR. PERENCANAAN</option>
<option value="PR.01">PR.01 POKOK KEBIJAKAN DAN STRATEGI PEMBANGUNAN</option>
<option value="PR.01.01">PR.01.01 RENCANA PEMBANGUNAN JANGKA PANJANG (RPJP)</option>
<option value="PR.01.02">PR.01.02 RENCANA PEMBANGUNAN JANGKA MENENGAH (RPJM)</option>
<option value="PR.01.03">PR.01.03 RENCANA STRATEGI</option>
<option value="PR.02">PR.02 RENCANA KERJA</option>
<option value="PR.02.01">PR.02.01 USULAN PERENCANAAN KEGIATAN</option>
<option value="PR.02.02">PR.02.02 RENCANA KERJA TAHUNAN</option>
<option value="PR.02.03">PR.02.03 RENCANA KERJA BERDASARKAN PAGU INDIKATIF</option>
<option value="PR.02.04">PR.02.04 INISIATIF BARU</option>
<option value="PR.03">PR.03 PENETAPAN KINERJA</option>
<option value="PR.04">PR.04 PERENCANAAN ANGGARAN</option>
<option value="PR.04.01">PR.04.01 PENYUSUNAN RENCANA ANGGARAN</option>
<option value="PR.04.02">PR.04.02 PENERIMAAN NEGARA BUKAN PAJAK (PNBP)</option>
<option value="PR.04.03">PR.04.03 STANDARISASI HARGA SATUAN PERENCANAAN BARANG (SHSPB)</option>
<option value="PR.04.04">PR.04.04 STANDAR BIAYA KELUARAN (SBK)</option>
<option value="PR.05">PR.05 REVISI DOKUMEN ANGGARAN</option>
<option value="PR.05.01">PR.05.01 REVISI DIPA DAN POK</option>
<option value="PR.05.02">PR.05.02 ANGGARAN BELANJA TAMBAHAN (ABT)</option>
<option value="PR.05.03">PR.05.03 ANGGARAN PENDAPATAN DAN BELANJA NEGARA-PERUBAHAN (APBN-P)</option>
<option value="PR.06">PR.06 EVALUASI</option>
<option value="PR.07">PR.07 PENYUSULAN LAPORAN</option>
<option value="PR.07.01">PR.07.01 LAPORAN BERKALA</option>
<option value="PR.07.02">PR.07.02 LAPORAN KHUSUS</option>
<option value="PR.07.03">PR.07.03 LAPORAN PERKEMBANGAN (PROGRESS REPORT)</option>
<option value="PR.07.04">PR.07.04 LAPORAN AKUNTABILITAS KINERJA INSTANSI PEMERINTAH (LAKIP)</option>
<option value="KU.01">KU.01 PELAKSANAAN ANGGARAN</option>
<option value="KU.01.01">KU.01.01 RAB</option>
<option value="KU.01.02">KU.01.02 PENGGAJIAN</option>
<option value="KU.01.03">KU.01.03 PENGELUARAN ANGGARAN</option>
<option value="KU.02">KU.02 PENGELOLAAN PERBENDAHARAAN</option>
<option value="KU.02.01">KU.02.01 PELAKSANAAN ANGGARAN</option>
<option value="KU.02.02">KU.02.02 PENGENDALIAN ANGGARAN</option>
<option value="KU.02.03">KU.02.03 PAJAK</option>
<option value="KU.02.04">KU.02.04 PENERIMAAN NEGARA BUKAN PAJAK (PNBP)</option>
<option value="KU.02.05">KU.02.05 PENGEMBALIAN BELANJA</option>
<option value="KU.02.06">KU.02.06 BERITA ACARA PEMERIKSAAN KAS</option>
<option value="KU.02.07">KU.02.07 TUNTUTAN GANTI RUGI</option>
<option value="KU.02.08">KU.02.08 PINJAMAN/BANTUAN LUAR NEGERI</option>
<option value="KU.02.09">KU.02.09 PEMBUKUAN ANGGARAN</option>
<option value="KU.03">KU.03 VERIFIKASI ANGGARAN</option>
<option value="KU.04">KU.04 AKUNTANSI DAN PELAPORAN</option>
<option value="KU.05">KU.05 PENGELOLAAN BELANJA PEGAWAI</option>
<option value="KU.05.01">KU.05.01 KETERANGAN PENGHASILAN</option>
<option value="KU.05.02">KU.05.02 IURAN KEANGGOTAAN ORGANISASI</option>
<option value="KP">KP. KEPEGAWAIAN</option>
<option value="KP.01">KP.01 BEZETTING/PERSEDIAAN PEGAWAI</option>
<option value="KP.02">KP.02 KEBUTUHAN PEGAWAI</option>
<option value="KP.02.01">KP.02.01 USULAN UNIT KERJA</option>
<option value="KP.02.02">KP.02.02 USULAN KEBUTUHAN PEGAWAI</option>
<option value="KP.02.03">KP.02.03 PENETAPAN KEBUTUHAN PEGAWAI</option>
<option value="KP.02.04">KP.02.04 PENETAPAN KEBUTUHAN PEGAWAI UNTUK FORMASI KHUSUS/TERTENTU</option>
<option value="KP.03">KP.03 PENGADAAN PEGAWAI ASN</option>
<option value="KP.03.01">KP.03.01 PENGADAAN PNS</option>
<option value="KP.03.02">KP.03.02 MASA PERCOBAAN CPNS</option>
<option value="KP.03.03">KP.03.03 PENGANGKATAN PNS</option>
<option value="KP.03.04">KP.03.04 PENGADAAN PPPK</option>
<option value="KP.03.05">KP.03.05 PENGANGKATAN PPPK</option>
<option value="KP.04">KP.04 UJIAN KENAIKAN PANGKAT/JABATAN</option>
<option value="KP.04.01">KP.04.01 UJIAN PENYESUAIAN IJAZAH</option>
<option value="KP.04.02">KP.04.02 UJIAN PENINGKATAN PENDIDIKAN</option>
<option value="KP.04.03">KP.04.03 UJIAN DINAS</option>
<option value="KP.05">KP.05 UJIAN KOMPETENSI</option>
<option value="KP.05.01">KP.05.01 TES PENILAIAN KOMPETENSI PEGAWAI</option>
<option value="KP.05.02">KP.05.02 PEMETAAN POTENSI PEGAWAI</option>
<option value="KP.06">KP.06 MUTASI</option>
<option value="KP.06.01">KP.06.01 KENAIKAN PANGKAT/GOLONGAN RUANG</option>
<option value="KP.06.02">KP.06.02 KENAIKAN GAJI BERKALA</option>
<option value="KP.06.03">KP.06.03 PENYESUAIAN MASA KERJA</option>
<option value="KP.06.04">KP.06.04 PENYESUAIAN TUNJANGAN KELUARGA</option>
<option value="KP.06.05">KP.06.05 PENYESUAIAN KELAS JABATAN</option>
<option value="KP.06.06">KP.06.06 MUTASI TUGAS/LOKASI</option>
<option value="KP.07">KP.07 PENGANGKATAN DAN PEMBERHENTIAN JABATAN</option>
<option value="KP.07.01">KP.07.01 PENGANGKATAN JABATAN</option>
<option value="KP.07.02">KP.07.02 PEMBERHENTIAN JABATAN</option>
<option value="KP.08">KP.08 PEMBERIAN KUASA/MANDAT</option>
<option value="KP.08.01">KP.08.01 PELAKSANA TUGAS (PLT)</option>
<option value="KP.08.02">KP.08.02 PELAKSANA HARIAN (PLH)</option>
<option value="KP.09">KP.09 PENDIDIKAN PEGAWAI</option>
<option value="KP.09.01">KP.09.01 PROGRAM DIPLOMA</option>
<option value="KP.09.02">KP.09.02 PROGRAM SARJANA ATAU DIPLOMA IV</option>
<option value="KP.09.03">KP.09.03 PROGRAM PASCA SARJANA</option>
<option value="KP.10">KP.10 PELATIHAN PEGAWAI</option>
<option value="KP.10.01">KP.10.01 PELATIHAN PENJENJANGAN</option>
<option value="KP.10.02">KP.10.02 PELATIHAN FUNGSIONAL</option>
<option value="KP.10.03">KP.10.03 PELATIHAN TEKNIS</option>
<option value="KP.11">KP.11 PROFIL PEGAWAI</option>
<option value="KP.11.01">KP.11.01 DATA PERSONAL PNS</option>
<option value="KP.11.02">KP.11.02 KEHADIRAN PEGAWAI</option>
<option value="KP.11.03">KP.11.03 KARTU PEGAWAI</option>
<option value="KP.11.04">KP.11.04 KARIS/KARSU</option>
<option value="KP.11.05">KP.11.05 KARTU TASPEN</option>
<option value="KP.11.06">KP.11.06 KARTU JAMINAN KESEHATAN</option>
<option value="KP.11.07">KP.11.07 TANDA JASA</option>
<option value="KP.11.08">KP.11.08 KETERANGAN PENERIMAAN PEMBAYARAN PENGHASILAN PEGAWAI (KP4)</option>
<option value="KP.11.09">KP.11.09 LAPORAN HARTA KEKAYAAN PENYELANGGARA NEGARA (LHKPN)</option>
<option value="KP.11.10">KP.11.10 TUNJANGAN KINERJA DAN UANG MAKAN</option>
<option value="KP.11.11">KP.11.11 DATA PERSONAL PPPK</option>
<option value="KP.11.12">KP.11.12 KEHADIRAN PPPK</option>
<option value="KP.12">KP.12 CUTI PEGAWAI</option>
<option value="KP.12.01">KP.12.01 CUTI TAHUNAN</option>
<option value="KP.12.02">KP.12.02 CUTI BESAR</option>
<option value="KP.12.03">KP.12.03 CUTI SAKIT</option>
<option value="KP.12.04">KP.12.04 CUTI BERSALIN</option>
<option value="KP.12.05">KP.12.05 CUTI ALASAN PENTING</option>
<option value="KP.12.06">KP.12.06 CUTI BERSAMA</option>
<option value="KP.12.07">KP.12.07 CUTI DI LUAR TANGGUNAN NEGARA (CLTN)</option>
<option value="KP.13">KP.13 PEMBINAAN PEGAWAI</option>
<option value="KP.13.01">KP.13.01 SASARAN KERJA PEGAWAI (SKP)</option>
<option value="KP.13.02">KP.13.02 PENILAIAN PRESTASI KERJA</option>
<option value="KP.13.03">KP.13.03 PEMBINAAN MENTAL</option>
<option value="KP.13.04">KP.13.04 KODE ETIK</option>
<option value="KP.13.05">KP.13.05 HUKUMAN DISIPLIN</option>
<option value="KP.14">KP.14 PEMBINAAN JABATAN FUNGSIONAL</option>
<option value="KP.14.01">KP.14.01 PENGANGKATAN JF TERTENTU</option>
<option value="KP.14.02">KP.14.02 KENAIKAN JENJANG JABATAN</option>
<option value="KP.14.03">KP.14.03 PEMINDAHAN JF TERTENTU</option>
<option value="KP.14.04">KP.14.04 PENGANGKATAN JF UMUM</option>
<option value="KP.14.05">KP.14.05 PEMINDAHAN JF UMUM</option>
<option value="KP.14.06">KP.14.06 PEMBERHENTIAN</option>
<option value="KP.15">KP.15 KESEJAHTERAAN</option>
<option value="KP.15.01">KP.15.01 KESEHATAN</option>
<option value="KP.15.02">KP.15.02 REKREASI/KESENIAN/OLAHRAGA</option>
<option value="KP.15.03">KP.15.03 BANTUAN SOSIAL</option>
<option value="KP.15.04">KP.15.04 PERUMAHAN</option>
<option value="KP.16">KP.16 PEMBERHENTIAN PEGAWAI</option>
<option value="KP.16.01">KP.16.01 DENGAN HORMAT</option>
<option value="KP.16.02">KP.16.02 TIDAK DENGAN HORMAT</option>
<option value="KP.17">KP.17 PEMBERHENTIAN DAN PENETAPAN PENSIUN PEGAWAI/JANDA/DUDA/PNS YANG TEWAS</option>
<option value="KP.18">KP.18 PERSELISIHAN/SENGKETA PEGAWAI</option>
<option value="OT.01">OT.01 ORGANISASI</option>
<option value="OT.01.01">OT.01.01 ANALISIS DAN EVALUASI ORGANISASI</option>
<option value="OT.01.02">OT.01.02 ORGANISASI PERUBAHAN</option>
<option value="OT.02">OT.02 ANALISIS JABATAN</option>
<option value="OT.02.01">OT.02.01 ANALISIS JABATAN STRUKTURAL</option>
<option value="OT.02.02">OT.02.02 ANALISIS JF TERTENTU</option>
<option value="OT.02.03">OT.02.03 ANALISIS JF UMUM</option>
<option value="OT.03">OT.03 STANDAR KOMPETENSI</option>
<option value="OT.03.01">OT.03.01 STANDAR KOMPETENSI JABATAN STRUKTURAL</option>
<option value="OT.03.02">OT.03.02 STANDAR KOMPETENSI JF</option>
<option value="OT.04">OT.04 ANALISIS BEBAN KERJA</option>
<option value="OT.05">OT.05 TATA LAKSANA</option>
<option value="OT.05.01">OT.05.01 PENYUSUNAN DAN PENYELARASAN PROSES BISNIS</option>
<option value="OT.05.02">OT.05.02 EVALUASI PROSES BISNIS</option>
<option value="OT.05.03">OT.05.03 PENYUSUNAN DAN PENYELARASAN SOP</option>
<option value="OT.05.04">OT.05.04 MONEV SOP</option>
<option value="OT.06">OT.06 REFORMASI BIROKRASI</option>
<option value="OT.07">OT.07 EVALUASI JABATAN</option>
<option value="OT.08">OT.08 ORGANISASI NON KEDINASAN</option>
<option value="OT.08.01">OT.08.01 KORPRI</option>
<option value="OT.08.02">OT.08.02 DHARMA WANITA</option>
<option value="OT.08.03">OT.08.03 LAIN-LAIN</option>
<option value="PL.01">PL.01 PERLENGKAPAN</option>
<option value="PL.01.01">PL.01.01 PERENCANAAN PENGADAAN BARANG DAN JASA</option>
<option value="PL.01.02">PL.01.02 ANALISIS KEBUTUHAN</option>
<option value="PL.01.03">PL.01.03 TATA RUANG</option>
<option value="PL.01.04">PL.01.04 DAFTAR REKANAN MEMENUHI KUALIFIKASI</option>
<option value="PL.02">PL.02 PELAKSANAAN PENGADAAN BARANG DAN JASA</option>
<option value="PL.02.01">PL.02.01 ALAT TULIS KANTOR</option>
<option value="PL.02.02">PL.02.02 INVENTARIS KANTOR</option>
<option value="PL.02.03">PL.02.03 TANAH DAN BANGUNAN</option>
<option value="PL.02.04">PL.02.04 KENDARAAN DINAS</option>
<option value="PL.02.05">PL.02.05 INSTALASI/JARINGAN</option>
<option value="PL.02.06">PL.02.06 PENGADAAN JASA/KONSULTAN</option>
<option value="PL.02.07">PL.02.07 APLIKASI/SOFTWARE</option>
<option value="PL.03">PL.03 PENGELOLAAN BARANG</option>
<option value="PL.03.01">PL.03.01 PENYIMPANAN/PERGUDANGAN BMN</option>
<option value="PL.03.02">PL.03.02 DISTRIBUSI/PENYALURAN BMN</option>
<option value="PL.03.03">PL.03.03 INVENTARISASI BMN</option>
<option value="PL.03.04">PL.03.04 PEMANFAATAN BMN</option>
<option value="PL.03.05">PL.03.05 PENGHAPUSAN BMN</option>
<option value="PL.03.06">PL.03.06 PELAPORAN BMN</option>
<option value="RT.01">RT.01 PEMELIHARAAN</option>
<option value="RT.01.01">RT.01.01 PEMELIHARAAN/PERAWATAN BMN</option>
<option value="RT.02">RT.02 FASILITAS</option>
<option value="RT.02.01">RT.02.01 KENDARAAN DINAS</option>
<option value="RT.02.02">RT.02.02 RUANG RAPAT</option>
<option value="RT.02.03">RT.02.03 TELEKOMUNIKASI</option>
<option value="RT.02.04">RT.02.04 LISTRIK</option>
<option value="RT.02.05">RT.02.05 AIR</option>
<option value="RT.03">RT.03 PENGAMANAN</option>
<option value="RT.04">RT.04 PENGELOLAAN PPNPN</option>
<option value="RT.04.01">RT.04.01 PETUGAS KEAMANAN</option>
<option value="RT.04.02">RT.04.02 PENGEMUDI</option>
<option value="RT.04.03">RT.04.03 PETUGAS KEBERSIHAN</option>
<option value="RT.04.04">RT.04.04 PRAMUBAKTI</option>
<option value="HM.01">HM.01 PUBLIKASI DAN DOKUMENTASI</option>
<option value="HM.02">HM.02 RAPAT DENGAR PENDAPAT</option>
<option value="HM.03">HM.03 HUBUNGAN MEDIA</option>
<option value="HM.04">HM.04 KERJA SAMA</option>
<option value="HM.04.01">HM.04.01 KERJA SAMA DALAM NEGERI</option>
<option value="HM.04.02">HM.04.02 KERJA SAMA LUAR NEGERI</option>
<option value="HM.05">HM.05 HUBUNGAN ANTAR LEMBAGA</option>
<option value="HM.05.01">HM.05.01 LEMBAGA NEGARA</option>
<option value="HM.05.02">HM.05.02 ORGANISASI NASIONAL DAN INTERNASIONAL</option>
<option value="HM.05.03">HM.05.03 PERUSAHAAN</option>
<option value="HM.05.04">HM.05.04 PERGURUAN TINGGI/SEKOLAH</option>
<option value="HM.05.05">HM.05.05 BAKOHUMAS</option>
<option value="HM.06">HM.06 KEPROTOKOLAN</option>
<option value="HM.06.01">HM.06.01 UPACARA/ACARA KEDINASAN</option>
<option value="HM.06.02">HM.06.02 KUNJUNGAN</option>
<option value="HM.06.03">HM.06.03 AGENDA PIMPINAN</option>
<option value="HM.07">HM.07 TANDA KENANG-KENANGAN</option>
<option value="HM.08">HM.08 UCAPAN</option>
<option value="HM.09">HM.09 PENGELOLAAN WEBSITE</option>
<option value="HM.10">HM.10 LAYANAN DIORAMA</option>
<option value="BM.01">BM.01 KINERJA ASN</option>
<option value="BM.01.01">BM.01.01 SISTEM PENILAIAN KINERJA DAN STANDAR KERJA</option>
<option value="BM.01.02">BM.01.02 PENGELOLAAN BASOS DATA DAN ANALISIS SISTEM INFORMASI KINERJA PEGAWAI ASN</option>
<option value="BM.01.03">BM.01.03 EVALUASI DAN PEMANTAUAN PENILAIAN KINERJA DAN STANDAR KINERJA JABATAN PEGAWAI ASN</option>
<option value="BM.02">BM.02 JABATAN ASN</option>
<option value="BM.02.01">BM.02.01 BIDANG KESEJAHTERAAN RAKYAT/PEMBERDAYAAN MANUSIA</option>
<option value="BM.02.02">BM.02.02 BIDANG PEREKONOMIAN</option>
<option value="BM.02.03">BM.02.03 BIDANG POLHUKAM</option>
<option value="BM.02.04">BM.02.04 BIDANG KEMARITIMAN</option>
<option value="BM.03">BM.03 KOMPETENSI ASN</option>
<option value="BM.03.01">BM.03.01 GAJI DAN FASILITAS</option>
<option value="BM.03.02">BM.03.02 JAMINAN PENSIUN, PERLINDANGAN, DAN PENGHARGAAN</option>
<option value="BM.03.03">BM.03.03 TUNJANGAN</option>
<option value="MP.01">MP.01 PENGADAAN DAN KEPANGKATAN</option>
<option value="MP.01.01">MP.01.01 ADMINISTRASI PENGADAAN DAN KEPANGKATAN ASN</option>
<option value="MP.01.02">MP.01.02 PENGADAAN ASN</option>
<option value="MP.01.03">MP.01.03 KP DAN JABATAN SELAIN PIMPINAN TINGGI UTAMA</option>
<option value="MP.01.04">MP.01.04 KP DAN JABATAN PIMPINAN UTAMA, PIMPINAN TINGGI MADYA, JF UTAMA</option>
<option value="MP.02">MP.02 PENSIUN PNS DAN PEJABAT NEGARA</option>
<option value="MP.02.01">MP.02.01 ADMINISTRASI PENSIUN PNS DAN PEJABAT NEGARA</option>
<option value="MP.02.02">MP.02.02 PENETAPAN PENSIUN PNS</option>
<option value="MP.02.03">MP.02.03 PENETAPAN PERTEK PENSIUN PNS</option>
<option value="MP.02.04">MP.02.04 PENSIUN PEJABAT NEGARA DAN JANDA/DUDANYA</option>
<option value="MP.03">MP.03 STATUS DAN KEDUDUKAN KEPEGAWAIAN</option>
<option value="MP.03.01">MP.03.01 PERTIMBANGAN STATUS KEPEGAWAIAN</option>
<option value="MP.03.02">MP.03.02 PERTIMBANGAN KEDUDUKAN KEPEGAWAIAN</option>
<option value="SI.01">SI.01 PENGOLAHAN DATA DAN INFORMASI KEPEGAWAIAN</option>
<option value="SI.01.01">SI.01.01 PENGOLAHAN DATA</option>
<option value="SI.01.02">SI.01.02 INFORMASI KEPEGAWAIAN</option>
<option value="SI.02">SI.02 PENGEMBANGAN SISTEM INFORMASI KEPEGAWAIAN</option>
<option value="SI.02.01">SI.02.01 PENGEMBANGAN TEKNOLOGI INFORMASI</option>
<option value="SI.02.02">SI.02.02 PEMANFAATAN TEKNOLOGI INFORMASI</option>
<option value="SI.02.03">SI.02.03 PELAYANAN TEKNOLOGI INFORMASI</option>
<option value="SI.03">SI.03 PENGEMBANGAN SISTEM PENGELOLAAN ARSIP KEPEGAWAIAN</option>
<option value="SI.03.01">SI.03.01 ARSIP KEPEGAWAIAN ELEKTRONIK</option>
<option value="SI.03.02">SI.03.02 PENYIMPANAN ARSIP KEPEGAWAIAN SECARA FISIK</option>
<option value="BP.01">BP.01 PERENCANAAN KEBUTUHAN ASN</option>
<option value="BP.01.01">BP.01.01 ANALISIS KEBUTUHAN</option>
<option value="BP.01.02">BP.01.02 PENGOLAHAN DATA KEBUTUHAN PNS DAN PPPK</option>
<option value="BP.02">BP.02 PERENCANA PERTIMBANGAN KEBUTUHAN ASN</option>
<option value="BP.02.02">BP.02.02 PERTEK PENETAPAN KEBUTUHAN PNS, PPPK INSTANSI DAERAH</option>
<option value="BP.03">BP.03 PENYUSUNAN STANDARISASI JABATAN</option>
<option value="BP.03.01">BP.03.01 INFORMASI JABATAN</option>
<option value="BP.03.02">BP.03.02 KOMPETENSI JABATAN</option>
<option value="BP.03.03">BP.03.03 KLASIFIKASI JABATAN</option>
<option value="BJ.01">BJ.01 PENGELOLAAN JF</option>
<option value="BJ.01.01">BJ.01.01 STANDARISASI, AKREDITASI, DAN SERTIFIKASI</option>
<option value="BJ.01.02">BJ.01.02 PENILAIAN ANGKA KREDIT</option>
<option value="BJ.01.03">BJ.01.03 PERTIMBANGAN PENGANGKATAN</option>
<option value="BJ.02">BJ.02 PENINGKATAN KOMPETENSI</option>
<option value="BJ.02.01">BJ.02.01 JABATAN ANALIS KEPEGAWAIAN</option>
<option value="BJ.02.02">BJ.02.02 JABATAN AUDITOR KEPEGAWAIAN</option>
<option value="BJ.02.03">BJ.02.03 JABATAN ASSESOR SDM APARATUR</option>
<option value="BJ.03">BJ.03 PENGOLAHAN DATA DAN INFORMASI</option>
<option value="BJ.03.01">BJ.03.01 PENGOLAHAN DATA</option>
<option value="BJ.03.02">BJ.03.02 INFORMASI DAN LAPORAN</option>
<option value="KS.01">KS.01 PENYUSUNAN KEBIJAKAN TEKNIS SISTEM REKRUTMEN</option>
<option value="KS.02">KS.02 PENGELOLAAN SISTEM REKRUTMEN</option>
<option value="KS.02.01">KS.02.01 KEBIJAKAN TEKNIS</option>
<option value="KS.02.02">KS.02.02 MATERI KOMPETENSI</option>
<option value="KS.03">KS.03 PENGELOLAAN TEKNOLOGI INFORMASI SELEKSI</option>
<option value="KS.03.01">KS.03.01 PENGELOLAAN DAN PENGEMBANGAN APLIKASI</option>
<option value="KS.03.02">KS.03.02 PENGELOLAAN IP ADDRESS</option>
<option value="KS.03.03">KS.03.03 PEMELIHARAAN</option>
<option value="KS.04">KS.04 FASILITASI PENYELENGGARAAN SELEKSI</option>
<option value="KS.04.01">KS.04.01 PELAYANAN ADMINISTRASI</option>
<option value="KS.04.02">KS.04.02 PENYELENGGARAAN DAN PENGOLAHAN</option>
<option value="KS.04.03">KS.04.03 SERTIFIKASI DAN PELAPORAN</option>
<option value="NK.01">NK.01 KEBIJAKAN TEKNIS PENILAIAN DAN POTENSI ASN</option>
<option value="NK.02">NK.02 PERENCANAAN DAN PENYELENGGARAAN PENILAIAN KOMPETENSI</option>
<option value="NK.02.01">NK.02.01 PERENCANAAN PROGRAM KEGIATAN PENILAIAN KOMPETENSI</option>
<option value="NK.02.02">NK.02.02 PENYELENGGARAAN PENILAIAN KOMPETENSI</option>
<option value="NK.03">NK.03 PENGEMBANGAN STANDAR DAN PENILAIAN KOMPETENSI</option>
<option value="NK.03.01">NK.03.01 PENGEMBANGAN METODE PENILAIAN</option>
<option value="NK.03.02">NK.03.02 AKREDITASI LEMBAGA PENILAIAN KOMPETENSI</option>
<option value="NK.04">NK.04 MONEV DAN PELAPORAN PENILAIAN KOMPETENSI</option>
<option value="NK.04.01">NK.04.01 MONEV</option>
<option value="NK.04.02">NK.04.02 PELAPORAN DAN PEMANFAATAN HASIL PENILAIAN</option>
<option value="KA.01">KA.01 PENGELOLAAN PENDIDIKAN DAN PELATIHAN ASN</option>
<option value="KA.01.01">KA.01.01 ANALISIS KEBUTUHAN PELATIHAN DAN PROGRAM</option>
<option value="KA.01.02">KA.01.02 KOORDINASI, KERJASAMA DAN FASILITASI</option>
<option value="KA.01.03">KA.01.03 EVALUASI DAN SERTIFIKASI</option>
<option value="KA.02">KA.02 PENGELOLAAN PROGRAM PENDIDIKAN ILMU KEPEGAWAIAN</option>
<option value="KA.02.01">KA.02.01 AKADEMIK</option>
<option value="KA.02.02">KA.02.02 KEMAHASISWAAN</option>
<option value="KA.03">KA.03 PENELITIAN KEPEGAWAIAN</option>
<option value="KB.01">KB.01 KONSULTASI HUKUM KEPEGAWAIAN</option>
<option value="KB.01.01">KB.01.01 KONSULTASI</option>
<option value="KB.01.02">KB.01.02 PEMANTAUAN DAN INVENTARISASI</option>
<option value="KB.02">KB.02 BANTUAN HUKUM KEPEGAWAIAN</option>
<option value="KB.02.01">KB.02.01 PENDAMPINGAN BANTUAN HUKUM</option>
<option value="KB.02.02">KB.02.02 PERTIMBANGAN DAN DOKUMENTASI PERKARA HUKUM</option>




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
                    <label>Dokumen</label>
                    <input type="file" name="dokumen" class="form-control" accept=".pdf,.doc,.docx" required>
                </div>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </form>
        </div>
    </div>
    <div class="toast-container position-fixed top-0 end-0 p-3">
    <div id="successToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <strong class="me-auto">Notifikasi</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            Surat berhasil disimpan.
        </div>
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

    <table class="table table-bordered">
    <thead>
    <tr>
        <th>Nomor Memo</th>
        <th>
    <a href="?order=<?= $order_type == 'ASC' ? 'desc' : 'asc' ?>">
        Tanggal
        <?php if ($order_type == 'ASC'): ?>
            <i class="fas fa-arrow-up"></i>
        <?php else: ?>
            <i class="fas fa-arrow-down"></i>
        <?php endif; ?>
    </a>
    </th>
        <th>Perihal</th>
        <th>Tujuan</th>
        <th>Dokumen</th>
        <th>Aksi</th>
    </tr>
    </thead>
    <tbody>
    <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?= htmlspecialchars($row['nomor_memo']) ?></td>
            <td><?= htmlspecialchars($row['tanggal']) ?></td>
            <td><?= htmlspecialchars($row['perihal']) ?></td>
            <td><?= htmlspecialchars($row['tujuan']) ?></td>
            <td><a href="uploads/<?= htmlspecialchars($row['dokumen']) ?>" target="_blank"><?= htmlspecialchars($row['dokumen']) ?></a></td>
            <td>
    <div class="d-flex justify-content-start">
        <a href="edit_nota_dinas.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm mr-2">Ubah</a>
        <form method="POST" action="nota_dinas.php" onsubmit="return confirm('Anda yakin ingin menghapus ini?')">
        <td>
    <a href="?delete_id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Anda yakin ingin menghapus ini?')">Hapus</a>
</td>

        </form>
    </div>
</td>

        </tr>
    <?php } ?>
    </tbody>
</table>

    <nav aria-label="Page navigation example">
        <ul class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php } ?>
        </ul>
    </nav>
</div>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('.select2').select2();
    
    // Memulihkan posisi gulir
    const scrollPosition = sessionStorage.getItem('scrollPosition');
    if (scrollPosition) {
        window.scrollTo(0, scrollPosition);
        sessionStorage.removeItem('scrollPosition'); // Hapus setelah digunakan
    }

    // Menyimpan posisi gulir sebelum pengalihan
    $(window).on('beforeunload', function() {
        sessionStorage.setItem('scrollPosition', window.scrollY);
    });

    // Menangani pencarian dokumen
    $('#searchButton').on('click', function() {
        const searchQuery = $('#searchInput').val();
        // Simpan posisi gulir sebelum pencarian
        sessionStorage.setItem('scrollPosition', window.scrollY);
        
        // Lakukan pencarian menggunakan AJAX
        $.ajax({
            url: 'url_to_your_search_endpoint', // Ganti dengan URL endpoint pencarian Anda
            method: 'GET',
            data: { query: searchQuery },
            success: function(data) {
                // Update hasil pencarian di sini
                $('#resultsContainer').html(data); // Ganti dengan elemen hasil pencarian Anda
            },
            error: function() {
                console.log('Terjadi kesalahan saat melakukan pencarian.');
            }
        });
    });
});
</script>
</body>
</html>
