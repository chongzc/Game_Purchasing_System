<script setup>
import { useAuthStore } from '@/stores/auth'
import axios from 'axios'
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const router = useRouter()

const games = ref([])
const loading = ref(true)

const handleAddNewGame = () => {
  router.push('/create-game')
}

// Fetch developer's games
const fetchGames = async () => {
  try {
    loading.value = true
    const response = await axios.get('/api/developer/games')
    games.value = response.data.games
  } catch (error) {
    console.error('Error fetching games:', error)
  } finally {
    loading.value = false
  }
}

// Get status color for chip
const getStatusColor = (status) => {
  switch (status.toLowerCase()) {
    case 'pending':
      return 'warning'
    case 'approved':
      return 'success'
    case 'rejected':
      return 'error'
    default:
      return 'grey'
  }
}

onMounted(() => {
  fetchGames()
})
</script>

<template>
  <VContainer>
    <VRow>
      <VCol cols="12">
        <VCard>
          <VCardTitle>
            Developer Dashboard
          </VCardTitle>
          <VCardText>
            <h2 class="text-h6 font-weight-medium mb-6">
              Welcome back, {{ authStore.user?.u_name || 'Developer' }}!
            </h2>
            <p>From here you can manage your published games, add new ones, and view statistics.</p>
          </VCardText>
        </VCard>
      </VCol>
      
      <VCol
        cols="24"
        md="12"
      >
        <VCard height="100%">
          <VCardTitle class="d-flex align-center justify-space-between">
            My Games
            <VBtn
              color="primary"
              variant="flat"
              size="small"
              @click="handleAddNewGame"
            >
              Add New Game
            </VBtn>
          </VCardTitle>
          <VCardText v-if="!games.length">
            <p>No games published yet.</p>
          </VCardText>
          <VCardText v-else>
            <VTable>
              <thead>
                <tr>
                  <th>Image</th>
                  <th>Title</th>
                  <th>Price</th>
                  <th>Status</th>
                  <th>Downloads</th>
                  <th>Rating</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="game in games" :key="game.id">
                  <td>
                    <VImg
                      :src="game.mainImage"
                      width="200"
                      height="120"
                      cover
                      class="rounded"
                    />
                  </td>
                  <td>{{ game.title }}</td>
                  <td>RM {{ game.price.toFixed(2) }}</td>
                  <td>
                    <VChip
                      :color="getStatusColor(game.status)"
                      size="small"
                    >
                      {{ game.status }}
                    </VChip>
                  </td>
                  <td>{{ game.downloadCount }}</td>
                  <td>{{ game.overallRate }}/5</td>
                  <td>
                    <VBtn
                      icon
                      variant="text"
                      color="primary"
                      size="small"
                      :to="'/games/' + game.id + '/view'"
                    >
                      <VIcon icon="bx-show" />
                    </VBtn>
                    <VBtn
                      icon
                      variant="text"
                      color="primary"
                      size="small"
                      :to="'/games/' + game.id + '/edit'"
                    >
                      <VIcon icon="bx-edit" />
                    </VBtn>
                  </td>
                </tr>
              </tbody>
            </VTable>
          </VCardText>
        </VCard>
      </VCol>
      
      <!-- <VCol
        cols="12"
        md="6"
      >
        <VCard height="100%">
          <VCardTitle>
            Sales Overview
          </VCardTitle>
          <VCardText>
            <p>No sales data available.</p>
          </VCardText>
        </VCard>
      </VCol> -->
      
      <VCol
        cols="12"
        md="6"
      >
        <VCard height="100%">
          <VCardTitle>
            Latest Reviews
          </VCardTitle>
          <VCardText>
            <p>No reviews yet.</p>
          </VCardText>
        </VCard>
      </VCol>
      
      <VCol
        cols="12"
        md="6"
      >
        <VCard height="100%">
          <VCardTitle>
            Quick Actions
          </VCardTitle>
          <VDivider />
          <VList>
            <VListItem
              title="Create New Game"
              prepend-icon="mdi-plus"
            />
            <!-- <VListItem
              title="View Sales Reports"
              prepend-icon="mdi-chart-line"
            /> -->
            <VListItem
              title="Update Profile"
              prepend-icon="mdi-account-edit"
            />
          </VList>
        </VCard>
      </VCol>
    </VRow>
  </VContainer>
</template> 
