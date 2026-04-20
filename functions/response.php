<?php
/**
 * ======================================================================
 *  FILE: functions/response.php (UPDATED VERSION)
 * ======================================================================
 *  Helper untuk JSON response dengan pembersihan output buffer
 */

/**
 * Mengirim response JSON ke client
 * @param array $data - Data yang akan dikirim dalam format JSON
 * @param int $statusCode - HTTP status code (default 200)
 * @return array - Data yang dikirim (untuk keperluan logging)
 */
function sendJSON($data, $statusCode = 200)
{
    // Bersihkan semua output buffer untuk menghindari output yang tidak diinginkan
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    // Set header untuk response JSON
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, must-revalidate');
    
    // Set HTTP status code
    http_response_code($statusCode);
    
    // Encode data ke JSON dan kirim ke client
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    
    // Return data agar bisa digunakan untuk logging
    return $data;
}

/**
 * Response JSON untuk operasi sukses
 * @param mixed $data - Data yang akan dikirim
 * @param string $message - Pesan sukses (default "Sukses")
 * @return array - Response data
 */
function jsonSuccess($data = null, $message = "Sukses")
{
    return sendJSON([
        "berhasil"  => true,
        "pesan"     => $message,
        "data"      => $data,
        "kesalahan" => null
    ]);
}

/**
 * Response JSON untuk operasi gagal/error
 * @param string $message - Pesan error (default "Gagal")
 * @param mixed $errors - Detail error jika ada
 * @param int $statusCode - HTTP status code (default 400)
 * @return array - Response data
 */
function jsonError($message = "Gagal", $errors = null, $statusCode = 400)
{
    return sendJSON([
        "berhasil"  => false,
        "pesan"     => $message,
        "data"      => null,
        "kesalahan" => $errors
    ], $statusCode);
}
