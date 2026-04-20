<template>
  <div class="min-h-screen relative bg-blue-50 p-4 md:p-6 overflow-hidden">
    <!-- Abstract Background Elements -->
    <div class="absolute inset-0">
      <div class="w-full h-full bg-gradient-to-tr from-blue-100 via-blue-200 to-blue-300 opacity-50 animate-pulse-slow"></div>
      <div class="absolute -top-20 -left-20 w-96 h-96 bg-blue-400 rounded-full opacity-30 mix-blend-multiply animate-blob"></div>
      <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-blue-500 rounded-full opacity-30 mix-blend-multiply animate-blob animation-delay-2000"></div>
      <div class="absolute top-1/3 right-1/4 w-80 h-80 bg-blue-300 rounded-full opacity-20 mix-blend-multiply animate-blob animation-delay-4000"></div>
    </div>

    <div class="max-w-5xl mx-auto space-y-6 relative z-10">
      <!-- Header -->
      <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <div>
          <h1 class="text-3xl font-bold text-gray-800">Import Data Karyawan</h1>
          <p class="text-gray-500 mt-1">Upload file Excel untuk import data karyawan secara massal</p>
        </div>
      </div>

      <!-- Instructions Card -->
      <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <div class="flex items-start gap-4">
          <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="flex-1">
            <div class="flex items-center justify-between mb-2">
              <h3 class="text-lg font-semibold text-gray-900">Panduan Import</h3>
              <a
                :href="templateUrl"
                download="sample_karyawan_import.xlsx"
                class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors shadow-md text-sm font-medium"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Download Template
              </a>
            </div>
            <ul class="space-y-2 text-sm text-gray-600">
              <li class="flex items-start gap-2">
                <span class="text-blue-600 font-bold">•</span>
                <span>File harus berformat <strong>Excel (.xls atau .xlsx)</strong></span>
              </li>
              <li class="flex items-start gap-2">
                <span class="text-blue-600 font-bold">•</span>
                <span>Ukuran file maksimal <strong>5 MB</strong></span>
              </li>
              <li class="flex items-start gap-2">
                <span class="text-blue-600 font-bold">•</span>
                <span>Baris pertama harus berisi header kolom</span>
              </li>
              <li class="flex items-start gap-2">
                <span class="text-blue-600 font-bold">•</span>
                <span>Kolom wajib: <strong>Nama Lengkap, Email, Telepon</strong></span>
              </li>
              <li class="flex items-start gap-2">
                <span class="text-blue-600 font-bold">•</span>
                <span>Format tanggal: <strong>YYYY-MM-DD</strong> (contoh: 1990-05-15)</span>
              </li>
              <li class="flex items-start gap-2">
                <span class="text-blue-600 font-bold">•</span>
                <span>Jenis kelamin: <strong>Laki-laki</strong> atau <strong>Perempuan</strong></span>
              </li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Upload Card -->
      <div class="bg-white rounded-xl shadow-sm p-8 border border-gray-100">
        <h3 class="text-xl font-semibold text-gray-900 mb-6">Upload File Excel</h3>

        <!-- Drag & Drop Area -->
        <div
          @dragover.prevent="isDragging = true"
          @dragleave.prevent="isDragging = false"
          @drop.prevent="handleDrop"
          :class="[
            'border-2 border-dashed rounded-xl p-12 text-center transition-all',
            isDragging 
              ? 'border-blue-500 bg-blue-50' 
              : 'border-gray-300 bg-gray-50 hover:border-blue-400 hover:bg-blue-50'
          ]"
        >
          <input
            ref="fileInput"
            type="file"
            accept=".xls,.xlsx"
            class="hidden"
            @change="handleFileSelect"
          />

          <div v-if="!selectedFile">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
            </svg>
            <p class="text-lg font-medium text-gray-700 mb-2">
              {{ isDragging ? 'Lepaskan file di sini' : 'Drag & Drop file Excel atau' }}
            </p>
            <button
              @click="$refs.fileInput.click()"
              class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors shadow-md font-medium"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
              </svg>
              <span>Pilih File</span>
            </button>
            <p class="text-sm text-gray-500 mt-3">Format: .xls, .xlsx (Max: 5 MB)</p>
          </div>

          <!-- Selected File Info -->
          <div v-else class="flex items-center justify-center gap-4">
            <div class="flex items-center gap-3 bg-white px-6 py-4 rounded-lg border border-gray-200 shadow-sm">
              <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              <div class="text-left">
                <p class="font-medium text-gray-900">{{ selectedFile.name }}</p>
                <p class="text-sm text-gray-500">{{ formatFileSize(selectedFile.size) }}</p>
              </div>
              <button
                @click="removeFile"
                class="ml-4 p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                title="Hapus file"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-between mt-6">
          <NuxtLink 
            to="/karyawan" 
            class="inline-flex items-center gap-2 px-6 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali</span>
          </NuxtLink>
          
          <div class="flex gap-3">
            <button
              @click="removeFile"
              :disabled="!selectedFile || uploading"
              class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Reset
            </button>
            <button
              @click="handleUpload"
              :disabled="!selectedFile || uploading"
              class="inline-flex items-center gap-2 px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors shadow-md font-medium disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg v-if="!uploading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
              </svg>
              <div v-else class="animate-spin rounded-full h-5 w-5 border-2 border-white border-t-transparent"></div>
              <span>{{ uploading ? 'Mengupload...' : 'Upload & Import' }}</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Result Card -->
      <div v-if="importResult" class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
        <div :class="[
          'p-6',
          importResult.berhasil ? 'bg-green-50 border-b border-green-200' : 'bg-red-50 border-b border-red-200'
        ]">
          <div class="flex items-start gap-4">
            <div :class="[
              'flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center',
              importResult.berhasil ? 'bg-green-500' : 'bg-red-500'
            ]">
              <svg v-if="importResult.berhasil" class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <svg v-else class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </div>
            <div class="flex-1">
              <h3 :class="[
                'text-xl font-bold mb-2',
                importResult.berhasil ? 'text-green-900' : 'text-red-900'
              ]">
                {{ importResult.berhasil ? 'Import Berhasil!' : 'Import Gagal' }}
              </h3>
              <p :class="[
                'text-sm',
                importResult.berhasil ? 'text-green-700' : 'text-red-700'
              ]">
                {{ importResult.pesan }}
              </p>
            </div>
          </div>
        </div>

        <!-- Summary -->
        <div v-if="importResult.data?.summary" class="p-6 bg-gray-50">
          <h4 class="font-semibold text-gray-900 mb-4">Ringkasan Import</h4>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-lg border border-gray-200">
              <p class="text-sm text-gray-600 mb-1">Total Data</p>
              <p class="text-2xl font-bold text-gray-900">{{ importResult.data.summary.total_processed }}</p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg border border-green-200">
              <p class="text-sm text-green-600 mb-1">Berhasil</p>
              <p class="text-2xl font-bold text-green-700">{{ importResult.data.summary.success }}</p>
            </div>
            <div class="bg-red-50 p-4 rounded-lg border border-red-200">
              <p class="text-sm text-red-600 mb-1">Gagal</p>
              <p class="text-2xl font-bold text-red-700">{{ importResult.data.summary.failed }}</p>
            </div>
            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
              <p class="text-sm text-blue-600 mb-1">Success Rate</p>
              <p class="text-2xl font-bold text-blue-700">{{ importResult.data.summary.success_rate }}</p>
            </div>
          </div>
        </div>

        <!-- Errors List -->
        <div v-if="importResult.data?.errors?.length > 0" class="p-6 border-t border-gray-200">
          <div class="flex items-center justify-between mb-4">
            <h4 class="font-semibold text-gray-900">Detail Error ({{ importResult.data.errors.length }})</h4>
            <button
              @click="showErrors = !showErrors"
              class="text-sm text-blue-600 hover:text-blue-700 font-medium"
            >
              {{ showErrors ? 'Sembunyikan' : 'Tampilkan' }}
            </button>
          </div>
          
          <div v-if="showErrors" class="space-y-2 max-h-96 overflow-y-auto">
            <div
              v-for="(error, index) in importResult.data.errors"
              :key="index"
              class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700"
            >
              {{ error }}
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="p-6 bg-gray-50 border-t border-gray-200 flex gap-3 justify-end">
          <button
            @click="resetImport"
            class="px-5 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium"
          >
            Import Lagi
          </button>
          <NuxtLink
            to="/karyawan"
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors shadow-md font-medium"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            <span>Lihat Data Karyawan</span>
          </NuxtLink>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useApi } from '~/composables/useApi'

const api = useApi()

const fileInput = ref<HTMLInputElement | null>(null)
const selectedFile = ref<File | null>(null)
const isDragging = ref(false)
const uploading = ref(false)
const importResult = ref<any>(null)
const showErrors = ref(false)

// Template URL from public folder
const templateUrl = computed(() => {
  return '/template/sample_karyawan_import.xlsx'
})

const handleFileSelect = (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  if (file) {
    validateAndSetFile(file)
  }
}

const handleDrop = (event: DragEvent) => {
  isDragging.value = false
  const file = event.dataTransfer?.files[0]
  if (file) {
    validateAndSetFile(file)
  }
}

const validateAndSetFile = (file: File) => {
  const validExtensions = ['xls', 'xlsx']
  const extension = file.name.split('.').pop()?.toLowerCase() || ''
  
  if (!validExtensions.includes(extension)) {
    alert('File harus berformat Excel (.xls atau .xlsx)')
    return
  }
  
  if (file.size > 5 * 1024 * 1024) {
    alert('Ukuran file maksimal 5 MB')
    return
  }
  
  selectedFile.value = file
  importResult.value = null
}

const removeFile = () => {
  selectedFile.value = null
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const handleUpload = async () => {
  if (!selectedFile.value) return
  
  uploading.value = true
  importResult.value = null
  
  try {
    const response = await api.karyawan.importExcel(selectedFile.value)
    importResult.value = response
    
    if (response.berhasil) {
      // Auto scroll to result
      setTimeout(() => {
        window.scrollTo({
          top: document.body.scrollHeight,
          behavior: 'smooth'
        })
      }, 100)
    }
  } catch (error) {
    console.error('Upload error:', error)
    importResult.value = {
      berhasil: false,
      pesan: 'Terjadi kesalahan saat upload file',
      data: null
    }
  } finally {
    uploading.value = false
  }
}

const resetImport = () => {
  selectedFile.value = null
  importResult.value = null
  showErrors.value = false
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const formatFileSize = (bytes: number) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
}
</script>

<style scoped>
@keyframes blob {
  0%, 100% { transform: translate(0, 0) scale(1); }
  33% { transform: translate(30px, -50px) scale(1.1); }
  66% { transform: translate(-20px, 20px) scale(0.9); }
}

.animate-blob {
  animation: blob 7s infinite;
}

.animation-delay-2000 {
  animation-delay: 2s;
}

.animation-delay-4000 {
  animation-delay: 4s;
}

.animate-pulse-slow {
  animation: pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>