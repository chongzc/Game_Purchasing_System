<script setup>
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()

// Check the authentication status when the app loads
authStore.checkAuth()

// Fetch user data when app loads
authStore.getUserData().catch(error => {
  console.error('Failed to fetch user data on app load:', error)
})
</script>

<template>
  <VApp>
    <VMain>
      <RouterView />
    </VMain>
  </VApp>
</template>
