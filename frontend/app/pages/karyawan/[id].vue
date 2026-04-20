<template>
  <div class="min-h-screen relative bg-blue-50 p-4 overflow-hidden">
    <!-- Abstract Background Elements -->
    <div class="absolute inset-0">
      <div class="w-full h-full bg-gradient-to-tr from-blue-100 via-blue-200 to-blue-300 opacity-50 animate-pulse-slow"></div>
      <div class="absolute -top-20 -left-20 w-96 h-96 bg-blue-400 rounded-full opacity-30 mix-blend-multiply animate-blob"></div>
      <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-blue-500 rounded-full opacity-30 mix-blend-multiply animate-blob animation-delay-2000"></div>
      <div class="absolute top-1/3 right-1/4 w-80 h-80 bg-blue-300 rounded-full opacity-20 mix-blend-multiply animate-blob animation-delay-4000"></div>
    </div>

    <div class="max-w-4xl mx-auto relative z-10">
      <!-- Loading State -->
      <div v-if="loading" class="bg-white rounded-xl shadow-sm p-12 text-center border border-gray-100">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-gray-200 border-t-blue-600"></div>
        <p class="text-gray-600 mt-4">Memuat data karyawan...</p>
      </div>

      <!-- Content -->
      <div v-else-if="karyawan">
        <!-- Header Title -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6 border border-gray-100">
          <h1 class="text-3xl font-bold text-gray-800">Detail Karyawan</h1>
          <p class="text-gray-500 mt-1">Informasi lengkap data karyawan</p>
        </div>

        <!-- Profile Card -->
        <div id="print-area" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
          <div class="bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-600 p-8">
            <div class="flex flex-col md:flex-row items-center gap-6">
              <div class="relative">
                <div class="w-32 h-32 rounded-full overflow-hidden border-4 border-white shadow-xl ring-4 ring-blue-200">
                  <img 
                    v-if="karyawan.foto" 
                    :src="`http://localhost/karyawan_nt/public/upload/foto/${karyawan.foto}`" 
                    :alt="karyawan.nama_lengkap"
                    class="w-full h-full object-cover"
                  />
                  <div v-else class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-200 to-gray-300">
                    <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                  </div>
                </div>
                <div class="absolute -bottom-2 -right-2 bg-green-500 rounded-full p-2 shadow-lg border-2 border-white">
                  <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                  </svg>
                </div>
              </div>
              <div class="flex-1 text-center md:text-left">
                <h2 class="text-3xl font-bold text-white mb-1">{{ karyawan.nama_lengkap }}</h2>
                <p class="text-blue-100 text-lg mb-3 flex items-center justify-center md:justify-start gap-2">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                  </svg>
                  {{ karyawan.nama_jabatan }}
                </p>
                <div class="flex flex-wrap justify-center md:justify-start gap-2">
                  <span :class="karyawan.jenis_kelamin === 'Laki-laki' ? 'bg-blue-500' : 'bg-pink-500'" 
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm text-white font-medium shadow-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    {{ karyawan.jenis_kelamin }}
                  </span>
                  <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm bg-white bg-opacity-20 backdrop-blur-sm text-white font-medium shadow-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    {{ calculateAge(karyawan.tanggal_lahir) }} Tahun
                  </span>
                  <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm bg-white bg-opacity-20 backdrop-blur-sm text-white font-medium shadow-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Rp {{ formatCurrency(karyawan.gaji_pokok) }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Info Grid -->
          <div class="p-8">
            <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              Informasi Personal
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <InfoItem 
                label="Tempat Lahir" 
                :value="karyawan.tempat_lahir"
                icon="location"
              />
              <InfoItem 
                label="Tanggal Lahir" 
                :value="formatDate(karyawan.tanggal_lahir)"
                icon="calendar"
              />
              <InfoItem 
                label="Email" 
                :value="karyawan.email" 
                icon="email"
              />
              <InfoItem 
                label="Telepon" 
                :value="karyawan.telepon" 
                icon="phone"
              />
              <InfoItem 
                label="Jabatan" 
                :value="karyawan.nama_jabatan"
                icon="briefcase"
              />
              <InfoItem 
                label="Gaji Pokok" 
                :value="`Rp ${formatCurrency(karyawan.gaji_pokok)}`"
                icon="money"
              />
            </div>
          </div>
        </div>

        <!-- Action Buttons - Moved Below Detail -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
          <div class="flex justify-between items-center gap-4">
            <button 
              @click="$router.back()" 
              class="inline-flex items-center gap-2 px-5 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
              </svg>
              Kembali
            </button>

            <div class="flex gap-3">
              <button 
                @click="printDetail" 
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors shadow-md font-medium"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Cetak
              </button>
              
              <NuxtLink 
                :to="`/karyawan/edit/${karyawan.id_karyawan}`"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors shadow-md font-medium"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit Data
              </NuxtLink>
              
              <button 
                @click="confirmDelete"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors shadow-md font-medium"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Hapus Data
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Error State -->
      <div v-else class="bg-white rounded-xl shadow-sm p-12 text-center border border-gray-100">
        <svg class="w-20 h-20 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p class="text-gray-600 text-xl mb-2 font-semibold">Data Tidak Ditemukan</p>
        <p class="text-gray-500 mb-6">Karyawan yang Anda cari tidak tersedia dalam sistem</p>
        <button 
          @click="$router.back()" 
          class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors shadow-md font-medium"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Kembali
        </button>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <Teleport to="body">
      <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" @click="showDeleteModal = false">
        <div class="bg-white rounded-xl p-8 max-w-md w-full shadow-2xl animate-scale-in" @click.stop>
          <div class="text-center">
            <div class="w-16 h-16 rounded-full bg-red-100 mx-auto mb-4 flex items-center justify-center">
              <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-2">Konfirmasi Hapus</h3>
            <p class="text-gray-600 mb-6">
              Apakah Anda yakin ingin menghapus data karyawan <strong class="text-gray-900">{{ karyawan?.nama_lengkap }}</strong>? Tindakan ini tidak dapat dibatalkan.
            </p>
            <div class="flex gap-3">
              <button 
                @click="showDeleteModal = false" 
                class="flex-1 px-5 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium"
              >
                Batal
              </button>
              <button 
                @click="deleteKaryawan" 
                class="flex-1 px-5 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium shadow-md"
              >
                Ya, Hapus
              </button>
            </div>
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
const loading = ref(true)
const karyawan = ref<any>(null)
const showDeleteModal = ref(false)

const loadDetail = async () => {
  loading.value = true
  const response = await api.karyawan.getById(route.params.id as string)
  if (response.berhasil) karyawan.value = response.data
  loading.value = false
}

const formatDate = (date: string) => new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })
const calculateAge = (birthDate: string) => {
  const today = new Date(), birth = new Date(birthDate)
  let age = today.getFullYear() - birth.getFullYear()
  if (today.getMonth() < birth.getMonth() || (today.getMonth() === birth.getMonth() && today.getDate() < birth.getDate())) age--
  return age
}
const formatCurrency = (value: number) => new Intl.NumberFormat('id-ID').format(value)
const printDetail = () => window.print()

const confirmDelete = () => {
  showDeleteModal.value = true
}

const deleteKaryawan = async () => {
  if (!karyawan.value) return
  try {
    const response = await api.karyawan.delete(karyawan.value.id_karyawan)
    if (response?.berhasil) {
      showDeleteModal.value = false
      router.push('/karyawan')
    }
  } catch (err) {
    console.error('Gagal hapus karyawan:', err)
    alert('Gagal menghapus data karyawan')
  }
}

onMounted(() => loadDetail())
</script>

<script lang="ts">
import { defineComponent, h } from 'vue'

const InfoItem = defineComponent({
  props: { 
    label: String, 
    value: String, 
    icon: { type: String, default: 'info' }
  },
  setup(props) {
    const getIcon = () => {
      const iconClass = 'w-5 h-5 text-blue-600'
      const icons = {
        email: h('svg', { class: iconClass, fill:'none', stroke:'currentColor', viewBox:'0 0 24 24' }, [ 
          h('path', { d:'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'stroke-linecap':'round','stroke-linejoin':'round','stroke-width':'2' })
        ]),
        phone: h('svg', { class: iconClass, fill:'none', stroke:'currentColor', viewBox:'0 0 24 24' }, [ 
          h('path', { d:'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z','stroke-linecap':'round','stroke-linejoin':'round','stroke-width':'2'})
        ]),
        location: h('svg', { class: iconClass, fill:'none', stroke:'currentColor', viewBox:'0 0 24 24' }, [ 
          h('path', { d:'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z','stroke-linecap':'round','stroke-linejoin':'round','stroke-width':'2'}),
          h('path', { d:'M15 11a3 3 0 11-6 0 3 3 0 016 0z','stroke-linecap':'round','stroke-linejoin':'round','stroke-width':'2'})
        ]),
        calendar: h('svg', { class: iconClass, fill:'none', stroke:'currentColor', viewBox:'0 0 24 24' }, [ 
          h('path', { d:'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z','stroke-linecap':'round','stroke-linejoin':'round','stroke-width':'2'})
        ]),
        briefcase: h('svg', { class: iconClass, fill:'none', stroke:'currentColor', viewBox:'0 0 24 24' }, [ 
          h('path', { d:'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z','stroke-linecap':'round','stroke-linejoin':'round','stroke-width':'2'})
        ]),
        money: h('svg', { class: iconClass, fill:'none', stroke:'currentColor', viewBox:'0 0 24 24' }, [ 
          h('path', { d:'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z','stroke-linecap':'round','stroke-linejoin':'round','stroke-width':'2'})
        ]),
      }
      return icons[props.icon] || null
    }

    return () => h('div', { 
      class:'bg-gradient-to-br from-blue-50 to-blue-100 p-4 rounded-xl border border-blue-200 hover:shadow-md transition-all duration-200 hover:scale-[1.02]'
    }, [ 
      h('div', { class: 'flex items-start gap-3' }, [
        h('div', { class: 'mt-0.5' }, [getIcon()]),
        h('div', { class: 'flex-1' }, [
          h('p', { class: 'text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1' }, props.label),
          h('p', { class: 'font-semibold text-gray-800 text-base' }, props.value)
        ])
      ])
    ])
  }
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

/* Print Styles */
@media print {
  body * { 
    visibility: hidden; 
  }
  #print-area, #print-area * { 
    visibility: visible; 
  }
  #print-area { 
    position: absolute; 
    top: 0; 
    left: 0; 
    width: 100%; 
  }
  .no-print {
    display: none !important;
  }
}
</style>