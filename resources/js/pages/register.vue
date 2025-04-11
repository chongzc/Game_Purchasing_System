<script setup>
import AuthProvider from '@/views/pages/authentication/AuthProvider.vue'
import logo from '@images/logo.svg?raw'
import authV1BottomShape from '@images/svg/auth-v1-bottom-shape.svg?url'
import authV1TopShape from '@images/svg/auth-v1-top-shape.svg?url'

const form = ref({
  username: '',
  email: '',
  password: '',
  confirmPassword: '',
  birthDate: null,
  formattedBirthDate: '',
  privacyPolicies: false,
})

const isPasswordVisible = ref(false)
const isConfirmPasswordVisible = ref(false)
const birthDateMenu = ref(false)

// Password match validation
const passwordsMatch = computed(() => {
  return !form.value.confirmPassword || form.value.password === form.value.confirmPassword
})

// Format the date to show only YYYY-MM-DD
function formatDate(date) {
  if (!date) return ''
  
  // Convert date object to YYYY-MM-DD format
  const d = new Date(date)
  const year = d.getFullYear()
  const month = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  
  return `${year}-${month}-${day}`
}

// Update formatted date when birthDate changes
watch(() => form.value.birthDate, newDate => {
  form.value.formattedBirthDate = formatDate(newDate)
})
</script>

<template>
  <div class="auth-wrapper d-flex align-center justify-center pa-4">
    <div class="position-relative my-sm-16">
      <!-- 👉 Top shape -->
      <VImg
        :src="authV1TopShape"
        class="text-primary auth-v1-top-shape d-none d-sm-block"
      />

      <!-- 👉 Bottom shape -->
      <VImg
        :src="authV1BottomShape"
        class="text-primary auth-v1-bottom-shape d-none d-sm-block"
      />

      <!-- 👉 Auth card -->
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
            <h1 class="app-logo-title">
              sneat
            </h1>
          </RouterLink>
        </VCardItem>

        <VCardText>
          <h4 class="text-h4 mb-1">
            Adventure starts here 🚀
          </h4>
          <p class="mb-0">
            Make your app management easy and fun!
          </p>
        </VCardText>

        <VCardText>
          <VForm @submit.prevent="$router.push('/')">
            <VRow>
              <!-- Username -->
              <VCol cols="12">
                <VTextField
                  v-model="form.username"
                  autofocus
                  label="Username"
                  placeholder="Johndoe"
                />
              </VCol>
              <!-- email -->
              <VCol cols="12">
                <VTextField
                  v-model="form.email"
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
                  autocomplete="new-password"
                  placeholder="············"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  :append-inner-icon="isPasswordVisible ? 'bx-hide' : 'bx-show'"
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
                      placeholder="YYYY-MM-DD"
                      prepend-inner-icon="bx-calendar"
                    />
                  </template>
                  <VDatePicker
                    v-model="form.birthDate"
                    @update:model-value="birthDateMenu = false"
                  />
                </VMenu>
              </VCol>

              <VCol cols="12">
                <div class="d-flex align-center my-6">
                  <VCheckbox
                    id="privacy-policy"
                    v-model="form.privacyPolicies"
                    inline
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
</style>
