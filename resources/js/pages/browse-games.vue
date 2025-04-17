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

            <!-- Hide Library Games -->
            <VCheckbox
              v-model="hideLibraryGames"
              label="Hide games in library"
              hide-details
              density="compact"
              class="mb-4"
            />

            <div class="text-subtitle-2 font-weight-bold mb-2">
              CATEGORIES
            </div>
            
            <div class="filter-categories">
              <VList density="compact">
                <VListItem
                  v-for="category in categories"
                  :key="category.name"
                  :active="selectedCategory === category.name"
                  @click="selectedCategory = selectedCategory === category.name ? null : category.name"
                  class="px-2"
                >
                  <template #prepend>
                    <VIcon
                      :icon="selectedCategory === category.name ? 'bx-check-circle' : 'bx-circle'"
                      size="small"
                      :color="selectedCategory === category.name ? 'primary' : undefined"
                    />
                  </template>
                  
                  <VListItemTitle class="d-flex justify-space-between align-center">
                    <span class="text-body-2">{{ category.name }}</span>
                    <span class="text-caption text-grey">{{ category.count }}</span>
                  </VListItemTitle>
                </VListItem>
              </VList>
            </div>

            <VDivider class="my-4" />

            <div class="text-subtitle-2 font-weight-bold mb-2">
              LANGUAGES
            </div>
            
            <div class="filter-categories">
              <VList density="compact">
                <VListItem
                  v-for="language in languages"
                  :key="language.name"
                  :active="selectedLanguage === language.name"
                  @click="selectedLanguage = selectedLanguage === language.name ? null : language.name"
                  class="px-2"
                >
                  <template #prepend>
                    <VIcon
                      :icon="selectedLanguage === language.name ? 'bx-check-circle' : 'bx-circle'"
                      size="small"
                      :color="selectedLanguage === language.name ? 'primary' : undefined"
                    />
                  </template>
                  
                  <VListItemTitle class="d-flex justify-space-between align-center">
                    <span class="text-body-2">{{ language.name }}</span>
                    <span class="text-caption text-grey">{{ language.count }}</span>
                  </VListItemTitle>
                </VListItem>
              </VList>
            </div>

            <VDivider class="my-4" />

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
              :to="null"
              class="game-list-item py-3"
              @click="navigateToGame(game.id, $event)"
            >
              <template #prepend>
                <div class="position-relative">
                  <VImg
                    :src="game.image || '/images/placeholder.jpg'"
                    width="324"
                    height="151"
                    cover
                    class="rounded"
                    style="margin-right: 20px;"
                  />
                  <WishlistButton
                    :game-id="game.id"
                    class="position-absolute top-0 end-0 ma-2"
                    @click.stop
                  />
                </div>
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
import WishlistButton from '@/components/WishlistButton.vue'
import axios from 'axios'
import { computed, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'

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
  { title: 'TOP RATED', value: 'top-rated' }
]

const activeTab = ref('all')

// Filter state
const searchQuery = ref('')
const hideLibraryGames = ref(false)
const priceRange = ref(500)
const loading = ref(true)
const games = ref([])
const categories = ref([])
const languages = ref([])
const selectedCategory = ref(null)
const selectedLanguage = ref(null)

// Load categories
const loadCategories = async () => {
  try {
    const response = await axios.get('/api/categories')
    categories.value = response.data.categories
  } catch (error) {
    console.error('Error loading categories:', error)
  }
}

// Load languages
const loadLanguages = async () => {
  try {
    const response = await axios.get('/api/languages')
    languages.value = response.data.languages
  } catch (error) {
    console.error('Error loading languages:', error)
  }
}

// Load games with filters
const loadGames = async () => {
  try {
    loading.value = true
    const response = await axios.get('/api/browseGames', {
      params: {
        tab: activeTab.value,
        search: searchQuery.value,
        maxPrice: priceRange.value,
        category: selectedCategory.value,
        language: selectedLanguage.value
      }
    })
    games.value = response.data.games
  } catch (error) {
    console.error('Error loading games:', error)
  } finally {
    loading.value = false
  }
}

// Methods
const clearFilters = () => {
  searchQuery.value = ''
  hideLibraryGames.value = false
  priceRange.value = 500
  activeTab.value = 'all'
  selectedCategory.value = null
  selectedLanguage.value = null
  loadGames()
}

// Watch for filter changes
watch([activeTab, searchQuery, priceRange, selectedCategory, selectedLanguage], () => {
  loadGames()
}, { deep: true })

// Lifecycle hooks
onMounted(() => {
  loadCategories()
  loadLanguages()
  loadGames()
})

// Computed properties
const filteredGames = computed(() => {
  let result = games.value

  // Filter out library games if enabled
  if (hideLibraryGames.value) {
    result = result.filter(game => !game.inLibrary)
  }

  return result
})

// Add this in the script section after the other methods
const navigateToGame = (gameId, event) => {
  // Check if the click was on the wishlist button or its children
  if (!event.target.closest('.v-btn')) {
    router.push(`/games/${gameId}`)
  }
}

// Add router import at the top with other imports
const router = useRouter()
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
