<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Data Karyawan dari Excel</title>
    <!-- Menyertakan Tailwind CSS dari CDN untuk styling -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<!-- Body dengan background biru muda, tinggi minimal full screen, dan padding -->
<body class="bg-blue-50 min-h-screen p-6">
<!-- Container utama dengan lebar maksimal -->
<div class="max-w-3xl mx-auto">
    
    <!-- Card Utama dengan background putih, shadow, rounded corners, dan padding -->
    <div class="bg-white shadow-lg rounded-xl p-8">
        
        <!-- Header halaman -->
        <div class="text-center mb-6">
            <!-- Judul halaman dengan icon dan styling -->
            <h2 class="text-3xl font-bold text-blue-600">📊 Import Data dari Excel</h2>
            <!-- Deskripsi singkat di bawah judul -->
            <p class="text-gray-600 mt-2">Upload file Excel untuk menambahkan data karyawan secara massal</p>
        </div>
        
        <!-- Alert Info Format dengan background kuning untuk menarik perhatian -->
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
            <div class="flex">
                <!-- Icon warning di sebelah kiri -->
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <!-- Konten informasi format file -->
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">Format File Excel</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p class="font-semibold">File Excel harus memiliki header di baris pertama dengan urutan kolom:</p>
                        <!-- List ordered untuk menjelaskan urutan kolom yang diperlukan -->
                        <ol class="list-decimal list-inside mt-2 space-y-1">
                            <!-- Kolom 1: Nama Lengkap wajib diisi -->
                            <li><strong>nama_lengkap</strong> (wajib)</li>
                            <!-- Kolom 2: Email wajib diisi dan harus unik -->
                            <li><strong>email</strong> (wajib, harus unik)</li>
                            <!-- Kolom 3: Telepon wajib diisi -->
                            <li><strong>telepon</strong> (wajib)</li>
                            <!-- Kolom 4: Tempat Lahir opsional -->
                            <li><strong>tempat_lahir</strong> (opsional)</li>
                            <!-- Kolom 5: Tanggal Lahir dengan format khusus -->
                            <li><strong>tanggal_lahir</strong> (format: YYYY-MM-DD, contoh: 1995-03-01)</li>
                            <!-- Kolom 6: Jenis Kelamin dengan pilihan Laki-laki atau Perempuan -->
                            <li><strong>jenis_kelamin</strong> (Laki-laki atau Perempuan)</li>
                            <!-- Kolom 7: Jabatan ID harus sesuai dengan ID di database -->
                            <li><strong>jabatan_id</strong> (angka, sesuai ID di database)</li>
                            <!-- Kolom 8: Gaji Pokok dalam format angka -->
                            <li><strong>gaji_pokok</strong> (angka, contoh: 5000000)</li>
                        </ol>
                        <!-- Contoh data yang valid -->
                        <p class="mt-3 text-xs">
                            <strong>Contoh:</strong><br>
                            azril | azrilbarber@example.com | 081234567 | Jakarta | 1995-03-01 | Laki-laki | 1 | 5000000
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Form Upload dengan enctype multipart untuk upload file -->
        <form id="formImport" enctype="multipart/form-data" class="space-y-4">
            
            <!-- File Input untuk memilih file Excel -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Pilih File Excel (.xls atau .xlsx)
                </label>
                <!-- Input file dengan accept hanya file Excel dan required -->
                <input type="file" name="file" id="fileInput" accept=".xls,.xlsx" required
                    class="block w-full text-sm text-gray-500
                        file:mr-4 file:py-3 file:px-4
                        file:rounded-lg file:border-0
                        file:text-sm file:font-semibold
                        file:bg-blue-600 file:text-white
                        hover:file:bg-blue-700 cursor-pointer">
                <!-- Keterangan ukuran maksimal file -->
                <p class="mt-1 text-xs text-gray-500">Maksimal ukuran file: 5MB</p>
            </div>
            
            <!-- Progress Bar yang default hidden, akan muncul saat proses upload -->
            <div id="progressBar" class="hidden">
                <!-- Container progress bar dengan background abu-abu -->
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <!-- Bar progress yang animasi pulse -->
                    <div class="bg-blue-600 h-2.5 rounded-full animate-pulse" style="width: 100%"></div>
                </div>
                <!-- Text informasi proses -->
                <p class="text-sm text-gray-600 mt-2 text-center">Sedang memproses import...</p>
            </div>
            
            <!-- Alert Result yang default hidden, akan muncul setelah proses selesai -->
            <div id="alertResult" class="hidden"></div>
            
            <!-- Container untuk tombol-tombol -->
            <div class="flex gap-3">
                <!-- Tombol Kembali di posisi kiri -->
                <a href="list.php"
                    class="bg-gray-300 text-gray-800 px-6 py-3 rounded-lg hover:bg-gray-400 font-semibold text-center">
                    ← Kembali
                </a>
                
                <!-- Tombol Download Template - langsung mengarah ke file template -->
                <a href="template/sample_karyawan_import.xlsx" download
                    class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 font-semibold shadow-md text-center">
                    ⬇ Download Template
                </a>
                
                <!-- Tombol Submit untuk upload dan import -->
                <button type="submit" id="btnSubmit"
                    class="flex-1 bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 font-semibold shadow-md">
                    📤 Upload & Import
                </button>
            </div>
            
        </form>
        
    </div>
    
</div>

<script>
// Event listener untuk form submit
document.getElementById('formImport').addEventListener('submit', async function(e) {
    // Mencegah form submit default behavior
    e.preventDefault();
    
    // Mengambil reference ke elemen-elemen yang diperlukan
    const fileInput = document.getElementById('fileInput');
    const progressBar = document.getElementById('progressBar');
    const alertResult = document.getElementById('alertResult');
    const btnSubmit = document.getElementById('btnSubmit');
    
    // Validasi apakah user sudah memilih file
    if (!fileInput.files.length) {
        showAlert('error', 'Silakan pilih file terlebih dahulu');
        return;
    }
    
    // Mengambil file yang dipilih
    const file = fileInput.files[0];
    // Mengambil ekstensi file
    const ext = file.name.split('.').pop().toLowerCase();
    
    // Validasi ekstensi file harus xls atau xlsx
    if (!['xls', 'xlsx'].includes(ext)) {
        showAlert('error', 'File harus berformat Excel (.xls atau .xlsx)');
        return;
    }
    
    // Validasi ukuran file maksimal 5MB
    if (file.size > 5 * 1024 * 1024) {
        showAlert('error', 'Ukuran file maksimal 5MB');
        return;
    }
    
    // Tampilkan progress bar dan hide alert result
    progressBar.classList.remove('hidden');
    alertResult.classList.add('hidden');
    // Disable tombol submit untuk mencegah double submit
    btnSubmit.disabled = true;
    btnSubmit.textContent = 'Sedang mengupload...';
    
    // Prepare FormData untuk mengirim file ke server
    const formData = new FormData();
    formData.append('file', file);
    
    try {
        // Kirim request POST ke API import Excel
        const response = await fetch('../api/karyawan/import_excel.php', {
            method: 'POST',
            body: formData
        });
        
        // Parse response JSON dari server
        const result = await response.json();
        
        // Hide progress bar dan enable kembali tombol submit
        progressBar.classList.add('hidden');
        btnSubmit.disabled = false;
        btnSubmit.textContent = '📤 Upload & Import';
        
        // Cek apakah import berhasil
        if (result.berhasil) {
            // Parsing data dari berbagai format response yang mungkin berbeda
            let sukses, gagal, total, errors;
            
            // Cek struktur data response
            if (result.data && typeof result.data === 'object') {
                if (result.data.sukses !== undefined) {
                    // Format 1: {sukses: 5, gagal: 2, total: 7}
                    sukses = result.data.sukses;
                    gagal = result.data.gagal;
                    total = result.data.total;
                    errors = result.data.errors || [];
                } else if (result.data.result) {
                    // Format 2: {result: {sukses: 5, gagal: 2, total: 7}, errors: []}
                    sukses = result.data.result.sukses;
                    gagal = result.data.result.gagal;
                    total = result.data.result.total;
                    errors = result.data.errors || [];
                }
            } else {
                // Fallback jika format berbeda dari yang diharapkan
                sukses = result.sukses || 0;
                gagal = result.gagal || 0;
                total = result.total || 0;
                errors = result.errors || result.kesalahan || [];
            }
            
            // Cek apakah ada data yang benar-benar sukses diimport
            if (sukses > 0) {
                // Ada data yang sukses (sebagian atau penuh)
                let errorHtml = '';
                // Jika ada error, tampilkan detail error
                if (errors && errors.length > 0) {
                    errorHtml = '<br><br><strong>Detail Error:</strong><div class="mt-2 text-xs max-h-40 overflow-y-auto bg-white p-2 rounded border border-green-200">';
                    errors.forEach(err => {
                        errorHtml += `<div class="py-1 text-red-600">• ${err}</div>`;
                    });
                    errorHtml += '</div>';
                }
                
                // Tampilkan alert sukses dengan detail jumlah data
                showAlert('success', `
                    <strong>Import Selesai!</strong><br>
                    ✅ Sukses: ${sukses} data berhasil diimport<br>
                    ❌ Gagal: ${gagal} data<br>
                    📊 Total: ${total} baris diproses
                    ${errorHtml}
                `);
                
                // Reset form input file
                fileInput.value = '';
                
                // Redirect ke halaman list setelah 5 detik
                setTimeout(() => {
                    window.location.href = 'list.php';
                }, 5000);
                
            } else {
                // Semua data gagal (sukses = 0)
                let errorHtml = '';
                // Tampilkan detail error jika ada
                if (errors && errors.length > 0) {
                    errorHtml = '<br><br><strong>Detail Error:</strong><div class="mt-2 text-xs max-h-60 overflow-y-auto bg-white p-2 rounded border border-red-200">';
                    errors.forEach(err => {
                        errorHtml += `<div class="py-1">• ${err}</div>`;
                    });
                    errorHtml += '</div>';
                }
                
                // Tampilkan alert error dengan detail
                showAlert('error', `
                    <strong>Import Gagal Total!</strong><br>
                    ❌ Tidak ada data yang berhasil diimport<br>
                    📊 Total: ${total} baris diproses, ${gagal} gagal
                    ${errorHtml}
                `);
            }
            
        } else {
            // Response berhasil = false (error dari API)
            let errorMessage = result.pesan || 'Terjadi kesalahan saat import';
            let errors = result.kesalahan || result.errors || [];
            
            let errorHtml = '';
            // Tampilkan detail error jika ada
            if (errors.length > 0) {
                errorHtml = '<br><br><strong>Detail Error:</strong><div class="mt-2 text-xs max-h-60 overflow-y-auto bg-white p-2 rounded border border-red-200">';
                errors.forEach(err => {
                    errorHtml += `<div class="py-1">• ${err}</div>`;
                });
                errorHtml += '</div>';
            }
            
            // Tampilkan alert error
            showAlert('error', `
                <strong>Import Gagal!</strong><br>
                ${errorMessage}
                ${errorHtml}
            `);
        }
        
    } catch (error) {
        // Handle error koneksi atau error parsing JSON
        progressBar.classList.add('hidden');
        btnSubmit.disabled = false;
        btnSubmit.textContent = '📤 Upload & Import';
        showAlert('error', '<strong>Terjadi kesalahan koneksi:</strong><br>' + error.message);
    }
});

// Fungsi untuk menampilkan alert dengan type success atau error
function showAlert(type, message) {
    const alertResult = document.getElementById('alertResult');
    // Definisi warna untuk setiap type alert
    const colors = {
        success: 'bg-green-50 border-green-400 text-green-800',
        error: 'bg-red-50 border-red-400 text-red-800'
    };
    
    // Set class dan content alert
    alertResult.className = `${colors[type]} border-l-4 p-4 rounded`;
    alertResult.innerHTML = message;
    // Tampilkan alert
    alertResult.classList.remove('hidden');
}
</script>

</body>
</html>
