<script setup>
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const router = useRouter()

const logout = async () => {
  await authStore.logout()
  router.push('/login')
}
</script>

<template>
  <VAppBar
    color="primary"
    app
    :elevation="2"
  >
    <VAppBarTitle>
      <RouterLink
        to="/"
        class="text-white text-decoration-none"
      >
        Game Store
      </RouterLink>
    </VAppBarTitle>

    <VSpacer />

    <div v-if="authStore.isLoggedIn">
      <!-- For all users -->
      <VBtn
        v-if="authStore.isUser || authStore.isDeveloper"
        to="/game-store"
        text
        color="white"
        class="mx-1"
      >
        Game Store
      </VBtn>
      
      <VBtn
        v-if="authStore.isUser"
        to="/game-library"
        text
        color="white"
        class="mx-1"
      >
        My Library
      </VBtn>
      
      <!-- Developer-specific links -->
      <VBtn
        v-if="authStore.isDeveloper"
        to="/developer-dashboard"
        text
        color="white"
        class="mx-1"
      >
        Developer Dashboard
      </VBtn>
      
      <!-- Admin-specific links -->
      <VBtn
        v-if="authStore.isAdmin"
        to="/admin-dashboard"
        text
        color="white"
        class="mx-1"
      >
        Admin Dashboard
      </VBtn>
      
      <!-- User menu -->
      <VMenu
        min-width="200px"
        transition="scale-transition"
      >
        <template #activator="{ props }">
          <VBtn
            color="white"
            icon
            v-bind="props"
          >
            <VIcon icon="mdi-account-circle" />
          </VBtn>
        </template>
        
        <VList>
          <VListItem>
            <VListItemTitle class="font-weight-bold">
              {{ authStore.user?.u_name || 'User' }}
            </VListItemTitle>
            <VListItemSubtitle>
              {{ authStore.user?.u_email || 'user@example.com' }}
            </VListItemSubtitle>
          </VListItem>
          
          <VDivider />
          
          <VListItem
            to="/account-settings"
            prepend-icon="mdi-account-cog"
          >
            <VListItemTitle>Account Settings</VListItemTitle>
          </VListItem>
          
          <VListItem
            prepend-icon="mdi-logout"
            @click="logout"
          >
            <VListItemTitle>Logout</VListItemTitle>
          </VListItem>
        </VList>
      </VMenu>
    </div>
    
    <div v-else>
      <VBtn
        to="/login"
        text
        color="white"
        class="mx-1"
      >
        Login
      </VBtn>
      
      <VBtn
        to="/register"
        text
        color="white"
        class="mx-1"
      >
        Register
      </VBtn>
    </div>
  </VAppBar>
</template> 
