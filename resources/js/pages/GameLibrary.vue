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
              v-model="filterPlatform"
              label="Platform"
              :items="platformOptions"
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
    
    <!-- Library Items -->
    <VRow>
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
          >
            <div class="game-platform">
              <VChip
                color="primary"
                size="small"
                variant="flat"
              >
                {{ game.platform }}
              </VChip>
            </div>
          </VImg>
          
          <VCardText>
            <div class="font-weight-bold text-subtitle-1 mb-1">
              {{ game.title }}
            </div>
            <div class="text-caption text-disabled mb-2">
              Purchased: {{ game.purchaseDate }}
            </div>
            
            <div class="d-flex align-center justify-space-between">
              <div class="text-caption">
                <span>Last played: {{ game.lastPlayed || 'Never' }}</span>
              </div>
              <div>
                <VChip
                  v-if="game.installed"
                  color="success"
                  size="small"
                  variant="flat"
                >
                  Installed
                </VChip>
                <VChip
                  v-else
                  color="grey"
                  size="small"
                  variant="flat"
                >
                  Not Installed
                </VChip>
              </div>
            </div>
          </VCardText>
          
          <VCardActions>
            <VBtn
              block
              :color="game.installed ? 'success' : 'primary'"
              size="small"
              variant="flat"
              @click="toggleInstallation(game)"
            >
              {{ game.installed ? 'Play Now' : 'Install' }}
            </VBtn>
          </VCardActions>
        </VCard>
      </VCol>
    </VRow>
    
    <!-- Empty State -->
    <VCard
      v-if="filteredGames.length === 0"
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
    
    <!-- Recent Activity -->
    <div
      v-if="recentActivity.length > 0"
      class="mt-8"
    >
      <h2 class="text-h5 font-weight-bold mb-6">
        Recent Activity
      </h2>
      
      <VTimeline
        align="start"
        truncate-line="both"
      >
        <VTimelineItem
          v-for="(activity, index) in recentActivity"
          :key="index"
          :dot-color="getActivityColor(activity.type)"
          size="small"
        >
          <template #opposite>
            <div class="text-caption text-disabled">
              {{ activity.date }}
            </div>
          </template>
          <VCard>
            <VCardText>
              <div class="d-flex align-center">
                <VAvatar
                  size="36"
                  class="mr-3"
                >
                  <VImg :src="activity.gameImage || '/images/placeholder.jpg'" />
                </VAvatar>
                <div>
                  <div class="font-weight-medium">
                    {{ activity.title }}
                  </div>
                  <div class="text-caption">
                    {{ activity.description }}
                  </div>
                </div>
              </div>
            </VCardText>
          </VCard>
        </VTimelineItem>
      </VTimeline>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

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

// Library data (in a real app, this would be fetched from an API)
const library = ref([
  {
    id: 1,
    title: 'Elden Ring',
    platform: 'PC',
    purchaseDate: 'Apr 10, 2023',
    lastPlayed: 'Yesterday',
    installed: true,
    image: '/images/placeholder.jpg',
  },
  {
    id: 2,
    title: 'God of War Ragnarök',
    platform: 'PS5',
    purchaseDate: 'Mar 15, 2023',
    lastPlayed: '2 weeks ago',
    installed: true,
    image: '/images/placeholder.jpg',
  },
  {
    id: 3,
    title: 'FIFA 23',
    platform: 'PC',
    purchaseDate: 'Jan 23, 2023',
    lastPlayed: '3 days ago',
    installed: true,
    image: '/images/placeholder.jpg',
  },
  {
    id: 4,
    title: 'Cyberpunk 2077',
    platform: 'PC',
    purchaseDate: 'Dec 10, 2022',
    lastPlayed: '1 month ago',
    installed: false,
    image: '/images/placeholder.jpg',
  },
  {
    id: 5,
    title: 'The Witcher 3: Wild Hunt',
    platform: 'PS5',
    purchaseDate: 'Nov 5, 2022',
    lastPlayed: null,
    installed: false,
    image: '/images/placeholder.jpg',
  },
  {
    id: 6,
    title: 'Hogwarts Legacy',
    platform: 'Xbox',
    purchaseDate: 'Feb 28, 2023',
    lastPlayed: '1 week ago',
    installed: true,
    image: '/images/placeholder.jpg',
  },
])

// Recent activity
const recentActivity = ref([
  {
    type: 'achievement',
    title: 'Elden Ring',
    description: 'Unlocked achievement: "Lord of the Frenzied Flame"',
    date: 'Yesterday, 8:24 PM',
    gameImage: '/images/placeholder.jpg',
  },
  {
    type: 'installation',
    title: 'Hogwarts Legacy',
    description: 'Installation completed',
    date: 'Apr 8, 2023, 1:15 PM',
    gameImage: '/images/placeholder.jpg',
  },
  {
    type: 'purchase',
    title: 'Red Dead Redemption 2',
    description: 'Added to your library',
    date: 'Apr 5, 2023, 3:42 PM',
    gameImage: '/images/placeholder.jpg',
  },
  {
    type: 'playtime',
    title: 'FIFA 23',
    description: 'Played for 2 hours',
    date: 'Apr 3, 2023, 9:50 PM',
    gameImage: '/images/placeholder.jpg',
  },
])

// Search and filtering
const searchQuery = ref('')
const filterPlatform = ref('All')
const sortOption = ref('recent')

// Filter options
const platformOptions = ['All', 'PC', 'PS5', 'Xbox']

const sortOptions = [
  { title: 'Recently Purchased', value: 'recent' },
  { title: 'Title (A-Z)', value: 'title-asc' },
  { title: 'Title (Z-A)', value: 'title-desc' },
  { title: 'Recently Played', value: 'played' },
]

// Filter and sort library
const filteredGames = computed(() => {
  let result = [...library.value]
  
  // Filter by search query
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()

    result = result.filter(game => game.title.toLowerCase().includes(query))
  }
  
  // Filter by platform
  if (filterPlatform.value !== 'All') {
    result = result.filter(game => game.platform === filterPlatform.value)
  }
  
  // Sort
  result.sort((a, b) => {
    switch (sortOption.value) {
    case 'title-asc':
      return a.title.localeCompare(b.title)
    case 'title-desc':
      return b.title.localeCompare(a.title)
    case 'played':
      // Sort by last played (null values at the end)
      if (!a.lastPlayed) return 1
      if (!b.lastPlayed) return -1
      
      return new Date(b.lastPlayed) - new Date(a.lastPlayed)
    case 'recent':
    default:
      // Sort by purchase date (newest first)
      return new Date(b.purchaseDate) - new Date(a.purchaseDate)
    }
  })
  
  return result
})

// Methods
const toggleInstallation = game => {
  if (game.installed) {
    // Launch game
    alert(`Launching ${game.title}...`)
  } else {
    // Install game
    alert(`Installing ${game.title}...`)
    game.installed = true
  }
}

const clearFilters = () => {
  searchQuery.value = ''
  filterPlatform.value = 'All'
  sortOption.value = 'recent'
}

// Get activity icon color based on type
const getActivityColor = type => {
  switch (type) {
  case 'achievement':
    return 'amber'
  case 'installation':
    return 'success'
  case 'purchase':
    return 'primary'
  case 'playtime':
    return 'info'
  default:
    return 'grey'
  }
}
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
