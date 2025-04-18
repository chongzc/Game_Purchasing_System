<script setup>
import avatar1 from '@images/avatars/avatar-1.png'
import axios from 'axios'
import { computed, onMounted, ref } from 'vue'

// Data refs
const users = ref([])
const loading = ref(false)
const error = ref(null)
const successMessage = ref('')
const searchQuery = ref('')
const showBannedOnly = ref(false)

// User status management
const userToUpdate = ref(null)
const confirmDialog = ref(false)
const actionLoading = ref(false)

// User details dialog
const userDetailsDialog = ref(false)
const selectedUser = ref(null)
const userDetailsLoading = ref(false)

// Table headers
const headers = [
  { title: 'ID', key: 'id', sortable: true },
  { title: 'Name', key: 'name', sortable: true },
  { title: 'Email', key: 'email', sortable: true },
  { title: 'Role', key: 'role', sortable: true },
  { title: 'Status', key: 'isBanned', sortable: true },
  { title: 'Profile Picture', key: 'profilePic', sortable: false },
  { title: 'Actions', key: 'actions', sortable: false },
]

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

// Fetch all users from the API
const fetchUsers = async () => {
  loading.value = true
  error.value = null
  
  try {
    const response = await axios.get('/api/admin/users')
    if (response.data.success) {
      users.value = response.data.users
    } else {
      error.value = response.data.message || 'Failed to fetch users'
    }
  } catch (err) {
    console.error('Error fetching users:', err)
    error.value = err.response?.data?.message || 'An error occurred while fetching users'
  } finally {
    loading.value = false
  }
}

// Search users by name
const searchUsers = async () => {
  if (!searchQuery.value || searchQuery.value.length < 2) {
    // If search query is empty or too short, fetch all users
    return fetchUsers()
  }
  
  loading.value = true
  error.value = null
  
  try {
    const response = await axios.post('/api/admin/users/find-by-name', {
      name: searchQuery.value,
    })
    
    if (response.data.success) {
      users.value = response.data.users
    } else {
      error.value = response.data.message || 'Failed to search users'
    }
  } catch (err) {
    console.error('Error searching users:', err)
    error.value = err.response?.data?.message || 'An error occurred while searching'
  } finally {
    loading.value = false
  }
}

// Filter users (for client-side filtering)
const filteredUsers = computed(() => {
  if (showBannedOnly.value) {
    return users.value.filter(user => user.isBanned === 'true')
  }
  
  return users.value
})

// Open ban confirmation dialog
const confirmUserAction = (user, action) => {
  userToUpdate.value = { 
    ...user,
    action: action, // 'ban' or 'unban'
  }
  confirmDialog.value = true
}

// Handle ban/unban action
const updateUserBanStatus = async () => {
  if (!userToUpdate.value) return
  
  actionLoading.value = true
  error.value = null
  successMessage.value = ''
  
  try {
    const willBeBanned = userToUpdate.value.action === 'ban'
    
    const response = await axios.patch(`/api/admin/users/${userToUpdate.value.id}/ban-status`, {
      isBanned: willBeBanned,
    })
    
    if (response.data.success) {
      // Update the user in the list
      const index = users.value.findIndex(u => u.id === userToUpdate.value.id)
      if (index !== -1) {
        users.value[index].isBanned = willBeBanned ? 'true' : 'false'
      }
      
      // Show success message
      successMessage.value = response.data.message || `User has been ${willBeBanned ? 'banned' : 'unbanned'} successfully`
    } else {
      error.value = response.data.message || 'Failed to update user status'
    }
  } catch (err) {
    console.error('Error updating user status:', err)
    error.value = err.response?.data?.message || 'An error occurred'
  } finally {
    actionLoading.value = false
    confirmDialog.value = false
    userToUpdate.value = null
  }
}

// Reset search
const resetSearch = () => {
  searchQuery.value = ''
  fetchUsers()
}

// Get badge color for user role
const getRoleBadgeColor = role => {
  switch (role) {
  case 'admin': return 'error'
  case 'developer': return 'primary'
  case 'user': return 'success'
  default: return 'grey'
  }
}

// View user details
const viewUserDetails = async user => {
  selectedUser.value = user
  userDetailsDialog.value = true
  userDetailsLoading.value = true
  
  try {
    // Optionally fetch more detailed user information if needed
    const response = await axios.get(`/api/admin/users/${user.id}`)
    if (response.data.success) {
      selectedUser.value = response.data.user
    }
  } catch (error) {
    console.error('Error fetching user details:', error)
  } finally {
    userDetailsLoading.value = false
  }
}

// Load users when component mounts
onMounted(() => {
  // Check if there's a filter parameter in the URL for banned users
  const urlParams = new URLSearchParams(window.location.search)
  if (urlParams.get('filter') === 'banned') {
    showBannedOnly.value = true
  }
  
  fetchUsers()
})
</script>

<template>
  <VCard>
    <VCardTitle class="d-flex justify-space-between align-center pt-6 mt-4">
      <h2>Users Management</h2>
    </VCardTitle>
    
    <VCardText>
      <p class="mb-4">
        Manage all system users, including the ability to ban users who violate platform rules.
      </p>
      
      <!-- Search and Filter Section -->
      <VRow class="mb-6">
        <VCol
          cols="12"
          md="6"
        >
          <VTextField
            v-model="searchQuery"
            label="Search users by name"
            prepend-inner-icon="mdi-magnify"
            density="compact"
            clearable
            @update:model-value="searchUsers"
            @click:clear="resetSearch"
          />
        </VCol>
        <VCol
          cols="12"
          md="3"
        >
          <div class="d-flex flex-column">
            <VSwitch
              v-model="showBannedOnly"
              color="error"
              label="Show banned users only"
              hide-details
              density="comfortable"
            />
            <div
              v-if="showBannedOnly"
              class="text-error text-caption ms-2"
            />
          </div>
        </VCol>
        <VCol
          cols="12"
          md="3"
          class="d-flex align-center justify-end"
        >
          <VBtn
            color="primary"
            :loading="loading"
            :disabled="loading"
            @click="fetchUsers"
          >
            Refresh
          </VBtn>
        </VCol>
      </VRow>
        
      <!-- Loading and Error States -->
      <div
        v-if="loading"
        class="d-flex justify-center py-4"
      >
        <VProgressCircular indeterminate />
      </div>
        
      <VAlert
        v-if="error"
        type="error"
        variant="tonal"
        closable
        class="mb-4"
      >
        {{ error }}
      </VAlert>
      
      <VAlert
        v-if="successMessage"
        type="success"
        variant="tonal"
        closable
        class="mb-4"
      >
        {{ successMessage }}
      </VAlert>
      
      <!-- Users Table -->
      <VDataTable
        v-if="!loading && filteredUsers.length > 0"
        :headers="headers"
        :items="filteredUsers"
        hover
        class="elevation-1"
        :item-class="item => item.isBanned === 'true' ? 'bg-error-lighten-5' : ''"
        :sort-by="[{key: 'id', order: 'asc'}]"
      >
        <!-- ID Column -->
        <template #item.id="{ item }">
          <span>{{ item.id }}</span>
        </template>
        
        <!-- Role Column -->
        <template #item.role="{ item }">
          <VChip
            :color="getRoleBadgeColor(item.role)"
            size="small"
            variant="flat"
          >
            {{ item.role }}
          </VChip>
        </template>
        
        <!-- Status Column -->
        <template #item.isBanned="{ item }">
          <VChip
            :color="item.isBanned === 'true' ? 'error' : 'success'"
            size="small"
            variant="flat"
          >
            {{ item.isBanned === 'true' ? 'Banned' : 'Active' }}
          </VChip>
        </template>
        
        <!-- Profile Picture Column -->
        <template #item.profilePic="{ item }">
          <VAvatar size="40">
            <VImg
              :src="getUserProfileImage(item.profilePic)"
              alt="User Avatar"
            />
          </VAvatar>
        </template>
        
        <!-- Actions Column -->
        <template #item.actions="{ item }">
          <div class="d-flex align-center gap-2">
            <VBtn
              v-if="item.isBanned !== 'true'"
              size="small"
              color="error"
              variant="tonal"
              title="Ban User"
              @click="confirmUserAction(item, 'ban')"
            >
              Ban
            </VBtn>
            
            <VBtn
              v-else
              size="small"
              color="success"
              variant="elevated"
              title="Unban User"
              class="px-4"
              @click="confirmUserAction(item, 'unban')"
            >
              Unban
            </VBtn>
            
            <VBtn
              size="small"
              color="info"
              variant="tonal"
              title="View User Details"
              @click="viewUserDetails(item)"
            >
              View
            </VBtn>
          </div>
        </template>
      </VDataTable>
      
      <!-- Empty State -->
      <div
        v-if="!loading && filteredUsers.length === 0"
        class="text-center py-8"
      >
        <VIcon
          icon="mdi-account-off"
          size="64"
          color="grey-lighten-1"
        />
        <h3 class="mt-4 text-h6">
          No users found
        </h3>
        <p class="text-body-2">
          {{ searchQuery ? 'Try a different search term' : 'There are no users in the system yet' }}
        </p>
        
        <VBtn
          v-if="searchQuery"
          color="primary"
          variant="outlined"
          class="mt-4"
          @click="resetSearch"
        >
          Clear Search
        </VBtn>
      </div>
    </VCardText>
    
    <!-- Confirmation Dialog -->
    <VDialog
      v-model="confirmDialog"
      max-width="500"
    >
      <VCard>
        <VCardTitle class="bg-primary text-white">
          {{ userToUpdate?.action === 'ban' ? 'Ban User' : 'Unban User' }}
        </VCardTitle>
        
        <VCardText class="pt-4">
          <p v-if="userToUpdate?.action === 'ban'">
            Are you sure you want to ban <strong>{{ userToUpdate?.name }}</strong>? This will prevent the user from logging in.
          </p>
          <p v-else>
            Are you sure you want to unban <strong>{{ userToUpdate?.name }}</strong>? This will restore the user's access to the platform.
          </p>
        </VCardText>
        
        <VCardActions>
          <VSpacer />
          <VBtn
            color="grey-darken-1"
            variant="text"
            @click="confirmDialog = false"
          >
            Cancel
          </VBtn>
          <VBtn
            :color="userToUpdate?.action === 'ban' ? 'error' : 'success'"
            :loading="actionLoading"
            @click="updateUserBanStatus"
          >
            {{ userToUpdate?.action === 'ban' ? 'Ban User' : 'Unban User' }}
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
    
    <!-- User Details Dialog -->
    <VDialog
      v-model="userDetailsDialog"
      max-width="700"
    >
      <VCard>
        <VCardTitle class="bg-info text-white d-flex align-center">
          <span>User Details</span>
          <VSpacer />
          <VBtn
            icon
            variant="text"
            color="white"
            @click="userDetailsDialog = false"
          >
            <VIcon icon="mdi-close" />
          </VBtn>
        </VCardTitle>
        
        <VCardText class="pa-4">
          <VProgressLinear 
            v-if="userDetailsLoading"
            indeterminate
            color="info"
            class="mb-4"
          />
          
          <div v-else-if="selectedUser">
            <div class="d-flex mb-6">
              <VAvatar
                size="100"
                class="me-4"
              >
                <VImg
                  :src="getUserProfileImage(selectedUser.profilePic)"
                  alt="User Avatar"
                />
              </VAvatar>
              
              <div>
                <h2 class="text-h4 mb-2">
                  {{ selectedUser.name }}
                </h2>
                <div class="d-flex align-center flex-wrap gap-2 mb-2">
                  <VChip
                    :color="getRoleBadgeColor(selectedUser.role)"
                    size="small"
                    class="me-2"
                  >
                    {{ selectedUser.role }}
                  </VChip>
                  
                  <VChip
                    :color="selectedUser.isBanned === 'true' ? 'error' : 'success'"
                    size="small"
                  >
                    {{ selectedUser.isBanned === 'true' ? 'Banned' : 'Active' }}
                  </VChip>
                </div>
                <p class="text-body-1">
                  <VIcon
                    icon="mdi-email"
                    size="small"
                    class="me-1"
                  />
                  {{ selectedUser.email }}
                </p>
              </div>
            </div>
            
            <VDivider />
            
            <div class="pt-4">
              <h3 class="text-h6 mb-3">
                User Information
              </h3>
              
              <VTable dense>
                <tbody>
                  <tr>
                    <td
                      class="text-subtitle-2 font-weight-bold"
                      width="150"
                    >
                      ID:
                    </td>
                    <td>{{ selectedUser.id }}</td>
                  </tr>
                  <tr>
                    <td class="text-subtitle-2 font-weight-bold">
                      Name:
                    </td>
                    <td>{{ selectedUser.name }}</td>
                  </tr>
                  <tr>
                    <td class="text-subtitle-2 font-weight-bold">
                      Email:
                    </td>
                    <td>{{ selectedUser.email }}</td>
                  </tr>
                  <tr>
                    <td class="text-subtitle-2 font-weight-bold">
                      Role:
                    </td>
                    <td>{{ selectedUser.role }}</td>
                  </tr>
                  <tr>
                    <td class="text-subtitle-2 font-weight-bold">
                      Status:
                    </td>
                    <td>{{ selectedUser.isBanned === 'true' ? 'Banned' : 'Active' }}</td>
                  </tr>
                  <tr v-if="selectedUser.createdAt">
                    <td class="text-subtitle-2 font-weight-bold">
                      Joined:
                    </td>
                    <td>{{ new Date(selectedUser.createdAt).toLocaleString() }}</td>
                  </tr>
                </tbody>
              </VTable>
            </div>
            
            <div class="d-flex justify-end mt-6">
              <VBtn
                v-if="selectedUser.isBanned !== 'true'"
                color="error"
                variant="tonal"
                class="me-2"
                prepend-icon="mdi-block-helper"
                @click="confirmUserAction(selectedUser, 'ban'); userDetailsDialog = false"
              >
                Ban This User
              </VBtn>
              
              <VBtn
                v-else
                color="success"
                variant="tonal"
                class="me-2"
                prepend-icon="mdi-check-circle"
                @click="confirmUserAction(selectedUser, 'unban'); userDetailsDialog = false"
              >
                Unban This User
              </VBtn>
              
              <VBtn
                color="grey-darken-1"
                variant="tonal"
                @click="userDetailsDialog = false"
              >
                Close
              </VBtn>
            </div>
          </div>
        </VCardText>
      </VCard>
    </VDialog>
  </VCard>
</template> 
