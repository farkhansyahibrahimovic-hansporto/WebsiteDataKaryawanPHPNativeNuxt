<?php
// Menyertakan file functions untuk logger

// Set timezone ke Asia/Jakarta
date_default_timezone_set('Asia/Jakarta');

/**
 * Menulis log request ke file
 * @param string $method - HTTP method (GET, POST, dll)
 * @param string $endpoint - Path endpoint yang diakses
 */
function tulisLogFile($method, $endpoint)
{
    // Tentukan folder logs
    $folder = __DIR__ . '/../logs';
    
    // Buat folder jika belum ada
    if (!is_dir($folder)) mkdir($folder, 0777, true);

    // Nama file log berdasarkan tanggal
    $file = $folder . '/log-' . date('Y-m-d') . '.log';
    
    // Format waktu
    $waktu = date('Y-m-d H:i:s');

    // Format pesan log
    $pesan = "[$waktu] [REQUEST] $method $endpoint";

    // Tulis ke file dengan mode append
    file_put_contents($file, $pesan . "\n", FILE_APPEND);
}

/**
 * Menulis log ke database
 * @param PDO $pdo - Koneksi database
 * @param string $endpoint - Path endpoint
 * @param string $method - HTTP method
 * @param array $reqBody - Request body
 * @param array $resBody - Response body
 */
function tulisLogDB($pdo, $endpoint, $method, $reqBody, $resBody)
{
    // Query INSERT log ke database
    $sql = "INSERT INTO data.logs (endpoint, method, request_body, response_body, timestamp)
            VALUES (:endpoint, :method, :req, :res, NOW())";

    $stmt = $pdo->prepare($sql);
    
    // Execute dengan parameter, convert array ke JSON
    $stmt->execute([
        ':endpoint' => $endpoint,
        ':method'   => $method,
        ':req'      => $reqBody ? json_encode($reqBody, JSON_UNESCAPED_UNICODE) : "null",
        ':res'      => $resBody ? json_encode($resBody, JSON_UNESCAPED_UNICODE) : "null",
    ]);
}

/**
 * ======================================================================
 *  FUNGSI LOGGING UNTUK EXPORT PDF & EXCEL
 * ======================================================================
 */

/**
 * Log aktivitas export (PDF/Excel)
 * @param string $type - Tipe export: 'PDF' atau 'EXCEL'
 * @param string $filename - Nama file yang diexport
 * @param int $totalData - Jumlah data yang diexport
 * @param array $filters - Filter yang digunakan
 * @param bool $success - Status keberhasilan
 * @param string|null $errorMessage - Pesan error jika gagal
 */
function logExport($type, $filename, $totalData, $filters = [], $success = true, $errorMessage = null)
{
    // Path file log export
    $logFile = __DIR__ . '/../logs/export.log';
    $logDir = dirname($logFile);
    
    // Buat direktori logs jika belum ada
    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true);
    }
    
    // Format timestamp
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] [$type] $filename\n";
    
    // Status export
    if ($success) {
        $logMessage .= "Status: ✓ SUKSES\n";
        $logMessage .= "Total Data Diexport: $totalData record\n";
    } else {
        $logMessage .= "Status: ✗ GAGAL\n";
        $logMessage .= "Error: $errorMessage\n";
    }
    
    // Detail filter yang digunakan
    if (!empty($filters)) {
        $logMessage .= "Filter:\n";
        
        foreach ($filters as $key => $value) {
            $displayKey = ucwords(str_replace('_', ' ', $key));
            $logMessage .= "  - $displayKey: $value\n";
        }
    } else {
        $logMessage .= "Filter: Tidak ada (Export semua data)\n";
    }
    
    // Informasi IP dan User Agent
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    
    $logMessage .= "IP Address: $ipAddress\n";
    $logMessage .= "User Agent: $userAgent\n";
    
    // Separator untuk memisahkan log
    $logMessage .= str_repeat('=', 80) . "\n\n";
    
    // Tulis ke file log
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

/**
 * ======================================================================
 *  TULIS LOG IMPORT
 * ======================================================================
 */

/**
 * Log aktivitas import Excel
 * @param string $type - Tipe import (biasanya 'EXCEL')
 * @param string $filename - Nama file yang diimport
 * @param array $result - Hasil import (sukses, gagal, total)
 * @param array $errors - Daftar error yang terjadi
 */
function logImport($type, $filename, $result, $errors = [])
{
    // Path file log import
    $logFile = __DIR__ . '/../logs/import.log';
    $logDir = dirname($logFile);
    
    // Buat direktori jika belum ada
    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true);
    }
    
    // Format timestamp dan pesan awal
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] [$type] $filename - ";
    
    // Detail hasil import
    if ($result) {
        $logMessage .= "Sukses: {$result['sukses']}, Gagal: {$result['gagal']}, Total: {$result['total']}";
    } else {
        $logMessage .= "GAGAL";
    }
    
    // Detail error jika ada
    if (!empty($errors)) {
        $logMessage .= "\nErrors: \n  - " . implode("\n  - ", $errors);
    }
    
    // Separator
    $logMessage .= "\n" . str_repeat('-', 80) . "\n";
    
    // Tulis ke file log
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}
