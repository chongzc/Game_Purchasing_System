<script setup>
import { useAuthStore } from '@/stores/auth'
import axios from 'axios'
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import GameManagement from './game-management.vue'
import UserManagement from './user-management.vue'

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
  <div class="admin-dashboard-container">
    <VContainer
      fluid
      class="max-width-1920"
    >
      <div class="d-flex align-center mb-6">
        <h1 class="text-h3 font-weight-bold">
          Admin Dashboard
        </h1>
      </div>
      <VRow>
        <VCol
          cols="12"
          md="6"
        >
          <VCard height="100%">
            <VCardTitle>
              Users Overview
            </VCardTitle>
            <VCardText>
              <div class="d-flex justify-space-around align-center">
                <div class="text-center px-4">
                  <h3 class="text-h6 text-medium-emphasis">
                    Total Users
                  </h3>
                  <p class="text-h4 mt-2">
                    {{ statistics.totalUsers + statistics.totalDevelopers + statistics.totalAdmins }}
                  </p>
                </div>
                <VDivider
                  vertical
                  class="mx-2"
                />
                <div class="text-center px-4">
                  <h3 class="text-h6 text-medium-emphasis">
                    Developers
                  </h3>
                  <p class="text-h4 mt-2">
                    {{ statistics.totalDevelopers }}
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
              <div class="d-flex justify-space-around align-center">
                <div class="text-center px-4">
                  <h3 class="text-h6 text-medium-emphasis">
                    Total Games
                  </h3>
                  <p class="text-h4 mt-2">
                    {{ statistics.totalGames }}
                  </p>
                </div>
                <VDivider
                  vertical
                  class="mx-4"
                />
                <div class="text-center px-4">
                  <h3 class="text-h6 text-medium-emphasis">
                    Pending Review
                  </h3>
                  <p class="text-h4 text-warning mt-2">
                    {{ statistics.pendingGames }}
                  </p>
                </div>
              </div>
            </VCardText>
          </VCard>
        </VCol>
        
        <VCol cols="12">
          <GameManagement />
        </VCol>

        <VCol cols="12">
          <UserManagement />
        </VCol>
      </VRow>
    </VContainer>
  </div>
</template>

<style scoped>
.max-width-1920 {
  max-width: 1920px !important;
  margin: 0 auto;
}

.admin-dashboard-container {
  width: 100%;
  display: flex;
  justify-content: center;
}
</style> 
