<template>
  <div>
    <VBreadcrumbs :items="breadcrumbs" class="pa-0 mb-4"></VBreadcrumbs>
    
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
        <VRow>
          <VCol v-for="(image, index) in game.gallery" :key="index" cols="3">
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
              <h1 class="text-h3 font-weight-bold">{{ game.title }}</h1>
              <VSpacer />
              <VBtn icon color="error" variant="text">
                <VIcon icon="bx-heart" />
              </VBtn>
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
              <VChip color="success" class="ml-2" size="small">{{ game.category }}</VChip>
            </div>
            
            <div class="d-flex align-center mb-6">
              <h2 class="text-h4 text-primary font-weight-bold">${{ game.price }}</h2>
              <div v-if="game.originalPrice > game.price" class="ms-4">
                <div class="text-decoration-line-through text-disabled">${{ game.originalPrice }}</div>
                <VChip color="error" size="small" variant="flat">
                  Save ${{ (game.originalPrice - game.price).toFixed(2) }}
                </VChip>
              </div>
            </div>
            
            <div class="mb-6">
              <p class="text-body-1">{{ game.description }}</p>
            </div>
            
            <VDivider class="mb-6" />
            
            <div class="d-flex mb-4">
              <div class="me-6">
                <div class="text-subtitle-2 text-disabled mb-1">Developer</div>
                <div class="text-body-1">{{ game.developer }}</div>
              </div>
              <div class="me-6">
                <div class="text-subtitle-2 text-disabled mb-1">Release Date</div>
                <div class="text-body-1">{{ game.releaseDate }}</div>
              </div>
              <div>
                <div class="text-subtitle-2 text-disabled mb-1">Platform</div>
                <div class="text-body-1">{{ game.platform }}</div>
              </div>
            </div>
            
            <VRow>
              <VCol cols="12" md="6">
                <VBtn
                  block
                  size="large"
                  color="error"
                  prepend-icon="bx-cart-add"
                  @click="addToCart"
                >
                  Add to Cart
                </VBtn>
              </VCol>
              <VCol cols="12" md="6">
                <VBtn
                  block
                  size="large"
                  color="primary"
                  variant="outlined"
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
        <VTab value="specifications">Specifications</VTab>
        <VTab value="reviews">Reviews ({{ game.reviewCount }})</VTab>
      </VTabs>
      
      <VDivider />
      
      <VWindow v-model="activeTab" class="pa-6">
        <!-- Description Tab -->
        <VWindowItem value="description">
          <div class="text-body-1">
            <p>{{ game.fullDescription }}</p>
            <h3 class="text-h6 font-weight-bold mt-4 mb-2">Key Features</h3>
            <VList>
              <VListItem v-for="(feature, index) in game.features" :key="index">
                <template #prepend>
                  <VIcon icon="bx-check" color="success" class="me-2" />
                </template>
                {{ feature }}
              </VListItem>
            </VList>
          </div>
        </VWindowItem>
        
        <!-- Specifications Tab -->
        <VWindowItem value="specifications">
          <h3 class="text-h6 font-weight-bold mb-4">System Requirements</h3>
          
          <h4 class="text-subtitle-1 font-weight-bold mb-2">Minimum Requirements</h4>
          <VTable>
            <tbody>
              <tr v-for="(req, key) in game.minRequirements" :key="key">
                <td class="font-weight-bold" style="width: 200px">{{ key }}</td>
                <td>{{ req }}</td>
              </tr>
            </tbody>
          </VTable>
          
          <h4 class="text-subtitle-1 font-weight-bold mb-2 mt-6">Recommended Requirements</h4>
          <VTable>
            <tbody>
              <tr v-for="(req, key) in game.recRequirements" :key="key">
                <td class="font-weight-bold" style="width: 200px">{{ key }}</td>
                <td>{{ req }}</td>
              </tr>
            </tbody>
          </VTable>
        </VWindowItem>
        
        <!-- Reviews Tab -->
        <VWindowItem value="reviews">
          <div class="d-flex mb-6">
            <div>
              <div class="text-h3 text-center font-weight-bold">{{ game.rating }}</div>
              <VRating
                :model-value="game.rating"
                color="amber"
                readonly
                size="small"
                class="me-2"
              />
              <div class="text-subtitle-2 text-disabled text-center">{{ game.reviewCount }} reviews</div>
            </div>
            
            <VDivider vertical class="mx-6" />
            
            <div class="flex-grow-1">
              <div v-for="i in 5" :key="i" class="d-flex align-center mb-1">
                <div style="width: 30px">{{ 6-i }}</div>
                <VProgressLinear
                  :model-value="game.ratingBreakdown[6-i]"
                  color="amber"
                  class="mx-4"
                  height="8"
                />
                <div style="width: 40px">{{ game.ratingBreakdown[6-i] }}%</div>
              </div>
            </div>
          </div>
          
          <VDivider class="mb-4" />
          
          <div v-if="isLoggedIn" class="mb-6">
            <h3 class="text-h6 font-weight-bold mb-2">Write a Review</h3>
            <div class="d-flex align-center mb-2">
              <div class="me-4">Your Rating:</div>
              <VRating v-model="userRating" color="amber" />
            </div>
            <VTextarea
              v-model="userReview"
              placeholder="Share your experience with this game..."
              rows="4"
              variant="outlined"
              class="mb-2"
            />
            <div class="text-right">
              <VBtn color="primary" @click="submitReview">Submit Review</VBtn>
            </div>
          </div>
          <VAlert
            v-else
            type="info"
            class="mb-6"
          >
            <div class="d-flex align-center">
              <span>Please log in to write a review</span>
              <VSpacer />
              <VBtn color="primary" to="/login">Log In</VBtn>
            </div>
          </VAlert>
          
          <VDivider class="mb-4" />
          
          <!-- Review List -->
          <div v-for="(review, index) in game.reviews" :key="index" class="mb-4">
            <div class="d-flex">
              <VAvatar class="me-4">
                <VImg :src="review.userAvatar || '/images/placeholder.jpg'" />
              </VAvatar>
              <div class="flex-grow-1">
                <div class="d-flex align-center">
                  <h4 class="text-subtitle-1 font-weight-bold mb-0">{{ review.userName }}</h4>
                  <VRating
                    :model-value="review.rating"
                    color="amber"
                    readonly
                    size="x-small"
                    class="mx-2"
                  />
                  <div class="text-caption text-disabled">{{ review.date }}</div>
                </div>
                <p class="text-body-2 mb-0 mt-1">{{ review.comment }}</p>
              </div>
            </div>
          </div>
        </VWindowItem>
      </VWindow>
    </VCard>
    
    <!-- Similar Games -->
    <h2 class="text-h5 font-weight-bold mt-8 mb-4">You Might Also Like</h2>
    <VRow>
      <VCol v-for="relatedGame in similarGames" :key="relatedGame.id" cols="12" sm="6" md="3">
        <VCard>
          <VCardItem class="pa-0">
            <div class="position-relative">
              <VImg :src="relatedGame.image || '/images/placeholder.jpg'" height="180" cover />
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
            <div class="text-subtitle-2 mb-1">{{ relatedGame.title }}</div>
            <div class="d-flex align-center">
              <div class="text-primary font-weight-medium">${{ relatedGame.price }}</div>
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
            <VBtn block color="error" variant="flat" @click="viewGame(relatedGame.id)">
              View Game
            </VBtn>
          </VCardActions>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()
const gameId = computed(() => route.params.id)

// Active tab state
const activeTab = ref('description')

// User review form state
const userRating = ref(0)
const userReview = ref('')
const isLoggedIn = ref(false) // You would check authentication status here

// Sample game data (in a real app, you would fetch this from an API)
const game = ref({
  id: 1,
  title: 'Elden Ring',
  description: 'THE NEW FANTASY ACTION RPG. Rise, Tarnished, and be guided by grace to brandish the power of the Elden Ring and become an Elden Lord in the Lands Between.',
  fullDescription: 'In the Lands Between ruled by Queen Marika the Eternal, the Elden Ring, the source of the Erdtree, has been shattered. Marika\'s offspring, demigods all, claimed the shards of the Elden Ring known as the Great Runes, and the mad taint of their newfound strength triggered a war: The Shattering. A war that meant abandonment by the Greater Will. And now the guidance of grace will be brought to the Tarnished who were spurned by the grace of gold and exiled from the Lands Between. Ye dead who yet live, your grace long lost, follow the path to the Lands Between beyond the foggy sea to stand before the Elden Ring.',
  price: 59.99,
  originalPrice: 69.99,
  rating: 4.8,
  reviewCount: 1423,
  category: 'RPG',
  developer: 'FromSoftware Inc.',
  releaseDate: 'Feb 25, 2022',
  platform: 'PC, PS5, Xbox Series X|S',
  mainImage: '/images/placeholder.jpg',
  gallery: [
    '/images/placeholder.jpg',
    '/images/placeholder.jpg',
    '/images/placeholder.jpg',
    '/images/placeholder.jpg'
  ],
  features: [
    'A vast world where open fields with a variety of situations and huge dungeons with complex and three-dimensional designs are seamlessly connected.',
    'Create your character and define your playstyle by experimenting with a wide variety of weapons, magical abilities, and skills.',
    'A multilayered story told in fragments. An epic drama in which the various thoughts of characters intersect in the Lands Between.',
    'In addition to multiplayer, where you can directly connect with other players, the game supports unique asynchronous online play.'
  ],
  minRequirements: {
    'OS': 'Windows 10',
    'Processor': 'INTEL CORE I5-8400 or AMD RYZEN 3 3300X',
    'Memory': '12 GB RAM',
    'Graphics': 'NVIDIA GEFORCE GTX 1060 3 GB or AMD RADEON RX 580 4 GB',
    'DirectX': 'Version 12',
    'Storage': '60 GB available space',
    'Sound Card': 'Windows Compatible Audio Device'
  },
  recRequirements: {
    'OS': 'Windows 10/11',
    'Processor': 'INTEL CORE I7-8700K or AMD RYZEN 5 3600X',
    'Memory': '16 GB RAM',
    'Graphics': 'NVIDIA GEFORCE GTX 1070 8 GB or AMD RADEON RX VEGA 56 8 GB',
    'DirectX': 'Version 12',
    'Storage': '60 GB available space',
    'Sound Card': 'Windows Compatible Audio Device'
  },
  ratingBreakdown: {
    5: 78,
    4: 15,
    3: 4,
    2: 2,
    1: 1
  },
  reviews: [
    {
      userName: 'John Doe',
      userAvatar: '/images/placeholder.jpg',
      rating: 5,
      date: 'March 2, 2022',
      comment: 'This game is a masterpiece. The open world design is breathtaking, and the combat is challenging but rewarding. I have spent over 100 hours exploring and still finding new areas and secrets.'
    },
    {
      userName: 'Jane Smith',
      userAvatar: '/images/placeholder.jpg',
      rating: 4,
      date: 'April 15, 2022',
      comment: 'Incredible game with amazing atmosphere and world design. The only reason I am not giving it 5 stars is because some of the boss fights feel a bit unfair at times.'
    },
    {
      userName: 'Mike Johnson',
      userAvatar: '/images/placeholder.jpg',
      rating: 5,
      date: 'May 7, 2022',
      comment: 'One of the best games I have ever played. The freedom to explore and tackle challenges in any order makes for a truly unique experience each playthrough.'
    }
  ]
})

// Similar games data
const similarGames = ref([
  {
    id: 2,
    title: 'Dark Souls III',
    price: 39.99,
    rating: 4.7,
    image: '/images/placeholder.jpg'
  },
  {
    id: 3,
    title: 'Sekiro: Shadows Die Twice',
    price: 49.99,
    rating: 4.6,
    image: '/images/placeholder.jpg'
  },
  {
    id: 4,
    title: 'Bloodborne',
    price: 29.99,
    rating: 4.8,
    image: '/images/placeholder.jpg'
  },
  {
    id: 5,
    title: 'Demon\'s Souls Remake',
    price: 59.99,
    rating: 4.5,
    image: '/images/placeholder.jpg'
  }
])

// Breadcrumbs
const breadcrumbs = ref([
  {
    title: 'Home',
    disabled: false,
    to: '/'
  },
  {
    title: 'Games',
    disabled: false,
    to: '/games'
  },
  {
    title: game.value.title,
    disabled: true
  }
])

// Methods
const addToCart = () => {
  // Add to cart logic here
  console.log('Game added to cart:', game.value.title)
}

const buyNow = () => {
  // Buy now logic here
  console.log('Proceeding to checkout for:', game.value.title)
  router.push('/checkout')
}

const submitReview = () => {
  // Submit review logic here
  console.log('Review submitted:', { rating: userRating.value, comment: userReview.value })
  
  // Reset form after submission
  userRating.value = 0
  userReview.value = ''
}

const viewGame = (id) => {
  // Navigate to another game detail page
  router.push(`/games/${id}`)
}
</script>

<style scoped>
.cursor-pointer {
  cursor: pointer;
}
</style> 
