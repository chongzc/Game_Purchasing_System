<template>
  <div>
    <VBreadcrumbs
      :items="breadcrumbs"
      class="pa-0 mb-6"
    />
    
    <div class="d-flex align-center mb-6">
      <h1 class="text-h3 font-weight-bold">
        My Game Library
      </h1>
      <VSpacer />
    </div>
    
    <!-- Search and Filter -->
    <VCard class="mb-6">
      <VCardText>
        <!-- Messages -->
        <VAlert
          v-if="successMessage"
          type="success"
          variant="tonal"
          closable
          class="mb-4"
        >
          {{ successMessage }}
        </VAlert>
        
        <VAlert
          v-if="errorMessage"
          type="error"
          variant="tonal"
          closable
          class="mb-4"
        >
          {{ errorMessage }}
        </VAlert>
        
        <VRow>
          <VCol
            cols="12"
            md="6"
          >
            <VTextField
              v-model="searchQuery"
              label="Search your games"
              placeholder="Enter game title"
              variant="outlined"
              density="compact"
              prepend-inner-icon="bx-search"
              hide-details
            />
          </VCol>
          <VCol
            cols="12"
            md="3"
          >
            <VSelect
              v-model="filterStatus"
              label="Status"
              :items="statusOptions"
              variant="outlined"
              density="compact"
              hide-details
            />
          </VCol>
          <VCol
            cols="12"
            md="3"
          >
            <VSelect
              v-model="sortOption"
              label="Sort by"
              :items="sortOptions"
              variant="outlined"
              density="compact"
              hide-details
            />
          </VCol>
        </VRow>
      </VCardText>
    </VCard>
    
    <!-- Loading State -->
    <VCard
      v-if="loading"
      class="pa-6 text-center"
    >
      <VProgressCircular
        indeterminate
        color="primary"
      />
      <div class="text-body-1 mt-4">
        Loading your library...
      </div>
    </VCard>
    
    <!-- Library Items -->
    <VRow v-else>
      <VCol
        v-for="game in filteredGames"
        :key="game.id"
        cols="12"
        sm="6"
        md="4"
        lg="3"
      >
        <VCard>
          <VImg
            :src="game.image || '/images/placeholder-game.jpg'"
            height="200"
            cover
            class="game-image"
            :alt="game.title"
          >
            <template #placeholder>
              <VRow
                class="fill-height ma-0"
                align="center"
                justify="center"
              >
                <VProgressCircular
                  indeterminate
                  color="grey-lighten-5"
                />
              </VRow>
            </template>
            <div class="image-overlay d-flex align-center justify-center">
              <VBtn
                icon="bx-play-circle"
                size="x-large"
                color="white"
                variant="text"
                @click="toggleInstallation(game)"
              />
            </div>
          </VImg>
          
          <VCardText>
            <div class="d-flex justify-space-between align-center mb-2">
              <div class="font-weight-bold text-subtitle-1">
                {{ game.title }}
              </div>
              <VChip
                :color="getStatusColor(game.status)"
                size="small"
                variant="flat"
              >
                {{ game.status }}
              </VChip>
            </div>
            
            <div class="text-caption text-grey">
              <div class="mb-1">
                <VIcon
                  size="small"
                  icon="bx-calendar"
                  class="me-1"
                />
                Added: {{ formatDate(game.purchaseDate) }}
              </div>
              <div class="mb-1">
                <VIcon
                  size="small"
                  icon="bx-purchase-tag"
                  class="me-1"
                />
                Price: ${{ game.price }}
              </div>
              <div
                v-if="game.developer"
                class="mb-1"
              >
                <VIcon
                  size="small"
                  icon="bx-code-alt"
                  class="me-1"
                />
                Developer: {{ game.developer.u_name }}
              </div>
            </div>
          </VCardText>
          
          <VCardActions>
            <VBtn
              :color="game.status === 'installed' ? (playingGameId === game.id ? 'error' : 'success') : 'primary'"
              :prepend-icon="game.status === 'installed' ? (playingGameId === game.id ? 'bx-stop' : 'bx-play') : 'bx-download'"
              size="small"
              variant="flat"
              class="me-2"
              @click="toggleInstallation(game)"
            >
              {{ game.status === 'installed' ? (playingGameId === game.id ? 'Stop' : 'Play Now') : 'Install' }}
            </VBtn>
            <VBtn
              v-if="game.status === 'installed'"
              color="error"
              size="small"
              variant="outlined"
              prepend-icon="bx-trash"
              @click="uninstallGame(game)"
            >
              Uninstall
            </VBtn>
          </VCardActions>
        </VCard>
      </VCol>
    </VRow>
    
    <!-- Empty State -->
    <VCard
      v-if="!loading && filteredGames.length === 0"
      class="pa-6 text-center"
    >
      <VIcon
        icon="bx-confused"
        size="64"
        color="grey-lighten-1"
      />
      <h2 class="text-h5 font-weight-bold mt-4 mb-2">
        No Games Found
      </h2>
      <p class="text-body-1 mb-6">
        {{ getEmptyStateMessage }}
      </p>
      <VBtn 
        v-if="library.length === 0"
        color="primary" 
        to="/game-store"
        prepend-icon="bx-shopping-bag"
      >
        Browse Games
      </VBtn>
      <VBtn 
        v-else
        color="primary" 
        @click="clearFilters"
      >
        Clear Filters
      </VBtn>
    </VCard>
  </div>
</template>

<script setup>
import axios from 'axios'
import { computed, onMounted, ref, watch } from 'vue'

// Breadcrumbs
const breadcrumbs = ref([
  {
    title: 'Home',
    disabled: false,
    to: '/',
  },
  {
    title: 'Game Library',
    disabled: true,
  },
])

// Library data
const library = ref([])
const loading = ref(true)
const allGames = ref([])

// Add tracking for currently playing game
const playingGameId = ref(null)

// Search and filtering
const searchQuery = ref('')
const filterStatus = ref('all')
const sortOption = ref('title-asc')

// Filter options
const statusOptions = [
  { title: 'All Games', value: 'all' },
  { title: 'Owned Games', value: 'owned' },
  { title: 'Installed Games', value: 'installed' },
]

const sortOptions = [
  { title: 'Title (A-Z)', value: 'title-asc' },
  { title: 'Title (Z-A)', value: 'title-desc' },
]

// Filter and sort library
const filteredGames = computed(() => {
  let result = [...library.value]
  
  // Filter by search query
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()

    result = result.filter(game => game.title.toLowerCase().includes(query))
  }
  
  // Filter by status
  if (filterStatus.value !== 'all') {
    result = result.filter(game => game.status.toLowerCase() === filterStatus.value)
  }
  
  // Sort
  result.sort((a, b) => {
    switch (sortOption.value) {
    case 'title-asc':
      return a.title.localeCompare(b.title)
    case 'title-desc':
      return b.title.localeCompare(a.title)
    default:
      return 0
    }
  })
  
  return result
})

// Methods
const fetchAllGames = async () => {
  try {
    const response = await axios.get('/api/browseGames')

    allGames.value = response.data
    
    return response.data
  } catch (error) {
    console.error('Error fetching games:', error)
    
    return []
  }
}

const fetchLibraryGames = async () => {
  try {
    loading.value = true

    const response = await axios.get('/api/library', {
      params: {
        status: filterStatus.value,
      },
    })
    
    console.log('API Response:', response.data)
    
    // Transform the response data with default values
    library.value = response.data.map(item => ({
      id: item.game?.g_id || 0,
      title: item.game?.g_title || 'Unknown Game',
      image: item.game?.g_image || '/images/placeholder-game.jpg',
      status: item.ul_status || 'owned',
      purchaseDate: item.ul_createdAt || new Date().toISOString(),
      price: item.game?.g_price || 0,
      description: item.game?.g_description || '',
      developer: item.game?.developer || 'Unknown Developer',
    }))

    console.log('Transformed Library:', library.value)
  } catch (error) {
    console.error('Error fetching library games:', error)
    library.value = [] // Reset library on error
  } finally {
    loading.value = false
  }
}

// Add refs for message states
const successMessage = ref('')
const errorMessage = ref('')

const toggleInstallation = async game => {
  successMessage.value = ''
  errorMessage.value = ''
  try {
    if (game.status === 'installed') {
      // If game is installed, toggle between play and stop
      if (playingGameId.value === game.id) {
        // Stop the game
        playingGameId.value = null
        successMessage.value = `Stopped ${game.title}`
        
        // Clear the "Stopped" message after 3 seconds
        setTimeout(() => {
          if (successMessage.value === `Stopped ${game.title}`) {
            successMessage.value = ''
          }
        }, 3000)
      } else {
        // Launch the game
        playingGameId.value = game.id
        successMessage.value = `Launching ${game.title}...`
      }
    } else {
      // If game is not installed, update status to installed
      const response = await axios.put(`/api/library/${game.id}/status`, {
        status: 'installed',
      })
      
      if (response.data.success) {
        // Update local state
        game.status = 'installed'
        successMessage.value = `${game.title} has been installed successfully`
      } else {
        throw new Error(response.data.message || 'Failed to update game status')
      }
    }
  } catch (error) {
    console.error('Error updating game status:', error)
    errorMessage.value = 'Failed to update game status. Please try again.'
  }
}

const uninstallGame = async game => {
  successMessage.value = ''
  errorMessage.value = ''
  try {
    const response = await axios.put(`/api/library/${game.id}/status`, {
      status: 'owned',
    })
    
    if (response.data.success) {
      // Update local state
      game.status = 'owned'
      successMessage.value = `${game.title} has been uninstalled`
    } else {
      throw new Error(response.data.message || 'Failed to uninstall game')
    }
  } catch (error) {
    console.error('Error uninstalling game:', error)
    errorMessage.value = 'Failed to uninstall game. Please try again.'
  }
}

const clearFilters = () => {
  searchQuery.value = ''
  filterStatus.value = 'all'
  sortOption.value = 'title-asc'
}

// Add new helper functions
const formatDate = date => {
  if (!date) return 'N/A'
  
  return new Date(date).toLocaleDateString()
}

const getStatusColor = status => {
  switch ((status || 'owned').toLowerCase()) {
  case 'installed':
    return 'success'
  case 'owned':
    return 'info'
  case 'downloading':
    return 'warning'
  case 'update-available':
    return 'error'
  default:
    return 'grey'
  }
}

// Add computed property for filtered games count
const filteredGamesCount = computed(() => {
  return library.value.length
})

// Update the empty state message
const getEmptyStateMessage = computed(() => {
  if (filteredGames.value.length === 0) {
    if (searchQuery.value || filterStatus.value !== 'all') {
      return "No games match your current filters."
    }
    
    return "You haven't added any games to your library yet."
  }
  
  return ""
})

// Add watch for filterStatus changes
watch(filterStatus, () => {
  fetchLibraryGames()
})

// Load library data on mount
onMounted(() => {
  fetchLibraryGames()
})
</script>

<style scoped>
.game-image {
  position: relative;
}

.image-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.4);
  opacity: 0;
  transition: opacity 0.3s ease;
}

.game-image:hover .image-overlay {
  opacity: 1;
}

.game-platform {
  position: absolute;
  top: 8px;
  right: 8px;
}
</style>
