<?php
/**
 * ======================================================================
 *  FUNGSI HELPER UNTUK IMPORT EXCEL
 * ======================================================================
 */

use Shuchkin\SimpleXLSX;

// Menyertakan konfigurasi database
require_once __DIR__ . '/../config/database.php';

/**
 * ======================================================================
 *  IMPORT DARI EXCEL (menggunakan SimpleXLSX)
 * ======================================================================
 */
function importFromExcel($filePath, &$errors = [])
{
    // Path ke library SimpleXLSX
    $simpleXlsxPath = __DIR__ . '/../lib/simplexlsx/src/SimpleXLSX.php';
    
    // Validasi keberadaan library
    if (!file_exists($simpleXlsxPath)) {
        $errors[] = "Library SimpleXLSX tidak ditemukan di: $simpleXlsxPath";
        $errors[] = "Download dari: https://github.com/shuchkin/simplexlsx/blob/master/src/SimpleXLSX.php";
        return false;
    }
    
    // Menyertakan library SimpleXLSX
    require_once $simpleXlsxPath;

    // Validasi keberadaan file Excel
    if (!file_exists($filePath)) {
        $errors[] = "File $filePath tidak ditemukan";
        return false;
    }

    // Cek class dengan atau tanpa namespace
    $useNamespace = class_exists('Shuchkin\SimpleXLSX');
    $classExists = $useNamespace || class_exists('SimpleXLSX');
    
    if (!$classExists) {
        $errors[] = "Class SimpleXLSX tidak ditemukan. Pastikan file library benar.";
        return false;
    }

    // Parse Excel dengan error handling
    try {
        if ($useNamespace) {
            $xlsx = \Shuchkin\SimpleXLSX::parse($filePath);
            if (!$xlsx) {
                $errors[] = "Gagal membaca Excel: " . \Shuchkin\SimpleXLSX::parseError();
                return false;
            }
        } else {
            $xlsx = SimpleXLSX::parse($filePath);
            if (!$xlsx) {
                $errors[] = "Gagal membaca Excel: " . SimpleXLSX::parseError();
                return false;
            }
        }
    } catch (Exception $e) {
        $errors[] = "Error parsing Excel: " . $e->getMessage();
        return false;
    }

    // Ambil semua baris dari Excel
    $rows = $xlsx->rows();

    // Validasi minimal ada header dan 1 data
    if (count($rows) < 2) {
        $errors[] = "File Excel kosong atau tidak ada data (minimal harus ada header + 1 baris data)";
        return false;
    }

    // Ambil header dan hapus dari array data
    $header = $rows[0];
    array_shift($rows);

    // Inisialisasi counter
    $sukses = 0;
    $gagal = 0;

    // Loop setiap baris data
    foreach ($rows as $index => $row) {
        $rowNumber = $index + 2;

        // Skip baris kosong
        if (empty(array_filter($row))) {
            continue;
        }

        // Map data dari Excel ke array dengan ID otomatis
        $data = [
            'id_karyawan'   => bin2hex(random_bytes(8)),
            'nama_lengkap'  => trim($row[0] ?? ''),
            'email'         => trim($row[1] ?? ''),
            'telepon'       => trim($row[2] ?? ''),
            'tempat_lahir'  => trim($row[3] ?? ''),
            'tanggal_lahir' => trim($row[4] ?? ''),
            'jenis_kelamin' => trim($row[5] ?? ''),
            'jabatan_id'    => trim($row[6] ?? ''),
            'gaji_pokok'    => preg_replace('/[^0-9]/', '', $row[7] ?? '0'),
            'foto'          => null,
        ];

        // Validasi data
        $validasi = validateKaryawanData($data);

        if (!$validasi['valid']) {
            $errors[] = "Baris $rowNumber: " . implode(', ', $validasi['errors']);
            $gagal++;
            continue;
        }

        // Cek email duplikat
        if (isEmailExists($data['email'])) {
            $errors[] = "Baris $rowNumber: Email {$data['email']} sudah terdaftar";
            $gagal++;
            continue;
        }

        // Insert ke database
        $insertError = '';
        $hasil = insertKaryawan($validasi['data'], $insertError);

        if ($hasil) {
            $sukses++;
        } else {
            $errors[] = "Baris $rowNumber: Gagal insert ke database" . ($insertError ? " - $insertError" : "");
            $gagal++;
        }
    }

    // Return hasil import
    return [
        'sukses' => $sukses,
        'gagal' => $gagal,
        'total' => $sukses + $gagal
    ];
}

/**
 * ======================================================================
 *  VALIDASI DATA KARYAWAN
 * ======================================================================
 */
function validateKaryawanData($data)
{
    $errors = [];
    
    // Validasi ID Karyawan
    if (empty($data['id_karyawan'])) {
        $errors[] = "ID Karyawan wajib ada (otomatis di-generate)";
    }
    
    // Validasi Nama
    if (empty($data['nama_lengkap'])) {
        $errors[] = "Nama lengkap wajib diisi";
    }
    
    // Validasi Email
    if (empty($data['email'])) {
        $errors[] = "Email wajib diisi";
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid";
    }
    
    // Validasi Telepon
    if (empty($data['telepon'])) {
        $errors[] = "Telepon wajib diisi";
    }
    
    // Validasi Tanggal Lahir
    if (!empty($data['tanggal_lahir'])) {
        $date = DateTime::createFromFormat('Y-m-d', $data['tanggal_lahir']);
        if (!$date || $date->format('Y-m-d') !== $data['tanggal_lahir']) {
            $errors[] = "Format tanggal lahir harus YYYY-MM-DD";
        }
    }
    
    // Validasi Jenis Kelamin
    if (!empty($data['jenis_kelamin'])) {
        if (!in_array($data['jenis_kelamin'], ['Laki-laki', 'Perempuan'])) {
            $errors[] = "Jenis kelamin harus 'Laki-laki' atau 'Perempuan'";
        }
    }
    
    // Validasi Jabatan ID
    if (!empty($data['jabatan_id'])) {
        if (!is_numeric($data['jabatan_id']) || $data['jabatan_id'] <= 0) {
            $errors[] = "Jabatan ID harus berupa angka positif";
        }
    }
    
    // Validasi Gaji
    if (!empty($data['gaji_pokok'])) {
        $gaji = preg_replace('/[^0-9]/', '', $data['gaji_pokok']);
        
        if (!is_numeric($gaji) || $gaji < 0) {
            $errors[] = "Gaji pokok harus berupa angka positif";
        } else {
            $data['gaji_pokok'] = $gaji;
        }
    }
    
    // Return hasil validasi
    return [
        'valid' => empty($errors),
        'errors' => $errors,
        'data' => $data
    ];
}

/**
 * ======================================================================
 *  CEK EMAIL SUDAH ADA ATAU BELUM
 * ======================================================================
 */
function isEmailExists($email)
{
    try {
        $pdo = getPDO();
        $sql = "SELECT COUNT(*) FROM karyawan WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        
        return $stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
        error_log("Check email error: " . $e->getMessage());
        return false;
    }
}

/**
 * ======================================================================
 *  INSERT KARYAWAN KE DATABASE
 * ======================================================================
 */
function insertKaryawan($data, &$errorMessage = '')
{
    try {
        $pdo = getPDO();
        
        // Prepare data untuk insert
        $dataToInsert = [
            ':id_karyawan'   => $data['id_karyawan'] ?? null,
            ':nama_lengkap'  => $data['nama_lengkap'] ?? null,
            ':email'         => $data['email'] ?? null,
            ':telepon'       => $data['telepon'] ?? null,
            ':tempat_lahir'  => !empty($data['tempat_lahir']) ? $data['tempat_lahir'] : null,
            ':tanggal_lahir' => !empty($data['tanggal_lahir']) ? $data['tanggal_lahir'] : null,
            ':jenis_kelamin' => !empty($data['jenis_kelamin']) ? $data['jenis_kelamin'] : null,
            ':jabatan_id'    => !empty($data['jabatan_id']) ? (int)$data['jabatan_id'] : null,
            ':gaji_pokok'    => !empty($data['gaji_pokok']) ? (int)$data['gaji_pokok'] : null,
            ':foto'          => $data['foto'] ?? null
        ];
        
        // Query SQL insert
        $sql = "INSERT INTO karyawan (
                    id_karyawan,
                    nama_lengkap, 
                    email, 
                    telepon, 
                    tempat_lahir, 
                    tanggal_lahir, 
                    jenis_kelamin, 
                    jabatan_id, 
                    gaji_pokok, 
                    foto
                ) VALUES (
                    :id_karyawan,
                    :nama_lengkap,
                    :email,
                    :telepon,
                    :tempat_lahir,
                    :tanggal_lahir,
                    :jenis_kelamin,
                    :jabatan_id,
                    :gaji_pokok,
                    :foto
                )";
        
        // Execute query
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute($dataToInsert);
        
        // Cek hasil insert
        if (!$result) {
            $errorInfo = $stmt->errorInfo();
            $errorMessage = "SQLSTATE: {$errorInfo[0]}, Error: {$errorInfo[2]}";
            error_log("INSERT FAILED: $errorMessage");
            return false;
        }
        
        return true;
        
    } catch (PDOException $e) {
        $errorMessage = $e->getMessage();
        error_log("Insert Karyawan Error: " . $errorMessage);
        
        // Analisis error untuk pesan yang lebih user-friendly
        if (strpos($errorMessage, 'foreign key constraint') !== false || 
            strpos($errorMessage, 'Cannot add or update a child row') !== false) {
            $errorMessage = "Jabatan ID tidak valid atau tidak ada di tabel jabatan";
        } elseif (strpos($errorMessage, "doesn't have a default value") !== false) {
            preg_match("/Column '([^']+)'/", $errorMessage, $matches);
            $column = $matches[1] ?? 'unknown';
            $errorMessage = "Kolom '$column' wajib diisi";
        } elseif (strpos($errorMessage, 'Duplicate entry') !== false) {
            $errorMessage = "Email atau ID sudah terdaftar";
        }
        
        return false;
    }
}
