<?php
// Menyertakan file konfigurasi database untuk koneksi ke database
require_once "../config/database.php";

// Menyertakan file functions untuk operasi CRUD karyawan
require_once "../functions/karyawan.php";

// Menyertakan file functions untuk operasi jabatan
require_once "../functions/jabatan.php";

// Menyertakan file functions untuk pencatatan log aktivitas
require_once "../functions/logger.php";

// Mendapatkan instance koneksi PDO dari fungsi getPDO
$pdo = getPDO();

// Mengambil semua data jabatan dari database untuk ditampilkan di dropdown
$jabatanList = ambilSemuaJabatan();

// Mengecek apakah request method adalah POST, artinya form telah disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Membuat ID karyawan unik menggunakan random bytes yang dikonversi ke hexadecimal
    $id_karyawan = bin2hex(random_bytes(8)); 

    // Inisialisasi variabel fotoName dengan null sebagai default
    $fotoName = null;

    // Mengecek apakah user mengupload file foto
    if (!empty($_FILES['foto']['name'])) {

        // Menentukan ukuran maksimal file yang diperbolehkan yaitu 2 MB dalam bytes
        $maxSize = 2 * 1024 * 1024; // 2 MB

        // Validasi ukuran file, jika melebihi batas maksimal maka proses dihentikan
        if ($_FILES['foto']['size'] > $maxSize) { 
            
            // Blok try-catch untuk menangani error saat logging
            try {
                // Menentukan method HTTP yang digunakan
                $method = 'POST';
                
                // Menentukan endpoint atau path file yang diakses
                $endpoint = '/karyawan/tambah.php';
                
                // Menulis log ke file teks dengan informasi method dan endpoint
                tulisLogFile($method, $endpoint);
                
                // Menyiapkan data request yang akan dicatat ke database
                $requestData = [
                    'file_size' => $_FILES['foto']['size'],
                    'max_size' => 2097152,
                    'file_name' => $_FILES['foto']['name']
                ];
                
                // Menyiapkan data response yang akan dicatat ke database
                $responseData = [
                    'status' => 'error',
                    'message' => 'File foto terlalu besar (max 2MB)',
                    'file_size_mb' => round($_FILES['foto']['size'] / 1024 / 1024, 2)
                ];
                
                // Menulis log error ke database dengan detail request dan response
                tulisLogDB($pdo, $endpoint, $method, $requestData, $responseData);
            } catch (Exception $e) {
                // Jika terjadi error saat logging, catat error ke error log PHP
                error_log("Error logging to DB: " . $e->getMessage());
            }
            
            // Redirect ke halaman tambah dengan parameter error
            header("Location: tambah.php?fileerror=1");
            exit;
        }

        // Mengambil ekstensi file dari nama file yang diupload
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        
        // Membuat nama file unik dengan menggabungkan uniqid dan ekstensi file
        $fotoName = uniqid() . "." . strtolower($ext);

        // Mengecek apakah folder upload/foto sudah ada
        if (!is_dir("upload/foto")) {
            // Jika belum ada, buat folder dengan permission 0777 secara rekursif
            mkdir("upload/foto", 0777, true);
        }

        // Memindahkan file dari temporary location ke folder upload/foto
        move_uploaded_file($_FILES['foto']['tmp_name'], "upload/foto/" . $fotoName);
    }

    // Menyiapkan array data karyawan yang akan disimpan ke database
    $data = [
        "id_karyawan"   => $id_karyawan,
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

    // Inisialisasi variabel success dengan false
    $success = false;
    
    // Memanggil fungsi buatKaryawan untuk menyimpan data ke database
    if (buatKaryawan($data)) {
        // Jika berhasil, set success menjadi true
        $success = true;
        
        // Blok try-catch untuk logging sukses
        try {
            // Menentukan method HTTP
            $method = 'POST';
            
            // Menentukan endpoint
            $endpoint = '/karyawan/tambah.php';
            
            // Menulis log ke file teks
            tulisLogFile($method, $endpoint);
            
            // Menyiapkan data request yang akan dicatat
            $requestData = [
                'id_karyawan' => $id_karyawan,
                'nama_lengkap' => $data['nama_lengkap'],
                'email' => $data['email'],
                'jabatan_id' => $data['jabatan_id'],
                'has_photo' => !empty($fotoName)
            ];
            
            // Menyiapkan data response sukses
            $responseData = [
                'status' => 'success',
                'message' => 'Karyawan berhasil ditambahkan',
                'new_employee' => $data
            ];
            
            // Menulis log sukses ke database
            tulisLogDB($pdo, $endpoint, $method, $requestData, $responseData);
        } catch (Exception $e) {
            // Jika terjadi error saat logging, catat ke error log
            error_log("Error logging to DB: " . $e->getMessage());
        }
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Karyawan</title>
    <!-- Menyertakan Tailwind CSS dari CDN untuk styling -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<!-- Body dengan background biru muda, tinggi minimal full screen, flex center, dan padding -->
<body class="bg-blue-50 min-h-screen flex items-center justify-center p-6">

<!-- Container utama dengan lebar maksimal, background putih, rounded corners, shadow, padding, dan border -->
<div class="w-full max-w-xl bg-white rounded-xl shadow-lg p-8 border border-blue-100">

    <!-- Judul halaman dengan styling bold, warna biru, dan center alignment -->
    <h1 class="text-2xl font-bold text-blue-700 mb-1 text-center">
        Tambah Karyawan
    </h1>

    <!-- Teks instruksi untuk user -->
    <p class="text-center text-gray-600 mb-4">Gulir kebawah untuk mengisi formulir.</p>

    <!-- Form dengan method POST dan enctype multipart untuk upload file -->
    <form id="formKaryawan" method="POST" enctype="multipart/form-data" class="space-y-4">

        <!-- Input field untuk Nama Lengkap -->
        <div>
            <label class="text-sm font-semibold text-gray-700">Nama Lengkap</label>
            <input name="nama_lengkap" class="mt-1 border border-blue-300 p-2 rounded-lg w-full focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Nama Lengkap" required>
        </div>

        <!-- Input field untuk Email dengan type email untuk validasi -->
        <div>
            <label class="text-sm font-semibold text-gray-700">Email</label>
            <input name="email" type="email" class="mt-1 border border-blue-300 p-2 rounded-lg w-full focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Email" required>
        </div>

        <!-- Input field untuk Telepon -->
        <div>
            <label class="text-sm font-semibold text-gray-700">Telepon</label>
            <input name="telepon" class="mt-1 border border-blue-300 p-2 rounded-lg w-full focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Telepon" required>
        </div>

        <!-- Input field untuk Tempat Lahir -->
        <div>
            <label class="text-sm font-semibold text-gray-700">Tempat Lahir</label>
            <input name="tempat_lahir" class="mt-1 border border-blue-300 p-2 rounded-lg w-full focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Tempat Lahir" required>
        </div>

        <!-- Input field untuk Tanggal Lahir dengan type date -->
        <div>
            <label class="text-sm font-semibold text-gray-700">Tanggal Lahir</label>
            <input type="date" name="tanggal_lahir" class="mt-1 border border-blue-300 p-2 rounded-lg w-full focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
        </div>

        <!-- Dropdown untuk memilih Jenis Kelamin -->
        <div>
            <label class="text-sm font-semibold text-gray-700">Jenis Kelamin</label>
            <select name="jenis_kelamin" class="mt-1 border border-blue-300 p-2 rounded-lg w-full focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                <option value="">-- Pilih --</option>
                <option value="Laki-laki">Laki-laki</option>
                <option value="Perempuan">Perempuan</option>
            </select>
        </div>

        <!-- Dropdown untuk memilih Jabatan yang di-generate dari database -->
        <div>
            <label class="text-sm font-semibold text-gray-700">Jabatan</label>
            <select name="jabatan_id" class="mt-1 border border-blue-300 p-2 rounded-lg w-full focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                <option value="">-- Pilih Jabatan --</option>
                <?php foreach($jabatanList as $j): ?>
                    <!-- Loop untuk menampilkan setiap jabatan dari database -->
                    <option value="<?= $j['id_jabatan'] ?>"><?= $j['nama_jabatan'] ?></option>
                <?php endforeach ?>
            </select>
        </div>

        <!-- Input field untuk Gaji Pokok dengan type number -->
        <div>
            <label class="text-sm font-semibold text-gray-700">Gaji Pokok</label>
            <input type="number" name="gaji_pokok" class="mt-1 border border-blue-300 p-2 rounded-lg w-full focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Gaji Pokok" required>
        </div>

        <!-- Input file untuk upload foto karyawan -->
        <div>
            <label class="text-sm font-semibold text-gray-700">Foto</label>
            <input type="file" name="foto" class="mt-1 border border-blue-300 focus:ring-blue-500 focus:border-blue-500 p-2 rounded-lg w-full">
        </div>

        <!-- Container untuk tombol Kembali dan Simpan -->
        <div class="flex gap-3 mt-4">
            
            <!-- Link untuk kembali ke halaman list karyawan -->
            <a href="list.php" class="bg-blue-600 text-white px-4 py-2 rounded">
               ⮜ Kembali
            </a>

            <!-- Tombol untuk trigger modal konfirmasi sebelum submit -->
            <button type="button" id="btnSimpan" class="bg-blue-600 text-white px-4 py-2 rounded">
                Simpan ⎙
            </button>
        </div>

    </form>

<!-- Modal untuk menampilkan error ketika field tidak lengkap diisi -->
<div id="modalError" class="fixed inset-0 hidden bg-black/50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 max-w-sm shadow-lg text-center">
        <h2 class="text-lg font-semibold text-red-600">Form Tidak Lengkap</h2>
        <p class="mt-2 text-gray-700">Mohon isi semua field wajib.</p>
        <button onclick="closeModal('modalError')" class="mt-4 bg-red-500 text-white px-4 py-2 rounded">OK</button>
    </div>
</div>

<!-- Modal untuk konfirmasi sebelum menyimpan data -->
<div id="modalConfirm" class="fixed inset-0 hidden bg-black/50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 max-w-sm shadow-lg text-center">
        <h2 class="text-lg font-semibold text-blue-600">Konfirmasi</h2>
        <p class="mt-2 text-gray-700">Yakin ingin menyimpan data?</p>

        <!-- Tombol untuk konfirmasi atau membatalkan -->
        <div class="mt-4 flex justify-center gap-3">
            <button onclick="submitForm()" class="bg-blue-600 text-white px-4 py-2 rounded">Ya, Simpan</button>
            <button onclick="closeModal('modalConfirm')" class="bg-gray-400 text-white px-4 py-2 rounded">Batal</button>
        </div>
    </div>
</div>

<!-- Modal untuk error ketika ukuran file foto terlalu besar -->
<div id="modalFileError" class="fixed inset-0 hidden bg-black/50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 max-w-sm shadow-lg text-center">
        <h2 class="text-lg font-semibold text-red-600">Ukuran File Terlalu Besar</h2>
        <p class="mt-2 text-gray-700">Ukuran foto maksimal 2MB.</p>
        <button onclick="closeModal('modalFileError')" class="mt-4 bg-red-500 text-white px-4 py-2 rounded">
            OK
        </button>
    </div>
</div>

<!-- Modal untuk menampilkan pesan sukses setelah data berhasil disimpan -->
<div id="modalSuccess" class="fixed inset-0 hidden bg-black/50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 max-w-sm shadow-lg text-center">
        <h2 class="text-lg font-semibold text-green-600">Berhasil!</h2>
        <p class="mt-2 text-gray-700">Data karyawan berhasil ditambahkan.</p>

        <!-- Tombol untuk kembali ke list atau tambah data lagi -->
        <div class="mt-4 flex justify-center gap-3">
            <a href="list.php" class="bg-blue-600 text-white px-4 py-2 rounded">Kembali ke Daftar</a>
            <button onclick="closeModal('modalSuccess')" class="bg-gray-400 text-white px-4 py-2 rounded">
                Tambah Lagi
            </button>
        </div>
    </div>
</div>

<script>
// Fungsi untuk membuka modal dengan menghilangkan class hidden
function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
}

// Fungsi untuk menutup modal dengan menambahkan class hidden
function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
}

// Event listener untuk tombol Simpan
document.getElementById("btnSimpan").addEventListener("click", function () {
    // Mengambil reference ke form
    const form = document.getElementById("formKaryawan");
    
    // Array berisi nama-nama field yang wajib diisi
    const fields = ["nama_lengkap", "email", "telepon", "tempat_lahir", "tanggal_lahir", "jenis_kelamin", "jabatan_id", "gaji_pokok"];

    // Loop untuk mengecek setiap field wajib
    for (let f of fields) {
        // Jika ada field yang kosong, tampilkan modal error
        if (form[f].value.trim() === "") {
            openModal("modalError");
            return;
        }
    }

    // Jika semua field terisi, tampilkan modal konfirmasi
    openModal("modalConfirm");
});

// Fungsi untuk submit form setelah user konfirmasi
function submitForm() {
    document.getElementById("formKaryawan").submit();
}

<?php if (isset($_GET['fileerror'])): ?>
// Jika ada parameter fileerror di URL, buka modal error file
openModal('modalFileError');
<?php endif; ?>

<?php if (isset($success) && $success === true): ?>
// Jika proses simpan sukses, buka modal sukses
openModal('modalSuccess');
<?php endif; ?>
</script>

</div>

</body>

</html>
