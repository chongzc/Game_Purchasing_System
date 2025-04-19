<script setup>
import { useAuthStore } from '@/stores/auth'
import { deleteCookie, getCookie } from '@/utils/cookie'
import logo from '@images/logo.png'
import authV1BottomShape from '@images/svg/auth-v1-bottom-shape.svg?url'
import authV1TopShape from '@images/svg/auth-v1-top-shape.svg?url'
import { ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const form = ref({
  email: getCookie('email') || '', // Pre-fill email from cookie
  password: getCookie('password') || '', // Pre-fill password from cookie
  remember: !!getCookie('email'), // Check if "remember" was enabled
})

const isPasswordVisible = ref(false)
const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const handleLogin = async () => {
  try {
    await authStore.login(form.value.email, form.value.password, form.value.remember)

    // Redirect based on user role or previous intended route
    const redirectPath = route.query.redirect
      ? route.query.redirect
      : authStore.isAdmin
        ? '/admin-dashboard'
        : authStore.isDeveloper
          ? '/developer-dashboard'
          : '/game-store'

    router.push(redirectPath)
  } catch (error) {
    console.error('Login failed:', error)
  }
}

// Watch for changes to the "remember" checkbox
watch(
  () => form.value.remember,
  newValue => {
    if (!newValue) {
      // Delete cookies when "Remember Me" is deselected
      deleteCookie('email')
      deleteCookie('password')
    }
  },
)
</script>

<template>
  <div class="auth-page">
    <!-- 👉 Background shapes -->
    <VImg
      :src="authV1TopShape"
      class="text-primary auth-v1-top-shape d-none d-sm-block"
    />
    <VImg
      :src="authV1BottomShape"
      class="text-primary auth-v1-bottom-shape d-none d-sm-block"
    />
    
    <!-- 👉 Auth Card -->
    <div class="auth-card-container">
      <VCard
        class="auth-card"
        max-width="460"
        :class="$vuetify.display.smAndUp ? 'pa-6' : 'pa-0'"
      >
        <VCardItem class="justify-center">
          <RouterLink
            to="/"
            class="app-logo"
          >
            <!-- eslint-disable vue/no-v-html -->
            <VImg
              :src="logo"
              height="80"
              width="80"
              cover
                    />
            <h1>
              Game Store
            </h1>
          </RouterLink>
        </VCardItem>

        <VCardText>
          <h4 class="text-h4 mb-1">
            Welcome! 👋🏻
          </h4>
          <p class="mb-0">
            Please sign-in to your account and start the adventure
          </p>
        </VCardText>

        <VCardText>
          <VForm @submit.prevent="handleLogin">
            <VRow>
              <!-- Error message alert -->
              <VCol
                v-if="authStore.error"
                cols="12"
              >
                <VAlert
                  density="compact"
                  type="error"
                  variant="tonal"
                  closable
                  @click:close="authStore.error = null"
                >
                  {{ authStore.error }}
                </VAlert>
              </VCol>
              
              <!-- email -->
              <VCol cols="12">
                <VTextField
                  v-model="form.email"
                  autofocus
                  label="Email"
                  type="email"
                  placeholder="johndoe@email.com"
                />
              </VCol>

              <!-- password -->
              <VCol cols="12">
                <VTextField
                  v-model="form.password"
                  label="Password"
                  placeholder="············"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  autocomplete="current-password"
                  :append-inner-icon="isPasswordVisible ? 'bx-hide' : 'bx-show'"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
                />

                <!-- remember me checkbox -->
                <div class="d-flex align-center justify-space-between flex-wrap my-6">
                  <VCheckbox
                    v-model="form.remember"
                    label="Remember me"
                  />

                  <a
                    class="text-primary"
                    href="javascript:void(0)"
                  >
                    Forgot Password?
                  </a>
                </div>

                <!-- login button -->
                <VBtn
                  block
                  type="submit"
                  :loading="authStore.loading"
                >
                  Login
                </VBtn>
              </VCol>

              <!-- create account -->
              <VCol
                cols="12"
                class="text-body-1 text-center"
              >
                <span class="d-inline-block">
                  New on our platform?
                </span>
                <RouterLink
                  class="text-primary ms-1 d-inline-block text-body-1"
                  to="/register"
                >
                  Create an account
                </RouterLink>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </div>
  </div>
</template>

<style lang="scss">
@use "@core-scss/template/pages/page-auth";

// Complete styling overhaul for positioning
.auth-page {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  width: 100%;
  position: relative;
  padding: 1rem;
  overflow: hidden;
}

.auth-v1-top-shape,
.auth-v1-bottom-shape {
  position: fixed !important;
  z-index: 0;
}

.auth-v1-top-shape {
  top: 0;
  left: 0;
}

.auth-v1-bottom-shape {
  bottom: 0;
  right: 0;
}

.auth-card-container {
  display: flex;
  justify-content: center;
  width: 100%;
  z-index: 10;
  position: relative;
}

.auth-card {
  width: 100%;
  max-width: 460px;
  position: relative;
  z-index: 10;
}
</style>
