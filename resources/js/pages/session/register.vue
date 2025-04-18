<script setup>
import { useAuthStore } from '@/stores/auth'
import { formatDate } from '@core/utils/formatters'
import logo from '@images/logo.svg?raw'
import authV1BottomShape from '@images/svg/auth-v1-bottom-shape.svg?url'
import authV1TopShape from '@images/svg/auth-v1-top-shape.svg?url'
import { useRouter } from 'vue-router'
import { ref, computed, watch } from 'vue'

const form = ref({
  username: '',
  email: '',
  password: '',
  confirmPassword: '',
  birthDate: null,
  formattedBirthDate: '',
  role: 'user', // default role
  privacyPolicies: false,
})

const roles = [
  { title: 'User', value: 'user' },
  { title: 'Developer', value: 'developer' },
]

const isPasswordVisible = ref(false)
const isConfirmPasswordVisible = ref(false)
const birthDateMenu = ref(false)
const errorMessages = ref({})
const formSubmitted = ref(false) // Track if the form has been submitted

const router = useRouter()
const authStore = useAuthStore()

// Password match validation
const passwordsMatch = computed(() => {
  return !form.value.confirmPassword || form.value.password === form.value.confirmPassword
})

// Username validation
const validateUsername = username => {
  const usernameRegex = /^[a-zA-Z0-9]{5,100}$/ // Alphanumeric, min 5 characters and max 100
  if (!username) return 'Username is required.'
  if (!usernameRegex.test(username)) return 'Username must be alphanumeric, max 5 characters, and no special characters.'
  return ''
}

// Password validation
const validatePassword = password => {
  const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/ // At least one uppercase, one lowercase, one number, one special character, min 8 characters
  if (!password) return 'Password is required.'
  if (!passwordRegex.test(password)) return 'Password must be at least 8 characters long and include an uppercase letter, a lowercase letter, a number, and a special character.'
  return ''
}

// Update formatted date when birthDate changes
watch(() => form.value.birthDate, newDate => {
  form.value.formattedBirthDate = newDate ? newDate.toISOString().split('T')[0] : ''
})

// Form validation
const isFormValid = computed(() => {
  if (!formSubmitted.value) return true // Skip validation if the form hasn't been submitted

  errorMessages.value.username = validateUsername(form.value.username)
  errorMessages.value.password = validatePassword(form.value.password)

  return (
    !errorMessages.value.username &&
    !errorMessages.value.password &&
    form.value.email &&
    form.value.confirmPassword &&
    passwordsMatch.value &&
    form.value.birthDate &&
    form.value.privacyPolicies
  )
})

// Handle registration
const handleRegister = async () => {
  formSubmitted.value = true // Mark the form as submitted

  if (!isFormValid.value) {
    errorMessages.value.form = 'Please fill in all required fields.'
    return
  }

  try {
    errorMessages.value = {}

    const userData = {
      username: form.value.username,
      email: form.value.email,
      password: form.value.password,
      birthDate: form.value.birthDate,
      role: form.value.role,
    }

    await authStore.register(userData)

    // Redirect based on role
    if (authStore.isAdmin) {
      router.push('/admin-dashboard')
    } else if (authStore.isDeveloper) {
      router.push('/developer-dashboard')
    } else {
      router.push('/game-store')
    }
  } catch (error) {
    console.error('Registration error:', error)

    if (error.response?.data?.errors) {
      errorMessages.value = error.response.data.errors
    } else {
      errorMessages.value.form = authStore.error || 'An error occurred during registration.'
    }
  }
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
            Adventure starts here 🚀
          </h4>
          <p class="mb-0">
            Create your account and start gaming!
          </p>
        </VCardText>

        <VCardText>
          <VForm @submit.prevent="handleRegister">
            <VRow>
              <!-- General error message -->
              <VCol
                v-if="errorMessages.form"
                cols="12"
              >
                <VAlert
                  density="compact"
                  type="error"
                  variant="tonal"
                  closable
                  @click:close="errorMessages.form = ''"
                >
                  {{ errorMessages.form }}
                </VAlert>
              </VCol>
              
              <!-- Username -->
              <VCol cols="12">
                <VTextField
                  v-model="form.username"
                  autofocus
                  label="Username"
                  placeholder="Johndoe"
                  :error-messages="errorMessages.username"
                />
              </VCol>
              <!-- email -->
              <VCol cols="12">
                <VTextField
                  v-model="form.email"
                  label="Email"
                  type="email"
                  placeholder="johndoe@email.com"
                  :error-messages="errorMessages.email"
                />
              </VCol>

              <!-- password -->
              <VCol cols="12">
                <VTextField
                  v-model="form.password"
                  label="Password"
                  autocomplete="new-password"
                  placeholder="············"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  :append-inner-icon="isPasswordVisible ? 'bx-hide' : 'bx-show'"
                  :error-messages="errorMessages.password"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
                />
              </VCol>
              
              <!-- confirm password -->
              <VCol cols="12">
                <VTextField
                  v-model="form.confirmPassword"
                  label="Confirm Password"
                  autocomplete="new-password"
                  placeholder="············"
                  :type="isConfirmPasswordVisible ? 'text' : 'password'"
                  :append-inner-icon="isConfirmPasswordVisible ? 'bx-hide' : 'bx-show'"
                  :error-messages="passwordsMatch ? undefined : 'Passwords do not match'"
                  @click:append-inner="isConfirmPasswordVisible = !isConfirmPasswordVisible"
                />
              </VCol>
              
              <!-- Birth Date Picker with formatted date -->
              <VCol cols="12">
                <VMenu
                  v-model="birthDateMenu"
                  :close-on-content-click="false"
                  transition="scale-transition"
                  max-width="290px"
                  min-width="auto"
                >
                  <template #activator="{ props }">
                    <VTextField
                      v-model="form.formattedBirthDate"
                      label="Birth Date"
                      readonly
                      v-bind="props"
                      placeholder="Your Birth Date"
                      :error-messages="errorMessages.birthDate"
                    />
                  </template>
                  <VDatePicker
                    v-model="form.birthDate"
                    @update:model-value="birthDateMenu = false"
                  />
                </VMenu>
              </VCol>

              <!-- Role -->
              <VCol cols="12">
                <VSelect
                  v-model="form.role"
                  :items="roles"
                  label="Role"
                  item-title="title"
                  item-value="value"
                  placeholder="Select Role"
                  :error-messages="errorMessages.role"
                />
              </VCol>

              <VCol cols="12">
                <div class="d-flex align-center my-6">
                  <VCheckbox
                    id="privacy-policy"
                    v-model="form.privacyPolicies"
                    inline
                    :error="!form.privacyPolicies && errorMessages.form"
                  />
                  <VLabel
                    for="privacy-policy"
                    style="opacity: 1;"
                  >
                    <span class="me-1 text-high-emphasis">I agree to</span>
                    <a
                      href="javascript:void(0)"
                      class="text-primary"
                    >privacy policy & terms</a>
                  </VLabel>
                </div>

                <VBtn
                  block
                  type="submit"
                  :loading="authStore.loading"
                  :disabled="!isFormValid"
                >
                  Sign up
                </VBtn>
              </VCol>

              <!-- login instead -->
              <VCol
                cols="12"
                class="text-center text-base"
              >
                <span>Already have an account?</span>
                <RouterLink
                  class="text-primary ms-1"
                  to="/login"
                >
                  Sign in instead
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

// Remove the global body/html styles that were causing scrolling issues
</style>
