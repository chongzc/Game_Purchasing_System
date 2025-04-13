<template>
  <nav class="bg-gray-800 text-white p-4">
    <div class="container mx-auto flex items-center justify-between">
      <div class="flex space-x-4">
        <RouterLink
          v-for="item in navigationItems"
          :key="item.to"
          :to="item.to"
          class="hover:text-gray-300 px-3 py-2 rounded-md"
          :class="{ 'bg-gray-900': $route.path === item.to }"
        >
          {{ item.title }}
        </RouterLink>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth'
import { getHeaderNavigationItems } from '@/utils/header-navigation'
import { computed } from 'vue'

const auth = useAuthStore()
const navigationItems = computed(() => getHeaderNavigationItems(auth.user))
</script>
