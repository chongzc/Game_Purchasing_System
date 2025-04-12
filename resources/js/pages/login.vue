<script setup>
import logo from '@images/logo.svg?raw'
import authV1BottomShape from '@images/svg/auth-v1-bottom-shape.svg?url'
import authV1TopShape from '@images/svg/auth-v1-top-shape.svg?url'
import { useRouter } from 'vue-router'

const form = ref({
  email: '',
  password: '',
  remember: false,
})

const isPasswordVisible = ref(false)
const router = useRouter()

// Function to handle login
const handleLogin = () => {
  // In a real application, you would make an API call to validate credentials
  // For demo purposes, we'll just set a cookie and redirect
  
  // Set authentication cookie that expires in 1 day (or longer if "remember me" is checked)
  const expiryDays = form.value.remember ? 30 : 1
  const expiryDate = new Date()

  expiryDate.setDate(expiryDate.getDate() + expiryDays)
  
  document.cookie = `user_authenticated=true; expires=${expiryDate.toUTCString()}; path=/`
  
  // Redirect to home page or last intended destination
  router.push('/game-store')
}
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
            <div
              class="d-flex"
              v-html="logo"
            />
            <h1>
              Game Store
            </h1>
          </RouterLink>
        </VCardItem>

        <VCardText>
          <h4 class="text-h4 mb-1">
            Welcome to Ali Game Store! 👋🏻
          </h4>
          <p class="mb-0">
            Please sign-in to your account and start the adventure
          </p>
        </VCardText>

        <VCardText>
          <VForm @submit.prevent="handleLogin">
            <VRow>
              <!-- email -->
              <VCol cols="12">
                <VTextField
                  v-model="form.email"
                  autofocus
                  label="Email or Username"
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
                  autocomplete="password"
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
  position: fixed !important; // Use !important to override any possible conflicting styles
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

// Reset any margins or padding that might cause shift
body, html {
  margin: 0;
  padding: 0;
  height: 100%;
  overflow: hidden;
}
</style>
