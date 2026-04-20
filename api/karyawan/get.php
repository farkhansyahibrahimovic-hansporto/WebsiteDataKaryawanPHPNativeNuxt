<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../../functions/karyawan.php';

$pdo = getPDO();

// GET by ID
if (!empty($_GET['id'])) {
    $data = ambilKaryawanById($_GET['id']);
    $response = jsonSuccess($data, $data ? "Data ditemukan" : "Data tidak ditemukan");

    tulisLogDB($pdo, $_SERVER['REQUEST_URI'], "GET", $_GET, $response);
    exit;
}

// FILTERS
$filters = [
    "jenis_kelamin" => $_GET['jenis_kelamin'] ?? '',
    "jabatan_id"    => $_GET['jabatan_id'] ?? '',
    "salary_min"    => $_GET['salary_min'] ?? '',
    "salary_max"    => $_GET['salary_max'] ?? '',
    "age_min"       => $_GET['age_min'] ?? '',
    "age_max"       => $_GET['age_max'] ?? '',
    "q"             => $_GET['q'] ?? '',
];

$data = ambilListKaryawan($filters);

$response = jsonSuccess([
    "count" => count($data),
    "filters" => $filters,
    "data" => $data
]);

tulisLogDB($pdo, $_SERVER['REQUEST_URI'], "GET", $_GET, $response);
tulisLogFile($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
