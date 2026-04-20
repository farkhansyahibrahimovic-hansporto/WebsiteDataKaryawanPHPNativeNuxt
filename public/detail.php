<?php
// Menyertakan file konfigurasi database untuk koneksi ke database
require_once "../config/database.php";

// Menyertakan file functions untuk operasi CRUD karyawan
require_once "../functions/karyawan.php";

// Menyertakan file functions untuk pencatatan log aktivitas
require_once "../functions/logger.php";

// Mendapatkan instance koneksi PDO dari fungsi getPDO untuk keperluan logging
$pdo = getPDO();

// Mengambil ID karyawan dari POST request
$id = $_POST['id'] ?? null;

// Validasi apakah ID karyawan ada, jika tidak tampilkan error dan hentikan eksekusi
if (!$id) {
    die("ID karyawan tidak ditemukan.");
}

// Ambil data karyawan berdasarkan ID untuk ditampilkan di halaman detail
$k = ambilKaryawanById($id);

// Validasi apakah data karyawan ditemukan, jika tidak tampilkan error dan hentikan eksekusi
if (!$k) {
    die("Karyawan tidak ditemukan.");
}

// Blok try-catch untuk logging akses halaman detail
try {
    // Mengambil method HTTP yang digunakan untuk request ini
    $method = $_SERVER['REQUEST_METHOD'];
    
    // Menentukan endpoint atau path halaman yang diakses
    $endpoint = '/karyawan/detail.php';
    
    // Menulis log akses halaman ke file teks
    tulisLogFile($method, $endpoint);
    
    // Menyiapkan data request yang berisi ID dan nama karyawan yang diakses
    $requestData = [
        'id_karyawan' => $id,
        'nama_lengkap' => $k['nama_lengkap']
    ];
    
    // Menyiapkan data response yang berisi status dan informasi akses
    $responseData = [
        'status' => 'success',
        'message' => 'Detail karyawan diakses',
        'view_type' => 'detail_page'
    ];
    
    // Menulis log akses ke database dengan detail request dan response
    tulisLogDB($pdo, $endpoint, $method, $requestData, $responseData);
} catch (Exception $e) {
    // Jika terjadi error saat logging, catat error ke error log PHP
    error_log("Error logging to DB: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Detail Karyawan</title>
    <!-- Menyertakan Tailwind CSS dari CDN untuk styling -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
      /* Styling khusus untuk mode print */
        @media print {

            /* Sembunyikan elemen dengan class no-print saat print (tombol-tombol) */
            .no-print {
                display: none !important;
            }

            /* Styling kotak konten agar tampak resmi saat dicetak */
            .print-box {
                border: 2px solid #000;
                padding: 20px;
                border-radius: 12px;
            }

            /* Styling foto agar pas dan rapi di halaman cetak */
            .print-photo {
                width: 120px;
                height: 120px;
                object-fit: cover;
                border-radius: 50%;
                margin: 0 auto 15px auto;
                display: block;
            }

            /* Styling judul untuk mode print */
            .print-title {
                text-align: center;
                font-size: 22px;
                font-weight: bold;
                margin-bottom: 20px;
            }

            /* Styling baris data agar rapi saat dicetak */
            .print-row {
                display: flex;
                justify-content: space-between;
                border-bottom: 1px solid #ddd;
                padding: 6px 0;
                font-size: 15px;
            }
        }
    </style>

</head>

<!-- Body dengan background biru muda, tinggi minimal full screen, dan padding -->
<body class="bg-blue-50 min-h-screen p-6">

<!-- Container utama dengan lebar maksimal, background putih, shadow, rounded corners, dan padding -->
<div class="max-w-2xl mx-auto bg-white shadow-lg rounded-xl p-6">

    <!-- Area yang akan dicetak, menggunakan class print-box untuk styling print -->
    <div class="print-box">

        <!-- Judul halaman dengan styling untuk tampilan normal dan print -->
        <h1 class="text-2xl font-bold text-blue-600 mb-6 text-center print-title">
            Detail Karyawan
        </h1>

        <!-- Container untuk foto dan nama karyawan dengan center alignment -->
        <div class="flex flex-col items-center mb-6">

            <?php if($k['foto']): ?>
                <!-- Tampilkan foto karyawan jika ada, dengan class untuk styling normal dan print -->
                <img src="upload/foto/<?= $k['foto'] ?>" 
                     class="w-32 h-32 object-cover rounded-full shadow mb-3 print-photo">
            <?php else: ?>
                <!-- Tampilkan placeholder jika tidak ada foto -->
                <div class="w-32 h-32 rounded-full bg-gray-200 flex items-center justify-center text-gray-500">
                    Tidak ada foto
                </div>
            <?php endif; ?>

            <!-- Nama lengkap karyawan dengan styling -->
            <h2 class="text-xl font-semibold text-gray-700 mt-2">
                <?= htmlspecialchars($k['nama_lengkap']) ?>
            </h2>
        </div>

        <!-- Container untuk detail informasi karyawan -->
        <div class="space-y-3 text-gray-700">

            <!-- Baris informasi Email dengan layout flex untuk label dan value -->
            <div class="flex justify-between border-b pb-2 print-row">
                <span class="font-semibold">Email:</span>
                <span><?= $k['email'] ?></span>
            </div>

            <!-- Baris informasi Telepon -->
            <div class="flex justify-between border-b pb-2 print-row">
                <span class="font-semibold">Telepon:</span>
                <span><?= $k['telepon'] ?></span>
            </div>

            <!-- Baris informasi Tempat Lahir -->
            <div class="flex justify-between border-b pb-2 print-row">
                <span class="font-semibold">Tempat Lahir:</span>
                <span><?= $k['tempat_lahir'] ?></span>
            </div>

            <!-- Baris informasi Tanggal Lahir -->
            <div class="flex justify-between border-b pb-2 print-row">
                <span class="font-semibold">Tanggal Lahir:</span>
                <span><?= $k['tanggal_lahir'] ?></span>
            </div>

            <!-- Baris informasi Jenis Kelamin -->
            <div class="flex justify-between border-b pb-2 print-row">
                <span class="font-semibold">Jenis Kelamin:</span>
                <span><?= $k['jenis_kelamin'] ?></span>
            </div>

            <!-- Baris informasi Jabatan -->
            <div class="flex justify-between border-b pb-2 print-row">
                <span class="font-semibold">Jabatan:</span>
                <span><?= $k['nama_jabatan'] ?></span>
            </div>

            <!-- Baris informasi Gaji Pokok dengan format rupiah -->
            <div class="flex justify-between border-b pb-2 print-row">
                <span class="font-semibold">Gaji Pokok:</span>
                <span>Rp <?= number_format($k['gaji_pokok'], 0, ",", ".") ?></span>
            </div>

        </div>

    </div>
    <!-- Akhir print-box -->

    <!-- Container untuk tombol kembali dan cetak, tidak akan tampil saat print -->
    <div class="mt-6 flex justify-between no-print">

        <!-- Link untuk kembali ke halaman list karyawan -->
        <a href="list.php" 
           class="bg-blue-600 text-white px-4 py-2 rounded-lg shadow hover:bg-blue-700">
            ⮜ Kembali
        </a>

        <!-- Tombol untuk trigger fungsi print -->
        <button onclick="cetakDetail()"
                class="bg-blue-600 text-white px-4 py-2 rounded-lg shadow hover:bg-blue-700">
            🖨 Cetak
        </button>

    </div>

</div>

<script>
// Fungsi untuk mencetak halaman detail karyawan
function cetakDetail() {
    // Memanggil fungsi print bawaan browser untuk mencetak halaman
    window.print();
}
</script>

</body>
</html>
