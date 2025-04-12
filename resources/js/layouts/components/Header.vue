<script setup>
import UserProfile from '@/layouts/components/UserProfile.vue'
import { headerNavigationItems } from '@/utils/header-navigation'
import { useWindowScroll } from '@vueuse/core'
import { useDisplay } from 'vuetify'

const display = useDisplay()
const { y } = useWindowScroll()
const sidebar = ref(false)

watch(() => display, () => {
  return display.mdAndUp ? sidebar.value = false : sidebar.value
}, { deep: true })
</script>

<template>
  <div class="front-page-navbar">
    <VAppBar
      :color="$vuetify.theme.current.dark ? 'rgba(var(--v-theme-surface),0.85)' : 'rgba(var(--v-theme-surface), 0.85)'"
      class="elevation-0"
      :class="[ 
        y > 10 ? 'app-bar-scrolled' : [$vuetify.theme.current.dark ? 'app-bar-dark' : 'app-bar-light']
      ]"
    >
      <div class="navbar-content">
        <!-- Mobile menu toggle -->
        <IconBtn
          id="vertical-nav-toggle-btn"
          class="ms-n3 me-2 d-inline-block d-md-none"
          @click="sidebar = !sidebar"
        >
          <VIcon
            size="26"
            icon="mdi-menu"
            color="rgba(var(--v-theme-on-surface))"
          />
        </IconBtn>

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
          <div class="text-base align-center d-none d-md-flex">
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

    <!-- Mobile Navigation Drawer -->
    <VNavigationDrawer
      v-model="sidebar"
      temporary
      location="left"
      class="ps-0 pb-0"
    >
      <!-- Close button -->
      <IconBtn
        id="navigation-drawer-close-btn"
        @click="sidebar = false"
      >
        <VIcon icon="mdi-close" />
      </IconBtn>

      <!-- Mobile Links -->
      <VList>
        <VListItem
          v-for="(item, index) in navigationItems"
          :key="index"
          :to="item.to"
          :value="item.title"
          @click="sidebar = false"
        >
          <template #title>
            {{ item.title }}
          </template>
        </VListItem>
      </VList>
    </VNavigationDrawer>
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

.app-bar-light {
  border: 2px solid rgba(var(--v-theme-surface), 0.1);
  background-color: rgba(var(--v-theme-surface), 0.85);
  transition: all 0.2s ease-in-out;
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

#navigation-drawer-close-btn {
  position: absolute;
  cursor: pointer;
  inset-block-start: 0.5rem;
  inset-inline-end: 1rem;
}
</style>
