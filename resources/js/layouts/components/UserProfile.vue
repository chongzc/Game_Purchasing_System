<script setup>
import { useAuthStore } from '@/stores/auth'
import avatar1 from '@images/avatars/avatar-1.png'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const router = useRouter()


const logout = async () => {
  await authStore.logout()
  router.push('/game-store')
}

const getUserRoleText = role => {
  if (role === 'admin') return 'Administrator'
  if (role === 'developer') return 'Developer'
  
  return 'User'
}
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
        v-if="authStore.user?.profilePic"
        :src="getUserProfileImage(authStore.user.profilePic)"
      />
      <VImg
        v-else
        :src="avatar1"
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
                      v-if="authStore.user?.profilePic"
                      :src="getUserProfileImage(authStore.user.profilePic)"
                    />
                    <VImg
                      v-else
                      :src="avatar1"
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
                  icon="bx-dashboard"
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

          <!-- 👉 Settings -->
          <VListItem
            to="/account-settings"
            link
          >
            <template #prepend>
              <VIcon
                class="me-2"
                icon="bx-cog"
                size="22"
              />
            </template>

            <VListItemTitle>Settings</VListItemTitle>
          </VListItem>

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
