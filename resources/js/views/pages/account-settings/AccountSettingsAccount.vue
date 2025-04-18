<script setup>
import avatar1 from '@images/avatars/avatar-1.png'
import axios from 'axios'
import { computed, onMounted, ref } from 'vue'

// Reactive variable to hold the profile image URL
const userProfileImage = ref('')
const userData = ref({})

const fetchUserProfile = async () => {
  try {
    const response = await axios.get('/api/profile')
 
    userData.value = response.data 
    userProfileImage.value = getUserProfileImage(userData.value.profilePic)
    
    accountDataLocal.value = {
      avatarImg: userProfileImage.value,
      name: userData.value.u_name || '',
      email: userData.value.u_email || '',
      birthdate: userData.value.u_birthdate ? new Date(userData.value.u_birthdate) : null,
    }
    
  } catch (error) {
    userProfileImage.value = avatar1 
  }
}

// Function to get profile image source with proper URL handling
const getUserProfileImage = imageSource => {
  if (!imageSource) return avatar1
  
  // If it's already a complete URL, return it
  if (imageSource.startsWith('http://') || imageSource.startsWith('https://')) {
    return imageSource
  }
  
  // If it's a relative path, convert it to absolute URL
  if (imageSource.startsWith('/')) {
    return window.location.origin + imageSource
  }
  
  // For paths without leading slash
  return window.location.origin + '/' + imageSource
}

const accountDataLocal = ref({
  avatarImg: '',
  name: '',
  email: '',
  birthdate: null,
})

const refInputEl = ref()
const birthDateMenu = ref(false)
const isSubmitting = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const resetForm = () => {
  // Reset form data to original values from the server
  accountDataLocal.value = {
    avatarImg: userProfileImage.value,
    name: userData.value.u_name || '',
    email: userData.value.u_email || '',
    birthdate: userData.value.u_birthdate ? new Date(userData.value.u_birthdate) : null,
  }
  
  // Reload the screen
  window.location.reload()
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
        userProfileImage.value = fileReader.result // This is a data URL which is a valid path
    }
    fileReader.readAsDataURL(file)
  } else {
    // If no file is selected, use default avatar
    userProfileImage.value = avatar1
  }
}

const handleSubmit = async () => {
  isSubmitting.value = true
  errorMessage.value = ''
  successMessage.value = ''
  
  try {
    // Create form data
    const formData = new FormData()
    
    if (refInputEl.value?.files?.[0]) {
      formData.append('profile_picture', refInputEl.value.files[0])
    }
    
    formData.append('name', accountDataLocal.value.name)
    formData.append('email', accountDataLocal.value.email)
    if (accountDataLocal.value.birthdate) {
      formData.append('birthdate', formatBirthDate(accountDataLocal.value.birthdate))
    }
    
    // Use axios for the request
    const response = await axios.post('/api/profile', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
        'Accept': 'application/json',
      },
    })
    
    // Update UI with success
    successMessage.value = response.data.message || 'Profile updated successfully'
    
    // Update the local data with the new profile data
    if (response.data.user?.profilePic) {
      userData.value = {
        ...userData.value,
        profilePic: response.data.user.profilePic,
      }
      
      // Update the display image with proper URL formatting
      userProfileImage.value = getUserProfileImage(response.data.user.profilePic)
    } else {
      // If no profile picture is returned, use default avatar
      userProfileImage.value = avatar1
    }
    
    // Refresh page to reflect changes
    window.location.reload()
  } catch (error) {
    console.error('Error updating profile:', error)
    errorMessage.value = error.response?.data?.message || error.message || 'Failed to update profile'
  } finally {
    isSubmitting.value = false
  }
}

// Computed property to ensure we always have a valid image source
const displayProfileImage = computed(() => {
  return userProfileImage.value || avatar1
})

onMounted(() => {
  fetchUserProfile() // Call the function to fetch user profile on component mount
})
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
              :src="displayProfileImage"
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
            </div>

            <p class="text-body-1 mb-0">
              Allowed JPG or PNG
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
                      :model-value="formatBirthDate(accountDataLocal.birthdate)"
                      label="Birth Date"
                      prepend-inner-icon="bx-calendar"
                      readonly
                      v-bind="props"
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
                :loading="isSubmitting"
              >
                Save changes
              </VBtn>

              <VBtn
                color="error"
                variant="tonal"
                @click="resetForm"
              >
                Reset
              </VBtn>
            </VCol>

            <!-- Success/Error Messages -->
            <VCol cols="12">
              <VAlert
                v-if="successMessage"
                type="success"
                variant="tonal"
                closable
                class="mt-4"
              >
                {{ successMessage }}
              </VAlert>

              <VAlert
                v-if="errorMessage"
                type="error"
                variant="tonal"
                closable
                class="mt-4"
              >
                {{ errorMessage }}
              </VAlert>
            </VCol>
          </VForm>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
