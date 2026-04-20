<?php
// Menyertakan file konfigurasi database untuk koneksi ke database
require_once "../config/database.php";

// Menyertakan file functions untuk operasi CRUD karyawan
require_once "../functions/karyawan.php";

// Menyertakan file functions untuk operasi jabatan
require_once "../functions/jabatan.php";

// Menyertakan file functions untuk pencatatan log aktivitas
require_once "../functions/logger.php";

// Mendapatkan instance koneksi PDO dari fungsi getPDO untuk keperluan logging
$pdo = getPDO();

// Ambil ID karyawan dari POST atau GET, prioritas dari POST
$id = $_POST['id'] ?? $_GET['id'] ?? null;

// Validasi apakah ID karyawan ada, jika tidak tampilkan error dan hentikan eksekusi
if (!$id) die("ID karyawan tidak ditemukan.");

// Ambil data karyawan berdasarkan ID untuk ditampilkan di form
$data = ambilKaryawanById($id);

// Validasi apakah data karyawan ditemukan, jika tidak tampilkan error dan hentikan eksekusi
if (!$data) die("Data tidak ditemukan.");

// Simpan data lama untuk keperluan logging perbandingan sebelum dan sesudah update
$dataLama = $data;

// Ambil semua data jabatan dari database untuk ditampilkan di dropdown
$jabatanList = ambilSemuaJabatan();

// Inisialisasi variabel untuk status update dan error file
$success = false;
$fileError = false;

// Cek apakah request method adalah POST dan ada data nama_lengkap yang dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nama_lengkap'])) {

    // Default menggunakan foto lama jika tidak ada foto baru yang diupload
    $fotoName = $data['foto'];

    // Cek apakah user mengupload foto baru
    if (!empty($_FILES['foto']['name'])) {

        // Menentukan ukuran maksimal file yang diperbolehkan yaitu 2 MB dalam bytes
        $maxSize = 2 * 1024 * 1024; // 2MB

        // Validasi ukuran file, jika melebihi batas maksimal maka set fileError true
        if ($_FILES['foto']['size'] > $maxSize) {
            $fileError = true;

        } else {

            // Hapus foto lama dari server jika ada dan file tersebut exists
            if (!empty($data['foto']) && file_exists("upload/foto/" . $data['foto'])) {
                unlink("upload/foto/" . $data['foto']);
            }

            // Mengambil ekstensi file dan convert ke lowercase
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            
            // Membuat nama file unik dengan menggabungkan uniqid dan ekstensi file
            $fotoName = uniqid() . "." . $ext;

            // Cek apakah folder upload/foto ada, jika tidak buat folder tersebut
            if (!is_dir("upload/foto")) mkdir("upload/foto", 0777, true);

            // Memindahkan file dari temporary location ke folder upload/foto
            move_uploaded_file($_FILES['foto']['tmp_name'], "upload/foto/" . $fotoName);
        }
    }

    // Jika tidak ada error upload foto, lanjutkan proses update data
    if (!$fileError) {

        // Menyiapkan array data yang akan diupdate ke database
        $updateData = [
            "nama_lengkap"  => $_POST['nama_lengkap'],
            "email"         => $_POST['email'],
            "telepon"       => $_POST['telepon'],
            "tempat_lahir"  => $_POST['tempat_lahir'],
            "tanggal_lahir" => $_POST['tanggal_lahir'],
            "jenis_kelamin" => $_POST['jenis_kelamin'],
            "jabatan_id"    => $_POST['jabatan_id'],
            "gaji_pokok"    => $_POST['gaji_pokok'],
            "foto"          => $fotoName
        ];

        // Memanggil fungsi updateKaryawan untuk menyimpan perubahan ke database
        if (updateKaryawan($id, $updateData)) {
            // Set success menjadi true jika update berhasil
            $success = true;
            
            // Reload data terbaru setelah update untuk ditampilkan di form
            $data = ambilKaryawanById($id);

            // Blok try-catch untuk logging update sukses
            try {
                // Menentukan method HTTP
                $method = 'POST';
                
                // Menentukan endpoint
                $endpoint = '/karyawan/update.php';
                
                // Menulis log ke file teks
                tulisLogFile($method, $endpoint);
                
                // Menyiapkan data request dengan perbandingan data lama vs baru
                $requestData = [
                    'id_karyawan' => $id,
                    'data_lama' => $dataLama,
                    'data_baru' => $updateData,
                    'foto_updated' => !empty($_FILES['foto']['name'])
                ];
                
                // Menyiapkan data response sukses dengan field yang diupdate
                $responseData = [
                    'status' => 'success',
                    'message' => 'Data karyawan berhasil diupdate',
                    'updated_fields' => array_keys($updateData)
                ];
                
                // Menulis log sukses ke database
                tulisLogDB($pdo, $endpoint, $method, $requestData, $responseData);
            } catch (Exception $e) {
                // Jika terjadi error saat logging, catat error ke error log PHP
                error_log("Error logging to DB: " . $e->getMessage());
            }
        }
    } else {
        // Blok try-catch untuk logging error file terlalu besar
        try {
            // Menentukan method HTTP
            $method = 'POST';
            
            // Menentukan endpoint
            $endpoint = '/karyawan/update.php';
            
            // Menulis log ke file teks
            tulisLogFile($method, $endpoint);
            
            // Menyiapkan data request dengan informasi ukuran file
            $requestData = [
                'id_karyawan' => $id,
                'file_size' => $_FILES['foto']['size'],
                'max_size' => 2097152
            ];
            
            // Menyiapkan data response error dengan detail ukuran file
            $responseData = [
                'status' => 'error',
                'message' => 'File foto terlalu besar (max 2MB)',
                'file_size_mb' => round($_FILES['foto']['size'] / 1024 / 1024, 2)
            ];
            
            // Menulis log error ke database
            tulisLogDB($pdo, $endpoint, $method, $requestData, $responseData);
        } catch (Exception $e) {
            // Jika terjadi error saat logging, catat error ke error log PHP
            error_log("Error logging to DB: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Karyawan</title>
    <!-- Menyertakan Tailwind CSS dari CDN untuk styling -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<!-- Body dengan background biru muda, tinggi minimal full screen, flex center, dan padding -->
<body class="bg-blue-50 min-h-screen flex items-center justify-center p-6">

<!-- Container utama dengan lebar maksimal, background putih, rounded corners, shadow, padding, dan border -->
<div class="w-full max-w-xl bg-white rounded-xl shadow-lg p-8 border border-blue-100">

    <!-- Judul halaman dengan styling bold, warna biru, dan center alignment -->
    <h1 class="text-2xl font-bold text-blue-700 mb-1 text-center">Edit Karyawan</h1>
    
    <!-- Deskripsi singkat di bawah judul -->
    <p class="text-center text-gray-600 mb-4">Perbarui informasi karyawan.</p>

    <!-- Form dengan method POST dan enctype multipart untuk upload file -->
    <form id="formEdit" method="POST" enctype="multipart/form-data" class="space-y-4">

        <!-- Hidden input untuk menyimpan ID karyawan yang akan diupdate -->
        <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">

        <!-- Input field untuk Nama Lengkap dengan value dari database -->
        <div>
            <label class="text-sm font-semibold text-gray-700">Nama Lengkap</label>
            <input name="nama_lengkap" value="<?= htmlspecialchars($data['nama_lengkap']) ?>"
                   class="mt-1 border border-blue-300 p-2 rounded-lg w-full" required>
        </div>

        <!-- Input field untuk Email dengan type email dan value dari database -->
        <div>
            <label class="text-sm font-semibold text-gray-700">Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($data['email']) ?>"
                   class="mt-1 border border-blue-300 p-2 rounded-lg w-full" required>
        </div>

        <!-- Input field untuk Telepon dengan value dari database -->
        <div>
            <label class="text-sm font-semibold text-gray-700">Telepon</label>
            <input name="telepon" value="<?= htmlspecialchars($data['telepon']) ?>"
                   class="mt-1 border border-blue-300 p-2 rounded-lg w-full" required>
        </div>

        <!-- Input field untuk Tempat Lahir dengan value dari database -->
        <div>
            <label class="text-sm font-semibold text-gray-700">Tempat Lahir</label>
            <input name="tempat_lahir" value="<?= htmlspecialchars($data['tempat_lahir']) ?>"
                   class="mt-1 border border-blue-300 p-2 rounded-lg w-full" required>
        </div>

        <!-- Input field untuk Tanggal Lahir dengan type date dan value dari database -->
        <div>
            <label class="text-sm font-semibold text-gray-700">Tanggal Lahir</label>
            <input type="date" name="tanggal_lahir" value="<?= htmlspecialchars($data['tanggal_lahir']) ?>"
                   class="mt-1 border border-blue-300 p-2 rounded-lg w-full" required>
        </div>

        <!-- Dropdown untuk Jenis Kelamin dengan selected state sesuai data dari database -->
        <div>
            <label class="text-sm font-semibold text-gray-700">Jenis Kelamin</label>
            <select name="jenis_kelamin" class="mt-1 border border-blue-300 p-2 rounded-lg w-full">
                <!-- Option Laki-laki dengan selected jika data dari database adalah Laki-laki -->
                <option value="Laki-laki" <?= $data['jenis_kelamin']=='Laki-laki'?'selected':'' ?>>Laki-laki</option>
                <!-- Option Perempuan dengan selected jika data dari database adalah Perempuan -->
                <option value="Perempuan" <?= $data['jenis_kelamin']=='Perempuan'?'selected':'' ?>>Perempuan</option>
            </select>
        </div>

        <!-- Dropdown untuk Jabatan yang di-generate dari database dengan selected state -->
        <div>
            <label class="text-sm font-semibold text-gray-700">Jabatan</label>
            <select name="jabatan_id" class="mt-1 border border-blue-300 p-2 rounded-lg w-full">
                <?php foreach ($jabatanList as $j): ?>
                    <!-- Loop untuk menampilkan setiap jabatan dengan selected jika ID cocok -->
                    <option value="<?= $j['id_jabatan'] ?>" <?= $data['jabatan_id']==$j['id_jabatan']?'selected':'' ?>>
                        <?= htmlspecialchars($j['nama_jabatan']) ?>
                    </option>
                <?php endforeach ?>
            </select>
        </div>

        <!-- Input field untuk Gaji Pokok dengan type number dan value dari database -->
        <div>
            <label class="text-sm font-semibold text-gray-700">Gaji Pokok</label>
            <input type="number" name="gaji_pokok" value="<?= htmlspecialchars($data['gaji_pokok']) ?>"
                   class="mt-1 border border-blue-300 p-2 rounded-lg w-full" required>
        </div>

        <!-- Input file untuk upload foto baru (opsional) -->
        <div>
            <label class="text-sm font-semibold text-gray-700">Foto Baru (Opsional)</label>
            <input type="file" name="foto" class="mt-1 border border-blue-300 p-2 rounded-lg w-full">

            <?php if(!empty($data['foto'])): ?>
                <!-- Tampilkan preview foto lama jika ada -->
                <img src="upload/foto/<?= $data['foto'] ?>" class="mt-2 w-24 h-24 rounded-lg border">
            <?php endif; ?>
        </div>

        <!-- Container untuk tombol Kembali dan Update -->
        <div class="flex gap-3 mt-4">
            <!-- Link untuk kembali ke halaman list karyawan -->
            <a href="list.php" class="bg-blue-600 text-white px-4 py-2 rounded">⮜ Kembali</a>
            
            <!-- Tombol untuk membuka modal konfirmasi sebelum submit -->
            <button type="button" onclick="openModal('modalConfirm')" class="bg-blue-600 text-white px-4 py-2 rounded">
                Update ✎
            </button>
        </div>

    </form>

</div>

<!-- Modal untuk konfirmasi sebelum mengupdate data -->
<div id="modalConfirm" class="fixed inset-0 hidden bg-black/50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 max-w-sm text-center">
        <h2 class="text-lg font-semibold text-blue-600">Konfirmasi</h2>
        <p class="mt-2 text-gray-700">Yakin ingin mengupdate data ini?</p>

        <!-- Container untuk tombol konfirmasi -->
        <div class="mt-4 flex justify-center gap-3">
            <!-- Tombol untuk submit form setelah konfirmasi -->
            <button onclick="submitForm()" class="bg-blue-600 text-white px-4 py-2 rounded">Ya, Update</button>
            <!-- Tombol untuk membatalkan dan menutup modal -->
            <button onclick="closeModal('modalConfirm')" class="bg-gray-500 text-white px-4 py-2 rounded">Batal</button>
        </div>
    </div>
</div>

<!-- Modal untuk menampilkan pesan sukses setelah update berhasil -->
<div id="modalSuccess" class="fixed inset-0 hidden bg-black/50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 max-w-sm text-center">
        <h2 class="text-lg font-semibold text-green-600">Berhasil!</h2>
        <p class="mt-2 text-gray-700">Data berhasil diperbarui.</p>

        <!-- Link untuk kembali ke halaman list setelah update sukses -->
        <a href="list.php" class="mt-4 bg-blue-600 text-white px-4 py-2 rounded inline-block">Kembali ke Daftar</a>
    </div>
</div>

<!-- Modal untuk error ketika ukuran file foto terlalu besar -->
<div id="modalFileError" class="fixed inset-0 hidden bg-black/50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 max-w-sm text-center">
        <h2 class="text-lg font-semibold text-red-600">Ukuran File Terlalu Besar</h2>
        <p class="mt-2 text-gray-700">Ukuran maksimal foto adalah 2MB.</p>
        <!-- Tombol untuk menutup modal error -->
        <button onclick="closeModal('modalFileError')" class="mt-4 bg-red-500 text-white px-4 py-2 rounded">OK</button>
    </div>
</div>

<script>
// Fungsi untuk membuka modal dengan menghilangkan class hidden
function openModal(id){ document.getElementById(id).classList.remove('hidden'); }

// Fungsi untuk menutup modal dengan menambahkan class hidden
function closeModal(id){ document.getElementById(id).classList.add('hidden'); }

// Fungsi untuk submit form setelah user konfirmasi
function submitForm(){
    document.getElementById("formEdit").submit();
}

<?php if($fileError): ?> 
    // Jika ada error file (ukuran terlalu besar), buka modal error
    openModal('modalFileError'); 
<?php endif; ?>

<?php if($success): ?> 
    // Jika update sukses, buka modal sukses
    openModal('modalSuccess'); 
<?php endif; ?>
</script>

</body>
</html>