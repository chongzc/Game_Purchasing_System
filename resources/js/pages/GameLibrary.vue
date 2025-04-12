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
      <VBtn
        color="primary"
        prepend-icon="bx-download"
      >
        Download Launcher
      </VBtn>
    </div>
    
    <!-- Search and Filter -->
    <VCard class="mb-6">
      <VCardText>
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
            :src="game.image || '/images/placeholder.jpg'"
            height="200"
            cover
            class="game-image"
          />
          
          <VCardText>
            <div class="font-weight-bold text-subtitle-1 mb-1">
              {{ game.title }}
            </div>
            
            <div class="d-flex align-center justify-space-between">
              <div class="text-caption">
                <span>Status: {{ game.status }}</span>
              </div>
              <div>
                <VChip
                  v-if="game.status === 'installed'"
                  color="success"
                  size="small"
                  variant="flat"
                >
                  Installed
                </VChip>
                <VChip
                  v-else-if="game.status === 'owned'"
                  color="info"
                  size="small"
                  variant="flat"
                >
                  Owned
                </VChip>
                <VChip
                  v-else
                  color="grey"
                  size="small"
                  variant="flat"
                >
                  {{ game.status }}
                </VChip>
              </div>
            </div>
          </VCardText>
          
          <VCardActions>
            <VBtn
              block
              :color="game.status === 'installed' ? 'success' : 'primary'"
              size="small"
              variant="flat"
              @click="toggleInstallation(game)"
            >
              {{ game.status === 'installed' ? 'Play Now' : 'Install' }}
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
        {{ library.length === 0 
          ? "You haven't purchased any games yet." 
          : "No games match your current filters." }}
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
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

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

// Search and filtering
const searchQuery = ref('')
const filterStatus = ref('All')
const sortOption = ref('title-asc')

// Filter options
const statusOptions = ['All', 'installed', 'owned', 'removed']

const sortOptions = [
  { title: 'Title (A-Z)', value: 'title-asc' },
  { title: 'Title (Z-A)', value: 'title-desc' },
  { title: 'Status', value: 'status' },
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
  if (filterStatus.value !== 'All') {
    result = result.filter(game => game.status === filterStatus.value)
  }
  
  // Sort
  result.sort((a, b) => {
    switch (sortOption.value) {
    case 'title-asc':
      return a.title.localeCompare(b.title)
    case 'title-desc':
      return b.title.localeCompare(a.title)
    case 'status':
      return a.status.localeCompare(b.status)
    default:
      return 0
    }
  })
  
  return result
})

// Methods
const fetchLibrary = async () => {
  try {
    loading.value = true
    const response = await axios.get('/game-library/games')
    library.value = response.data
  } catch (error) {
    console.error('Error fetching library:', error)
  } finally {
    loading.value = false
  }
}

const toggleInstallation = async (game) => {
  try {
    // Here we would make an API call to update the game status
    // For now, just update locally
    if (game.status === 'installed') {
      alert(`Launching ${game.title}...`)
    } else {
      alert(`Installing ${game.title}...`)
      game.status = 'installed'
    }
  } catch (error) {
    console.error('Error updating game status:', error)
  }
}

const clearFilters = () => {
  searchQuery.value = ''
  filterStatus.value = 'All'
  sortOption.value = 'title-asc'
}

// Load library data on mount
onMounted(() => {
  fetchLibrary()
})
</script>

<style scoped>
.game-image {
  position: relative;
}

.game-platform {
  position: absolute;
  top: 8px;
  right: 8px;
}
</style>
