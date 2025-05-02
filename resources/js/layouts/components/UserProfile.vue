<script setup>
import { useAuthStore } from '@/stores/auth'
import avatar1 from '@images/avatars/avatar-1.png'
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'


const authStore = useAuthStore()
const router = useRouter()

// Add getUserProfileImage function
const userProfileImage = ref(avatar1) // Initialize with default avatar

// Computed property to always get a valid image URL
const displayProfileImage = computed(() => {
  const profilePic = userProfileImage.value
  
  if (!profilePic) return avatar1
  
  // If it's already a valid URL, return it
  if (profilePic.startsWith('http://') || profilePic.startsWith('https://')) {
    return profilePic
  }
  
  // Otherwise, try to construct an absolute URL
  try {
    return new URL(profilePic, window.location.origin).href
  } catch (e) {
    console.error('Invalid profile image path:', profilePic, e)
    
    return avatar1
  }
})

const fetchUserProfile = async () => {
  try {
    // Use the auth store's refreshUserData method to update user data across the app
    await authStore.refreshUserData()
    
    // Get the profile pic from the auth store
    userProfileImage.value = authStore.user?.profilePic || avatar1
    console.log('User profile image:', userProfileImage.value)
  } catch (error) {
    console.error('Error fetching user profile:', error)
    userProfileImage.value = avatar1 // Fallback to default avatar on error
  }
}

const logout = async () => {
  await authStore.logout()
  router.push('/game-store')
}

const getUserRoleText = role => {
  if (role === 'admin') return 'Administrator'
  if (role === 'developer') return 'Developer'
  
  return 'User'
}

onMounted(() => {
  fetchUserProfile() // Call the function to fetch user profile on component mount
})
</script>

<template>
  <!-- Not logged in: Show sign in button -->
  <div v-if="!authStore.isLoggedIn">
    <VBtn
      to="/login"
      color="primary"
      variant="flat"
    >
      Sign In
    </VBtn>
  </div>
  
  <!-- Logged in: Show user profile -->
  <VBadge
    v-else
    dot
    location="bottom right"
    offset-x="3"
    offset-y="3"
    color="success"
    bordered
  >
    <VAvatar
      class="cursor-pointer"
      color="primary"
      variant="tonal"
    >
      <VImg
        :src="displayProfileImage"
        alt="User Profile Image"
      />

      <!-- SECTION Menu -->
      <VMenu
        activator="parent"
        width="230"
        location="bottom end"
        offset="14px"
      >
        <VList>
          <!-- 👉 User Avatar & Name -->
          <VListItem>
            <template #prepend>
              <VListItemAction start>
                <VBadge
                  dot
                  location="bottom right"
                  offset-x="3"
                  offset-y="3"
                  color="success"
                >
                  <VAvatar
                    color="primary"
                    variant="tonal"
                  >
                    <VImg
                      :src="displayProfileImage"
                      alt="User Profile Image"
                    />
                  </VAvatar>
                </VBadge>
              </VListItemAction>
            </template>

            <VListItemTitle class="font-weight-semibold">
              {{ authStore.user?.u_name || 'User' }}
            </VListItemTitle>
            <VListItemSubtitle>{{ getUserRoleText(authStore.userRole) }}</VListItemSubtitle>
          </VListItem>
          <VDivider class="my-2" />

          <!-- 👉 Profile -->
          <VListItem
            to="/account-settings"
            link
          >
            <template #prepend>
              <VIcon
                class="me-2"
                icon="bx-user"
                size="22"
              />
            </template>

            <VListItemTitle>Profile</VListItemTitle>
          </VListItem>

          <!-- 👉 Role-specific items -->
          <!-- Admin items -->
          <template v-if="authStore.isAdmin">
            <VListItem
              to="/admin-dashboard"
              link
            >
              <template #prepend>
                <VIcon
                  class="me-2"
                  icon="bx-code-alt"
                  size="22"
                />
              </template>

              <VListItemTitle>Admin Dashboard</VListItemTitle>
            </VListItem>
          </template>

          <!-- Developer items -->
          <template v-if="authStore.isDeveloper">
            <VListItem
              to="/developer-dashboard"
              link
            >
              <template #prepend>
                <VIcon
                  class="me-2"
                  icon="bx-code-alt"
                  size="22"
                />
              </template>

              <VListItemTitle>Developer Dashboard</VListItemTitle>
            </VListItem>
          </template>
          <!-- Divider -->
          <VDivider class="my-2" />

          <!-- 👉 Logout -->
          <VListItem
            link
            @click="logout"
          >
            <template #prepend>
              <VIcon
                class="me-2"
                icon="bx-log-out"
                size="22"
              />
            </template>

            <VListItemTitle>Logout</VListItemTitle>
          </VListItem>
        </VList>
      </VMenu>
      <!-- !SECTION -->
    </VAvatar>
  </VBadge>
</template>
