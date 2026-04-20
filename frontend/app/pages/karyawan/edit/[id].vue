<template>
  <div class="min-h-screen relative bg-blue-50 p-4 overflow-hidden">
    <!-- Abstract Background Elements -->
    <div class="absolute inset-0">
      <div class="w-full h-full bg-gradient-to-tr from-blue-100 via-blue-200 to-blue-300 opacity-50 animate-pulse-slow"></div>
      <div class="absolute -top-20 -left-20 w-96 h-96 bg-blue-400 rounded-full opacity-30 mix-blend-multiply animate-blob"></div>
      <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-blue-500 rounded-full opacity-30 mix-blend-multiply animate-blob animation-delay-2000"></div>
      <div class="absolute top-1/3 right-1/4 w-80 h-80 bg-blue-300 rounded-full opacity-20 mix-blend-multiply animate-blob animation-delay-4000"></div>
    </div>

    <div class="max-w-3xl mx-auto relative z-10 space-y-4">
      <!-- Header -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">Edit Karyawan</h1>
          <p class="text-gray-500 text-sm mt-1">Perbarui informasi data karyawan</p>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loadingData" class="bg-white rounded-xl shadow-sm p-12 text-center border border-gray-100">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-gray-200 border-t-blue-600"></div>
        <p class="text-gray-600 mt-4">Memuat data...</p>
      </div>

      <!-- Form -->
      <div v-else class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 space-y-6">
          <!-- Foto Profile Section -->
          <div class="flex items-center gap-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
            <div class="relative">
              <div class="w-24 h-24 rounded-full overflow-hidden shadow-lg border-4 border-white">
                <img 
                  v-if="previewImage" 
                  :src="previewImage" 
                  alt="Preview" 
                  class="w-full h-full object-cover"
                />
                <img 
                  v-else-if="form.foto" 
                  :src="`http://localhost/karyawan_nt/public/upload/foto/${form.foto}`" 
                  alt="Current"
                  class="w-full h-full object-cover"
                />
                <div v-else class="w-full h-full bg-gray-200 flex items-center justify-center">
                  <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                </div>
              </div>
              <div class="absolute -bottom-1 -right-1 bg-blue-600 rounded-full p-2 shadow-lg">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
              </div>
            </div>
            <div class="flex-1">
              <h3 class="font-semibold text-gray-800 mb-1">Foto Profil</h3>
              <p class="text-sm text-gray-600 mb-3">JPG, PNG atau JPEG (Max. 2MB)</p>
              <label class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg cursor-pointer transition-colors shadow-md text-sm font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                {{ previewImage ? 'Ganti Foto' : 'Upload Foto' }}
                <input type="file" accept="image/*" class="hidden" @change="handleFileChange" />
              </label>
            </div>
          </div>

          <!-- Form Fields -->
          <div class="space-y-4">
            <!-- Nama Lengkap -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
              <input 
                v-model="form.nama_lengkap" 
                type="text" 
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                placeholder="Masukkan nama lengkap"
              />
            </div>

            <!-- Email & Telepon -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input 
                  v-model="form.email" 
                  type="email" 
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                  placeholder="email@example.com"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Telepon</label>
                <input 
                  v-model="form.telepon" 
                  type="tel" 
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                  placeholder="08xxxxxxxxxx"
                />
              </div>
            </div>

            <!-- Tempat & Tanggal Lahir -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tempat Lahir</label>
                <input 
                  v-model="form.tempat_lahir" 
                  type="text" 
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                  placeholder="Kota"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lahir</label>
                <input 
                  v-model="form.tanggal_lahir" 
                  type="date" 
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
              </div>
            </div>

            <!-- Jenis Kelamin & Jabatan -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kelamin</label>
                <select 
                  v-model="form.jenis_kelamin" 
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                  <option value="">Pilih Jenis Kelamin</option>
                  <option value="Laki-laki">Laki-laki</option>
                  <option value="Perempuan">Perempuan</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan</label>
                <select 
                  v-model="form.jabatan_id" 
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                  <option value="">Pilih Jabatan</option>
                  <option v-for="j in jabatanList" :key="j.id_jabatan" :value="j.id_jabatan">
                    {{ j.nama_jabatan }}
                  </option>
                </select>
              </div>
            </div>

            <!-- Gaji Pokok -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Gaji Pokok</label>
              <div class="relative">
                <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-500 font-medium">Rp</span>
                <input 
                  v-model="form.gaji_pokok" 
                  type="number" 
                  class="w-full px-4 py-2 pl-12 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                  placeholder="0"
                />
              </div>
            </div>
          </div>

          <!-- Submit Buttons -->
          <div class="pt-4 border-t border-gray-200">
            <div class="flex gap-3">
              <button 
                @click="$router.back()" 
                class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium"
              >
                <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali
              </button>
              
              <button 
                @click="handleSubmit" 
                :disabled="loadingSubmit" 
                class="flex-1 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed font-semibold shadow-lg flex items-center justify-center gap-2"
              >
                <svg v-if="!loadingSubmit" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <div v-if="loadingSubmit" class="inline-block animate-spin rounded-full h-5 w-5 border-3 border-white border-t-transparent"></div>
                {{ loadingSubmit ? 'Menyimpan Data...' : 'Update Data Karyawan' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Success Modal -->
    <Teleport to="body">
      <div v-if="showSuccessModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" @click="closeSuccessModal">
        <div class="bg-white rounded-xl p-8 max-w-md w-full shadow-2xl animate-scale-in" @click.stop>
          <div class="text-center">
            <div class="w-20 h-20 rounded-full bg-green-100 mx-auto mb-4 flex items-center justify-center">
              <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-2">Berhasil Diperbarui!</h3>
            <p class="text-gray-600 mb-6">
              Data karyawan telah berhasil diperbarui dalam sistem.
            </p>
            <button 
              @click="closeSuccessModal" 
              class="px-8 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-semibold shadow-md w-full"
            >
              OK, Mengerti
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'default' })

const route = useRoute()
const router = useRouter()
const api = useApi()

const loadingData = ref(true)
const loadingSubmit = ref(false)
const showSuccessModal = ref(false)
const previewImage = ref<string | null>(null)
const selectedFile = ref<File | null>(null)
const jabatanList = ref<any[]>([])
const oldPhoto = ref<string>('')

const form = ref({
  id_karyawan: '',
  nama_lengkap: '',
  email: '',
  telepon: '',
  tempat_lahir: '',
  tanggal_lahir: '',
  jenis_kelamin: '',
  jabatan_id: '',
  gaji_pokok: '',
  foto: ''
})

// Load jabatan
const loadJabatan = async () => {
  const res = await api.jabatan.getAll()
  if (res.berhasil) jabatanList.value = res.data.data || []
}

// Load karyawan
const loadKaryawan = async () => {
  loadingData.value = true
  const res = await api.karyawan.getById(route.params.id as string)
  if (res.berhasil && res.data) {
    form.value = {...res.data}
    oldPhoto.value = res.data.foto || '' // Simpan foto lama
  }
  loadingData.value = false
}

// Preview foto
const handleFileChange = (e: Event) => {
  const file = (e.target as HTMLInputElement).files?.[0]
  if (file) {
    if (file.size > 2*1024*1024) {
      alert('Ukuran file maksimal 2MB!')
      return
    }
    selectedFile.value = file
    const reader = new FileReader()
    reader.onload = (ev) => previewImage.value = ev.target?.result as string
    reader.readAsDataURL(file)
  }
}

// Submit update
const handleSubmit = async () => {
  loadingSubmit.value = true
  try {
    const formData = new FormData()
    
    // Kirim semua field form
    Object.entries(form.value).forEach(([k,v]) => {
      if(k !== 'foto' && v !== '' && v != null) {
        formData.append(k, v as string)
      }
    })
    
    // Jika ada foto baru, kirim foto baru dan foto lama (untuk dihapus)
    if(selectedFile.value) {
      formData.append('foto', selectedFile.value)
      if(oldPhoto.value) {
        formData.append('old_foto', oldPhoto.value) // Backend akan hapus foto lama
      }
    }

    const response = await api.karyawan.update(formData)
    
    if(response.berhasil) {
      showSuccessModal.value = true
      // Reset preview dan selected file
      previewImage.value = null
      selectedFile.value = null
      // Reload data untuk mendapatkan foto terbaru
      await loadKaryawan()
    }
  } catch (err) {
    console.error(err)
    alert('Gagal memperbarui data karyawan')
  } finally {
    loadingSubmit.value = false
  }
}

const closeSuccessModal = () => {
  showSuccessModal.value = false
  router.push('/karyawan')
}

onMounted(()=>{
  loadJabatan()
  loadKaryawan()
})
</script>

<style scoped>
/* Abstract background animations */
@keyframes blob {
  0%, 100% { 
    transform: translate(0, 0) scale(1); 
  }
  33% { 
    transform: translate(30px, -50px) scale(1.1); 
  }
  66% { 
    transform: translate(-20px, 20px) scale(0.9); 
  }
}

.animate-blob {
  animation: blob 8s infinite;
}

.animate-pulse-slow {
  animation: pulse 6s infinite;
}

.animation-delay-2000 {
  animation-delay: 2s;
}

.animation-delay-4000 {
  animation-delay: 4s;
}

@keyframes scale-in {
  0% {
    transform: scale(0.9);
    opacity: 0;
  }
  100% {
    transform: scale(1);
    opacity: 1;
  }
}

.animate-scale-in {
  animation: scale-in 0.3s ease-out;
}
</style>