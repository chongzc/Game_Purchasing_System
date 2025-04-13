<script setup>
import { useAuthStore } from '@/stores/auth'
import { useUserProfileStore } from '@/stores/userProfile'
import avatar1 from '@images/avatars/avatar-1.png'

const authStore = useAuthStore()
const userProfileStore = useUserProfileStore()

const accountDataLocal = ref({
  avatarImg: authStore.user?.u_profilePic || avatar1,
  name: authStore.user?.u_name || '',
  email: authStore.user?.u_email || '',
  birthdate: authStore.user?.u_birthdate || null,
})

const refInputEl = ref()
const isAccountDeactivated = ref(false)
const birthDateMenu = ref(false)

const resetForm = () => {
  accountDataLocal.value = {
    avatarImg: authStore.user?.u_profilePic || avatar1,
    name: authStore.user?.u_name || '',
    email: authStore.user?.u_email || '',
    birthdate: authStore.user?.u_birthdate || null,
  }
}

const formatBirthDate = date => {
  if (!date) return ''

  return new Date(date).toISOString().split('T')[0]
}

const changeAvatar = event => {
  const file = event.target.files[0]
  if (file) {
    const fileReader = new FileReader()

    fileReader.onload = () => {
      if (typeof fileReader.result === 'string')
        accountDataLocal.value.avatarImg = fileReader.result
    }
    fileReader.readAsDataURL(file)
  }
}

const handleSubmit = async () => {
  try {
    const formData = {
      name: accountDataLocal.value.name,
      email: accountDataLocal.value.email,
      birthdate: accountDataLocal.value.birthdate,
    }

    if (refInputEl.value?.files?.[0]) {
      formData.profilePicture = refInputEl.value.files[0]
    }

    await userProfileStore.updateProfile(formData)
    
    // Update auth store with new user data
    await authStore.getUser()
  } catch (error) {
    console.error('Error updating profile:', error)
  }
}
</script>

<template>
  <VRow>
    <VCol cols="12">
      <VCard title="Account Details">
        <VCardText class="d-flex">
          <!-- Avatar -->
          <VAvatar
            rounded="lg"
            size="100"
            class="me-6"
          >
            <VImg
              :src="accountDataLocal.avatarImg"
              alt="Profile Picture"
            />
          </VAvatar>

          <!-- Upload Photo -->
          <form class="d-flex flex-column justify-center gap-5">
            <div class="d-flex flex-wrap gap-2">
              <VBtn
                color="primary"
                @click="refInputEl?.click()"
              >
                <VIcon
                  icon="bx-cloud-upload"
                  class="d-sm-none"
                />
                <span class="d-none d-sm-block">Upload new photo</span>
              </VBtn>

              <input
                ref="refInputEl"
                type="file"
                name="file"
                accept=".jpeg,.png,.jpg,GIF"
                hidden
                @input="changeAvatar"
              >

              <VBtn
                type="reset"
                color="error"
                variant="tonal"
                @click="resetForm"
              >
                <span class="d-none d-sm-block">Reset</span>
                <VIcon
                  icon="bx-refresh"
                  class="d-sm-none"
                />
              </VBtn>
            </div>

            <p class="text-body-1 mb-0">
              Allowed JPG, GIF or PNG. Max size of 800K
            </p>
          </form>
        </VCardText>

        <VDivider />

        <VCardText>
          <!-- Form -->
          <VForm
            class="mt-6"
            @submit.prevent="handleSubmit"
          >
            <VRow>
              <!-- Name -->
              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="accountDataLocal.name"
                  label="Name"
                  placeholder="John Doe"
                />
              </VCol>

              <!-- Email -->
              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="accountDataLocal.email"
                  label="E-mail"
                  type="email"
                  placeholder="johndoe@example.com"
                />
              </VCol>

              <!-- Birth Date -->
              <VCol
                cols="12"
                md="6"
              >
                <VMenu
                  v-model="birthDateMenu"
                  :close-on-content-click="false"
                  transition="scale-transition"
                  max-width="290px"
                  min-width="auto"
                >
                  <template #activator="{ props }">
                    <VTextField
                      v-model="accountDataLocal.birthdate"
                      label="Birth Date"
                      prepend-inner-icon="bx-calendar"
                      readonly
                      v-bind="props"
                      :model-value="formatBirthDate(accountDataLocal.birthdate)"
                    />
                  </template>
                  <VDatePicker
                    v-model="accountDataLocal.birthdate"
                    @update:model-value="birthDateMenu = false"
                  />
                </VMenu>
              </VCol>
            </VRow>

            <!-- Form Actions -->
            <VCol
              cols="12"
              class="d-flex flex-wrap gap-4"
            >
              <VBtn 
                type="submit"
                :loading="userProfileStore.loading"
              >
                Save changes
              </VBtn>

              <VBtn
                color="secondary"
                variant="tonal"
                @click="resetForm"
              >
                Reset
              </VBtn>
            </VCol>

            <!-- Success/Error Messages -->
            <VCol cols="12">
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
            </VCol>
          </VForm>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
