<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../../functions/karyawan.php';

$pdo = getPDO();

$input = $GLOBALS['RAW_JSON'] ?? $_POST;

if (empty($input['id_karyawan'])) {
    $res = jsonError("ID tidak ditemukan");
    tulisLogDB($pdo, $_SERVER['REQUEST_URI'], "DELETE", $input, $res);
    exit;
}

$ok = hapusKaryawan($input['id_karyawan']);

$res = $ok ? jsonSuccess(null, "Dihapus") : jsonError("Gagal hapus");

tulisLogDB($pdo, $_SERVER['REQUEST_URI'], "DELETE", $input, $res);
tulisLogFile($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
