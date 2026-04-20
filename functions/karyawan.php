<?php
// Menyertakan konfigurasi database
require_once __DIR__ . '/../config/database.php';

/**
 * ======================================================================
 *  FUNGSI UTAMA: AMBIL DATA (DENGAN FILTER)
 * ======================================================================
 */

/**
 * Membuat query filter untuk karyawan
 * @param array $filters - Array filter yang akan diterapkan
 * @param array $params - Reference array untuk parameter PDO
 * @return string - Query SQL untuk filter
 */
function _queryFilterKaryawan($filters, &$params)
{
    // Query dasar dengan JOIN ke tabel jabatan
    $sql = "FROM karyawan k
            LEFT JOIN data.jabatan j ON k.jabatan_id = j.id_jabatan
            WHERE 1=1";

    // Filter jenis kelamin
    if (!empty($filters['jenis_kelamin'])) {
        $sql .= " AND k.jenis_kelamin = :jk";
        $params[':jk'] = $filters['jenis_kelamin'];
    }

    // Filter jabatan ID
    if (!empty($filters['jabatan_id'])) {
        $sql .= " AND k.jabatan_id = :jid";
        $params[':jid'] = (int)$filters['jabatan_id'];
    }

    // Filter gaji minimum
    if (array_key_exists('salary_min', $filters) && $filters['salary_min'] !== '') {
        $sql .= " AND k.gaji_pokok >= :smin";
        $params[':smin'] = (int)$filters['salary_min'];
    }

    // Filter gaji maksimum
    if (array_key_exists('salary_max', $filters) && $filters['salary_max'] !== '') {
        $sql .= " AND k.gaji_pokok <= :smax";
        $params[':smax'] = (int)$filters['salary_max'];
    }

    // Filter umur minimum menggunakan fungsi PostgreSQL
    if (array_key_exists('age_min', $filters) && $filters['age_min'] !== '') {
        $sql .= " AND date_part('year', age(k.tanggal_lahir)) >= :amin";
        $params[':amin'] = (int)$filters['age_min'];
    }

    // Filter umur maksimum menggunakan fungsi PostgreSQL
    if (array_key_exists('age_max', $filters) && $filters['age_max'] !== '') {
        $sql .= " AND date_part('year', age(k.tanggal_lahir)) <= :amax";
        $params[':amax'] = (int)$filters['age_max'];
    }

    // Filter pencarian keyword (nama, email, atau telepon)
    if (!empty($filters['q'])) {
        $sql .= " AND (k.nama_lengkap ILIKE :q
                       OR k.email ILIKE :q
                       OR k.telepon ILIKE :q)";
        $params[':q'] = "%" . $filters['q'] . "%";
    }

    return $sql;
}

/**
 * Mengambil semua data karyawan dengan filter
 * @param array $filters - Array filter opsional
 * @return array - Array data karyawan
 */
function ambilListKaryawan($filters = [])
{
    $pdo = getPDO();
    $params = [];

    // Build query filter
    $filterSQL = _queryFilterKaryawan($filters, $params);

    // Query lengkap dengan ORDER BY
    $sql = "SELECT k.*, j.nama_jabatan
            $filterSQL
            ORDER BY k.nama_lengkap ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * ======================================================================
 *  PAGINATION
 * ======================================================================
 */

/**
 * Menghitung total data karyawan sesuai filter
 * @param array $filters - Array filter opsional
 * @return int - Jumlah total data
 */
function hitungTotalKaryawan($filters = [])
{
    $pdo = getPDO();
    $params = [];

    // Build query filter
    $filterSQL = _queryFilterKaryawan($filters, $params);

    // Query COUNT
    $sql = "SELECT COUNT(*) AS total $filterSQL";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $r = $stmt->fetch();
    return $r['total'] ?? 0;
}

/**
 * Menghitung total halaman untuk pagination
 * @param array $filters - Array filter
 * @param int $perPage - Jumlah data per halaman
 * @return int - Total halaman
 */
function hitungTotalHalaman($filters, $perPage)
{
    $total = hitungTotalKaryawan($filters);
    return ceil($total / $perPage);
}

/**
 * Mengambil data karyawan dengan pagination
 * @param array $filters - Array filter
 * @param int $perPage - Jumlah data per halaman
 * @param int $page - Halaman yang diakses
 * @return array - Array data karyawan
 */
function ambilListKaryawanPaginate($filters = [], $perPage = 10, $page = 1)
{
    $pdo = getPDO();
    $params = [];

    // Hitung offset berdasarkan halaman
    $offset = ($page - 1) * $perPage;

    // Build query filter
    $filterSQL = _queryFilterKaryawan($filters, $params);

    // Query dengan LIMIT dan OFFSET
    $sql = "SELECT k.id_karyawan, k.foto, k.nama_lengkap, k.email, k.telepon,
                   k.jenis_kelamin, k.gaji_pokok, j.nama_jabatan
            $filterSQL
            ORDER BY k.nama_lengkap ASC
            LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($sql);

    // Bind parameter filter normal
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }

    // Bind limit dan offset sebagai integer
    $stmt->bindValue(':limit', (int)$perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

    $stmt->execute();

    return $stmt->fetchAll();
}

/**
 * ======================================================================
 *  CRUD: GET, CREATE, UPDATE, DELETE
 * ======================================================================
 */

/**
 * Mengambil data karyawan berdasarkan ID
 * @param string $id - ID karyawan
 * @return array|null - Data karyawan atau null
 */
function ambilKaryawanById($id)
{
    $pdo = getPDO();
    
    // Query dengan JOIN ke tabel jabatan
    $sql = "SELECT k.*, j.nama_jabatan
            FROM karyawan k
            LEFT JOIN data.jabatan j ON k.jabatan_id = j.id_jabatan
            WHERE k.id_karyawan = :id LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);

    $r = $stmt->fetch();
    return $r ?: null;
}

/**
 * Membuat data karyawan baru
 * @param array $data - Data karyawan yang akan disimpan
 * @return bool - Status berhasil atau gagal
 */
function buatKaryawan($data)
{
    $pdo = getPDO();

    // Generate ID otomatis jika tidak ada
    $id_karyawan = $data['id_karyawan'] ?? bin2hex(random_bytes(8));

    // Query INSERT dengan semua field
    $sql = "INSERT INTO karyawan
            (id_karyawan, nama_lengkap, email, telepon, tempat_lahir, tanggal_lahir,
             jenis_kelamin, jabatan_id, gaji_pokok, foto, created_at, updated_at)
            VALUES
            (:id, :nama, :email, :telp, :tmp, :tgl,
             :jk, :jid, :gaji, :foto, NOW(), NOW())";

    $stmt = $pdo->prepare($sql);
    
    // Execute dengan data
    return $stmt->execute([
        ':id'   => $id_karyawan,
        ':nama' => $data['nama_lengkap'],
        ':email' => $data['email'],
        ':telp' => $data['telepon'],
        ':tmp'  => $data['tempat_lahir'],
        ':tgl'  => $data['tanggal_lahir'],
        ':jk'   => $data['jenis_kelamin'],
        ':jid'  => $data['jabatan_id'],
        ':gaji' => $data['gaji_pokok'],
        ':foto' => $data['foto'] ?? null,
    ]);
}

/**
 * Update data karyawan
 * @param string $id - ID karyawan
 * @param array $data - Data baru yang akan diupdate
 * @return bool - Status berhasil atau gagal
 */
function updateKaryawan($id, $data)
{
    $pdo = getPDO();

    // Ambil data lama untuk perbandingan
    $stmt = $pdo->prepare("SELECT * FROM karyawan WHERE id_karyawan = :id");
    $stmt->execute(['id' => $id]);
    $lama = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$lama) return false;

    // Handle foto baru
    if (!empty($data['foto'])) {
        // Hapus foto lama jika ada
        if (!empty($lama['foto']) && file_exists(__DIR__ . '/../../public/upload/foto/' . $lama['foto'])) {
            unlink(__DIR__ . '/../../public/upload/foto/' . $lama['foto']);
        }
        $fotoBaru = $data['foto'];
    } else {
        // Tetap pakai foto lama
        $fotoBaru = $lama['foto'];
    }

    // Ambil data baru atau gunakan data lama jika tidak ada perubahan
    $nama_lengkap   = $data['nama_lengkap']   ?? $lama['nama_lengkap'];
    $email          = $data['email']          ?? $lama['email'];
    $telepon        = $data['telepon']        ?? $lama['telepon'];
    $tempat_lahir   = $data['tempat_lahir']   ?? $lama['tempat_lahir'];
    $tanggal_lahir  = $data['tanggal_lahir']  ?? $lama['tanggal_lahir'];
    $jenis_kelamin  = $data['jenis_kelamin']  ?? $lama['jenis_kelamin'];
    $jabatan_id     = $data['jabatan_id']     ?? $lama['jabatan_id'];
    $gaji_pokok     = $data['gaji_pokok']     ?? $lama['gaji_pokok'];

    // Query UPDATE
    $sql = "UPDATE karyawan SET 
                nama_lengkap = :nama,
                email = :email,
                telepon = :telp,
                tempat_lahir = :tmp,
                tanggal_lahir = :tgl,
                jenis_kelamin = :jk,
                jabatan_id = :jid,
                gaji_pokok = :gaji,
                foto = :foto,
                updated_at = NOW()
            WHERE id_karyawan = :id";

    $stmt = $pdo->prepare($sql);

    // Execute dengan semua parameter
    return $stmt->execute([
        ':nama' => $nama_lengkap,
        ':email' => $email,
        ':telp' => $telepon,
        ':tmp' => $tempat_lahir,
        ':tgl' => $tanggal_lahir,
        ':jk'  => $jenis_kelamin,
        ':jid' => $jabatan_id,
        ':gaji'=> $gaji_pokok,
        ':foto'=> $fotoBaru,
        ':id'  => $id
    ]);
}

/**
 * Menghapus data karyawan beserta fotonya
 * @param string $id - ID karyawan
 * @return bool - Status berhasil atau gagal
 */
function hapusKaryawan($id)
{
    $pdo = getPDO();

    // Ambil data foto berdasarkan ID
    $stmt = $pdo->prepare("SELECT foto FROM karyawan WHERE id_karyawan = :id");
    $stmt->bindValue(':id', $id, PDO::PARAM_STR);
    $stmt->execute();
    $karyawan = $stmt->fetch(PDO::FETCH_ASSOC);

    // Validasi data ditemukan
    if (!$karyawan) {
        return false;
    }

    // Hapus file foto jika ada
    if (!empty($karyawan['foto'])) {
        $filePath = __DIR__ . "/../public/upload/foto/" . $karyawan['foto'];

        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    // Hapus data karyawan dari database
    $stmt = $pdo->prepare("DELETE FROM karyawan WHERE id_karyawan = :id");
    $stmt->bindValue(':id', $id, PDO::PARAM_STR);

    $stmt->execute();
    return $stmt->rowCount() > 0;
}

/**
 * Membuat URL filter tanpa parameter halaman
 * @return string - Query string untuk filter
 */
function urlFilter()
{
    $qs = $_GET;
    unset($qs['halaman']); // Hapus parameter halaman
    return http_build_query($qs); 
}