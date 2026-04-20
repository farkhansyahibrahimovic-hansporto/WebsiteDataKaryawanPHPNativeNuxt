<?php
// Menyertakan konfigurasi database
require_once __DIR__ . '/../config/database.php';

/**
 * Mengambil semua data jabatan dari database
 * @return array - Array berisi semua data jabatan yang diurutkan berdasarkan nama
 */
function ambilSemuaJabatan()
{
    // Mendapatkan koneksi PDO
    $pdo = getPDO();
    
    // Query untuk mengambil semua jabatan diurutkan berdasarkan nama
    $sql = "SELECT id_jabatan, nama_jabatan FROM data.jabatan ORDER BY nama_jabatan ASC";
    
    // Eksekusi query
    $stmt = $pdo->query($sql);
    
    // Return hasil dalam bentuk array
    return $stmt->fetchAll();
}

/**
 * Mengambil data jabatan berdasarkan ID
 * @param int $id - ID jabatan yang akan dicari
 * @return array|null - Data jabatan atau null jika tidak ditemukan
 */
function ambilJabatanById($id)
{
    // Mendapatkan koneksi PDO
    $pdo = getPDO();
    
    // Query untuk mengambil jabatan berdasarkan ID
    $sql = "SELECT id_jabatan, nama_jabatan FROM data.jabatan WHERE id_jabatan = :id LIMIT 1";
    
    // Prepare statement untuk menghindari SQL injection
    $stmt = $pdo->prepare($sql);
    
    // Execute dengan parameter ID
    $stmt->execute([':id' => $id]);
    
    // Fetch data
    $r = $stmt->fetch();
    
    // Return data atau null jika tidak ditemukan
    return $r ?: null;
}