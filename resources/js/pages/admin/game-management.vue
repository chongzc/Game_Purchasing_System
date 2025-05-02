<template>
  <div>
    <div class="d-flex align-center mb-6">
      <h2 class="text-h3 font-weight-bold">
        Game Management
      </h2>
    </div>
    
    <!-- Filters -->
    <VCard class="mb-6">
      <VCardText>
        <!-- Success and Error Messages -->
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
            md="4"
          >
            <VTextField
              v-model="filters.search"
              label="Search games"
              placeholder="Enter game title or developer"
              variant="outlined"
              density="compact"
              prepend-inner-icon="bx-search"
              hide-details
              @update:model-value="applyFilters"
            />
          </VCol>
          <VCol
            cols="12"
            md="2"
          >
            <VSelect
              v-model="filters.category"
              label="Category"
              :items="categoryOptions"
              variant="outlined"
              density="compact"
              hide-details
              @update:model-value="applyFilters"
            />
          </VCol>
          <VCol
            cols="12"
            md="2"
          >
            <VSelect
              v-model="filters.status"
              label="Status"
              :items="statusOptions"
              variant="outlined"
              density="compact"
              hide-details
              @update:model-value="applyFilters"
            />
          </VCol>
          <VCol
            cols="12"
            md="2"
          >
            <VSelect
              v-model="filters.priceRange"
              label="Price Range"
              :items="priceRangeOptions"
              variant="outlined"
              density="compact"
              hide-details
              @update:model-value="applyFilters"
            />
          </VCol>
          <VCol
            cols="12"
            md="2"
          >
            <VBtn 
              color="primary" 
              variant="outlined" 
              block
              height="40"
              @click="resetFilters"
            >
              Reset Filters
            </VBtn>
          </VCol>
        </VRow>
      </VCardText>
    </VCard>
    
    <!-- Games Data Table -->
    <VCard>
      <VCardText class="pa-0">
        <VDataTable
          v-model:items-per-page="itemsPerPage"
          :headers="headers"
          :items="filteredGames"
          :search="filters.search"
          class="elevation-1"
          item-value="id"
        >
          <!-- Image Column -->
          <template #item.image="{ item }">
            <div class="d-flex align-center py-2">
              <VImg
                :src="item.image || '/images/placeholder.jpg'"
                height="80"
                width="80"
                cover
                class="rounded me-3"
              />
            </div>
          </template>
          
          <!-- Title Column -->
          <template #item.title="{ item }">
            <div class="font-weight-medium">
              {{ item.title }}
            </div>
            <div class="text-caption text-disabled">
              ID: {{ item.id }}
            </div>
          </template>
          
          <!-- Price Column -->
          <template #item.price="{ item }">
            <div>${{ item.discountedPrice.toFixed(2) }}</div>
            <div
              v-if="item.originalPrice"
              class="text-caption text-decoration-line-through text-disabled"
            >
              ${{ item.originalPrice.toFixed(2) }}
            </div>
          </template>
          
          <!-- Category Column -->
          <template #item.category="{ item }">
            <VChip
              :color="getCategoryColor(item.category)"
              size="small"
              variant="flat"
            >
              {{ item.category }}
            </VChip>
          </template>
          
          <!-- Status Column -->
          <template #item.status="{ item }">
            <VChip
              :color="getStatusColor(item.status)"
              size="small"
              variant="flat"
            >
              {{ item.status }}
            </VChip>
          </template>
          
          <!-- Date Column -->
          <template #item.releaseDate="{ item }">
            {{ formatDate(item.releaseDate) }}
          </template>
          
          <!-- Actions Column -->
          <template #item.actions="{ item }">
            <div>
              <VBtn
                icon
                variant="text"
                size="small"
                color="white"
                title="View Game Details"
                @click="viewGame(item)"
              >
                <VIcon icon="bx-show" />
              </VBtn>
              
              <!-- Status Update Menu -->
              <VMenu>
                <template #activator="{ props }">
                  <VBtn
                    icon
                    variant="text"
                    size="small"
                    color="white"
                    v-bind="props"
                    title="Update Status"
                  >
                    <VIcon icon="bx-dots-vertical" />
                  </VBtn>
                </template>
                <VList density="compact">
                  <VListItem
                    v-if="item.status !== 'verified'"
                    title="Approve Game"
                    prepend-icon="bx-check-circle"
                    color="white"
                    @click="updateGameStatus(item, 'verified')"
                  />
                  <VListItem
                    v-if="item.status !== 'pending'"
                    title="Set to Pending Review"
                    prepend-icon="bx-time"
                    color="warning"
                    @click="updateGameStatus(item, 'pending')"
                  />
                </VList>
              </VMenu>
            </div>
          </template>
        </VDataTable>
      </VCardText>
    </VCard>
  </div>
</template>

<script setup>
import axios from 'axios'
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const loading = ref(false)
const games = ref([])
const filteredGames = ref([])
const itemsPerPage = ref(10)

// Filters for the data table
const filters = ref({
  search: '',
  category: '',
  status: '',
  priceRange: '',
})

// Data table headers
const headers = [
  { title: 'Image', key: 'image', sortable: false },
  { title: 'Title', key: 'title', sortable: true },
  { title: 'Developer', key: 'developer.name', sortable: true },
  { title: 'Price', key: 'price', sortable: true },
  { title: 'Category', key: 'category', sortable: true },
  { title: 'Status', key: 'status', sortable: true },
  { title: 'Date', key: 'releaseDate', sortable: true },
  { title: 'Actions', key: 'actions', sortable: false, align: 'end' },
]

// Options for filters
const categoryOptions = ['All', 'Action', 'Adventure', 'RPG', 'Strategy', 'Sports', 'Racing', 'Simulation', 'Puzzle', 'Horror', 'Fighting']
const statusOptions = ['All', 'pending', 'verified']
const priceRangeOptions = ['All', 'Free', 'Under $10', '$10-$30', '$30-$60', 'Over $60']

// Breadcrumbs for navigation
const breadcrumbs = [
  { title: 'Admin Dashboard', to: '/admin-dashboard' },
  { title: 'Game Management', disabled: true },
]

// Add refs for messages
const successMessage = ref('')
const errorMessage = ref('')

// Fetch all games
const fetchGames = async () => {
  loading.value = true
  errorMessage.value = ''
  try {
    const response = await axios.get('/api/admin/games')

    console.log('Raw API response:', response.data) // Debug log without semicolon

    // Transform the backend data to match our table expectations
    games.value = response.data.games.map(game => ({
      id: game.g_id,
      title: game.g_title,
      description: game.g_description,
      price: parseFloat(game.g_price),
      originalPrice: game.g_discount > 0 ? parseFloat(game.g_price) : null,
      discountedPrice: game.g_discount > 0 ? 
        parseFloat(game.g_price) - (parseFloat(game.g_price) * (game.g_discount / 100)) : 
        parseFloat(game.g_price),
      discount: game.g_discount,
      status: game.g_status,
      category: game.g_category,
      image: game.g_mainImage,
      releaseDate: game.created_at,
      developer: {
        id: game.developer?.u_id,
        name: game.developer?.u_name || 'Unknown Developer',
      },
    }))
    
    console.log('Transformed games data:', games.value) // Debug log without semicolon
    applyFilters()
  } catch (error) {
    console.error('Error fetching games:', error)
    errorMessage.value = 'Failed to load games. Please try again.'
  } finally {
    loading.value = false
  }
}

// Apply filters to games
const applyFilters = () => {
  let result = [...games.value]
  
  // Apply search filter
  if (filters.value.search) {
    const searchTerm = filters.value.search.toLowerCase()

    result = result.filter(game => 
      game.title.toLowerCase().includes(searchTerm) || 
      (game.developer?.name && game.developer.name.toLowerCase().includes(searchTerm)),
    )
  }
  
  // Apply category filter
  if (filters.value.category && filters.value.category !== 'All') {
    result = result.filter(game => game.category === filters.value.category)
  }
  
  // Apply status filter
  if (filters.value.status && filters.value.status !== 'All') {
    result = result.filter(game => game.status === filters.value.status)
  }
  
  // Apply price range filter
  if (filters.value.priceRange && filters.value.priceRange !== 'All') {
    switch(filters.value.priceRange) {
    case 'Free':
      result = result.filter(game => parseFloat(game.price) === 0)
      break
    case 'Under $10':
      result = result.filter(game => parseFloat(game.price) > 0 && parseFloat(game.price) < 10)
      break
    case '$10-$30':
      result = result.filter(game => parseFloat(game.price) >= 10 && parseFloat(game.price) <= 30)
      break
    case '$30-$60':
      result = result.filter(game => parseFloat(game.price) > 30 && parseFloat(game.price) <= 60)
      break
    case 'Over $60':
      result = result.filter(game => parseFloat(game.price) > 60)
      break
    }
  }
  
  filteredGames.value = result
}

// Reset all filters
const resetFilters = () => {
  filters.value = {
    search: '',
    category: '',
    status: '',
    priceRange: '',
  }
  applyFilters()
}

// View game details
const viewGame = game => {
  router.push(`/games/${game.id}`)
}

// Update a game's status
const updateGameStatus = async (game, newStatus) => {
  successMessage.value = ''
  errorMessage.value = ''
  try {
    const response = await axios.patch(`/api/admin/games/${game.id}/status`, { status: newStatus })
    if (response.data.success) {
      // Update the game status locally to avoid a full refresh
      const gameIndex = games.value.findIndex(g => g.id === game.id)
      if (gameIndex !== -1) {
        games.value[gameIndex].status = newStatus
        applyFilters() // Reapply filters to update the view
      }
      
      // Show success message
      successMessage.value = `Game "${game.title}" has been ${getStatusActionText(newStatus)}`
    }
  } catch (error) {
    console.error('Error updating game status:', error)
    errorMessage.value = 'Failed to update game status. Please try again.'
  }
}

// Helper function to get appropriate status action text
const getStatusActionText = status => {
  switch(status) {
  case 'verified': return 'approved'
  case 'pending': return 'set to pending review'
  default: return 'updated'
  }
}

// Helper functions for UI
const getCategoryColor = category => {
  const colors = {
    'Action': 'red',
    'Adventure': 'green',
    'RPG': 'blue',
    'Strategy': 'orange',
    'Sports': 'lime',
    'Racing': 'indigo',
    'Simulation': 'cyan',
    'Puzzle': 'purple',
    'Platformer': 'amber',
    'Fighting': 'pink',
    'Shooter': 'teal',
  }
  
  return colors[category] || 'grey'
}

const getStatusColor = status => {
  const colors = {
    'pending': 'warning',
    'verified': 'success',
    'reported': 'error',
    'removed': 'grey',
  }
  
  return colors[status] || 'grey'
}

const formatDate = dateString => {
  const options = { year: 'numeric', month: 'short', day: 'numeric' }
  
  return new Date(dateString).toLocaleDateString(undefined, options)
}

// Load data when component mounts
onMounted(() => {
  fetchGames()
})
</script> 
