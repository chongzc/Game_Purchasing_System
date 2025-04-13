<script setup>
import UserProfile from '@/layouts/components/UserProfile.vue'
import { useAuthStore } from '@/stores/auth'
import { getHeaderNavigationItems } from '@/utils/header-navigation'
import { useWindowScroll } from '@vueuse/core'
import { computed } from 'vue'
import { useDisplay } from 'vuetify'

const display = useDisplay()
const { y } = useWindowScroll()
const auth = useAuthStore()
const headerNavigationItems = computed(() => getHeaderNavigationItems(auth.user))
</script>

<template>
  <div class="front-page-navbar">
    <VAppBar
      color="rgba(var(--v-theme-surface),0.85)"
      class="elevation-0"
      :class="[y > 10 ? 'app-bar-scrolled' : 'app-bar-dark']"
    >
      <div class="navbar-content">
        <!-- Logo and nav items -->
        <div class="d-flex align-center">
          <VAppBarTitle class="me-6">
            <RouterLink
              to="/"
              class="d-flex align-center text-decoration-none"
            >
              <span class="font-weight-bold text-primary">Game Store</span>
            </RouterLink>
          </VAppBarTitle>

          <!-- Desktop navigation -->
          <div class="text-base align-center d-flex">
            <RouterLink
              v-for="(item, index) in headerNavigationItems"
              :key="index"
              :to="item.to"
              class="nav-link font-weight-medium py-2 px-2 px-lg-4"
            >
              {{ item.title }}
            </RouterLink>
          </div>
        </div>

        <VSpacer />

        <!-- Right side items -->
        <div class="d-flex gap-x-4 align-center">
          <UserProfile />
        </div>
      </div>
    </VAppBar>
  </div>
</template>

<style lang="scss" scoped>
.nav-link {
  color: rgb(var(--v-theme-on-surface));
  text-decoration: none;
  
  &:hover {
    color: rgb(var(--v-theme-primary));
  }
}

.front-page-navbar {
  position: relative;
  margin-bottom: 64px;
  
  .v-toolbar {
    width: 100% !important;
    max-width: 100% !important;
    backdrop-filter: blur(10px);
  }
}

.navbar-content {
  width: 100%;
  max-width: 1080px;
  margin: 0 auto;
  padding: 0 16px;
  display: flex;
  align-items: center;
}

.app-bar-dark {
  border: 2px solid rgba(var(--v-theme-surface), 0.1);
  background-color: rgba(255, 255, 255, 0.03);
  transition: all 0.2s ease-in-out;
}

.app-bar-scrolled {
  background-color: rgb(var(--v-theme-surface)) !important;
  border-color: transparent;
  transition: all 0.2s ease-in-out;
}
</style>
