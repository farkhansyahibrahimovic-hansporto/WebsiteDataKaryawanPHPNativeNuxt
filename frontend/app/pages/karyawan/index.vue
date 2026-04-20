<template>
  <div class="min-h-screen relative bg-blue-50 p-4 md:p-6 overflow-hidden">
    <!-- Abstract Background Elements -->
    <div class="absolute inset-0">
      <div class="w-full h-full bg-gradient-to-tr from-blue-100 via-blue-200 to-blue-300 opacity-50 animate-pulse-slow"></div>
      <div class="absolute -top-20 -left-20 w-96 h-96 bg-blue-400 rounded-full opacity-30 mix-blend-multiply animate-blob"></div>
      <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-blue-500 rounded-full opacity-30 mix-blend-multiply animate-blob animation-delay-2000"></div>
      <div class="absolute top-1/3 right-1/4 w-80 h-80 bg-blue-300 rounded-full opacity-20 mix-blend-multiply animate-blob animation-delay-4000"></div>
    </div>

    <div class="max-w-7xl mx-auto space-y-6 relative z-10">
      <!-- Header -->
      <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
          <div>
            <h1 class="text-3xl font-bold text-gray-800">Data Karyawan</h1>
            <p class="text-gray-500 mt-1">Kelola data karyawan perusahaan Anda</p>
          </div>
          <div class="flex flex-wrap gap-2">
            <NuxtLink 
              to="/karyawan/tambah" 
              class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors shadow-md"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              <span class="font-medium">Tambah Data</span>
            </NuxtLink>
            
            <!-- Export Buttons -->
            <button
              @click="handleExportPDF"
              :disabled="loading || exporting"
              class="inline-flex items-center gap-2 px-5 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors shadow-md disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
              </svg>
              <span class="font-medium">{{ exporting ? 'Exporting...' : 'Export PDF' }}</span>
            </button>

            <button
              @click="handleExportExcel"
              :disabled="loading || exporting"
              class="inline-flex items-center gap-2 px-5 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors shadow-md disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              <span class="font-medium">{{ exporting ? 'Exporting...' : 'Export Excel' }}</span>
            </button>

            <!-- Import Button -->
            <NuxtLink
              to="/karyawan/import"
              class="inline-flex items-center gap-2 px-5 py-2.5 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors shadow-md"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
              </svg>
              <span class="font-medium">Import Excel</span>
            </NuxtLink>
          </div>
        </div>
      </div>

      <!-- Alert Messages -->
      <div v-if="message" :class="[
        'p-4 rounded-lg border',
        message.type === 'success' ? 'bg-green-50 text-green-800 border-green-200' : 'bg-red-50 text-red-800 border-red-200'
      ]">
        <div class="flex items-start gap-3">
          <svg v-if="message.type === 'success'" class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
          </svg>
          <svg v-else class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
          </svg>
          <span>{{ message.text }}</span>
        </div>
      </div>

      <!-- Filter & Search Card -->
      <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <div class="space-y-4">
          <!-- Search Bar -->
          <div class="flex gap-3">
            <div class="flex-1 relative">
              <input 
                v-model="searchQuery"
                type="text"
                placeholder="Cari nama, email, atau telepon..."
                class="w-full px-4 py-2.5 pl-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                @keyup.enter="applySearch"
              />
              <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
            <button 
              @click="applySearch" 
              class="px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium shadow-md"
            >
              Cari
            </button>
            <button 
              @click="refreshData" 
              class="px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors shadow-md"
              title="Refresh Data"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
            </button>
            <button 
              @click="showFilters = !showFilters" 
              class="px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors shadow-md"
              :class="{'ring-2 ring-blue-300': showFilters}"
              title="Filter Data"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L15 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 019 21v-7.586L3.293 6.707A1 1 0 013 6V4z" />
              </svg>
            </button>
          </div>

          <!-- Advanced Filters -->
          <div v-if="showFilters" class="pt-4 border-t border-gray-200 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kelamin</label>
                <select v-model="tempFilters.jenis_kelamin" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                  <option value="">Semua</option>
                  <option value="Laki-laki">Laki-laki</option>
                  <option value="Perempuan">Perempuan</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan</label>
                <select v-model="tempFilters.jabatan_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                  <option value="">Semua Jabatan</option>
                  <option v-for="j in jabatanList" :key="j.id_jabatan" :value="j.id_jabatan">{{ j.nama_jabatan }}</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Gaji Minimum</label>
                <input 
                  v-model="tempFilters.salary_min" 
                  type="number" 
                  placeholder="0" 
                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Gaji Maximum</label>
                <input 
                  v-model="tempFilters.salary_max" 
                  type="number" 
                  placeholder="0" 
                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Umur Minimum</label>
                <input 
                  v-model="tempFilters.age_min" 
                  type="number" 
                  placeholder="0" 
                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Umur Maximum</label>
                <input 
                  v-model="tempFilters.age_max" 
                  type="number" 
                  placeholder="0" 
                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
            </div>

            <!-- Filter Actions -->
            <div class="flex gap-3 justify-end pt-2">
              <button 
                @click="resetFilters" 
                class="px-5 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium"
              >
                Reset
              </button>
              <button 
                @click="applyFilters" 
                class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium shadow-md"
              >
                Terapkan Filter
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="bg-white rounded-xl shadow-sm p-12 text-center border border-gray-100">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-gray-200 border-t-blue-600"></div>
        <p class="text-gray-600 mt-4">Memuat data...</p>
      </div>

      <!-- Table -->
      <div v-else-if="paginatedList.length > 0" class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-blue-600 text-white">
              <tr>
                <th class="px-6 py-4 text-center text-sm font-semibold">No</th>
                <th class="px-6 py-4 text-left text-sm font-semibold">Nama</th>
                <th class="px-6 py-4 text-left text-sm font-semibold">Email</th>
                <th class="px-6 py-4 text-left text-sm font-semibold">Telepon</th>
                <th class="px-6 py-4 text-center text-sm font-semibold">JK</th>
                <th class="px-6 py-4 text-left text-sm font-semibold">Jabatan</th>
                <th class="px-6 py-4 text-right text-sm font-semibold">Gaji</th>
                <th class="px-6 py-4 text-center text-sm font-semibold">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="(k, index) in paginatedList" :key="k.id_karyawan" class="hover:bg-blue-50 transition-colors">
                <td class="px-6 py-4 text-sm text-center font-medium text-gray-700">{{ ((currentPage - 1) * perPage) + index + 1 }}</td>
                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ k.nama_lengkap }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ k.email }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ k.telepon }}</td>
                <td class="px-6 py-4 text-sm text-center text-gray-600">{{ k.jenis_kelamin }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ k.nama_jabatan }}</td>
                <td class="px-6 py-4 text-sm text-right font-medium text-gray-900">Rp {{ formatCurrency(k.gaji_pokok) }}</td>
                <td class="px-6 py-4">
                  <div class="flex gap-2 justify-center">
                    <!-- Tombol Detail dengan icon info -->
                    <NuxtLink 
                      :to="`/karyawan/${k.id_karyawan}`" 
                      class="p-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600 transition-colors"
                      title="Detail"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                    </NuxtLink>
                    
                    <!-- Tombol Edit dengan icon pensil -->
                    <NuxtLink 
                      :to="`/karyawan/edit/${k.id_karyawan}`" 
                      class="p-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
                      title="Edit"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </NuxtLink>
                    
                    <!-- Tombol Hapus dengan icon trash -->
                    <button 
                      @click="confirmDelete(k)" 
                      class="p-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors"
                      title="Hapus"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-600">
              Menampilkan {{ ((currentPage - 1) * perPage) + 1 }} - {{ Math.min(currentPage * perPage, karyawanList.length) }} dari {{ karyawanList.length }} data
            </div>
            <div class="flex gap-2">
              <button 
                @click="prevPage" 
                :disabled="currentPage === 1"
                class="px-3 py-2 rounded-lg border transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                :class="currentPage === 1 ? 'border-gray-200 text-gray-400 bg-white' : 'border-gray-300 text-gray-700 bg-white hover:bg-gray-50'"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
              </button>

              <div class="hidden sm:flex gap-2">
                <button 
                  v-for="p in visiblePages" 
                  :key="p" 
                  @click="goToPage(p)"
                  class="min-w-[40px] px-3 py-2 rounded-lg font-medium transition-colors"
                  :class="currentPage === p 
                    ? 'bg-blue-600 text-white shadow-sm' 
                    : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50'"
                >
                  {{ p }}
                </button>
              </div>

              <div class="sm:hidden">
                <span class="px-4 py-2 text-sm font-medium text-gray-700">
                  {{ currentPage }} / {{ totalPages }}
                </span>
              </div>

              <button 
                @click="nextPage" 
                :disabled="currentPage === totalPages"
                class="px-3 py-2 rounded-lg border transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                :class="currentPage === totalPages ? 'border-gray-200 text-gray-400 bg-white' : 'border-gray-300 text-gray-700 bg-white hover:bg-gray-50'"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="bg-white rounded-xl shadow-sm p-12 text-center border border-gray-100">
        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
        </svg>
        <p class="text-gray-600 mb-4">Tidak ada data karyawan</p>
        <NuxtLink 
          to="/karyawan/tambah" 
          class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium shadow-md"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          <span>Tambah Data</span>
        </NuxtLink>
      </div>

      <!-- Delete Modal -->
      <Teleport to="body">
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" @click="showDeleteModal = false">
          <div class="bg-white rounded-xl p-6 max-w-md w-full shadow-2xl" @click.stop>
            <div class="text-center">
              <div class="w-12 h-12 rounded-full bg-red-100 mx-auto mb-4 flex items-center justify-center">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
              </div>
              <h3 class="text-xl font-bold text-gray-900 mb-2">Konfirmasi Hapus</h3>
              <p class="text-gray-600 mb-6">
                Apakah Anda yakin ingin menghapus data karyawan <strong class="text-gray-900">{{ deleteTarget?.nama_lengkap }}</strong>? Tindakan ini tidak dapat dibatalkan.
              </p>
              <div class="flex gap-3 justify-center">
                <button 
                  @click="showDeleteModal = false" 
                  class="px-5 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium"
                >
                  Batal
                </button>
                <button 
                  @click="deleteKaryawan" 
                  class="px-5 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium shadow-md"
                >
                  Ya, Hapus
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Success Modal -->
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
                Data karyawan berhasil dihapus dari sistem.
              </p>
              <button 
                @click="showSuccessModal = false" 
                class="px-6 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium shadow-md"
              >
                OK
              </button>
            </div>
          </div>
        </div>
      </Teleport>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { useApi } from '~/composables/useApi'

const api = useApi()
const loading = ref(true)
const exporting = ref(false)
const showFilters = ref(false)
const showDeleteModal = ref(false)
const showSuccessModal = ref(false)
const deleteTarget = ref<any>(null)
const message = ref<{ type: 'success' | 'error', text: string } | null>(null)

const karyawanList = ref<any[]>([])
const jabatanList = ref<any[]>([])

const searchQuery = ref('')
const appliedFilters = reactive({
  q: '',
  jenis_kelamin: '',
  jabatan_id: '',
  salary_min: '',
  salary_max: '',
  age_min: '',
  age_max: ''
})

const tempFilters = reactive({
  jenis_kelamin: '',
  jabatan_id: '',
  salary_min: '',
  salary_max: '',
  age_min: '',
  age_max: ''
})

// Pagination
const currentPage = ref(1)
const perPage = 10
const totalPages = computed(() => Math.ceil(karyawanList.value.length / perPage))
const paginatedList = computed(() => {
  const start = (currentPage.value - 1) * perPage
  return karyawanList.value.slice(start, start + perPage)
})

const visiblePages = computed(() => {
  const total = totalPages.value
  const current = currentPage.value
  const pages = []
  
  if (total <= 7) {
    for (let i = 1; i <= total; i++) pages.push(i)
  } else {
    if (current <= 4) {
      for (let i = 1; i <= 5; i++) pages.push(i)
      pages.push('...')
      pages.push(total)
    } else if (current >= total - 3) {
      pages.push(1)
      pages.push('...')
      for (let i = total - 4; i <= total; i++) pages.push(i)
    } else {
      pages.push(1)
      pages.push('...')
      for (let i = current - 1; i <= current + 1; i++) pages.push(i)
      pages.push('...')
      pages.push(total)
    }
  }
  
  return pages
})

const nextPage = () => { if(currentPage.value < totalPages.value) currentPage.value++ }
const prevPage = () => { if(currentPage.value > 1) currentPage.value-- }
const goToPage = (page: number | string) => { 
  if (typeof page === 'number') currentPage.value = page 
}

const loadKaryawan = async () => {
  loading.value = true
  const cleanFilters = Object.fromEntries(
    Object.entries(appliedFilters).filter(([_, v]) => v !== '' && v != null)
  )
  try {
    const response = await api.karyawan.getAll(cleanFilters)
    karyawanList.value = response?.berhasil ? response.data?.data || [] : []
    currentPage.value = 1
  } catch (err) {
    console.error('Gagal load karyawan:', err)
    karyawanList.value = []
  }
  loading.value = false
}

const loadJabatan = async () => {
  try {
    const response = await api.jabatan.getAll()
    jabatanList.value = response?.berhasil ? response.data?.data || [] : []
  } catch (err) {
    console.error('Gagal load jabatan:', err)
    jabatanList.value = []
  }
}

const applySearch = () => {
  appliedFilters.q = searchQuery.value
  loadKaryawan()
}

const applyFilters = () => {
  appliedFilters.jenis_kelamin = tempFilters.jenis_kelamin
  appliedFilters.jabatan_id = tempFilters.jabatan_id
  appliedFilters.salary_min = tempFilters.salary_min
  appliedFilters.salary_max = tempFilters.salary_max
  appliedFilters.age_min = tempFilters.age_min
  appliedFilters.age_max = tempFilters.age_max
  loadKaryawan()
}

const resetFilters = () => {
  searchQuery.value = ''
  Object.keys(appliedFilters).forEach(k => appliedFilters[k] = '')
  Object.keys(tempFilters).forEach(k => tempFilters[k] = '')
  loadKaryawan()
}

const refreshData = () => {
  loadKaryawan()
  loadJabatan()
}

const confirmDelete = (karyawan: any) => { 
  deleteTarget.value = karyawan
  showDeleteModal.value = true 
}

const deleteKaryawan = async () => {
  if(!deleteTarget.value) return
  try {
    const response = await api.karyawan.delete(deleteTarget.value.id_karyawan)
    if(response?.berhasil) {
      showDeleteModal.value = false
      showSuccessModal.value = true
      await loadKaryawan()
      deleteTarget.value = null
      
      setTimeout(() => {
        showSuccessModal.value = false
      }, 2000)
    }
  } catch(err) { 
    console.error('Gagal hapus karyawan:', err) 
  }
}

// Export Functions
const handleExportPDF = async () => {
  exporting.value = true
  message.value = null
  
  const cleanFilters = Object.fromEntries(
    Object.entries(appliedFilters).filter(([_, v]) => v !== '' && v != null)
  )
  
  const response = await api.karyawan.exportPDF(cleanFilters)
  
  exporting.value = false
  
  if (response.berhasil) {
    message.value = { type: 'success', text: 'Export PDF berhasil! File telah didownload.' }
  } else {
    message.value = { type: 'error', text: response.pesan || 'Gagal export PDF' }
  }
  
  setTimeout(() => { message.value = null }, 5000)
}

const handleExportExcel = async () => {
  exporting.value = true
  message.value = null
  
  const cleanFilters = Object.fromEntries(
    Object.entries(appliedFilters).filter(([_, v]) => v !== '' && v != null)
  )
  
  const response = await api.karyawan.exportExcel(cleanFilters)
  
  exporting.value = false
  
  if (response.berhasil) {
    message.value = { type: 'success', text: 'Export Excel berhasil! File telah didownload.' }
  } else {
    message.value = { type: 'error', text: response.pesan || 'Gagal export Excel' }
  }
  
  setTimeout(() => { message.value = null }, 5000)
}

const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('id-ID').format(value)
}

onMounted(() => {
  loadKaryawan()
  loadJabatan()
})
</script>