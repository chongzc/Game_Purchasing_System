<script setup>
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()

// Check the authentication status when the app loads
authStore.checkAuth()
</script>

<template>
  <VApp>
    <VMain>
      <RouterView />
    </VMain>
  </VApp>
</template>
