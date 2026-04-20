<?php
// Menyertakan file konfigurasi database untuk koneksi ke database
require_once "../config/database.php";

// Menyertakan file functions untuk operasi CRUD karyawan
require_once "../functions/karyawan.php";

// Menyertakan file functions untuk pencatatan log aktivitas
require_once "../functions/logger.php";

// Mendapatkan instance koneksi PDO dari fungsi getPDO untuk keperluan logging
$pdo = getPDO();

// Mengambil ID karyawan dari parameter GET, jika tidak ada akan bernilai null
$id = $_GET['id'] ?? null;

// Validasi apakah ID karyawan ada, jika tidak tampilkan error dan hentikan eksekusi
if (!$id) {
    die("ID karyawan tidak ditemukan.");
}

// Ambil data karyawan berdasarkan ID sebelum dihapus untuk keperluan logging
$karyawanData = ambilKaryawanById($id);

// Validasi apakah data karyawan ditemukan, jika tidak tampilkan error dan hentikan eksekusi
if (!$karyawanData) {
    die("Data karyawan tidak ditemukan.");
}

// Mengambil method HTTP yang digunakan untuk request ini
$method = $_SERVER['REQUEST_METHOD'];

// Menentukan endpoint atau path file yang diakses
$endpoint = '/karyawan/delete.php';

// Menulis log akses delete ke file teks
tulisLogFile($method, $endpoint);

// Memanggil fungsi hapusKaryawan untuk menghapus data dari database
$berhasil = hapusKaryawan($id);

// Cek apakah proses hapus berhasil
if ($berhasil) {
    // Blok try-catch untuk logging sukses ke database
    try {
        // Menyiapkan data request yang berisi informasi karyawan yang dihapus
        $requestData = [
            'id_karyawan' => $id,
            'nama_lengkap' => $karyawanData['nama_lengkap'],
            'email' => $karyawanData['email']
        ];
        
        // Menyiapkan data response yang berisi status sukses dan data yang dihapus
        $responseData = [
            'status' => 'success',
            'message' => 'Karyawan berhasil dihapus',
            'deleted_data' => $karyawanData
        ];
        
        // Menulis log sukses ke database dengan detail request dan response
        tulisLogDB($pdo, $endpoint, $method, $requestData, $responseData);
    } catch (Exception $e) {
        // Jika terjadi error saat logging, catat error ke error log PHP
        error_log("Error logging to DB: " . $e->getMessage());
    }
    
    // Redirect ke halaman list karyawan setelah berhasil menghapus
    header("Location: list.php");
    exit;
} else {
    // Blok try-catch untuk logging gagal ke database
    try {
        // Menyiapkan data request yang hanya berisi ID karyawan
        $requestData = [
            'id_karyawan' => $id
        ];
        
        // Menyiapkan data response yang berisi status error
        $responseData = [
            'status' => 'error',
            'message' => 'Gagal menghapus karyawan'
        ];
        
        // Menulis log gagal ke database dengan detail request dan response
        tulisLogDB($pdo, $endpoint, $method, $requestData, $responseData);
    } catch (Exception $e) {
        // Jika terjadi error saat logging, catat error ke error log PHP
        error_log("Error logging to DB: " . $e->getMessage());
    }
    
    // Tampilkan pesan error dan hentikan eksekusi jika gagal menghapus
    die("Gagal menghapus karyawan.");
}