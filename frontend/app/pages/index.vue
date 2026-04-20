<template>
  <div class="min-h-screen flex items-center justify-center relative overflow-hidden bg-blue-50">
    <!-- Background abstrak -->
    <div class="absolute inset-0">
      <div class="w-full h-full bg-gradient-to-tr from-blue-100 via-blue-200 to-blue-300 opacity-50 animate-pulse-slow"></div>
      <div class="absolute -top-20 -left-20 w-96 h-96 bg-blue-400 rounded-full opacity-30 mix-blend-multiply animate-blob"></div>
      <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-blue-500 rounded-full opacity-30 mix-blend-multiply animate-blob animation-delay-2000"></div>
    </div>
    
    <!-- Konten Utama -->
    <div class="relative text-center animate-slide-in p-8 max-w-lg bg-white/80 rounded-2xl shadow-lg">
      <!-- Hero Icon -->
      <div class="mb-8 flex justify-center">
        <div class="relative">
          <div class="w-32 h-32 bg-blue-500 rounded-3xl flex items-center justify-center shadow-2xl transform hover:rotate-6 transition-transform duration-300">
            <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
          </div>
          <div class="absolute -top-2 -right-2 w-6 h-6 bg-green-500 rounded-full animate-pulse"></div>
        </div>
      </div>

      <!-- Welcome Text -->
      <h1 class="text-5xl font-bold mb-2 text-blue-600">
        Selamat Datang
      </h1>
      <h2 class="text-2xl font-semibold text-blue-700 mb-4">
        PT Nusantara Teknologi
      </h2>
      <p class="text-blue-800 text-lg mb-8">
        Sistem manajemen karyawan modern dan efisien. Kelola data karyawan dengan mudah.
      </p>

      <!-- CTA Button -->
      <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
        <NuxtLink to="/karyawan" class="px-6 py-3 bg-blue-600 text-white rounded-xl shadow hover:bg-blue-700 transition-colors flex items-center space-x-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
          </svg>
          <span>Lihat Data Karyawan</span>
        </NuxtLink>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'default'
})
</script>

<style>
/* Animasi abstrak */
@keyframes blob {
  0%, 100% { transform: translate(0, 0) scale(1); }
  33% { transform: translate(30px, -50px) scale(1.1); }
  66% { transform: translate(-20px, 20px) scale(0.9); }
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
</style>


