<?php
/**
 * ======================================================================
 *  API ENDPOINT: EXPORT DATA KARYAWAN KE EXCEL
 * ======================================================================
 */

// CRITICAL: Bersihkan output buffer SEBELUM require apapun
while (ob_get_level() > 0) {
    ob_end_clean();
}

// Suppress errors agar tidak ada output yang mengotori response
error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../../functions/karyawan.php';
require_once __DIR__ . '/../../functions/export.php';

$pdo = getPDO();
$method = $_SERVER['REQUEST_METHOD'];
$endpoint = $_SERVER['REQUEST_URI'];

// CORS Headers - HARUS di awal sebelum output apapun
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Hanya terima GET request
if ($method !== 'GET') {
    header('Content-Type: application/json');
    http_response_code(405);
    $res = jsonError("Method tidak diizinkan. Gunakan GET", null, 405);
    tulisLogDB($pdo, $endpoint, $method, [], $res);
    echo json_encode($res);
    exit;
}

try {
    // ============================================
    // AMBIL FILTER
    // ============================================
    $filters = [];
    
    $allowedFilters = ['jenis_kelamin', 'jabatan_id', 'salary_min', 'salary_max', 'age_min', 'age_max', 'q'];
    
    foreach ($allowedFilters as $filter) {
        if (isset($_GET[$filter]) && $_GET[$filter] !== '') {
            $filters[$filter] = $_GET[$filter];
        }
    }
    
    error_log("Export Excel - Filters: " . json_encode($filters));
    
    // Ambil semua data karyawan
    $dataKaryawan = ambilListKaryawan($filters);
    
    error_log("Export Excel - Total data: " . count($dataKaryawan));
    
    if (empty($dataKaryawan)) {
        header('Content-Type: application/json');
        http_response_code(404);
        $res = jsonError("Tidak ada data karyawan untuk diexport", null, 404);
        tulisLogDB($pdo, $endpoint, $method, $_GET, $res);
        logExport('EXCEL', 'data_karyawan_' . date('Ymd_His') . '.xlsx', 0, $filters, false, "Tidak ada data untuk diexport");
        echo json_encode($res);
        exit;
    }
    
    // Log ke database - SEBELUM generate file
    tulisLogDB($pdo, $endpoint, $method, $_GET, [
        'status' => 'generating',
        'count' => count($dataKaryawan),
        'filters_applied' => $filters
    ]);
    
    // Generate filename
    $filename = 'data_karyawan_' . date('Ymd_His') . '.xlsx';
    
    // Log export
    logExport('EXCEL', $filename, count($dataKaryawan), $filters, true);
    
    // ✅ CRITICAL: Jangan bersihkan buffer atau remove headers lagi
    // Fungsi generateExcel() akan handle semuanya
    
    // Generate & output Excel
    generateExcel($dataKaryawan, $filename);
    // Fungsi generateExcel sudah exit, kode di bawah tidak akan dijalankan
    
} catch (Exception $e) {
    // Log error
    error_log("Export Excel Error: " . $e->getMessage());
    
    header('Content-Type: application/json');
    http_response_code(500);
    
    $res = jsonError("Gagal export Excel: " . $e->getMessage(), null, 500);
    tulisLogDB($pdo, $endpoint, $method, $_GET, $res);
    
    $filename = 'data_karyawan_' . date('Ymd_His') . '.xlsx';
    logExport('EXCEL', $filename, 0, $filters ?? [], false, $e->getMessage());
    
    echo json_encode($res);
    exit;
}