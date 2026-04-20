<?php
/**
 * ======================================================================
 *  API ENDPOINT: IMPORT DATA KARYAWAN DARI EXCEL 
 * ======================================================================
 */

// CRITICAL: Bersihkan output buffer SEBELUM require apapun
while (ob_get_level() > 0) {
    ob_end_clean();
}

// Suppress errors
error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../../functions/import.php';

$pdo = getPDO();
$method = $_SERVER['REQUEST_METHOD'];
$endpoint = $_SERVER['REQUEST_URI'];

// CORS Headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Handle preflight
if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Hanya terima POST request
if ($method !== 'POST') {
    http_response_code(405);
    
    $response = [
        'berhasil' => false,
        'pesan' => 'Method tidak diizinkan. Gunakan POST',
        'data' => null
    ];
    
    echo json_encode($response);
    tulisLogDB($pdo, $endpoint, $method, null, $response);
    exit;
}

// Validasi file upload
if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    
    $errorCode = $_FILES['file']['error'] ?? 'no_file';
    $errorMessages = [
        UPLOAD_ERR_INI_SIZE => 'File terlalu besar (melebihi upload_max_filesize)',
        UPLOAD_ERR_FORM_SIZE => 'File terlalu besar (melebihi MAX_FILE_SIZE)',
        UPLOAD_ERR_PARTIAL => 'File hanya terupload sebagian',
        UPLOAD_ERR_NO_FILE => 'Tidak ada file yang diupload',
        UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak ditemukan',
        UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk',
        UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh ekstensi PHP',
    ];
    
    $response = [
        'berhasil' => false,
        'pesan' => $errorMessages[$errorCode] ?? 'File tidak ditemukan atau gagal diupload',
        'data' => [
            'error_code' => $errorCode,
            'max_upload_size' => ini_get('upload_max_filesize'),
            'max_post_size' => ini_get('post_max_size')
        ]
    ];
    
    echo json_encode($response);
    tulisLogDB($pdo, $endpoint, $method, ['file_error' => $errorCode], $response);
    exit;
}

$file = $_FILES['file'];
$allowedExt = ['xls', 'xlsx'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

// Validasi ekstensi file
if (!in_array($ext, $allowedExt)) {
    http_response_code(400);
    
    $response = [
        'berhasil' => false,
        'pesan' => 'File harus berformat Excel (.xls atau .xlsx)',
        'data' => [
            'filename' => $file['name'],
            'extension' => $ext,
            'allowed' => $allowedExt
        ]
    ];
    
    echo json_encode($response);
    tulisLogDB($pdo, $endpoint, $method, ['filename' => $file['name'], 'extension' => $ext], $response);
    exit;
}

// Validasi ukuran file (max 5MB)
if ($file['size'] > 5 * 1024 * 1024) {
    http_response_code(400);
    
    $response = [
        'berhasil' => false,
        'pesan' => 'Ukuran file maksimal 5MB',
        'data' => [
            'filename' => $file['name'],
            'size' => $file['size'],
            'size_mb' => round($file['size'] / 1024 / 1024, 2),
            'max_size_mb' => 5
        ]
    ];
    
    echo json_encode($response);
    tulisLogDB($pdo, $endpoint, $method, ['filename' => $file['name'], 'size' => $file['size']], $response);
    exit;
}

try {
    // Buat folder import jika belum ada
    $uploadDir = __DIR__ . '/../../public/upload/import/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Generate nama file unik
    $filename = 'import_' . date('Ymd_His') . '_' . uniqid() . '.' . $ext;
    $targetPath = $uploadDir . $filename;
    
    // Pindahkan file ke folder temporary
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        http_response_code(500);
        
        $response = [
            'berhasil' => false,
            'pesan' => 'Gagal menyimpan file. Periksa permission folder.',
            'data' => [
                'upload_dir' => $uploadDir,
                'is_writable' => is_writable($uploadDir)
            ]
        ];
        
        echo json_encode($response);
        tulisLogDB($pdo, $endpoint, $method, ['filename' => $file['name']], $response);
        exit;
    }
    
    // Proses import
    $errors = [];
    $result = importFromExcel($targetPath, $errors);
    
    // Log hasil import ke FILE (opsional)
    logImport('EXCEL', $filename, $result, $errors);
    
    // Hapus file temporary
    if (file_exists($targetPath)) {
        unlink($targetPath);
    }
    
    // Response
    if ($result === false) {
        http_response_code(400);
        
        $response = [
            'berhasil' => false,
            'pesan' => 'Gagal import data',
            'data' => [
                'errors' => $errors,
                'total_errors' => count($errors)
            ]
        ];
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        tulisLogDB($pdo, $endpoint, $method, [
            'filename' => $file['name'],
            'original_name' => $file['name']
        ], $response);
        
    } else {
        http_response_code(200);
        $message = "Import selesai. Sukses: {$result['sukses']}, Gagal: {$result['gagal']}";
        
        $response = [
            'berhasil' => true,
            'pesan' => $message,
            'data' => [
                'result' => $result,
                'errors' => $errors,
                'summary' => [
                    'total_processed' => $result['total'],
                    'success' => $result['sukses'],
                    'failed' => $result['gagal'],
                    'success_rate' => round(($result['sukses'] / $result['total']) * 100, 2) . '%'
                ]
            ]
        ];
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        tulisLogDB($pdo, $endpoint, $method, [
            'filename' => $file['name'],
            'original_name' => $file['name'],
            'size' => $file['size']
        ], $response);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    
    // Hapus file jika ada error
    if (isset($targetPath) && file_exists($targetPath)) {
        unlink($targetPath);
    }
    
    $response = [
        'berhasil' => false,
        'pesan' => 'Error: ' . $e->getMessage(),
        'data' => [
            'error_type' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    tulisLogDB($pdo, $endpoint, $method, [
        'filename' => $file['name'] ?? 'unknown',
        'error' => $e->getMessage()
    ], $response);
}

exit;