<script setup>
import { useAllUsersQuery } from '@/hooks/profile/useProfileApi'
import avatar1 from '@images/avatars/avatar-1.png'

const props = defineProps({
  autoFetch: {
    type: Boolean,
    default: true,
  },
})

// Function to get profile image source with proper URL handling
const getUserProfileImage = imageSource => {
  if (!imageSource) return avatar1
  
  // If it's already a complete URL, return it
  if (imageSource?.startsWith('http://') || imageSource?.startsWith('https://')) {
    return imageSource
  }
  
  // If it's a relative path, convert it to absolute URL
  if (imageSource?.startsWith('/')) {
    return window.location.origin + imageSource
  }
  
  // For paths without leading slash
  return window.location.origin + '/' + imageSource
}

// Using TanStack Query to fetch users
const { 
  data: users,
  isLoading,
  isFetching,
  isError,
  error,
  refetch,
  fetchUsers,
} = useAllUsersQuery({
  enabled: props.autoFetch, // Auto fetch when the component mounts if autoFetch is true
})

// Load users function for use with the fetch button
const loadUsers = () => {
  fetchUsers()
}
</script>

<template>
  <VCard title="Users Management">
    <VCardText>
      <h2 class="text-h5 mb-4">
        System Users
      </h2>
      <p class="mb-6">
        This page allows administrators to view all users registered in the system.
      </p>
      
      <!-- Users Table Component with TanStack Query integration -->
      <div>
        <!-- Fetch Button (when not auto-fetching) -->
        <div
          v-if="!props.autoFetch"
          class="mb-4"
        >
          <VBtn
            color="info"
            :loading="isLoading || isFetching"
            :disabled="isLoading || isFetching"
            @click="loadUsers"
          >
            <VIcon
              icon="bx-user"
              class="me-2"
            />
            Fetch Users
          </VBtn>
          
          <VBtn
            v-if="users?.length > 0"
            color="secondary"
            :loading="isFetching"
            :disabled="isLoading || isFetching"
            variant="tonal"
            class="ms-2"
            @click="refetch"
          >
            <VIcon icon="bx-refresh" />
            Refresh
          </VBtn>
        </div>
        
        <!-- Loading indicator -->
        <div
          v-if="isLoading || isFetching"
          class="d-flex justify-center py-4"
        >
          <VProgressCircular indeterminate />
        </div>
        
        <!-- Error message -->
        <VAlert
          v-if="isError"
          type="error"
          variant="tonal"
          closable
          class="mb-4"
        >
          {{ error?.message || 'Failed to fetch users' }}
        </VAlert>
        
        <!-- Users table -->
        <VTable v-if="users?.length > 0">
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
                    :src="getUserProfileImage(user.profilePic)"
                    alt="User Avatar"
                  />
                </VAvatar>
              </td>
            </tr>
          </tbody>
        </VTable>
        
        <!-- Empty state -->
        <div
          v-else-if="!isLoading && !isFetching"
          class="text-center py-4"
        >
          <p class="text-subtitle-1">
            No users data available
          </p>
          <p
            v-if="!props.autoFetch"
            class="text-body-2"
          >
            Click the button above to fetch users
          </p>
        </div>
      </div>
    </VCardText>
  </VCard>
</template> 
