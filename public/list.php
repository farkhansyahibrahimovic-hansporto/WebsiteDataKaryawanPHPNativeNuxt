<?php
// Menyertakan file konfigurasi database untuk koneksi ke database
include "../config/database.php";

// Menyertakan file functions untuk operasi CRUD karyawan
include "../functions/karyawan.php";

// Menyertakan file functions untuk operasi jabatan
include "../functions/jabatan.php";

// Menyertakan file functions untuk pencatatan log aktivitas
include "../functions/logger.php";

// Mendapatkan instance koneksi PDO dari fungsi getPDO untuk keperluan logging
$pdo = getPDO();

// Membuat log request setiap user mengakses halaman endpoint/list.php
// Mengambil method HTTP yang digunakan untuk request ini
$method = $_SERVER['REQUEST_METHOD'];

// Menentukan endpoint atau path halaman yang diakses
$endpoint = '/karyawan/list.php';

// Catat akses halaman ke file log
tulisLogFile($method, $endpoint);

// Catat akses halaman ke database log dengan blok try-catch untuk error handling
try {
    // Menyiapkan data request yang berisi informasi halaman dan filter yang digunakan
    $requestData = [
        'page' => $_GET['halaman'] ?? 1,
        'filters' => array_filter($_GET, fn($key) => $key !== 'halaman', ARRAY_FILTER_USE_KEY)
    ];
    
    // Menulis log awal ke database (response masih null karena belum diproses)
    tulisLogDB($pdo, $endpoint, $method, $requestData, null);
} catch (Exception $e) {
    // Abaikan error logging agar tidak mengganggu flow aplikasi
    error_log("Error logging to DB: " . $e->getMessage());
}

// Penyesuaian untuk paginasi dan filter untuk search
// Menentukan jumlah data per halaman
$perPage = 5;

// Mengambil nomor halaman dari parameter GET, default halaman 1
$page = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;

// Memastikan halaman minimal adalah 1
$page = max(1, $page);

// Filter disesuaikan dengan function karyawan.php
// Array filters yang kosong, nantinya akan diisi sesuai dengan filter yang diterapkan
$filters = [];

// Filter pencarian menggunakan "cari" dari parameter GET
// Jika ada keyword pencarian, simpan ke filter dengan key "q"
if (!empty($_GET['cari'])) {
    $filters['q'] = $_GET['cari'];
}

// Filter jenis kelamin
// Jika user memilih filter jenis kelamin, simpan ke array filters
if (!empty($_GET['jenis_kelamin'])) {
    $filters['jenis_kelamin'] = $_GET['jenis_kelamin'];
}

// Filter jabatan
// Jika user memilih filter jabatan, simpan ke array filters
if (!empty($_GET['jabatan_id'])) {
    $filters['jabatan_id'] = $_GET['jabatan_id'];
}

// Filter gaji/pendapatan menggunakan min-max
// Jika ada filter gaji minimum, simpan ke array filters
if (!empty($_GET['salary_min'])) {
    $filters['salary_min'] = $_GET['salary_min'];
}

// Jika ada filter gaji maksimum, simpan ke array filters
if (!empty($_GET['salary_max'])) {
    $filters['salary_max'] = $_GET['salary_max'];
}

// Filter umur menggunakan min-max
// Jika ada filter umur minimum, simpan ke array filters
if (!empty($_GET['age_min'])) {
    $filters['age_min'] = $_GET['age_min'];
}

// Jika ada filter umur maksimum, simpan ke array filters
if (!empty($_GET['age_max'])) {
    $filters['age_max'] = $_GET['age_max'];
}

// Mengambil keyword pencarian dari parameter GET untuk ditampilkan di input search
$cari = $_GET['cari'] ?? "";

// Ambil data karyawan dengan paginasi dan filter yang sudah ditentukan
$semuaKaryawan = ambilListKaryawanPaginate($filters, $perPage, $page);

// Hitung total halaman berdasarkan filter dan jumlah data per halaman
$totalHalaman = hitungTotalHalaman($filters, $perPage);

// Menghitung nomor urut data pada halaman aktif
$no = ($page - 1) * $perPage + 1; 

// Log response menggunakan query berdasarkan filter
// Mencatat hasil query ke database log
if (isset($pdo)) {
    try {
        // Menyiapkan data request dengan halaman dan filter aktif
        $requestData = [
            'page' => $page,
            'filters' => $filters
        ];
        
        // Menyiapkan data response dengan informasi hasil query
        $responseData = [
            'total_data' => count($semuaKaryawan),
            'total_halaman' => $totalHalaman,
            'halaman_aktif' => $page
        ];
        
        // Update log di database dengan response yang berisi hasil query
        tulisLogDB($pdo, $endpoint, $method, $requestData, $responseData);
    } catch (Exception $e) {
        // Jika terjadi error saat update log, catat ke error log
        error_log("Error updating log response: " . $e->getMessage());
    }
}

// Fungsi untuk membuild URL export dengan filter
// Fungsi ini akan menambahkan parameter filter ke URL export PDF dan Excel
function buildExportURL($baseURL, $filters) {
    // Array untuk menampung parameter URL
    $params = [];
    
    // Konversi 'q' menjadi 'q' (sudah sesuai dengan API)
    // Jika ada filter pencarian, tambahkan ke params
    if (!empty($filters['q'])) {
        $params['q'] = $filters['q'];
    }
    
    // Jika ada filter jenis kelamin, tambahkan ke params
    if (!empty($filters['jenis_kelamin'])) {
        $params['jenis_kelamin'] = $filters['jenis_kelamin'];
    }
    
    // Jika ada filter jabatan, tambahkan ke params
    if (!empty($filters['jabatan_id'])) {
        $params['jabatan_id'] = $filters['jabatan_id'];
    }
    
    // Jika ada filter gaji minimum, tambahkan ke params
    if (!empty($filters['salary_min'])) {
        $params['salary_min'] = $filters['salary_min'];
    }
    
    // Jika ada filter gaji maksimum, tambahkan ke params
    if (!empty($filters['salary_max'])) {
        $params['salary_max'] = $filters['salary_max'];
    }
    
    // Jika ada filter umur minimum, tambahkan ke params
    if (!empty($filters['age_min'])) {
        $params['age_min'] = $filters['age_min'];
    }
    
    // Jika ada filter umur maksimum, tambahkan ke params
    if (!empty($filters['age_max'])) {
        $params['age_max'] = $filters['age_max'];
    }
    
    // Jika tidak ada filter yang aktif, kembalikan URL dasar tanpa parameter
    if (empty($params)) {
        return $baseURL;
    }
    
    // Jika ada filter, build URL dengan query string dari params
    return $baseURL . '?' . http_build_query($params);
}

// Membuat URL export PDF dengan filter yang aktif
$exportPDFUrl = buildExportURL('../api/karyawan/export_pdf.php', $filters);

// Membuat URL export Excel dengan filter yang aktif
$exportExcelUrl = buildExportURL('../api/karyawan/export_excel.php', $filters);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Karyawan</title>
    <!-- Menyertakan Tailwind CSS dari CDN untuk styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    </head>
<!-- Body dengan background biru muda, tinggi minimal full screen, dan padding -->
<body class="bg-blue-50 min-h-screen p-6">
<!-- Container utama dengan lebar maksimal, background putih, shadow, rounded corners, dan padding -->
<div class="max-w-7xl mx-auto bg-white shadow-lg rounded-xl p-6">

    <!-- Judul halaman untuk form daftar karyawan -->
    <h2 class="text-2xl font-bold text-blue-600 text-center">
        Data Karyawan
    </h2>

    <!-- Deskripsi singkat dibawah judul -->
    <p class="text-gray-600 mt-1 mb-4 text-center"> 
        Aetmin HRD sudah datang! Mari berkeliling.
    </p>

    <!-- Form untuk fitur search dan filter dengan method GET -->
    <form method="GET">

    <!-- Fitur Search atau cari -->
    <div class="flex gap-2 mb-5">
        <!-- Input field untuk pencarian dengan value yang diambil dari GET parameter -->
        <input type="text" name="cari" value="<?= htmlspecialchars($cari) ?>"
            class="border border-blue-300 rounded-lg px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Cari nama, email, atau telepon...">

        <!-- Tombol submit untuk melakukan pencarian -->
        <button type="submit" 
            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">🔍︎</button>

        <!-- Link untuk reset filter dan kembali ke halaman awal tanpa filter -->
        <a href="list.php"
            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">⭮</a>

        <!-- Tombol untuk toggle tampilan filter box -->
        <button type="button" onclick="toggleFilter()"
        class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
        ☰
        </button>
    </div>

    <!-- FILTER BOX yang default hidden, akan muncul ketika tombol filter diklik -->
    <div id="filter-box" class="hidden bg-blue-50 p-4 rounded-lg grid grid-cols-2 md:grid-cols-4 gap-3">

        <!-- Dropdown filter jenis kelamin dengan value yang dipertahankan sesuai filter aktif -->
        <select name="jenis_kelamin" class="border border-blue-300 rounded-lg px-3 py-2">
            <option value="">Semua JK</option>
            <option value="Laki-laki" <?= ($filters['jenis_kelamin'] ?? '') == 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
            <option value="Perempuan" <?= ($filters['jenis_kelamin'] ?? '') == 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
        </select>

        <!-- Dropdown filter jabatan yang di-generate dari database -->
        <select name="jabatan_id" class="border border-blue-300 rounded-lg px-3 py-2">
            <option value="">Semua Jabatan</option>
            <?php foreach (ambilSemuaJabatan() as $j): ?>
                <!-- Loop untuk menampilkan setiap jabatan dengan selected state jika sedang difilter -->
                <option value="<?= $j['id_jabatan'] ?>" 
                    <?= ($filters['jabatan_id'] ?? '') == $j['id_jabatan'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($j['nama_jabatan']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <!-- Input untuk filter gaji minimum dengan value yang dipertahankan -->
        <input type="number" name="salary_min" placeholder="Gaji Min"
            value="<?= $filters['salary_min'] ?? '' ?>"
            class="border border-blue-300 rounded-lg px-3 py-2">

        <!-- Input untuk filter gaji maksimum dengan value yang dipertahankan -->
        <input type="number" name="salary_max" placeholder="Gaji Max"
            value="<?= $filters['salary_max'] ?? '' ?>"
            class="border border-blue-300 rounded-lg px-3 py-2">

        <!-- Input untuk filter umur minimum dengan value yang dipertahankan -->
        <input type="number" name="age_min" placeholder="Umur Min"
            value="<?= $filters['age_min'] ?? '' ?>"
            class="border border-blue-300 rounded-lg px-3 py-2">

        <!-- Input untuk filter umur maksimum dengan value yang dipertahankan -->
        <input type="number" name="age_max" placeholder="Umur Max"
            value="<?= $filters['age_max'] ?? '' ?>"
            class="border border-blue-300 rounded-lg px-3 py-2">

        <!-- Tombol submit untuk menerapkan semua filter yang dipilih -->
        <button type="submit"
            class="col-span-2 md:col-span-4 bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">
            Terapkan Filter
        </button>

    </div>

</form>


    <!-- Container untuk table dengan overflow hidden dan shadow -->
<div class="overflow-hidden shadow rounded-lg">
    <!-- Table untuk menampilkan data karyawan dengan border collapse -->
    <table class="w-full border border-gray-300 border-collapse">
        <!-- Header table dengan background biru dan text putih -->
        <thead class="bg-blue-600 text-white">
            <tr>
                <!-- Kolom nomor urut -->
                <th class="py-3 px-4 text-center border">No</th>
                <!-- Kolom foto karyawan -->
                <th class="py-3 px-4 text-center border">Gambar</th>
                <!-- Kolom nama lengkap -->
                <th class="py-3 px-4 text-center border">Nama</th>
                <!-- Kolom email -->
                <th class="py-3 px-4 text-center border">Email</th>
                <!-- Kolom telepon -->
                <th class="py-3 px-4 text-center border">Telepon</th>
                <!-- Kolom jenis kelamin -->
                <th class="py-3 px-4 text-center border">JK</th>
                <!-- Kolom jabatan -->
                <th class="py-3 px-4 text-center border">Jabatan</th>
                <!-- Kolom gaji pokok -->
                <th class="py-3 px-4 text-center border">Gaji</th>
                <!-- Kolom aksi untuk button detail, edit, dan hapus -->
                <th class="py-3 px-4 text-center border">Aksi</th>
            </tr>
        </thead>

        <tbody>

            <?php if (empty($semuaKaryawan)): ?>
                <!-- Jika tidak ada data karyawan, tampilkan pesan -->
                <tr>
                    <td colspan="9" class="text-center py-3 text-gray-500">
                        Tidak ada data ditemukan.
                    </td>
                </tr>
            <?php endif; ?>

            <?php foreach ($semuaKaryawan as $k): ?>
            <!-- Loop untuk menampilkan setiap data karyawan dalam row -->
            <tr class="border-b hover:bg-blue-50">
                <!-- Kolom nomor urut dengan auto increment -->
                <td class="py-3 px-4 border text-center"><?= $no++; ?></td>
                <!-- Kolom foto karyawan -->
                <td class="py-3 px-4 border text-center">
                    <?php if (!empty($k['foto'])): ?>
                        <!-- Jika ada foto, tampilkan gambar dengan ukuran dan styling -->
                        <img src="upload/foto/<?= htmlspecialchars($k['foto']) ?>" 
                            alt="Foto <?= htmlspecialchars($k['nama_lengkap']) ?>" 
                            class="w-12 h-12 rounded-lg mx-auto object-cover">
                    <?php else: ?>
                        <!-- Jika tidak ada foto, tampilkan tanda - -->
                        -
                    <?php endif; ?>
                </td>
                <!-- Kolom nama lengkap dengan htmlspecialchars untuk keamanan -->
                <td class="py-3 px-4 border"><?= htmlspecialchars($k['nama_lengkap']) ?></td>
                <!-- Kolom email dengan htmlspecialchars untuk keamanan -->
                <td class="py-3 px-4 border"><?= htmlspecialchars($k['email']) ?></td>
                <!-- Kolom telepon dengan htmlspecialchars untuk keamanan -->
                <td class="py-3 px-4 border"><?= htmlspecialchars($k['telepon']) ?></td>
                <!-- Kolom jenis kelamin -->
                <td class="py-3 px-4 border text-center"><?= $k['jenis_kelamin'] ?></td>
                <!-- Kolom nama jabatan dengan htmlspecialchars untuk keamanan -->
                <td class="py-3 px-4 border"><?= htmlspecialchars($k['nama_jabatan']) ?></td>
                <!-- Kolom gaji dengan format rupiah -->
                <td class="py-3 px-4 border">Rp <?= number_format($k['gaji_pokok'], 0, ',', '.') ?></td>
                <!-- Kolom aksi dengan flex layout untuk button detail, edit, dan hapus -->
                <td class="py-3 px-4 flex items-center justify-center gap-2">

                <!-- Form untuk button detail dengan method POST -->
                <form action="detail.php" method="POST">
                    <!-- Hidden input untuk mengirim ID karyawan -->
                    <input type="hidden" name="id" value="<?= $k['id_karyawan'] ?>">
                    <!-- Button detail dengan icon info -->
                    <button class="px-3 py-1.5 rounded-lg bg-yellow-500 text-white text-sm shadow hover:bg-yellow-600">
                    ⓘ
                    </button>
                </form>

                <!-- Form untuk button edit dengan method POST -->
                <form action="edit.php" method="POST">
                    <!-- Hidden input untuk mengirim ID karyawan -->
                    <input type="hidden" name="id" value="<?= $k['id_karyawan'] ?>">
                    <!-- Button edit dengan icon pensil -->
                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-blue-600 text-white text-sm shadow hover:bg-blue-700">✎</button>
                </form>

                <!-- Button hapus yang akan membuka modal konfirmasi -->
                <button onclick="openDeleteModal('<?= $k['id_karyawan'] ?>')"
                    class="px-3 py-1.5 rounded-lg bg-red-600 text-white text-sm shadow hover:bg-red-700">
                        🗙
                </button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

        <!-- Bagian untuk paginasi dan tombol tambah data karyawan -->
   <div class="flex justify-between items-center mt-6">

    <!-- Kiri: Tombol Tambah dan Dropdown Export/Import -->
    <div class="flex items-center gap-2 relative">
        <!-- Link untuk menambah data karyawan baru -->
        <a href="tambah.php"
           class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 shadow">
            ✚ Tambah Data
        </a>

        <!-- Container untuk dropdown menu -->
        <div class="relative">
            <!-- Tombol utama untuk membuka dropdown -->
            <button id="dropdownButton"
                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 shadow text-sm flex items-center justify-center"
                type="button">
                ☰
            </button>

            <!-- Dropdown menu dengan URL dinamis yang menyertakan filter aktif -->
            <div id="dropdownMenu" class="hidden absolute top-0 left-full ml-2 w-40 bg-white shadow-lg rounded-lg z-10">
                <!-- Link export PDF dengan filter yang sudah diapply -->
                <a href="<?= htmlspecialchars($exportPDFUrl) ?>" target="_blank"
                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-100 text-center">
                    🗐 Export PDF
                </a>
                <!-- Link export Excel dengan filter yang sudah diapply -->
                <a href="<?= htmlspecialchars($exportExcelUrl) ?>" target="_blank"
                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-100 text-center">
                    🗐 Export Excel
                </a>
                <!-- Link ke halaman import Excel -->
                <a href="import_excel_page.php"
                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-100 text-center">
                    Import Excel
                </a>
            </div>
        </div>
    </div>

    <!-- Kanan: Paginasi -->
    <div class="flex gap-1 items-center">
        <!-- Button Previous -->
        <?php if ($page > 1): ?>
            <!-- Jika bukan halaman pertama, tampilkan link prev yang aktif -->
            <a href="?halaman=<?= $page - 1 ?><?= urlFilter() ? "&" . urlFilter() : "" ?>"
               class="px-3 py-1 rounded-lg border bg-white text-blue-600 hover:bg-blue-100">
               ⮜
            </a>
        <?php else: ?>
            <!-- Jika halaman pertama, tampilkan button prev yang disabled -->
            <span class="px-3 py-1 rounded-lg border bg-gray-200 text-gray-400 cursor-not-allowed">⮜</span>
        <?php endif; ?>

        <!-- Halaman pertama -->
        <?php if ($page > 2): ?>
            <!-- Jika halaman aktif lebih dari 2, tampilkan link ke halaman 1 -->
            <a href="?halaman=1<?= urlFilter() ? "&" . urlFilter() : "" ?>"
               class="px-3 py-1 rounded-lg border bg-white text-blue-600 hover:bg-blue-100">
               1
            </a>
        <?php endif; ?>

        <?php if ($page > 3): ?>
            <!-- Jika halaman aktif lebih dari 3, tampilkan ellipsis -->
            <span class="px-3 py-1">...</span>
        <?php endif; ?>

        <!-- Halaman aktif plus minus 1 -->
        <?php for ($i = max(1, $page - 1); $i <= min($totalHalaman, $page + 1); $i++): ?>
            <!-- Loop untuk menampilkan halaman sebelum, aktif, dan sesudah halaman aktif -->
            <a href="?halaman=<?= $i ?><?= urlFilter() ? "&" . urlFilter() : "" ?>"
               class="px-3 py-1 rounded-lg border 
               <?= ($i == $page) ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-blue-600 hover:bg-blue-100' ?>">
               <?= $i ?>
            </a>
        <?php endfor; ?>

        <?php if ($page < $totalHalaman - 2): ?>
            <!-- Jika halaman aktif kurang dari total-2, tampilkan ellipsis -->
            <span class="px-3 py-1">...</span>
        <?php endif; ?>

        <!-- Halaman terakhir -->
        <?php if ($page < $totalHalaman - 1): ?>
            <!-- Jika bukan 2 halaman terakhir, tampilkan link ke halaman terakhir -->
            <a href="?halaman=<?= $totalHalaman ?><?= urlFilter() ? "&" . urlFilter() : "" ?>"
               class="px-3 py-1 rounded-lg border bg-white text-blue-600 hover:bg-blue-100">
               <?= $totalHalaman ?>
            </a>
        <?php endif; ?>

        <!-- Button Next -->
        <?php if ($page < $totalHalaman): ?>
            <!-- Jika bukan halaman terakhir, tampilkan link next yang aktif -->
            <a href="?halaman=<?= $page + 1 ?><?= urlFilter() ? "&" . urlFilter() : "" ?>"
               class="px-3 py-1 rounded-lg border bg-white text-blue-600 hover:bg-blue-100">
               ➢
            </a>
        <?php else: ?>
            <!-- Jika halaman terakhir, tampilkan button next yang disabled -->
            <span class="px-3 py-1 rounded-lg border bg-gray-200 text-gray-400 cursor-not-allowed">➢</span>
        <?php endif; ?>
    </div>
</div>
</div>


<!-- Modal konfirmasi yang akan muncul saat user ingin menghapus data -->
<div id="modalHapus"
    class="hidden fixed inset-0 bg-black bg-opacity-40 flex justify-center items-center">

    <!-- Container modal dengan styling -->
    <div class="bg-white w-full max-w-sm p-6 rounded-xl shadow-lg text-center animate-fadeIn">
        <!-- Judul modal -->
        <h3 class="text-xl font-bold text-red-600 mb-2">Konfirmasi Hapus</h3>
        <!-- Pesan konfirmasi -->
        <p class="text-gray-600">Apakah kamu yakin ingin menghapus data ini?</p>

        <!-- Container untuk tombol aksi -->
        <div class="flex justify-center gap-3 mt-5">
            <!-- Tombol untuk membatalkan penghapusan -->
            <button onclick="closeDeleteModal()"
            class="px-4 py-2 rounded-lg bg-gray-300 text-gray-800 hover:bg-gray-400">
                Batal
            </button>

            <!-- Link untuk konfirmasi hapus, href akan diisi dinamis oleh JavaScript -->
            <a id="btnDeleteConfirm" href="#"
            class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">
                Ya hapus
            </a>
        </div>
    </div>
</div>

<style>
/* Keyframe animation untuk efek fade in pada modal */
@keyframes fadeIn {
    from {opacity: 0; transform: scale(0.95);}
    to {opacity: 1; transform: scale(1);}
}
/* Class untuk menerapkan animasi fade in */
.animate-fadeIn { animation: fadeIn 0.2s ease-out; }
</style>

<script>
// Fungsi untuk membuka modal hapus dan set ID karyawan ke link konfirmasi
function openDeleteModal(id) {
    // Set href link konfirmasi dengan ID karyawan yang akan dihapus
    document.getElementById("btnDeleteConfirm").href = "hapus.php?id=" + id;
    // Tampilkan modal dengan menghilangkan class hidden
    document.getElementById("modalHapus").classList.remove("hidden");
}

// Fungsi untuk menutup modal hapus
function closeDeleteModal() {
    // Sembunyikan modal dengan menambahkan class hidden
    document.getElementById("modalHapus").classList.add("hidden");
}

// Fungsi untuk toggle tampilan filter box
function toggleFilter() {
    // Ambil reference ke filter box
    const box = document.getElementById("filter-box");
    // Toggle class hidden untuk show/hide filter box
    box.classList.toggle("hidden");
}

// Ambil reference ke button dropdown
const btn = document.getElementById('dropdownButton');
// Ambil reference ke menu dropdown
const menu = document.getElementById('dropdownMenu');

// Event listener untuk membuka/menutup dropdown saat button diklik
btn.addEventListener('click', () => {
    menu.classList.toggle('hidden');
});

// Event listener untuk menutup dropdown ketika user klik di luar dropdown
window.addEventListener('click', (e) => {
    // Jika yang diklik bukan button dan bukan menu, tutup dropdown
    if (!btn.contains(e.target) && !menu.contains(e.target)) {
        menu.classList.add('hidden');
    }
});
</script>

</body>
</html>