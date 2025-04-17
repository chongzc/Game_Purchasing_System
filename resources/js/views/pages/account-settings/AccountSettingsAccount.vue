<script setup>
import { useAuthStore } from '@/stores/auth'
import { useUserProfileStore } from '@/stores/userProfile'
import avatar1 from '@images/avatars/avatar-1.png'

const authStore = useAuthStore()
const userProfileStore = useUserProfileStore()

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
  avatarImg: getUserProfileImage(authStore.user?.profilePic),
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
    avatarImg: getUserProfileImage(authStore.user?.profilePic),
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

// Function to get CSRF token from cookie
const getCsrfToken = () => {
  const cookies = document.cookie.split(';')

  const csrfToken = cookies
    .find(cookie => cookie.trim().startsWith('XSRF-TOKEN='))
    ?.split('=')[1]
  
  return csrfToken ? decodeURIComponent(csrfToken) : null
}

// Function to fetch all users
const fetchUsers = async () => {
  isLoadingUsers.value = true
  usersFetchError.value = ''
  users.value = []
  
  try {
    // Get CSRF token
    const csrfToken = getCsrfToken()
    
    const response = await fetch('/api/users', {
      method: 'GET',
      credentials: 'same-origin',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        ...(csrfToken ? { 'X-XSRF-TOKEN': csrfToken } : {}),
      },
    })
    
    if (!response.ok) {
      throw new Error(`Failed to fetch users: ${response.status} ${response.statusText}`)
    }
    
    const data = await response.json()

    users.value = data
    successMessage.value = 'Users fetched successfully'
  } catch (error) {
    console.error('Error fetching users:', error)
    usersFetchError.value = error.message || 'Failed to fetch users'
  } finally {
    isLoadingUsers.value = false
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
  } catch (error) {
    console.error('Error updating profile:', error)
    errorMessage.value = error.message || 'Failed to update profile'
  } finally {
    isSubmitting.value = false
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

    <!-- Users section -->
    <VCol cols="12">
      <VCard title="Users List">
        <VCardText>
          <VBtn
            color="info"
            :loading="isLoadingUsers"
            class="mb-4"
            @click="fetchUsers"
          >
            <VIcon
              icon="bx-user"
              class="me-2"
            />
            Fetch Users
          </VBtn>

          <VAlert
            v-if="usersFetchError"
            type="error"
            variant="tonal"
            closable
            class="mb-4"
          >
            {{ usersFetchError }}
          </VAlert>

          <VTable v-if="users.length > 0">
            <thead>
              <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Profile Picture</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="user in users"
                :key="user.id"
              >
                <td>{{ user.id }}</td>
                <td>{{ user.name }}</td>
                <td>{{ user.email }}</td>
                <td>{{ user.role }}</td>
                <td>
                  <VAvatar size="40">
                    <VImg
                      :src="user.profilePic || avatar1"
                      alt="User Avatar"
                    />
                  </VAvatar>
                </td>
              </tr>
            </tbody>
          </VTable>
          <div
            v-else-if="!isLoadingUsers"
            class="text-center py-4"
          >
            <p class="text-subtitle-1">
              No users data available
            </p>
            <p class="text-body-2">
              Click the button above to fetch users
            </p>
          </div>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
