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
const getStatusColor = status => {
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
  <div class="developer-dashboard-container">
    <VContainer
      fluid
      class="max-width-1920"
    >
      <VRow>
        <h1 class="text-h3 font-weight-bold">
          Developer Dashboard
        </h1>

        
        <VCol
          cols="24"
          md="12"
        >
          <VCard
            height="100%"
            style="padding: 15px;"
          >
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
                    <th class="text-center">
                      Discount
                    </th>
                    <th>Rating</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="game in games"
                    :key="game.id"
                  >
                    <td>
                      <VImg
                        :src="game.mainImage"
                        width="200"
                        height="120"
                        cover
                        class="rounded"
                        style="margin: 10px;"
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
                    <td class="text-center">
                      {{ game.discount || 0 }}%
                    </td>
                    <td>{{ game.overallRate }}/5</td>
                    <td>
                      <VBtn
                        icon
                        variant="text"
                        color="primary"
                        size="small"
                        :to="'/games/' + game.id"
                      >
                        <VIcon icon="bx-show" />
                      </VBtn>
                      <VBtn
                        icon
                        variant="text"
                        color="primary"
                        size="small"
                        :to="'/game-edit/' + game.id"
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
      </VRow>
    </VContainer>
  </div>
</template>

<style scoped>
.max-width-1920 {
  max-width: 1920px !important;
  margin: 0 auto;
}

.developer-dashboard-container {
  width: 100%;
  display: flex;
  justify-content: center;
}
</style> 
