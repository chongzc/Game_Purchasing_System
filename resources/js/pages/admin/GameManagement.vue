<template>
  <div>
    <VBreadcrumbs :items="breadcrumbs" class="pa-0 mb-6"></VBreadcrumbs>
    
    <div class="d-flex align-center mb-6">
      <h1 class="text-h3 font-weight-bold">Game Management</h1>
      <VSpacer />
      <VBtn color="primary" prepend-icon="bx-plus" @click="openAddGameDialog">
        Add New Game
      </VBtn>
    </div>
    
    <!-- Filters -->
    <VCard class="mb-6">
      <VCardText>
        <VRow>
          <VCol cols="12" md="4">
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
          <VCol cols="12" md="2">
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
          <VCol cols="12" md="2">
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
          <VCol cols="12" md="2">
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
          <VCol cols="12" md="2">
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
                width="60"
                height="40"
                cover
                class="rounded mr-3"
              />
            </div>
          </template>
          
          <!-- Title Column -->
          <template #item.title="{ item }">
            <div class="font-weight-medium">{{ item.title }}</div>
            <div class="text-caption text-disabled">ID: {{ item.id }}</div>
          </template>
          
          <!-- Price Column -->
          <template #item.price="{ item }">
            <div>${{ item.price.toFixed(2) }}</div>
            <div v-if="item.originalPrice" class="text-caption text-decoration-line-through text-disabled">
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
            <div class="d-flex">
              <VBtn
                icon
                variant="text"
                size="small"
                color="primary"
                @click="editGame(item)"
              >
                <VIcon icon="bx-edit" />
              </VBtn>
              <VBtn
                icon
                variant="text"
                size="small"
                color="success"
                @click="viewGame(item)"
              >
                <VIcon icon="bx-show" />
              </VBtn>
              <VBtn
                icon
                variant="text"
                size="small"
                color="error"
                @click="confirmDeleteGame(item)"
              >
                <VIcon icon="bx-trash" />
              </VBtn>
            </div>
          </template>
        </VDataTable>
      </VCardText>
    </VCard>
    
    <!-- Add/Edit Game Dialog -->
    <VDialog v-model="gameDialog.show" max-width="800">
      <VCard>
        <VCardTitle class="d-flex align-center pa-4">
          <h3 class="text-h5 font-weight-bold">{{ gameDialog.isEdit ? 'Edit Game' : 'Add New Game' }}</h3>
          <VSpacer />
          <VBtn icon variant="text" @click="gameDialog.show = false">
            <VIcon icon="bx-x" />
          </VBtn>
        </VCardTitle>
        
        <VDivider />
        
        <VCardText class="pa-4">
          <VForm @submit.prevent="saveGame">
            <VRow>
              <VCol cols="12" md="8">
                <VTextField
                  v-model="gameDialog.form.title"
                  label="Game Title"
                  variant="outlined"
                  required
                />
              </VCol>
              <VCol cols="12" md="4">
                <VSelect
                  v-model="gameDialog.form.status"
                  label="Status"
                  :items="statusOptions"
                  variant="outlined"
                  required
                />
              </VCol>
            </VRow>
            
            <VRow>
              <VCol cols="12" md="6">
                <VSelect
                  v-model="gameDialog.form.category"
                  label="Category"
                  :items="categoryOptions"
                  variant="outlined"
                  required
                />
              </VCol>
              <VCol cols="12" md="3">
                <VTextField
                  v-model="gameDialog.form.price"
                  label="Price ($)"
                  type="number"
                  variant="outlined"
                  required
                />
              </VCol>
              <VCol cols="12" md="3">
                <VTextField
                  v-model="gameDialog.form.originalPrice"
                  label="Original Price ($)"
                  type="number"
                  variant="outlined"
                />
              </VCol>
            </VRow>
            
            <VRow>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="gameDialog.form.developer"
                  label="Developer"
                  variant="outlined"
                  required
                />
              </VCol>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="gameDialog.form.releaseDate"
                  label="Release Date"
                  type="date"
                  variant="outlined"
                  required
                />
              </VCol>
            </VRow>
            
            <VTextarea
              v-model="gameDialog.form.description"
              label="Description"
              variant="outlined"
              rows="4"
              required
              class="mt-4"
            />
            
            <VTextField
              v-model="gameDialog.form.imageUrl"
              label="Image URL"
              variant="outlined"
              class="mt-4"
            />
            
            <div class="d-flex justify-end mt-6">
              <VBtn
                variant="text"
                color="default"
                class="mr-2"
                @click="gameDialog.show = false"
              >
                Cancel
              </VBtn>
              <VBtn
                color="primary"
                type="submit"
                :loading="gameDialog.loading"
              >
                {{ gameDialog.isEdit ? 'Update Game' : 'Add Game' }}
              </VBtn>
            </div>
          </VForm>
        </VCardText>
      </VCard>
    </VDialog>
    
    <!-- Delete Confirmation Dialog -->
    <VDialog v-model="deleteDialog.show" max-width="500">
      <VCard>
        <VCardTitle class="text-h5 pa-4">Delete Game</VCardTitle>
        
        <VCardText>
          Are you sure you want to delete <strong>{{ deleteDialog.game?.title }}</strong>? This action cannot be undone.
        </VCardText>
        
        <VCardActions class="pa-4">
          <VSpacer />
          <VBtn
            variant="text"
            color="default"
            @click="deleteDialog.show = false"
          >
            Cancel
          </VBtn>
          <VBtn
            color="error"
            variant="flat"
            :loading="deleteDialog.loading"
            @click="deleteGame"
          >
            Delete
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// Breadcrumbs
const breadcrumbs = ref([
  {
    title: 'Home',
    disabled: false,
    to: '/'
  },
  {
    title: 'Admin',
    disabled: false,
    to: '/admin'
  },
  {
    title: 'Game Management',
    disabled: true
  }
])

// Table headers
const headers = [
  { title: 'Image', key: 'image', sortable: false },
  { title: 'Title', key: 'title', align: 'start' },
  { title: 'Price', key: 'price' },
  { title: 'Category', key: 'category' },
  { title: 'Developer', key: 'developer' },
  { title: 'Status', key: 'status' },
  { title: 'Release Date', key: 'releaseDate' },
  { title: 'Actions', key: 'actions', sortable: false, align: 'center' }
]

// Pagination
const itemsPerPage = ref(10)

// Filter options
const categoryOptions = ['All', 'Action', 'Adventure', 'RPG', 'Strategy', 'Sports', 'Simulation']
const statusOptions = ['All', 'Active', 'Upcoming', 'On Sale', 'Inactive']
const priceRangeOptions = [
  { title: 'All Prices', value: 'all' },
  { title: 'Under $20', value: 'under-20' },
  { title: '$20 - $40', value: '20-40' },
  { title: '$40 - $60', value: '40-60' },
  { title: 'Over $60', value: 'over-60' }
]

// Filters
const filters = ref({
  search: '',
  category: 'All',
  status: 'All',
  priceRange: 'all'
})

// Games data (in a real app, this would be fetched from an API)
const games = ref([
  {
    id: 1,
    title: 'Elden Ring',
    description: 'THE NEW FANTASY ACTION RPG. Rise, Tarnished, and be guided by grace to brandish the power of the Elden Ring and become an Elden Lord in the Lands Between.',
    price: 59.99,
    originalPrice: 69.99,
    category: 'RPG',
    developer: 'FromSoftware Inc.',
    releaseDate: '2022-02-25',
    status: 'Active',
    image: '/images/placeholder.jpg'
  },
  {
    id: 2,
    title: 'God of War Ragnarök',
    description: 'Embark on a journey through the nine realms as Kratos and Atreus struggle with holding on and letting go.',
    price: 49.99,
    originalPrice: 69.99,
    category: 'Action',
    developer: 'Santa Monica Studio',
    releaseDate: '2022-11-09',
    status: 'On Sale',
    image: '/images/placeholder.jpg'
  },
  {
    id: 3,
    title: 'FIFA 23',
    description: 'Experience the excitement of the biggest tournament in soccer with EA SPORTS FIFA 23 and the men's FIFA World Cup.',
    price: 44.99,
    originalPrice: 59.99,
    category: 'Sports',
    developer: 'EA Vancouver',
    releaseDate: '2022-09-30',
    status: 'Active',
    image: '/images/placeholder.jpg'
  },
  {
    id: 4,
    title: 'Cyberpunk 2077',
    description: 'Cyberpunk 2077 is an open-world, action-adventure RPG set in the megalopolis of Night City',
    price: 39.99,
    originalPrice: 59.99,
    category: 'RPG',
    developer: 'CD Projekt Red',
    releaseDate: '2020-12-10',
    status: 'Active',
    image: '/images/placeholder.jpg'
  },
  {
    id: 5,
    title: 'Starfield',
    description: 'Starfield is the first new universe in 25 years from Bethesda Game Studios, the award-winning creators of The Elder Scrolls V: Skyrim and Fallout 4.',
    price: 69.99,
    originalPrice: null,
    category: 'RPG',
    developer: 'Bethesda Game Studios',
    releaseDate: '2023-09-06',
    status: 'Active',
    image: '/images/placeholder.jpg'
  },
  {
    id: 6,
    title: 'The Last of Us Part II',
    description: 'Five years after their dangerous journey across the post-pandemic United States, Ellie and Joel have settled down in Jackson, Wyoming.',
    price: 29.99,
    originalPrice: 59.99,
    category: 'Action',
    developer: 'Naughty Dog',
    releaseDate: '2020-06-19',
    status: 'Active',
    image: '/images/placeholder.jpg'
  },
  {
    id: 7,
    title: 'Call of Duty: Modern Warfare III',
    description: 'The ultimate Modern Warfare experience continues, featuring intense first-person shooter action.',
    price: 69.99,
    originalPrice: null,
    category: 'Action',
    developer: 'Infinity Ward',
    releaseDate: '2023-11-10',
    status: 'Upcoming',
    image: '/images/placeholder.jpg'
  },
  {
    id: 8,
    title: 'Red Dead Redemption 2',
    description: 'America, 1899. Arthur Morgan and the Van der Linde gang are outlaws on the run.',
    price: 39.99,
    originalPrice: 59.99,
    category: 'Adventure',
    developer: 'Rockstar Games',
    releaseDate: '2018-10-26',
    status: 'Active',
    image: '/images/placeholder.jpg'
  }
])

// Game dialog
const gameDialog = ref({
  show: false,
  isEdit: false,
  loading: false,
  form: {
    id: null,
    title: '',
    description: '',
    price: null,
    originalPrice: null,
    category: '',
    developer: '',
    releaseDate: '',
    status: 'Active',
    imageUrl: ''
  }
})

// Delete dialog
const deleteDialog = ref({
  show: false,
  game: null,
  loading: false
})

// Filtered games
const filteredGames = computed(() => {
  let result = [...games.value]
  
  // Filter by category
  if (filters.value.category !== 'All') {
    result = result.filter(game => game.category === filters.value.category)
  }
  
  // Filter by status
  if (filters.value.status !== 'All') {
    result = result.filter(game => game.status === filters.value.status)
  }
  
  // Filter by price range
  if (filters.value.priceRange !== 'all') {
    switch (filters.value.priceRange) {
      case 'under-20':
        result = result.filter(game => game.price < 20)
        break
      case '20-40':
        result = result.filter(game => game.price >= 20 && game.price <= 40)
        break
      case '40-60':
        result = result.filter(game => game.price > 40 && game.price <= 60)
        break
      case 'over-60':
        result = result.filter(game => game.price > 60)
        break
    }
  }
  
  return result
})

// Methods
const applyFilters = () => {
  // This function would normally send a request to the server
  // In this case, we're just using the computed filteredGames property
}

const resetFilters = () => {
  filters.value = {
    search: '',
    category: 'All',
    status: 'All',
    priceRange: 'all'
  }
}

const openAddGameDialog = () => {
  gameDialog.value.isEdit = false
  gameDialog.value.form = {
    id: null,
    title: '',
    description: '',
    price: null,
    originalPrice: null,
    category: '',
    developer: '',
    releaseDate: new Date().toISOString().slice(0, 10),
    status: 'Active',
    imageUrl: ''
  }
  gameDialog.value.show = true
}

const editGame = (game) => {
  gameDialog.value.isEdit = true
  gameDialog.value.form = { ...game }
  gameDialog.value.show = true
}

const viewGame = (game) => {
  router.push(`/games/${game.id}`)
}

const saveGame = () => {
  gameDialog.value.loading = true
  
  // Simulate API call with a timeout
  setTimeout(() => {
    if (gameDialog.value.isEdit) {
      // Find and update the game
      const index = games.value.findIndex(g => g.id === gameDialog.value.form.id)
      if (index !== -1) {
        games.value[index] = { ...gameDialog.value.form }
      }
    } else {
      // Add new game with a new ID
      const newId = Math.max(...games.value.map(g => g.id)) + 1
      games.value.push({
        ...gameDialog.value.form,
        id: newId,
        image: gameDialog.value.form.imageUrl || '/images/placeholder.jpg'
      })
    }
    
    gameDialog.value.loading = false
    gameDialog.value.show = false
  }, 1000)
}

const confirmDeleteGame = (game) => {
  deleteDialog.value.game = game
  deleteDialog.value.show = true
}

const deleteGame = () => {
  if (!deleteDialog.value.game) return
  
  deleteDialog.value.loading = true
  
  // Simulate API call with a timeout
  setTimeout(() => {
    // Remove game from array
    const index = games.value.findIndex(g => g.id === deleteDialog.value.game.id)
    if (index !== -1) {
      games.value.splice(index, 1)
    }
    
    deleteDialog.value.loading = false
    deleteDialog.value.show = false
    deleteDialog.value.game = null
  }, 1000)
}

// Utility functions
const formatDate = (dateString) => {
  if (!dateString) return ''
  
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const getCategoryColor = (category) => {
  const colors = {
    'Action': 'error',
    'Adventure': 'success',
    'RPG': 'primary',
    'Strategy': 'warning',
    'Sports': 'info',
    'Simulation': 'grey'
  }
  
  return colors[category] || 'default'
}

const getStatusColor = (status) => {
  const colors = {
    'Active': 'success',
    'Upcoming': 'info',
    'On Sale': 'error',
    'Inactive': 'grey'
  }
  
  return colors[status] || 'default'
}
</script> 
