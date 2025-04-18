<script setup>
import { useUserProfileStore } from '@/stores/userProfile'

const userProfileStore = useUserProfileStore()

const isCurrentPasswordVisible = ref(false)
const isNewPasswordVisible = ref(false)
const isConfirmPasswordVisible = ref(false)
const currentPassword = ref('')
const newPassword = ref('')
const confirmPassword = ref('')

const passwordRequirements = [
  'Minimum 8 characters long - the more, the better',
  'At least one lowercase character',
  'At least one number, symbol, or whitespace character',
]


const handlePasswordChange = async () => {
  if (newPassword.value !== confirmPassword.value) {
    userProfileStore.error = 'New password and confirm password do not match'
    
    return
  }

  if (newPassword.value.length < 8) {
    userProfileStore.error = 'New password must be at least 8 characters long'
    
    return
  }

  try {
    await userProfileStore.updatePassword({
      currentPassword: currentPassword.value,
      newPassword: newPassword.value,
    })

    // Clear form on success
    currentPassword.value = ''
    newPassword.value = ''
    confirmPassword.value = ''
  } catch (error) {
    console.error('Error updating password:', error)
  }
}
</script>

<template>
  <VRow>
    <!-- SECTION: Change Password -->
    <VCol cols="12">
      <VCard title="Change Password">
        <VForm @submit.prevent="handlePasswordChange">
          <VCardText>
            <!-- 👉 Current Password -->
            <VRow>
              <VCol
                cols="12"
                md="6"
              >
                <!-- 👉 current password -->
                <VTextField
                  v-model="currentPassword"
                  :type="isCurrentPasswordVisible ? 'text' : 'password'"
                  :append-inner-icon="isCurrentPasswordVisible ? 'bx-hide' : 'bx-show'"
                  label="Current Password"
                  placeholder="············"
                  @click:append-inner="isCurrentPasswordVisible = !isCurrentPasswordVisible"
                />
              </VCol>
            </VRow>

            <!-- 👉 New Password -->
            <VRow>
              <VCol
                cols="12"
                md="6"
              >
                <!-- 👉 new password -->
                <VTextField
                  v-model="newPassword"
                  :type="isNewPasswordVisible ? 'text' : 'password'"
                  :append-inner-icon="isNewPasswordVisible ? 'bx-hide' : 'bx-show'"
                  label="New Password"
                  autocomplete="on"
                  placeholder="············"
                  @click:append-inner="isNewPasswordVisible = !isNewPasswordVisible"
                />
              </VCol>

              <VCol
                cols="12"
                md="6"
              >
                <!-- 👉 confirm password -->
                <VTextField
                  v-model="confirmPassword"
                  :type="isConfirmPasswordVisible ? 'text' : 'password'"
                  :append-inner-icon="isConfirmPasswordVisible ? 'bx-hide' : 'bx-show'"
                  label="Confirm New Password"
                  placeholder="············"
                  @click:append-inner="isConfirmPasswordVisible = !isConfirmPasswordVisible"
                />
              </VCol>
            </VRow>
          </VCardText>

          <!-- 👉 Password Requirements -->
          <VCardText>
            <p class="text-base font-weight-medium mt-2">
              Password Requirements:
            </p>

            <ul class="d-flex flex-column gap-y-3">
              <li
                v-for="item in passwordRequirements"
                :key="item"
                class="d-flex"
              >
                <div>
                  <VIcon
                    size="7"
                    icon="bxs-circle"
                    class="me-3"
                  />
                </div>
                <span class="font-weight-medium">{{ item }}</span>
              </li>
            </ul>
          </VCardText>

          <!-- 👉 Action Buttons -->
          <VCardText class="d-flex flex-wrap gap-4">
            <VBtn 
              type="submit"
              :loading="userProfileStore.loading"
            >
              Save changes
            </VBtn>

            <VBtn
              type="reset"
              color="secondary"
              variant="tonal"
              @click="() => {
                currentPassword = ''
                newPassword = ''
                confirmPassword = ''
              }"
            >
              Reset
            </VBtn>
          </VCardText>

          <!-- Success/Error Messages -->
          <VCardText v-if="userProfileStore.success || userProfileStore.error">
            <VAlert
              v-if="userProfileStore.success"
              type="success"
              variant="tonal"
              closable
              class="mt-4"
            >
              {{ userProfileStore.success }}
            </VAlert>

            <VAlert
              v-if="userProfileStore.error"
              type="error"
              variant="tonal"
              closable
              class="mt-4"
            >
              {{ userProfileStore.error }}
            </VAlert>
          </VCardText>
        </VForm>
      </VCard>
    </VCol>
  </VRow>
</template>
