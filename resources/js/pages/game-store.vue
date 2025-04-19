<template>
  <div class="game-store-page">
    <!-- Featured Games Carousel -->
    <div class="carousel-container">
      <VCard
        v-if="!loading.featured && featuredGames.length > 0"
        class="carousel-card"
      >
        <VCarousel
          cycle
          height="500"
          hide-delimiter-background
          show-arrows="hover"
          interval="5000"
        >
          <VCarouselItem
            v-for="game in featuredGames"
            :key="game.id"
          >
            <div class="carousel-content">
              <VImg
                :src="`/storage/${game.g_mainImage}`"
                height="500"
                cover
                class="carousel-image"
              >
                <template #placeholder>
                  <div class="d-flex align-center justify-center fill-height">
                    <VProgressCircular
                      indeterminate
                      color="grey-lighten-5"
                    />
                  </div>
                </template>
                
                <div class="carousel-overlay">
                  <VContainer>
                    <div class="carousel-text">
                      <h2 class="text-h3 font-weight-bold mb-4">
                        {{ game.g_title }}
                      </h2>
                      <p class="text-h6 mb-6">
                        {{ game.g_description }}
                      </p>
                      <VBtn
                        color="primary"
                        size="large"
                        :to="`/games/${game.g_id}`"
                      >
                        Learn More
                      </VBtn>
                    </div>
                  </VContainer>
                </div>
              </VImg>
            </div>
          </VCarouselItem>
        </VCarousel>
      </VCard>

      <!-- Loading State -->
      <VCard
        v-else-if="loading.featured"
        height="500"
        class="d-flex align-center justify-center"
      >
        <VProgressCircular
          indeterminate
          color="primary"
        />
      </VCard>

      <!-- Empty State -->
      <VCard
        v-else
        height="500"
        class="d-flex align-center justify-center"
      >
        <div class="text-center">
          <VIcon
            icon="bx-image"
            size="64"
            color="grey-lighten-1"
          />
          <div class="text-h6 mt-2">
            No featured games available
          </div>
        </div>
      </VCard>
    </div>

    <!-- Flash Sales Section -->
    <section class="section-container dark-section">
      <VContainer>
        <h2 class="section-title">
          Flash Sales
        </h2>
        <div
          v-if="!loading.flashSales"
          class="game-grid"
        >
          <VCard
            v-for="game in flashSales"
            :key="game.id"
            :to="'/games/' + game.id"
            class="game-card"
            elevation="0"
          >
            <VImg
              :src="game.cover_image || '/images/placeholder-game.jpg'"
              :alt="game.title"
              class="game-card-image"
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
            </VImg>
            <VCardTitle>{{ game.title }}</VCardTitle>
            <VCardText>
              <div class="price-container">
                <span
                  v-if="game.discount > 0"
                  class="discount-badge"
                >-{{ game.discount }}%</span>
                <div class="text-right">
                  <span
                    v-if="game.discount > 0"
                    class="original-price"
                  >${{ game.price.toFixed(2) }}</span>
                  <span class="final-price">${{ getDiscountedPrice(game.price, game.discount).toFixed(2) }}</span>
                </div>
              </div>
            </VCardText>
          </VCard>
        </div>
        <VSkeletonLoader
          v-else
          type="article, article, article, article"
          class="mt-4"
        />
      </VContainer>
    </section>

    <!-- Browse By Category -->
    <section class="section-container">
      <VContainer>
        <h2 class="section-title">
          Browse by Category
        </h2>
        <div
          v-if="!loading.categories"
          class="categories-grid"
        >
          <VCard
            v-for="category in categories"
            :key="category.name"
            :to="'/browse-games?category=' + category.name"
            class="category-card"
            elevation="0"
          >
            <VIcon
              color="primary"
              size="x-large"
            >
              {{ category.icon }}
            </VIcon>
            <div class="category-title">
              {{ category.name }}
            </div>
          </VCard>
        </div>
        <VSkeletonLoader
          v-else
          type="article, article, article, article, article"
          class="mt-4"
        />
      </VContainer>
    </section>

    <!-- Top Rated Products -->
    <section class="section-container dark-section">
      <VContainer>
        <h2 class="section-title">
          Top Rated
        </h2>
        <div
          v-if="!loading.bestSelling"
          class="game-grid"
        >
          <VCard
            v-for="game in bestSelling"
            :key="game.id"
            :to="'/games/' + game.id"
            class="game-card"
            elevation="0"
          >
            <VImg
              :src="game.cover_image || '/images/placeholder-game.jpg'"
              :alt="game.title"
              class="game-card-image"
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
            </VImg>
            <VCardTitle>{{ game.title }}</VCardTitle>
            <VCardText>
              <div class="price-container">
                <div class="d-flex align-center">
                  <VRating
                    :model-value="game.rating"
                    color="amber"
                    density="compact"
                    size="small"
                    readonly
                  />
                </div>
                <div class="text-right">
                  <span
                    v-if="game.discount > 0"
                    class="original-price"
                  >${{ game.price.toFixed(2) }}</span>
                  <span class="final-price">${{ getDiscountedPrice(game.price, game.discount).toFixed(2) }}</span>
                </div>
              </div>
            </VCardText>
          </VCard>
        </div>
        <VSkeletonLoader
          v-else
          type="article, article, article, article"
          class="mt-4"
        />
      </VContainer>
    </section>

    <!-- Explore More Section -->
    <section class="mb-8">
      <VCard
        color="#151516"
        dark
        class="pa-6"
      >
        <VRow>
          <VCol
            cols="12"
            md="8"
          >
            <h3 class="text-h5 mb-2">
              Explore More Games
            </h3>
            <p class="mb-4">
              Discover a vast collection of games across different genres. From action-packed adventures to mind-bending puzzles, find your next favorite game today.
            </p>
            <div class="d-flex mb-4">
              <VIcon
                icon="bx-joystick"
                class="rounded-circle bg-white text-black p-2 mr-2"
              />
              <VIcon
                icon="bx-game"
                class="rounded-circle bg-white text-black p-2 mr-2"
              />
              <VIcon
                icon="bx-rocket"
                class="rounded-circle bg-white text-black p-2 mr-2"
              />
              <VIcon
                icon="bx-trophy"
                class="rounded-circle bg-white text-black p-2"
              />
            </div>
            <VBtn
              color="primary"
              class="rounded-0"
              to="/browse-games"
            >
              BROWSE ALL GAMES
            </VBtn>
          </VCol>
          <VCol
            cols="12"
            md="4"
            class="d-flex align-center justify-center"
          >
            <VImg
              src="/images/staticImg.PNG"
              width="150"
              class="glitch-effect"
            />
          </VCol>
        </VRow>
      </VCard>
    </section>

    <!-- Explore Products -->
    <section class="section-container">
      <VContainer>
        <h2 class="section-title">
          Explore Our Products
        </h2>
        <div
          v-if="!loading.explore"
          class="game-grid"
        >
          <VCard
            v-for="game in exploreProducts"
            :key="game.id"
            :to="'/games/' + game.id"
            class="game-card"
            elevation="0"
          >
            <VImg
              :src="game.cover_image || '/images/placeholder-game.jpg'"
              :alt="game.title"
              class="game-card-image"
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
            </VImg>
            <VCardTitle>{{ game.title }}</VCardTitle>
            <VCardText>
              <div class="price-container">
                <div class="d-flex align-center">
                  <VRating
                    :model-value="game.rating"
                    color="amber"
                    density="compact"
                    size="small"
                    readonly
                  />
                </div>
                <div class="text-right">
                  <span
                    v-if="game.discount > 0"
                    class="original-price"
                  >${{ game.price.toFixed(2) }}</span>
                  <span class="final-price">${{ getDiscountedPrice(game.price, game.discount).toFixed(2) }}</span>
                </div>
              </div>
            </VCardText>
          </VCard>
        </div>
        <VSkeletonLoader
          v-else
          type="article, article, article, article"
          class="mt-4"
        />
      </VContainer>
    </section>

    <!-- Features Section -->
    <section class="mb-8">
      <VCard
        flat
        class="bg-grey-lighten-5 pa-4"
      >
        <VRow class="justify-center">
          <VCol
            cols="12"
            md="4"
            class="d-flex align-center justify-center text-center"
          >
            <div class="feature-item">
              <VIcon
                icon="bx-download"
                size="x-large"
                class="mb-2"
                color="primary"
              />
              <div>
                <div class="font-weight-bold">
                  INSTANT DIGITAL DELIVERY
                </div>
                <div class="text-caption">
                  Download and play immediately after purchase
                </div>
              </div>
            </div>
          </VCol>
          <VCol
            cols="12"
            md="4"
            class="d-flex align-center justify-center text-center"
          >
            <div class="feature-item">
              <VIcon
                icon="bx-support"
                size="x-large"
                class="mb-2"
                color="primary"
              />
              <div>
                <div class="font-weight-bold">
                  24/7 GAMING SUPPORT
                </div>
                <div class="text-caption">
                  Expert gaming assistance anytime you need
                </div>
              </div>
            </div>
          </VCol>
          <VCol
            cols="12"
            md="4"
            class="d-flex align-center justify-center text-center"
          >
            <div class="feature-item">
              <VIcon
                icon="bx-refresh"
                size="x-large"
                class="mb-2"
                color="primary"
              />
              <div>
                <div class="font-weight-bold">
                  SATISFACTION GUARANTEE
                </div>
                <div class="text-caption">
                  2-hour playtime refund policy
                </div>
              </div>
            </div>
          </VCol>
        </VRow>
      </VCard>
    </section>
  </div>
</template>

<script setup>
import axios from 'axios'
import { onMounted, ref } from 'vue'

const loading = ref({
  featured: true,
  flashSales: true,
  categories: true,
  bestSelling: true,
  explore: true,
})

const featuredGames = ref([])
const flashSales = ref([])
const categories = ref([])
const bestSelling = ref([])
const exploreProducts = ref([])

// Computed function for calculating discounted price
const getDiscountedPrice = (price, discount) => {
  return price - (price * (discount / 100))
}

const fetchFeaturedGames = async () => {
  try {
    loading.value.featured = true

    const response = await axios.get('/api/store/featured')

    featuredGames.value = response.data
    console.log('Featured games:', featuredGames.value)
  } catch (error) {
    console.error('Error fetching featured games:', error)
    featuredGames.value = []
  } finally {
    loading.value.featured = false
  }
}

const fetchFlashSales = async () => {
  try {
    loading.value.flashSales = true

    const response = await axios.get('/api/store/flash-sales')

    flashSales.value = response.data.map(game => ({
      id: game.g_id,
      title: game.g_title,
      price: game.g_price,
      discount: game.discount,
      cover_image: game.g_mainImage ? `/storage/${game.g_mainImage}` : null,
    }))
  } catch (error) {
    console.error('Error fetching flash sales:', error)
  } finally {
    loading.value.flashSales = false
  }
}

const fetchCategories = async () => {
  try {
    loading.value.categories = true

    const response = await axios.get('/api/store/categories')

    categories.value = response.data
  } catch (error) {
    console.error('Error fetching categories:', error)
  } finally {
    loading.value.categories = false
  }
}

const fetchBestSelling = async () => {
  try {
    loading.value.bestSelling = true

    const response = await axios.get('/api/store/best-selling')

    bestSelling.value = response.data.map(game => ({
      id: game.g_id,
      title: game.g_title,
      price: game.g_price,
      rating: game.g_overallRate || 0,
      discount: game.g_discount || 0,
      cover_image: game.g_mainImage ? `/storage/${game.g_mainImage}` : null,
    }))
  } catch (error) {
    console.error('Error fetching top rated games:', error)
  } finally {
    loading.value.bestSelling = false
  }
}

const fetchExploreProducts = async () => {
  try {
    loading.value.explore = true

    const response = await axios.get('/api/store/explore')

    exploreProducts.value = response.data.map(game => ({
      id: game.g_id,
      title: game.g_title,
      price: game.g_price,
      rating: game.g_overallRate || 0,
      discount: game.g_discount || 0,
      cover_image: game.g_mainImage ? `/storage/${game.g_mainImage}` : null,
    }))
  } catch (error) {
    console.error('Error fetching explore products:', error)
  } finally {
    loading.value.explore = false
  }
}

onMounted(() => {
  fetchFeaturedGames()
  fetchFlashSales()
  fetchCategories()
  fetchBestSelling()
  fetchExploreProducts()
})

// Countdown data for Flash Sale
const countdown = ref({
  'Days': '03',
  'Hours': '23',
  'Min': '19',
  'Sec': '56',
})
</script>

<style scoped>
.game-store-page {
  max-width: 1920px;
  margin: 0 auto;
}

.countdown-item {
  text-align: center;
  min-width: 40px;
}

.game-card {
  height: 100%;
  display: flex;
  flex-direction: column;
  background-color: rgb(36, 37, 47) !important;
}

.game-card .v-img {
  aspect-ratio: 16/9 !important;
  width: 100%;
  object-fit: cover;
}

.game-card .v-card-title {
  font-size: 1.1rem;
  line-height: 1.4;
  padding: 12px 16px;
  color: white;
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
  background-color: rgba(30, 31, 40, 0.95);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.game-card .v-card-text {
  padding: 12px 16px;
  background-color: rgba(30, 31, 40, 0.95);
  flex-grow: 1;
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
}

.category-card {
  height: 160px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  transition: transform 0.2s;
  background-color: rgb(36, 37, 47) !important;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.category-card:hover {
  transform: translateY(-4px);
  border-color: rgba(255, 255, 255, 0.2);
}

.category-card .v-icon {
  margin-bottom: 16px;
  font-size: 36px;
}

.category-title {
  color: white;
  font-size: 1.1rem;
  font-weight: 500;
}

.price-container {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 8px;
}

.discount-badge {
  background-color: rgb(0, 200, 83);
  color: white;
  padding: 4px 8px;
  border-radius: 4px;
  font-weight: 600;
}

.original-price {
  color: rgba(255, 255, 255, 0.6);
  text-decoration: line-through;
  font-size: 0.9rem;
  margin-right: 8px;
}

.final-price {
  color: white;
  font-size: 1.2rem;
  font-weight: 600;
}

.section-title {
  color: white;
  font-size: 1.75rem;
  font-weight: 600;
  margin-bottom: 24px;
}

.section-container {
  padding: 48px 0;
  width: 100%;
  max-width: 1920px;
  margin: 0 auto;
}

.dark-section {
  background-color: rgb(30, 31, 40);
}

/* Update the grid layout for game cards */
.game-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 24px;
  width: 100%;
}

.categories-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 24px;
  width: 100%;
}

/* Fix the icon spacing issues */
.rounded-circle.bg-white.text-black.p-2.mr-2 {
  margin-right: 8px;
}

.rounded-circle.bg-white.text-black.p-2 {
  margin-right: 0;
}

/* Media queries for responsive design */
@media (min-width: 1920px) {
  .v-container {
    max-width: 1800px !important;
  }
  
  .game-grid {
    grid-template-columns: repeat(5, 1fr);
  }
}

@media (min-width: 1600px) and (max-width: 1919px) {
  .v-container {
    max-width: 1500px !important;
  }
  
  .game-grid {
    grid-template-columns: repeat(5, 1fr);
  }
}

@media (min-width: 1264px) and (max-width: 1599px) {
  .game-grid {
    grid-template-columns: repeat(4, 1fr);
  }
  
  .categories-grid {
    grid-template-columns: repeat(5, 1fr);
  }
}

@media (max-width: 1264px) {
  .categories-grid {
    grid-template-columns: repeat(4, 1fr);
  }
}

@media (max-width: 960px) {
  .game-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 768px) {
  .game-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 600px) {
  .game-grid {
    grid-template-columns: repeat(1, 1fr);
  }
}

.feature-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 1rem;
  width: 100%;
  max-width: 300px;
}

.feature-item .v-icon {
  margin-bottom: 1rem;
  font-size: 2.5rem;
}

.feature-item .font-weight-bold {
  margin-bottom: 0.5rem;
  font-size: 1.1rem;
}

.feature-item .text-caption {
  color: rgba(0, 0, 0, 0.6);
  line-height: 1.4;
}

.carousel-container {
  margin-bottom: 48px;
}

.carousel-card {
  overflow: hidden;
  border-radius: 8px;
}

.carousel-content {
  position: relative;
  height: 500px;
}

.carousel-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.carousel-overlay {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.4) 50%, rgba(0,0,0,0) 100%);
  padding: 48px 0;
}

.carousel-text {
  color: white;
  max-width: 600px;
  padding: 0 24px;
}

.glitch-effect {
  filter: drop-shadow(0 0 10px rgba(0, 255, 255, 0.5));
  animation: glitch 3s infinite;
}

@keyframes glitch {
  0% {
    filter: drop-shadow(0 0 10px rgba(0, 255, 255, 0.5));
  }
  50% {
    filter: drop-shadow(0 0 15px rgba(255, 0, 255, 0.5));
  }
  100% {
    filter: drop-shadow(0 0 10px rgba(0, 255, 255, 0.5));
  }
}
</style>
