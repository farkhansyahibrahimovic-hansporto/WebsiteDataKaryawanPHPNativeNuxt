<?php
// api/bootstrap.php

// Set timezone ke Asia/Jakarta
date_default_timezone_set('Asia/Jakarta');

// Set CORS headers untuk mengizinkan akses dari origin manapun
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Content-Type: application/json; charset=utf-8");

// Handle preflight request (OPTIONS method)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Menyertakan file-file yang diperlukan
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../functions/logger.php';
require_once __DIR__ . '/../functions/response.php';

// =============================================
// RAW BODY FIX — dibaca sekali & disimpan global
// =============================================
// Membaca raw body dari php://input dan simpan ke global variable
$GLOBALS['RAW_BODY'] = file_get_contents("php://input");

// Decode JSON dari raw body jika ada
if (!empty($GLOBALS['RAW_BODY'])) {
    $GLOBALS['RAW_JSON'] = json_decode($GLOBALS['RAW_BODY'], true);
} else {
    $GLOBALS['RAW_JSON'] = null;
}

// =============================================
// METHOD OVERRIDE (untuk FormData PUT/DELETE)
// =============================================
// Mengizinkan override HTTP method melalui parameter _method di POST
if (isset($_POST['_method'])) {
    $_SERVER['REQUEST_METHOD'] = strtoupper($_POST['_method']);
}