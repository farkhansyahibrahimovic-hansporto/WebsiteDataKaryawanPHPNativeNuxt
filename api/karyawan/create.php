<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../../functions/karyawan.php';

$pdo = getPDO();

$input = !empty($_POST) ? $_POST : ($GLOBALS['RAW_JSON'] ?? []);

if (!$input) {
    $res = jsonError("Input kosong", null, 400);
    tulisLogDB($pdo, $_SERVER['REQUEST_URI'], "POST", $input, $res);
    exit;
}

// proses upload foto
$foto = null;

if (!empty($_FILES['foto']['name'])) {
    $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
    $foto = uniqid() . ".$ext";
    move_uploaded_file($_FILES['foto']['tmp_name'], __DIR__ . '/../../public/upload/foto/' . $foto);
}

$input['foto'] = $foto;

$ok = buatKaryawan($input);

$res = $ok ? jsonSuccess(null, "Karyawan dibuat") : jsonError("Gagal membuat data");

tulisLogDB($pdo, $_SERVER['REQUEST_URI'], "POST", $input, $res);
tulisLogFile($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
