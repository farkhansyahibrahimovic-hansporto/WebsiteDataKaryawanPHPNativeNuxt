<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../../functions/karyawan.php';

$pdo = getPDO();
$method = $_SERVER['REQUEST_METHOD'];

$input = ($_POST ?: $GLOBALS['RAW_JSON']);

if (!$input || empty($input['id_karyawan'])) {
    $res = jsonError("ID tidak ditemukan");
    tulisLogDB($pdo, $_SERVER['REQUEST_URI'], $method, $input, $res);
    exit;
}

//  Proses upload foto jika dikirim
if (!empty($_FILES['foto']['name'])) {
    
    //  Hapus foto lama jika ada
    if (!empty($input['old_foto'])) {
        $oldFotoPath = __DIR__ . '/../../public/upload/foto/' . $input['old_foto'];
        if (file_exists($oldFotoPath)) {
            unlink($oldFotoPath); // Hapus foto lama dari folder
        }
    }

    //  Upload foto baru
    $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
    $namaBaru = 'foto_' . time() . '_' . rand(1000,9999) . '.' . $ext;

    $targetDir = __DIR__ . '/../../public/upload/foto/';
    $targetFile = $targetDir . $namaBaru;

    if (move_uploaded_file($_FILES['foto']['tmp_name'], $targetFile)) {
        $input['foto'] = $namaBaru; // kirim ke updateKaryawan
    }
}

// Hapus parameter old_foto sebelum update ke database
unset($input['old_foto']);

$ok = updateKaryawan($input['id_karyawan'], $input);

$res = $ok ? jsonSuccess(null, "Data diupdate") : jsonError("Gagal update");

tulisLogDB($pdo, $_SERVER['REQUEST_URI'], $method, $input, $res);
tulisLogFile($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);