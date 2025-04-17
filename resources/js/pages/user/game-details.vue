<script setup>
import WishlistButton from '@/components/WishlistButton.vue'
import { useAuthStore } from '@/stores/auth'
import axios from 'axios'
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const gameId = computed(() => route.params.id)

// Active tab state
const activeTab = ref('description')

// User review form state
const userRating = ref(0)
const userReview = ref('')
const isLoggedIn = computed(() => {
  console.log('Auth Store State:', {
    user: authStore.user,
    isLoggedIn: authStore.isLoggedIn
  })
  return authStore.user !== null && authStore.isLoggedIn
})
const isSubmittingReview = ref(false)
const reviewError = ref('')
const reviewSuccess = ref(false)

// Game data state
const game = ref(null)
const similarGames = ref([])
const loading = ref(true)

// Game status state
const gameStatus = ref('')

// Breadcrumbs
const breadcrumbs = computed(() => [
  {
    title: 'Home',
    disabled: false,
    to: '/',
  },
  {
    title: 'Games',
    disabled: false,
    to: '/games',
  },
  {
    title: game.value?.title || 'Loading...',
    disabled: true,
  },
])

// Computed property for discounted price
const discountedPrice = computed(() => {
  if (!game.value) return 0
  return game.value.price - (game.value.price * (game.value.discount / 100))
})

// Methods
const fetchGameDetails = async () => {
  try {
    loading.value = true
    const response = await axios.get(`/api/games/${gameId.value}`)
    if (response.data.success) {
      game.value = response.data.game
      similarGames.value = response.data.similarGames
      gameStatus.value = response.data.game.libraryStatus || ''
    }
  } catch (error) {
    console.error('Error fetching game details:', error)
  } finally {
    loading.value = false
  }
}

const addToCart = async () => {
  try {
    await axios.post('/api/cart', { gameId: gameId.value })
    // Add your cart update logic here
  } catch (error) {
    console.error('Error adding to cart:', error)
  }
}

const buyNow = () => {
  router.push(`/checkout?gameId=${gameId.value}`)
}

const playGame = async () => {
  try {
    await axios.post(`/api/library/play/${gameId.value}`)
    // Add your game launch logic here
  } catch (error) {
    console.error('Error launching game:', error)
  }
}

const purchaseGame = () => {
  router.push(`/checkout?gameId=${gameId.value}`)
}

const submitReview = async () => {
  try {
    // Reset states
    reviewError.value = ''
    reviewSuccess.value = false
    
    // Validate input
    if (!userRating.value) {
      reviewError.value = 'Please select a rating'
      return
    }
    if (!userReview.value.trim()) {
      reviewError.value = 'Please write a review'
      return
    }
    
    isSubmittingReview.value = true
    
    const response = await axios.post(`/api/games/${gameId.value}/reviews`, {
      rating: userRating.value,
      comment: userReview.value.trim()
    })
    
    if (response.data.success) {
      // Reset form
      userRating.value = 0
      userReview.value = ''
      reviewSuccess.value = true
      
      // Update game's overall rating immediately
      if (game.value && response.data.newOverallRating) {
        game.value.rating = response.data.newOverallRating
      }
      
      // Refresh game details to show new review and updated stats
      await fetchGameDetails()
    } else {
      reviewError.value = response.data.message || 'Failed to submit review'
    }
  } catch (error) {
    console.error('Error submitting review:', error)
    reviewError.value = error.response?.data?.message || 'Failed to submit review'
  } finally {
    isSubmittingReview.value = false
  }
}

const viewGame = (id) => {
  router.push(`/games/${id}`)
}

// Format date helper
const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

// Load data on mount
onMounted(async () => {
  await authStore.checkAuth()
  await fetchGameDetails()
})
</script>

<template>
  <div class="game-details">
    <VBreadcrumbs
      :items="breadcrumbs"
      class="pa-0 mb-4"
    />
    
    <VCard v-if="loading" class="pa-6 d-flex justify-center">
      <VProgressCircular indeterminate />
    </VCard>
    
    <template v-else-if="game">
      <VRow>
        <!-- Game Image and Gallery Section -->
        <VCol cols="12" md="5">
          <VCard class="mb-4">
            <VImg
              :src="game.mainImage || '/images/placeholder.jpg'"
              height="400"
              cover
              class="rounded"
            />
          </VCard>
          
          <!-- Thumbnail Gallery -->
          <VRow v-if="game.gallery?.length">
            <VCol
              v-for="(image, index) in game.gallery"
              :key="index"
              cols="3"
            >
              <VImg
                :src="image || '/images/placeholder.jpg'"
                height="80"
                cover
                class="rounded cursor-pointer"
              />
            </VCol>
          </VRow>
        </VCol>
        
        <!-- Game Details Section -->
        <VCol cols="12" md="7">
          <VRow>
            <VCol cols="12">
              <div class="d-flex align-center mb-2">
                <h1 class="text-h3 font-weight-bold">
                  {{ game.title }}
                </h1>
                <VSpacer />
                <WishlistButton 
                  :game-id="Number(gameId)" 
                  :is-wishlisted="game.isWishlisted"
                />
              </div>
              
              <div class="d-flex align-center mb-4">
                <VRating
                  :model-value="game.rating"
                  color="amber"
                  readonly
                  size="small"
                  class="me-2"
                />
                <span class="text-body-1">{{ game.rating }} ({{ game.reviewCount }} reviews)</span>
                <VSpacer />
                <VChip
                  v-if="game.category"
                  color="success"
                  class="ml-2"
                  size="small"
                >
                  {{ game.category }}
                </VChip>
                <template v-for="(feature, index) in game.features" :key="index">
                  <VChip
                    color="info"
                    class="ml-2"
                    size="small"
                  >
                    {{ feature }}
                  </VChip>
                </template>
              </div>
              
              <div class="d-flex align-center mb-6">
                <h2 class="text-h4 text-primary font-weight-bold">
                  ${{ discountedPrice.toFixed(2) }}
                </h2>
                <div
                  v-if="game.discount > 0"
                  class="ms-4"
                >
                  <div class="text-decoration-line-through text-disabled">
                    ${{ game.originalPrice }}
                  </div>
                  <VChip
                    color="error"
                    size="small"
                  >
                    -{{ game.discount }}%
                  </VChip>
                </div>
              </div>
              
              <div class="mb-6">
                <p class="text-body-1">
                  {{ game.description }}
                </p>
              </div>
              
              <VDivider class="mb-6" />
              
              <div class="d-flex mb-4">
                <div class="me-6">
                  <div class="text-subtitle-2 text-disabled mb-1">
                    Developer
                  </div>
                  <div class="text-body-1">
                    {{ game.developer }}
                  </div>
                </div>
                <div class="me-6">
                  <div class="text-subtitle-2 text-disabled mb-1">
                    Release Date
                  </div>
                  <div class="text-body-1">
                    {{ formatDate(game.releaseDate) }}
                  </div>
                </div>
                <div>
                  <div class="text-subtitle-2 text-disabled mb-1">
                    Platform
                  </div>
                  <div class="text-body-1">
                    {{ game.platform }}
                  </div>
                </div>
              </div>
              
              <!-- Game Status Section -->
              <VCard v-if="game.libraryStatus" class="mb-4">
                <VCardText>
                  <div class="d-flex align-center justify-space-between">
                    <div>
                      <VChip
                        :color="game.libraryStatus === 'owned' ? 'success' : 'warning'"
                        class="me-2"
                      >
                        {{ game.libraryStatus === 'owned' ? 'Owned' : 'Not Owned' }}
                      </VChip>
                    </div>
                    
                    <div class="d-flex align-center">
                      <VBtn
                        :color="game.libraryStatus === 'owned' ? 'success' : 'primary'"
                        @click="game.libraryStatus === 'owned' ? playGame() : purchaseGame()"
                      >
                        {{ game.libraryStatus === 'owned' ? 'Play Now' : 'Purchase' }}
                      </VBtn>
                    </div>
                  </div>
                </VCardText>
              </VCard>
              
              <!-- Purchase Buttons -->
              <VRow v-else class="mt-4">
                <VCol cols="12" md="6">
                  <VBtn
                    block
                    size="large"
                    color="primary"
                    prepend-icon="mdi-cart"
                    @click="addToCart"
                  >
                    Add to Cart
                  </VBtn>
                </VCol>
                <VCol cols="12" md="6">
                  <VBtn
                    block
                    size="large"
                    color="success"
                    @click="buyNow"
                  >
                    Buy Now
                  </VBtn>
                </VCol>
              </VRow>
            </VCol>
          </VRow>
        </VCol>
      </VRow>
      
      <!-- Game Details Tabs -->
      <VCard class="mt-6">
        <VTabs v-model="activeTab">
          <VTab value="description">Description</VTab>
          <VTab value="reviews">Reviews ({{ game.reviewCount }})</VTab>
        </VTabs>
        
        <VDivider />
        
        <VWindow v-model="activeTab">
          <!-- Description Tab -->
          <VWindowItem value="description">
            <div class="text-body-1" style="padding: 0 20px; margin-top: 20px;">
              <p>{{ game.fullDescription }}</p>
              <h3 class="text-h6 font-weight-bold mt-4 mb-2">
                Key Features
              </h3>
              <VList v-if="game.features?.length">
                <VListItem
                  v-for="(feature, index) in game.features"
                  :key="index"
                >
                  <template #prepend>
                    <VIcon
                      icon="bx-check"
                      color="success"
                      class="me-2"
                    />
                  </template>
                  {{ feature }}
                </VListItem>
              </VList>
            </div>
          </VWindowItem>
          
          <!-- Specifications Tab -->
          <!-- <VWindowItem value="specifications">
            <h3 class="text-h6 font-weight-bold mb-4">
              System Requirements
            </h3>
            
            <template v-if="game.minRequirements">
              <h4 class="text-subtitle-1 font-weight-bold mb-2">
                Minimum Requirements
              </h4>
              <VTable>
                <tbody>
                  <tr
                    v-for="(req, key) in game.minRequirements"
                    :key="key"
                  >
                    <td class="font-weight-bold" style="width: 200px">
                      {{ key }}
                    </td>
                    <td>{{ req }}</td>
                  </tr>
                </tbody>
              </VTable>
            </template>
            
            <template v-if="game.recRequirements">
              <h4 class="text-subtitle-1 font-weight-bold mb-2 mt-6">
                Recommended Requirements
              </h4>
              <VTable>
                <tbody>
                  <tr
                    v-for="(req, key) in game.recRequirements"
                    :key="key"
                  >
                    <td class="font-weight-bold" style="width: 200px">
                      {{ key }}
                    </td>
                    <td>{{ req }}</td>
                  </tr>
                </tbody>
              </VTable>
            </template>
          </VWindowItem> -->
          
          <!-- Reviews Tab -->
          <VWindowItem value="reviews">
            <VCard flat>
              <VCardText>
                <div class="d-flex mb-6">
                  <div>
                    <div class="text-h3 text-center font-weight-bold">
                      {{ game.rating }}
                    </div>
                    <VRating
                      :model-value="game.rating"
                      color="amber"
                      readonly
                      size="small"
                      class="me-2"
                    />
                    <div class="text-subtitle-2 text-disabled text-center">
                      {{ game.reviewCount }} reviews
                    </div>
                  </div>
                  
                  <VDivider vertical class="mx-6" />
                  
                  <div class="flex-grow-1">
                    <div
                      v-for="i in 5"
                      :key="i"
                      class="d-flex align-center mb-1"
                    >
                      <div style="width: 30px">{{ 6-i }}</div>
                      <VProgressLinear
                        :model-value="game.ratingBreakdown?.[6-i] || 0"
                        color="amber"
                        class="mx-4"
                        height="8"
                      />
                      <div style="width: 40px">
                        {{ game.ratingBreakdown?.[6-i] || 0 }}%
                      </div>
                    </div>
                  </div>
                </div>
                
                <VDivider class="mb-4" />
                
                <!-- Review Form -->
                <template v-if="isLoggedIn">
                  <div class="mb-6">
                    <h3 class="text-h6 font-weight-bold mb-2">
                      Write a Review
                    </h3>
                    <VAlert
                      v-if="reviewError"
                      type="error"
                      class="mb-2"
                      closable
                      @click:close="reviewError = ''"
                    >
                      {{ reviewError }}
                    </VAlert>
                    <VAlert
                      v-if="reviewSuccess"
                      type="success"
                      class="mb-2"
                      closable
                      @click:close="reviewSuccess = false"
                    >
                      Your review has been submitted successfully!
                    </VAlert>
                    <div class="d-flex align-center mb-2">
                      <div class="me-4">Your Rating:</div>
                      <VRating
                        v-model="userRating"
                        color="amber"
                        hover
                        :rules="[v => !!v || 'Rating is required']"
                      />
                    </div>
                    <VTextarea
                      v-model="userReview"
                      placeholder="Share your experience with this game..."
                      rows="4"
                      variant="outlined"
                      class="mb-2"
                      :rules="[v => !!v.trim() || 'Review text is required']"
                    />
                    <div class="text-right">
                      <VBtn
                        color="primary"
                        :loading="isSubmittingReview"
                        :disabled="isSubmittingReview"
                        @click="submitReview"
                      >
                        Submit Review
                      </VBtn>
                    </div>
                  </div>
                </template>
                <VAlert v-else type="info" class="mb-6">
                  <div class="d-flex align-center">
                    <span>Please log in to write a review</span>
                    <VSpacer />
                    <VBtn color="primary" to="/login" :href="null">
                      Log In
                    </VBtn>
                  </div>
                </VAlert>
                
                <VDivider class="mb-4" />
                
                <!-- Review List -->
                <div v-if="game.reviews?.length">
                  <div
                    v-for="(review, index) in game.reviews"
                    :key="index"
                    class="mb-4"
                  >
                    <div class="d-flex">
                      <VAvatar class="me-4">
                        <VImg :src="review.userAvatar || '/images/placeholder.jpg'" />
                      </VAvatar>
                      <div class="flex-grow-1">
                        <div class="d-flex align-center">
                          <h4 class="text-subtitle-1 font-weight-bold mb-0">
                            {{ review.userName }}
                          </h4>
                          <VRating
                            :model-value="review.rating"
                            color="amber"
                            readonly
                            size="x-small"
                            class="mx-2"
                          />
                          <div class="text-caption text-disabled">
                            {{ formatDate(review.date) }}
                          </div>
                        </div>
                        <p class="text-body-2 mb-0 mt-1">
                          {{ review.comment }}
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
                <VAlert v-else type="info">
                  No reviews yet. Be the first to review this game!
                </VAlert>
              </VCardText>
            </VCard>
          </VWindowItem>
        </VWindow>
      </VCard>
      
      <!-- Similar Games -->
      <template v-if="similarGames.length">
        <h2 class="text-h5 font-weight-bold mt-8 mb-4">
          You Might Also Like
        </h2>
        <VRow>
          <VCol
            v-for="relatedGame in similarGames"
            :key="relatedGame.id"
            cols="12"
            sm="6"
            md="3"
          >
            <VCard>
              <VCardItem class="pa-0">
                <div class="position-relative">
                  <VImg
                    :src="relatedGame.image || '/images/placeholder.jpg'"
                    height="180"
                    cover
                  />
                  <VBtn
                    icon
                    variant="text"
                    color="default"
                    size="small"
                    class="position-absolute top-0 end-0 mt-1 me-1"
                  >
                    <VIcon icon="bx-heart" />
                  </VBtn>
                </div>
              </VCardItem>
              
              <VCardText class="pt-2 pb-1">
                <div class="text-subtitle-2 mb-1">
                  {{ relatedGame.title }}
                </div>
                <div class="d-flex align-center">
                  <div class="text-primary font-weight-medium">
                    ${{ relatedGame.price }}
                  </div>
                  <div class="ms-auto">
                    <VRating
                      :model-value="relatedGame.rating"
                      size="x-small"
                      readonly
                      dense
                    />
                  </div>
                </div>
              </VCardText>
              
              <VCardActions>
                <VBtn
                  block
                  color="primary"
                  variant="flat"
                  @click="viewGame(relatedGame.id)"
                >
                  View Game
                </VBtn>
              </VCardActions>
            </VCard>
          </VCol>
        </VRow>
      </template>

      <!-- Game Actions -->
      <!-- <VCard class="mt-4" v-if="isLoggedIn">
        <VCardText>
          <div class="d-flex align-center justify-space-between">
            <div>
              <VChip
                :color="gameStatus === 'ready' ? 'success' : 
                       gameStatus === 'downloading' ? 'info' : 'warning'"
                class="me-2"
              >
                {{ gameStatus === 'ready' ? 'Ready to Play' :
                   gameStatus === 'downloading' ? 'Downloading...' : 'Not Installed' }}
              </VChip>
              <template v-if="lastPlayed">
                <span class="text-caption">Last played: {{ new Date(lastPlayed).toLocaleDateString() }}</span>
              </template>
              <template v-if="playTime > 0">
                <span class="text-caption ms-2">
                  Play time: {{ Math.floor(playTime) }}h
                </span>
              </template>
            </div> -->
            
            <!-- <div class="d-flex align-center">
              <template v-if="gameStatus === 'downloading'">
                <VProgressLinear
                  v-model="downloadProgress"
                  color="primary"
                  height="8"
                  class="me-4"
                  style="width: 200px"
                >
                  <template v-slot:default="{ value }">
                    <span class="text-caption">{{ Math.ceil(value) }}%</span>
                  </template>
                </VProgressLinear>
              </template>
              
              <VBtn
                :color="gameStatus === 'ready' ? 'success' : 'primary'"
                :disabled="gameStatus === 'downloading'"
                @click="gameStatus === 'ready' ? playGame() : downloadGame()"
              >
                {{ gameStatus === 'ready' ? 'Play Now' : 
                   gameStatus === 'downloading' ? 'Downloading...' : 'Download' }}
              </VBtn>
            </div>
          </div>
        </VCardText>
      </VCard> -->
    </template> 
    
    <VAlert v-else type="error" class="mt-4">
      Game not found
    </VAlert>
  </div>
</template>

<style scoped>
.cursor-pointer {
  cursor: pointer;
}
</style>
