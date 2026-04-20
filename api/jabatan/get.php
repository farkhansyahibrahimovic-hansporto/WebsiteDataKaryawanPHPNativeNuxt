<?php
require_once __DIR__ . '/../../api/bootstrap.php';
require_once __DIR__ . '/../../functions/jabatan.php';
require_once __DIR__ . '/../../functions/response.php';
require_once __DIR__ . '/../../functions/logger.php';

// =======================================
//   GET JABATAN BY ID
// =======================================

$pdo = getPDO();
$method = $_SERVER['REQUEST_METHOD'];

if (!empty($_GET['id'])) {
    $id = intval($_GET['id']);

    if ($id <= 0) {
        $res = jsonError("ID tidak valid");
        tulisLogDB($pdo, $_SERVER['REQUEST_URI'], $method, $_GET, $res);
        exit;
    }

    $jab = ambilJabatanById($id);

    if (!$jab) {
        $res = jsonError("Jabatan dengan ID $id tidak ditemukan");
        tulisLogDB($pdo, $_SERVER['REQUEST_URI'], $method, $_GET, $res);
        exit;
    }

    $res = jsonSuccess($jab, "Data jabatan ditemukan");
    tulisLogDB($pdo, $_SERVER['REQUEST_URI'], $method, $_GET, $res);
    exit;
}

// =======================================
//   FILTER PENCARIAN
// =======================================

$filters = [];
if (!empty($_GET['q'])) {
    $filters['q'] = $_GET['q'];
}

// =======================================
//   PAGINATION
// =======================================

$page    = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = isset($_GET['per_page']) ? max(1, intval($_GET['per_page'])) : 10;

// Hitung total data
$sqlCount = "SELECT COUNT(*) FROM data.jabatan WHERE TRUE";
$params   = [];

if (!empty($filters['q'])) {
    $sqlCount .= " AND nama_jabatan ILIKE :q";
    $params[':q'] = "%{$filters['q']}%";
}

$stmtCount = $pdo->prepare($sqlCount);
$stmtCount->execute($params);
$totalData = $stmtCount->fetchColumn();

$totalHalaman = ceil($totalData / $perPage);

// Ambil data paginasi
$sql = "SELECT id_jabatan, nama_jabatan
        FROM data.jabatan
        WHERE TRUE";

if (!empty($filters['q'])) {
    $sql .= " AND nama_jabatan ILIKE :q";
}

$sql .= " ORDER BY nama_jabatan ASC LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);

if (!empty($filters['q'])) {
    $stmt->bindValue(':q', "%{$filters['q']}%");
}

$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', ($page - 1) * $perPage, PDO::PARAM_INT);

$stmt->execute();
$data = $stmt->fetchAll();

// =======================================
//   RESPONSE JSON + LOGGING
// =======================================

$res = jsonSuccess([
    "page"        => $page,
    "per_page"    => $perPage,
    "total_data"  => $totalData,
    "total_pages" => $totalHalaman,
    "data"        => $data
], "Daftar jabatan");

tulisLogDB($pdo, $_SERVER['REQUEST_URI'], "GET", $_GET, $res);
tulisLogFile($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
