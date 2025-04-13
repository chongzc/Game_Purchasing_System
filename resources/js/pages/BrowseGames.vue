<template>
  <div>
    <VBreadcrumbs
      :items="breadcrumbs"
      class="pa-0 mb-6"
    />

    <h1 class="text-h3 font-weight-bold mb-6">
      Browse Games
    </h1>

    <!-- Navigation Tabs -->
    <VCard class="mb-6">
      <VTabs
        v-model="activeTab"
        show-arrows
        grow
        slider-color="primary"
        class="v-tabs-pill"
      >
        <VTab
          v-for="tab in tabs"
          :key="tab.value"
          :value="tab.value"
          color="primary"
        >
          {{ tab.title }}
        </VTab>
      </VTabs>
    </VCard>

    <!-- Filters Sidebar and Game Grid -->
    <VRow>
      <!-- Filters Sidebar -->
      <VCol
        cols="12"
        md="3"
        lg="2"
      >
        <VCard>
          <VCardTitle class="text-subtitle-1 font-weight-bold">
            FILTERS
          </VCardTitle>
          
          <VDivider />
          
          <!-- Search Box -->
          <VCardText>
            <VTextField
              v-model="searchQuery"
              label="Search"
              variant="outlined"
              density="compact"
              prepend-inner-icon="bx-search"
              hide-details
              class="mb-4"
            />

            <!-- Language Filter -->
            <div class="d-flex align-center mb-2">
              <VBtn
                size="x-small"
                icon
                variant="text"
                density="comfortable"
                class="mr-2"
              >
                <VIcon icon="bx-x" />
              </VBtn>
              <span class="text-body-2">English</span>
            </div>
            
            <!-- Hide Ignored Items -->
            <VCheckbox
              v-model="hideIgnoredItems"
              label="Hide ignored items"
              hide-details
              density="compact"
              class="mb-4"
            />

            <div class="text-subtitle-2 font-weight-bold mb-2">
              TOP-LEVEL GENRES
            </div>
            
            <div class="filter-categories">
              <div 
                v-for="(count, genre) in genres" 
                :key="genre"
                class="d-flex justify-space-between align-center mb-1"
              >
                <span class="text-caption">{{ genre }}</span>
                <span class="text-caption text-grey">{{ count }}</span>
              </div>
            </div>

            <VExpansionPanels
              variant="accordion"
              class="mt-4"
            >
              <VExpansionPanel
                v-for="(items, category) in filterCategories"
                :key="category"
              >
                <VExpansionPanelTitle class="text-subtitle-2 font-weight-bold">
                  {{ category }}
                </VExpansionPanelTitle>
                <VExpansionPanelText>
                  <div 
                    v-for="item in items" 
                    :key="item.name"
                    class="d-flex justify-space-between align-center mb-1"
                  >
                    <span class="text-caption">{{ item.name }}</span>
                    <span class="text-caption text-grey">{{ item.count }}</span>
                  </div>
                </VExpansionPanelText>
              </VExpansionPanel>
            </VExpansionPanels>

            <div class="text-subtitle-2 font-weight-bold mt-4 mb-2">
              PRICE
            </div>
            
            <VSlider
              v-model="priceRange"
              :max="500"
              :step="10"
              color="primary"
              thumb-label="always"
              class="mb-4"
            >
              <template #thumb-label="{ modelValue }">
                RM{{ modelValue }}
              </template>
            </VSlider>
          </VCardText>
        </VCard>
      </VCol>
      
      <!-- Game Grid -->
      <VCol
        cols="12"
        md="9"
        lg="10"
      >
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
            Loading games...
          </div>
        </VCard>
        
        <!-- Game List -->
        <VCard v-else>
          <!-- Game Item -->
          <VList lines="three">
            <VListItem
              v-for="game in filteredGames"
              :key="game.id"
              :to="`/games/${game.id}`"
              class="game-list-item py-3"
            >
              <template #prepend>
                <VImg
                  :src="game.image || '/images/placeholder.jpg'"
                  width="324"
                  height="151"
                  cover
                  class="rounded"
                />
              </template>
              
              <VListItemTitle class="text-subtitle-1 font-weight-bold d-flex align-center mb-1">
                {{ game.title }}
                <VChip
                  v-if="game.inLibrary"
                  color="primary"
                  size="x-small"
                  class="ml-2"
                >
                  IN LIBRARY
                </VChip>
              </VListItemTitle>
              
              <VListItemSubtitle>
                <div class="d-flex flex-wrap game-tags">
                  <VChip
                    v-for="tag in game.tags"
                    :key="tag"
                    size="small"
                    variant="outlined"
                    color="primary"
                    class="mr-1 mb-1"
                  >
                    {{ tag }}
                  </VChip>
                </div>
                
                <div class="d-flex align-center mt-1">
                  <VIcon
                    v-if="game.multiPlayer"
                    icon="bx-group"
                    size="small"
                    color="primary"
                    class="mr-1"
                  />
                  <VIcon
                    v-if="game.openWorld"
                    icon="bx-world"
                    size="small"
                    color="primary"
                    class="mr-1"
                  />
                </div>
              </VListItemSubtitle>
              
              <template #append>
                <div>
                  <!-- Reviews -->
                  <div class="text-caption text-primary mb-1">
                    {{ game.reviewStatus }} ({{ game.reviewCount.toLocaleString() }} Reviews)
                  </div>
                  
                  <!-- Price -->
                  <div
                    v-if="game.onSale"
                    class="d-flex align-center"
                  >
                    <VChip
                      color="success"
                      size="x-small"
                      class="mr-2"
                    >
                      -{{ game.discount }}%
                    </VChip>
                    <div class="text-decoration-line-through text-caption mr-2">
                      RM{{ game.originalPrice.toFixed(2) }}
                    </div>
                    <div class="text-primary font-weight-bold">
                      RM{{ game.price.toFixed(2) }}
                    </div>
                  </div>
                  <div
                    v-else-if="game.price === 0"
                    class="font-weight-bold text-success"
                  >
                    Free To Play
                  </div>
                  <div
                    v-else
                    class="font-weight-bold text-primary"
                  >
                    RM{{ game.price.toFixed(2) }}
                  </div>
                </div>
              </template>
            </VListItem>
          </VList>
          
          <!-- Empty State -->
          <div
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
              Try adjusting your filters to find what you're looking for.
            </p>
            <VBtn 
              color="primary" 
              @click="clearFilters"
            >
              Clear Filters
            </VBtn>
          </div>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'

// Breadcrumbs
const breadcrumbs = ref([
  {
    title: 'Home',
    disabled: false,
    to: '/',
  },
  {
    title: 'Browse Games',
    disabled: true,
  },
])

// Tab Navigation
const tabs = [
  { title: 'ALL', value: 'all' },
  { title: 'NEW & TRENDING', value: 'trending' },
  { title: 'TOP SELLERS', value: 'top-sellers' },
  { title: 'TOP RATED', value: 'top-rated' },
  { title: 'POPULAR DISCOUNTED', value: 'discounted' },
  { title: 'POPULAR UPCOMING', value: 'upcoming' },
]

const activeTab = ref('all')

// Filter state
const searchQuery = ref('')
const hideIgnoredItems = ref(false)
const priceRange = ref(500)
const loading = ref(true)

// Mock filter categories data
const genres = {
  'Action': 56532,
  'Casual': 55273,
  'Adventure': 54789,
  'Simulation': 27647,
  'Strategy': 27018,
}

const filterCategories = {
  'GENRES': [
    { name: 'Action', count: 56532 },
    { name: 'RPG', count: 35214 },
    { name: 'Strategy', count: 27018 },
    { name: 'Simulation', count: 27647 },
    { name: 'Sports', count: 15321 },
  ],
  'SUB-GENRES': [
    { name: 'FPS', count: 23500 },
    { name: 'Platformer', count: 15800 },
    { name: 'Puzzle', count: 12450 },
    { name: 'Roguelike', count: 8750 },
    { name: 'Metroidvania', count: 7350 },
  ],
  'VISUALS & VIEWPOINT': [
    { name: '2D', count: 35200 },
    { name: '3D', count: 85400 },
    { name: 'Isometric', count: 12350 },
    { name: 'Top-Down', count: 9800 },
    { name: 'First-Person', count: 32150 },
  ],
}

// Mock games data
const games = ref([
  {
    id: 1,
    title: 'Counter-Strike 2',
    image: '/images/placeholder.jpg',
    tags: ['FPS', 'Shooter', 'Multiplayer', 'Competitive', 'Action'],
    price: 0,
    originalPrice: 0,
    discount: 0,
    onSale: false,
    reviewStatus: 'Very Positive',
    reviewCount: 8704251,
    releaseDate: '2012-08-22',
    multiPlayer: true,
    openWorld: false,
    inLibrary: true,
  },
  {
    id: 2,
    title: 'Schedule I',
    image: '/images/placeholder.jpg',
    tags: ['Simulation', 'Co-op', 'Crime', 'Multiplayer', 'Open World'],
    price: 49.00,
    originalPrice: 49.00,
    discount: 0,
    onSale: false,
    reviewStatus: 'Overwhelmingly Positive',
    reviewCount: 108974,
    releaseDate: '2025-03-25',
    multiPlayer: true,
    openWorld: true,
    inLibrary: false,
  },
  {
    id: 3,
    title: 'Marvel Rivals',
    image: '/images/placeholder.jpg',
    tags: ['Free to Play', 'Multiplayer', 'Hero Shooter', 'Third-Person Shooter', 'Superhero'],
    price: 0,
    originalPrice: 0,
    discount: 0,
    onSale: false,
    reviewStatus: 'Very Positive',
    reviewCount: 263248,
    releaseDate: '2024-12-06',
    multiPlayer: true,
    openWorld: false,
    inLibrary: false,
  },
  {
    id: 4,
    title: 'R.E.P.O.',
    image: '/images/placeholder.jpg',
    tags: ['Horror', 'Online Co-Op', 'Comedy', 'Multiplayer', 'Co-op'],
    price: 26.75,
    originalPrice: 26.75,
    discount: 0,
    onSale: false,
    reviewStatus: 'Overwhelmingly Positive',
    reviewCount: 94431,
    releaseDate: '2025-02-26',
    multiPlayer: true,
    openWorld: false,
    inLibrary: false,
  },
  {
    id: 5,
    title: 'PUBG: BATTLEGROUNDS',
    image: '/images/placeholder.jpg',
    tags: ['Survival', 'Shooter', 'Battle Royale', 'Multiplayer', 'FPS'],
    price: 0,
    originalPrice: 0,
    discount: 0,
    onSale: false,
    reviewStatus: 'Mixed',
    reviewCount: 2532065,
    releaseDate: '2017-12-21',
    multiPlayer: true,
    openWorld: true,
    inLibrary: true,
  },
  {
    id: 6,
    title: 'Red Dead Redemption 2',
    image: '/images/placeholder.jpg',
    tags: ['Open World', 'Story Rich', 'Western', 'Adventure', 'Multiplayer'],
    price: 62.25,
    originalPrice: 249.00,
    discount: 75,
    onSale: true,
    reviewStatus: 'Very Positive',
    reviewCount: 653452,
    releaseDate: '2019-12-06',
    multiPlayer: true,
    openWorld: true,
    inLibrary: false,
  },
  {
    id: 6,
    title: 'Red Dead Redemption 2',
    image: '/images/placeholder.jpg',
    tags: ['Open World', 'Story Rich', 'Western', 'Adventure', 'Multiplayer'],
    price: 62.25,
    originalPrice: 249.00,
    discount: 75,
    onSale: true,
    reviewStatus: 'Very Positive',
    reviewCount: 653452,
    releaseDate: '2019-12-06',
    multiPlayer: true,
    openWorld: true,
    inLibrary: false,
  },
  {
    id: 6,
    title: 'Red Dead Redemption 2',
    image: '/images/placeholder.jpg',
    tags: ['Open World', 'Story Rich', 'Western', 'Adventure', 'Multiplayer'],
    price: 62.25,
    originalPrice: 249.00,
    discount: 75,
    onSale: true,
    reviewStatus: 'Very Positive',
    reviewCount: 653452,
    releaseDate: '2019-12-06',
    multiPlayer: true,
    openWorld: true,
    inLibrary: false,
  },
  {
    id: 6,
    title: 'Red Dead Redemption 2',
    image: '/images/placeholder.jpg',
    tags: ['Open World', 'Story Rich', 'Western', 'Adventure', 'Multiplayer'],
    price: 62.25,
    originalPrice: 249.00,
    discount: 75,
    onSale: true,
    reviewStatus: 'Very Positive',
    reviewCount: 653452,
    releaseDate: '2019-12-06',
    multiPlayer: true,
    openWorld: true,
    inLibrary: false,
  },
  {
    id: 6,
    title: 'Red Dead Redemption 2',
    image: '/images/placeholder.jpg',
    tags: ['Open World', 'Story Rich', 'Western', 'Adventure', 'Multiplayer'],
    price: 62.25,
    originalPrice: 249.00,
    discount: 75,
    onSale: true,
    reviewStatus: 'Very Positive',
    reviewCount: 653452,
    releaseDate: '2019-12-06',
    multiPlayer: true,
    openWorld: true,
    inLibrary: false,
  },
  {
    id: 6,
    title: 'Red Dead Redemption 2',
    image: '/images/placeholder.jpg',
    tags: ['Open World', 'Story Rich', 'Western', 'Adventure', 'Multiplayer'],
    price: 62.25,
    originalPrice: 249.00,
    discount: 75,
    onSale: true,
    reviewStatus: 'Very Positive',
    reviewCount: 653452,
    releaseDate: '2019-12-06',
    multiPlayer: true,
    openWorld: true,
    inLibrary: false,
  },
])

// Computed properties
const filteredGames = computed(() => {
  let result = [...games.value]
  
  // Apply tab filter
  if (activeTab.value !== 'all') {
    switch (activeTab.value) {
    case 'trending':
      result = result.filter(game => new Date(game.releaseDate) > new Date(Date.now() - 90 * 24 * 60 * 60 * 1000))
      break
    case 'top-sellers':
      result = result.sort((a, b) => b.reviewCount - a.reviewCount)
      break
    case 'top-rated':
      result = result.filter(game => 
        game.reviewStatus === 'Very Positive' || 
          game.reviewStatus === 'Overwhelmingly Positive',
      )
      break
    case 'discounted':
      result = result.filter(game => game.onSale)
      break
    case 'upcoming':
      result = result.filter(game => new Date(game.releaseDate) > new Date())
      break
    }
  }
  
  // Apply search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()

    result = result.filter(game => 
      game.title.toLowerCase().includes(query) || 
      game.tags.some(tag => tag.toLowerCase().includes(query)),
    )
  }
  
  // Apply price filter
  result = result.filter(game => game.price <= priceRange.value)
  
  // Apply hide ignored items filter
  if (hideIgnoredItems.value) {
    // This is a mock implementation, you would need to implement ignored items logic
    // result = result.filter(game => !game.ignored)
  }
  
  return result
})

// Methods
const clearFilters = () => {
  searchQuery.value = ''
  hideIgnoredItems.value = false
  priceRange.value = 500
  activeTab.value = 'all'
}

// Lifecycle hooks
onMounted(() => {
  // Simulate loading delay
  setTimeout(() => {
    loading.value = false
  }, 1000)
})
</script>

<style lang="scss" scoped>
.v-tabs-pill {
  .v-tab {
    font-weight: 500;
    text-transform: uppercase;

    &--selected {
      background-color: rgba(var(--v-theme-primary), 0.1);
    }
  }
}

.game-list-item {
  &:hover {
    background-color: rgba(var(--v-theme-on-surface), 0.04);
  }
}

.filter-categories {
  max-height: 200px;
  overflow-y: auto;
}

.game-tags {
  max-width: 600px;
}
</style> 
