<template>
  <div class="user-wishlist">
    <VContainer class="max-width-container">
      <VBreadcrumbs
        :items="breadcrumbs"
        class="pa-0 mb-4"
      />
      
      <h1 class="text-h4 mb-6">
        My Wishlist
      </h1>

      <!-- Loading State -->
      <VCard
        v-if="loading"
        class="pa-6 d-flex justify-center"
      >
        <VProgressCircular indeterminate />
      </VCard>

      <!-- Empty State -->
      <VCard
        v-else-if="!wishlistGames.length"
        class="pa-6 text-center"
      >
        <VIcon
          icon="bx-heart"
          size="64"
          color="grey"
          class="mb-4"
        />
        <h2 class="text-h5 mb-2">
          Your wishlist is empty
        </h2>
        <p class="text-body-1 mb-4">
          Browse our game store to find games you love!
        </p>
        <VBtn
          color="primary"
          to="/game-store"
          :href="null"
        >
          Browse Games
        </VBtn>
      </VCard>

      <!-- Wishlist Games Grid -->
      <VRow v-else>
        <VCol
          v-for="game in wishlistGames"
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
              class="position-relative"
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
              <!-- Wishlist Button -->
              <WishlistButton
                :game-id="game.id"
                class="position-absolute top-0 end-0 ma-2"
                @update:wishlist="onWishlistUpdate(game.id)"
              />
            </VImg>

            <VCardTitle class="pt-4">
              {{ game.title }}
            </VCardTitle>

            <VCardText>
              <div class="d-flex align-center mb-2">
                <VRating
                  :model-value="game.rating"
                  color="amber"
                  density="compact"
                  readonly
                  size="small"
                />
                <span class="text-body-2 ms-2">({{ game.rating }})</span>
              </div>

              <div class="d-flex align-center">
                <div class="text-h6">
                  ${{ calculateDiscountedPrice(game.price, game.discount).toFixed(2) }}
                </div>
                <div
                  v-if="game.discount > 0"
                  class="ms-2"
                >
                  <span class="text-decoration-line-through text-disabled">${{ game.price }}</span>
                  <VChip
                    color="error"
                    size="small"
                    class="ms-2"
                  >
                    -{{ game.discount }}%
                  </VChip>
                </div>
              </div>

              <div class="text-caption text-disabled mt-1">
                Added {{ formatDate(game.addedAt) }}
              </div>
            </VCardText>

            <VCardActions>
              <VBtn
                block
                color="primary"
                variant="flat"
                :to="'/games/' + game.id"
                :href="null"
              >
                View Details
              </VBtn>
            </VCardActions>
          </VCard>
        </VCol>
      </VRow>
    </VContainer>
  </div>
</template>

<script setup>
import WishlistButton from '@/components/WishlistButton.vue'
import axios from 'axios'
import { computed, onMounted, ref } from 'vue'

const loading = ref(true)
const wishlistGames = ref([])

// Breadcrumbs
const breadcrumbs = computed(() => [
  {
    title: 'Home',
    disabled: false,
    to: '/',
  },
  {
    title: 'My Wishlist',
    disabled: true,
  },
])

const calculateDiscountedPrice = (price, discount) => {
  return price - (price * (discount / 100))
}

const formatDate = dateString => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

const fetchWishlist = async () => {
  try {
    loading.value = true
    
    const response = await axios.get('/api/wishlist')
    
    console.log('Wishlist response:', response.data) // Debug log
    
    if (response.data.success) {
      wishlistGames.value = (response.data.wishlist || []).map(game => ({
        id: game.id,
        title: game.title,
        image: game.image ? `/storage/${game.image}` : '/images/placeholder-game.jpg',
        price: game.price || 0,
        discount: game.discount || 0,
        rating: game.rating || 0,
        addedAt: game.addedAt,
      }))
    }
  } catch (error) {
    console.error('Error fetching wishlist:', error)
    wishlistGames.value = [] // Reset on error
  } finally {
    loading.value = false
  }
}

const onWishlistUpdate = gameId => {
  // Remove the game from the list when it's removed from wishlist
  wishlistGames.value = wishlistGames.value.filter(game => game.id !== gameId)
}

onMounted(() => {
  fetchWishlist()
})
</script>

<style scoped>
.user-wishlist {
  min-height: 400px;
}

.max-width-container {
  max-width: 1920px !important;
  margin: 0 auto;
}
</style> 
