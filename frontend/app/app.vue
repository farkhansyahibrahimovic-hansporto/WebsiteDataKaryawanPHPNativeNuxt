<script setup>
import { useRouter } from 'vue-router'

const router = useRouter()
router.replace('/')   // redirect ke pages/index.vue
</script>

<template>
  <NuxtPage />
</template>


