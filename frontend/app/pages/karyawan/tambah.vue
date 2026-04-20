<template>
  <div class="min-h-screen relative bg-blue-50 p-4 md:p-6 overflow-hidden">
    <!-- Abstract Background Elements -->
    <div class="absolute inset-0">
      <div class="w-full h-full bg-gradient-to-tr from-blue-100 via-blue-200 to-blue-300 opacity-50 animate-pulse-slow"></div>
      <div class="absolute -top-20 -left-20 w-96 h-96 bg-blue-400 rounded-full opacity-30 mix-blend-multiply animate-blob"></div>
      <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-blue-500 rounded-full opacity-30 mix-blend-multiply animate-blob animation-delay-2000"></div>
      <div class="absolute top-1/3 right-1/4 w-80 h-80 bg-blue-300 rounded-full opacity-20 mix-blend-multiply animate-blob animation-delay-4000"></div>
    </div>

    <div class="max-w-3xl mx-auto relative z-10">
      <div class="bg-white rounded-2xl shadow-xl p-6 border border-gray-100">
        
        <!-- Header -->
        <div class="flex justify-between items-center gap-4 mb-6 pb-4 border-b border-gray-200">
          <div>
            <h1 class="text-2xl font-bold text-gray-800">Tambah Karyawan</h1>
            <p class="text-gray-500 text-sm mt-0.5">Lengkapi formulir untuk menambahkan karyawan baru</p>
          </div>
        </div>

        <!-- Error Alert -->
        <div v-if="showError" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg flex items-start gap-2">
          <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <div class="flex-1">
            <h3 class="font-semibold text-red-800 text-sm">Terdapat kesalahan pada formulir</h3>
            <p class="text-xs text-red-700 mt-0.5">{{ errorMessage }}</p>
          </div>
          <button @click="showError = false" class="text-red-400 hover:text-red-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Foto Upload -->
        <div class="text-center mb-6 pb-6 border-b border-gray-200">
          <div class="inline-block relative">
            <div class="w-24 h-24 rounded-full overflow-hidden mx-auto shadow-lg border-4 border-blue-100 bg-gray-100">
              <img v-if="previewImage" :src="previewImage" alt="Preview" class="w-full h-full object-cover"/>
              <div v-else class="w-full h-full flex items-center justify-center">
                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
              </div>
            </div>
            <label class="absolute bottom-0 right-0 bg-blue-600 text-white p-2 rounded-full cursor-pointer hover:bg-blue-700 transition-colors shadow-md">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              <input type="file" accept="image/*" class="hidden" @change="handleFileChange"/>
            </label>
          </div>
          <p class="text-xs text-gray-500 mt-2">Upload foto profil (Opsional, Max 2MB)</p>
        </div>

        <!-- Grid Form -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Nama Lengkap -->
          <div class="md:col-span-2">
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">
              Nama Lengkap <span class="text-red-500">*</span>
            </label>
            <input 
              v-model="form.nama_lengkap" 
              type="text" 
              class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all text-sm"
              :class="errors.nama_lengkap ? 'border-red-300 bg-red-50' : 'border-gray-300'"
              placeholder="Masukkan nama lengkap"
              @input="clearError('nama_lengkap')"
            />
            <p v-if="errors.nama_lengkap" class="text-red-600 text-xs mt-1">{{ errors.nama_lengkap }}</p>
          </div>

          <!-- Email -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">
              Email <span class="text-red-500">*</span>
            </label>
            <input 
              v-model="form.email" 
              type="email" 
              class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all text-sm"
              :class="errors.email ? 'border-red-300 bg-red-50' : 'border-gray-300'"
              placeholder="email@example.com"
              @input="clearError('email')"
            />
            <p v-if="errors.email" class="text-red-600 text-xs mt-1">{{ errors.email }}</p>
          </div>

          <!-- Telepon -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">
              Telepon <span class="text-red-500">*</span>
            </label>
            <input 
              v-model="form.telepon" 
              type="tel" 
              class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all text-sm"
              :class="errors.telepon ? 'border-red-300 bg-red-50' : 'border-gray-300'"
              placeholder="08xxxxxxxxxx"
              @input="clearError('telepon')"
            />
            <p v-if="errors.telepon" class="text-red-600 text-xs mt-1">{{ errors.telepon }}</p>
          </div>

          <!-- Tempat Lahir -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">
              Tempat Lahir <span class="text-red-500">*</span>
            </label>
            <input 
              v-model="form.tempat_lahir" 
              type="text" 
              class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all text-sm"
              :class="errors.tempat_lahir ? 'border-red-300 bg-red-50' : 'border-gray-300'"
              placeholder="Nama kota"
              @input="clearError('tempat_lahir')"
            />
            <p v-if="errors.tempat_lahir" class="text-red-600 text-xs mt-1">{{ errors.tempat_lahir }}</p>
          </div>

          <!-- Tanggal Lahir -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">
              Tanggal Lahir <span class="text-red-500">*</span>
            </label>
            <input 
              v-model="form.tanggal_lahir" 
              type="date" 
              class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all text-sm"
              :class="errors.tanggal_lahir ? 'border-red-300 bg-red-50' : 'border-gray-300'"
              @input="clearError('tanggal_lahir')"
            />
            <p v-if="errors.tanggal_lahir" class="text-red-600 text-xs mt-1">{{ errors.tanggal_lahir }}</p>
          </div>

          <!-- Jenis Kelamin -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">
              Jenis Kelamin <span class="text-red-500">*</span>
            </label>
            <select 
              v-model="form.jenis_kelamin" 
              class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all text-sm"
              :class="errors.jenis_kelamin ? 'border-red-300 bg-red-50' : 'border-gray-300'"
              @change="clearError('jenis_kelamin')"
            >
              <option value="">Pilih jenis kelamin</option>
              <option value="Laki-laki">Laki-laki</option>
              <option value="Perempuan">Perempuan</option>
            </select>
            <p v-if="errors.jenis_kelamin" class="text-red-600 text-xs mt-1">{{ errors.jenis_kelamin }}</p>
          </div>

          <!-- Jabatan -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">
              Jabatan <span class="text-red-500">*</span>
            </label>
            <select 
              v-model="form.jabatan_id" 
              class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all text-sm"
              :class="errors.jabatan_id ? 'border-red-300 bg-red-50' : 'border-gray-300'"
              @change="clearError('jabatan_id')"
            >
              <option value="">Pilih jabatan</option>
              <option v-for="j in jabatanList" :key="j.id_jabatan" :value="j.id_jabatan">{{ j.nama_jabatan }}</option>
            </select>
            <p v-if="errors.jabatan_id" class="text-red-600 text-xs mt-1">{{ errors.jabatan_id }}</p>
          </div>

          <!-- Gaji Pokok -->
          <div class="md:col-span-2">
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">
              Gaji Pokok <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 font-medium text-sm">Rp</span>
              <input 
                v-model="form.gaji_pokok" 
                type="number" 
                class="w-full px-3 py-2 pl-10 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all text-sm"
                :class="errors.gaji_pokok ? 'border-red-300 bg-red-50' : 'border-gray-300'"
                placeholder="0"
                @input="clearError('gaji_pokok')"
              />
            </div>
            <p v-if="errors.gaji_pokok" class="text-red-600 text-xs mt-1">{{ errors.gaji_pokok }}</p>
          </div>
        </div>

        <!-- Submit Button -->
        <div class="mt-6 pt-4 border-t border-gray-200 flex gap-3">
          <button 
            @click="$router.back()" 
            class="px-5 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium text-sm flex items-center gap-2"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali</span>
          </button>
          <button 
            @click="handleSubmit" 
            :disabled="loading" 
            class="flex-1 px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium shadow-md disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 text-sm"
          >
            <svg v-if="loading" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>{{ loading ? 'Menyimpan...' : 'Simpan Data' }}</span>
          </button>
        </div>

      </div>
    </div>

    <!-- Success Modal -->
    <Teleport to="body">
      <div v-if="showSuccessModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" @click="showSuccessModal = false">
        <div class="bg-white rounded-xl p-6 max-w-md w-full shadow-2xl animate-scale-in" @click.stop>
          <div class="text-center">
            <div class="w-16 h-16 rounded-full bg-green-100 mx-auto mb-4 flex items-center justify-center">
              <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-2">Berhasil!</h3>
            <p class="text-gray-600 mb-4">
              Data karyawan berhasil ditambahkan ke sistem.
            </p>
            <button 
              @click="closeSuccessModal" 
              class="px-6 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium shadow-md"
            >
              OK
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useApi } from '~/composables/useApi'

definePageMeta({ layout: 'default' })

const api = useApi()
const router = useRouter()

const loading = ref(false)
const showError = ref(false)
const showSuccessModal = ref(false)
const errorMessage = ref('')
const previewImage = ref<string | null>(null)
const selectedFile = ref<File | null>(null)
const jabatanList = ref<any[]>([])

const form = ref({
  nama_lengkap: '',
  email: '',
  telepon: '',
  tempat_lahir: '',
  tanggal_lahir: '',
  jenis_kelamin: '',
  jabatan_id: '',
  gaji_pokok: ''
})

const errors = reactive<Record<string, string>>({
  nama_lengkap: '',
  email: '',
  telepon: '',
  tempat_lahir: '',
  tanggal_lahir: '',
  jenis_kelamin: '',
  jabatan_id: '',
  gaji_pokok: ''
})

const clearError = (field: string) => {
  errors[field] = ''
  if (showError.value) showError.value = false
}

const validateForm = (): boolean => {
  let isValid = true
  
  // Reset errors
  Object.keys(errors).forEach(key => errors[key] = '')
  
  if (!form.value.nama_lengkap.trim()) {
    errors.nama_lengkap = 'Nama lengkap wajib diisi'
    isValid = false
  }
  
  if (!form.value.email.trim()) {
    errors.email = 'Email wajib diisi'
    isValid = false
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email)) {
    errors.email = 'Format email tidak valid'
    isValid = false
  }
  
  if (!form.value.telepon.trim()) {
    errors.telepon = 'Nomor telepon wajib diisi'
    isValid = false
  }
  
  if (!form.value.tempat_lahir.trim()) {
    errors.tempat_lahir = 'Tempat lahir wajib diisi'
    isValid = false
  }
  
  if (!form.value.tanggal_lahir) {
    errors.tanggal_lahir = 'Tanggal lahir wajib diisi'
    isValid = false
  }
  
  if (!form.value.jenis_kelamin) {
    errors.jenis_kelamin = 'Jenis kelamin wajib dipilih'
    isValid = false
  }
  
  if (!form.value.jabatan_id) {
    errors.jabatan_id = 'Jabatan wajib dipilih'
    isValid = false
  }
  
  if (!form.value.gaji_pokok || Number(form.value.gaji_pokok) <= 0) {
    errors.gaji_pokok = 'Gaji pokok wajib diisi dengan nilai yang valid'
    isValid = false
  }
  
  if (!isValid) {
    errorMessage.value = 'Mohon lengkapi semua field yang wajib diisi'
    showError.value = true
    // Scroll to top to show error message
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }
  
  return isValid
}

const loadJabatan = async () => {
  try {
    const res = await api.jabatan.getAll()
    if (res?.berhasil) {
      jabatanList.value = res.data?.data || []
    }
  } catch (err) {
    console.error('Gagal load jabatan:', err)
  }
}

const handleFileChange = (e: Event) => {
  const file = (e.target as HTMLInputElement).files?.[0]
  if (file) {
    if (file.size > 2 * 1024 * 1024) {
      errorMessage.value = 'Ukuran file maksimal 2MB'
      showError.value = true
      return
    }
    selectedFile.value = file
    const reader = new FileReader()
    reader.onload = (ev) => {
      previewImage.value = ev.target?.result as string
    }
    reader.readAsDataURL(file)
  }
}

const handleSubmit = async () => {
  if (!validateForm()) return
  
  loading.value = true
  try {
    const formData = new FormData()
    Object.entries(form.value).forEach(([k, v]) => {
      formData.append(k, v as string)
    })
    if (selectedFile.value) {
      formData.append('foto', selectedFile.value)
    }
    
    const res = await api.karyawan.create(formData)
    if (res?.berhasil) {
      showSuccessModal.value = true
    } else {
      errorMessage.value = 'Gagal menyimpan data karyawan'
      showError.value = true
    }
  } catch (err) {
    console.error('Error:', err)
    errorMessage.value = 'Terjadi kesalahan saat menyimpan data'
    showError.value = true
  } finally {
    loading.value = false
  }
}

const closeSuccessModal = () => {
  showSuccessModal.value = false
  router.push('/karyawan')
}

onMounted(() => {
  loadJabatan()
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

/* Success modal animation */
@keyframes scaleIn {
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
  animation: scaleIn 0.3s ease-out;
}

/* Remove number input arrows */
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

input[type="number"] {
  -moz-appearance: textfield;
}
</style>