<script setup>
import { useAuthStore } from '@/stores/auth'
import axios from 'axios'
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const router = useRouter()

const statistics = ref({
  totalGames: 0,
  pendingGames: 0,
  verifiedGames: 0,
  reportedGames: 0,
  removedGames: 0,
  totalDevelopers: 0,
  totalUsers: 0,
  totalAdmins: 0,
  bannedUsers: 0,
})

// Fetch statistics for the dashboard
const fetchStatistics = async () => {
  try {
    const response = await axios.get('/api/admin/statistics')

    console.log('Statistics response:', response.data)
    
    if (response.data.success) {
      // Handle different response formats from both statistics endpoints
      if (response.data.statistics.totalGames !== undefined) {
        // New format with the statistics object
        statistics.value = response.data.statistics
      } else if (response.data.statistics.total_games !== undefined) {
        // Old format with snake_case keys
        statistics.value = {
          totalGames: response.data.statistics.total_games,
          pendingGames: response.data.statistics.pending_games,
          verifiedGames: response.data.statistics.verified_games,
          reportedGames: response.data.statistics.reported_games,
          totalDevelopers: response.data.statistics.top_categories?.length || 0,
          totalUsers: 0,
          totalAdmins: 0,
          bannedUsers: 0,
        }
      }
    }
  } catch (error) {
    console.error('Error fetching statistics:', error)

    // Try alternate endpoint if first fails
    try {
      const response = await axios.get('/api/admin/statistics/summary')

      console.log('Fallback statistics response:', response.data)
      
      if (response.data.success) {
        statistics.value = response.data.statistics
      }
    } catch (fallbackError) {
      console.error('Error fetching fallback statistics:', fallbackError)
    }
  }
}

// Navigate to the game management page
const navigateToGameManagement = () => {
  router.push('/admin/games')
}

// Navigate to the user management page
const navigateToUserManagement = () => {
  router.push('/admin/users')
}

onMounted(() => {
  fetchStatistics()
})
</script>

<template>
  <VContainer>
    <VRow>
      <VCol cols="12">
        <VCard>
          <VCardTitle>
            Admin Dashboard
          </VCardTitle>
          <VCardText>
            <h2 class="text-h6 font-weight-medium mb-6">
              Welcome back, {{ authStore.user?.u_name || 'Admin' }}!
            </h2>
            <p>From here you can manage the game store platform, users, and view statistics.</p>
          </VCardText>
        </VCard>
      </VCol>
      
      <VCol
        cols="12"
        md="6"
      >
        <VCard height="100%">
          <VCardTitle>
            Users Overview
          </VCardTitle>
          <VCardText>
            <div class="d-flex justify-space-between align-center">
              <div>
                <h3 class="text-h6">
                  Total Users
                </h3>
                <p class="text-h4">
                  {{ statistics.totalUsers + statistics.totalDevelopers + statistics.totalAdmins }}
                </p>
              </div>
              <VDivider
                vertical
                class="mx-4"
              />
              <div>
                <h3 class="text-h6">
                  Developers
                </h3>
                <p class="text-h4">
                  {{ statistics.totalDevelopers }}
                </p>
              </div>
              <VDivider
                vertical
                class="mx-4"
              />
              <div>
                <h3 class="text-h6">
                  Customers
                </h3>
                <p class="text-h4">
                  {{ statistics.totalUsers }}
                </p>
              </div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
      
      <VCol
        cols="12"
        md="6"
      >
        <VCard height="100%">
          <VCardTitle>
            Games Overview
          </VCardTitle>
          <VCardText>
            <div class="d-flex justify-space-between align-center">
              <div>
                <h3 class="text-h6">
                  Total Games
                </h3>
                <p class="text-h4">
                  {{ statistics.totalGames }}
                </p>
              </div>
              <VDivider
                vertical
                class="mx-4"
              />
              <div>
                <h3 class="text-h6">
                  Pending Review
                </h3>
                <p class="text-h4 text-warning">
                  {{ statistics.pendingGames }}
                </p>
              </div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
      
      <VCol cols="12">
        <VCard v-if="statistics.pendingGames > 0">
          <VCardTitle class="bg-warning-subtle">
            <VIcon
              icon="mdi-alert"
              color="warning"
              class="me-2"
            />
            Pending Games Approval
          </VCardTitle>
          <VCardText>
            <p>There are {{ statistics.pendingGames }} games waiting for your approval.</p>
            <VBtn
              color="warning"
              variant="outlined"
              class="mt-2"
              @click="navigateToGameManagement"
            >
              Review Pending Games
            </VBtn>
          </VCardText>
        </VCard>
      </VCol>
      
      <VCol cols="12">
        <VCard
          v-if="statistics.bannedUsers > 0"
          class="border-error"
        >
          <VCardTitle class="bg-error-lighten-5 text-error">
            <VIcon
              icon="mdi-account-cancel"
              color="error"
              class="me-2"
            />
            Banned Users
          </VCardTitle>
          <VCardText>
            <p>There are currently {{ statistics.bannedUsers }} banned users in the system.</p>
            <VBtn
              color="error"
              variant="outlined"
              class="mt-2"
              @click="router.push('/admin/users?filter=banned')"
            >
              Manage Banned Users
            </VBtn>
          </VCardText>
        </VCard>
      </VCol>
      
      <VCol cols="12">
        <VCard>
          <VCardTitle>
            Quick Actions
          </VCardTitle>
          <VDivider />
          <VCardText>
            <VRow>
              <VCol
                cols="12"
                md="4"
              >
                <VBtn
                  prepend-icon="mdi-account-group"
                  block
                  variant="flat"
                  color="primary"
                  class="mb-4"
                  @click="navigateToUserManagement"
                >
                  Manage Users
                </VBtn>
              </VCol>
              <VCol
                cols="12"
                md="4"
              >
                <VBtn
                  prepend-icon="mdi-gamepad-variant"
                  block
                  variant="flat"
                  color="primary"
                  class="mb-4"
                  @click="navigateToGameManagement"
                >
                  Manage Games
                </VBtn>
              </VCol>
              <VCol
                cols="12"
                md="4"
              >
                <VBtn
                  prepend-icon="mdi-chart-bar"
                  block
                  variant="flat"
                  color="primary"
                  class="mb-4"
                >
                  View Reports
                </VBtn>
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </VContainer>
</template> 
