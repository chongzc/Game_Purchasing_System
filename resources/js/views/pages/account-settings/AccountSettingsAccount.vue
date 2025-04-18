<script setup>
import { useAuthStore } from '@/stores/auth';
import { useUserProfileStore } from '@/stores/userProfile';
import avatar1 from '@images/avatars/avatar-1.png';
import axios from 'axios';
import { ref } from 'vue';

const authStore = useAuthStore()
const userProfileStore = useUserProfileStore()

// Add getUserProfileImage function
const userProfileImage = ref(''); // Reactive variable to hold the profile image URL

const fetchUserProfile = async () => {
  try {
    const response = await axios.get('/api/profile'); // Fetch user profile data
    const userData = response.data; // Assuming the response contains user data
    console.log('User profile data:', userData);
    userProfileImage.value = userData.profilePic; // Set the profile image
    console.log('User profile image:', userProfileImage.value);
  } catch (error) {
    console.error('Error fetching user profile:', error);
    userProfileImage.value = avatar1; // Fallback to default avatar on error
  }
};

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
  avatarImg: userProfileImage.value,
  name: authStore.user?.u_name || '',
  email: authStore.user?.u_email || '',
  birthdate: authStore.user?.u_birthdate || null,
})

const refInputEl = ref()
const isAccountDeactivated = ref(false)
const birthDateMenu = ref(false)
const isSubmitting = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const users = ref([])
const isLoadingUsers = ref(false)
const usersFetchError = ref('')

const resetForm = () => {
  accountDataLocal.value = {
    avatarImg: fetchUserProfile(),
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
        userProfileImage.value = fileReader.result
    }
    fileReader.readAsDataURL(file)
  }
}

// Function to get CSRF token from cookie
const getCsrfToken = () => {
  const cookies = document.cookie.split(';')

  const csrfToken = cookies
    .find(cookie => cookie.trim().startsWith('XSRF-TOKEN='))
    ?.split('=')[1]
  
  return csrfToken ? decodeURIComponent(csrfToken) : null
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
      formData.append('birthdate', accountDataLocal.value.birthdate)
    }
    
    // Get CSRF token
    const csrfToken = getCsrfToken()
    
    // Use fetch API for simplicity
    const response = await fetch('/api/profile', {
      method: 'POST',
      body: formData,
      credentials: 'same-origin', // Include cookies
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        ...(csrfToken ? { 'X-XSRF-TOKEN': csrfToken } : {}),
      },
    })
    
    if (!response.ok) {
      const errorText = await response.text().catch(() => 'Unknown error')

      console.error('Server response:', errorText)
      throw new Error(`Upload failed with status ${response.status}: ${response.statusText}`)
    }
    
    const data = await response.json()
    
    // Update UI with success
    successMessage.value = data.message || 'Profile updated successfully'
    
    // Update auth store manually
    if (data.user?.profilePic) {
      // Update the user object with the new profile picture
      authStore.user = {
        ...authStore.user,
        profilePic: data.user.profilePic,
      }
      
      // Important: Update localStorage to persist the profile picture
      localStorage.setItem('user', JSON.stringify(authStore.user))
      
      // Update the display image with proper URL formatting
      accountDataLocal.value.avatarImg = getUserProfileImage(data.user.profilePic)
    }
    window.location.reload()
  } catch (error) {
    console.error('Error updating profile:', error)
    errorMessage.value = error.message || 'Failed to update profile'
  } finally {
    isSubmitting.value = false
  }

}

onMounted(() => {
    fetchUserProfile(); // Call the function to fetch user profile on component mount
  });
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
              :src="userProfileImage"
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
                :loading="isSubmitting"
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
