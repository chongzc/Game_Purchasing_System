<template>
  <div>
    <!-- Header Banner with Game Promotion -->
    <!-- <VCard class="mb-6">
      <VCarousel
        show-arrows="hover"
        hide-delimiter-background
        height="350"
        cycle
      >
        <VCarouselItem>
          <VImg
            src="/images/placeholder.jpg"
            cover
            height="350"
          >
            <div class="d-flex flex-column align-center justify-center fill-height">
              <h2 class="text-h3 text-white font-weight-bold">
                Up to 10% off Voucher
              </h2>
              <VBtn
                color="primary"
                class="mt-4"
              >
                SHOP NOWs
              </VBtn>
            </div>
          </VImg>
        </VCarouselItem>
        <VCarouselItem
          v-for="i in 4"
          :key="i"
        >
          <VImg
            src="/images/placeholder.jpg"
            cover
            height="350"
          >
            <div class="d-flex flex-column align-center justify-center fill-height">
              <h2 class="text-h3 text-white font-weight-bold">
                Featured Games
              </h2>
              <VBtn
                color="primary"
                class="mt-4"
              >
                EXPLORE
              </VBtn>
            </div>
          </VImg>
        </VCarouselItem>
      </VCarousel>
    </VCard> -->

    <!-- Featured Games Carousel -->
    <v-carousel
      v-if="!loading.featured && featuredGames.length > 0"
      cycle
      height="400"
      hide-delimiter-background
      show-arrows="hover"
    >
      <v-carousel-item
        v-for="game in featuredGames"
        :key="game.id"
      >
        <v-img
          :src="game.banner_image || game.cover_image || '/images/placeholder-game.jpg'"
          height="400"
          cover
          :alt="game.title"
        >
          <template v-slot:placeholder>
            <v-row class="fill-height ma-0" align="center" justify="center">
              <v-progress-circular indeterminate color="grey-lighten-5"></v-progress-circular>
            </v-row>
          </template>
          <v-sheet
            class="fill-height"
            gradient="to bottom, rgba(0,0,0,.1), rgba(0,0,0,.5)"
          >
            <v-container class="fill-height">
              <v-row align="end" class="fill-height">
                <v-col cols="12" class="text-white">
                  <h2 class="text-h4 font-weight-bold mb-2">{{ game.title }}</h2>
                  <p class="text-h6">{{ game.short_description }}</p>
                  <v-btn
                    color="primary"
                    class="mt-4"
                    :to="'/games/' + game.id"
                    :href="null"
                  >
                    Learn More
                  </v-btn>
                </v-col>
              </v-row>
            </v-container>
          </v-sheet>
        </v-img>
      </v-carousel-item>
    </v-carousel>

    <!-- Loading State for Carousel -->
    <v-card
      v-else-if="loading.featured"
      height="400"
      class="d-flex align-center justify-center"
    >
      <v-progress-circular indeterminate color="primary"></v-progress-circular>
    </v-card>

    <!-- Empty State for Carousel -->
    <v-card
      v-else
      height="400"
      class="d-flex align-center justify-center"
    >
      <div class="text-center">
        <v-icon icon="bx-image" size="64" color="grey-lighten-1"></v-icon>
        <div class="text-h6 mt-2">No featured games available</div>
      </div>
    </v-card>

    <!-- Flash Sales Section -->
    <section class="section-container dark-section">
      <v-container>
        <h2 class="section-title">Flash Sales</h2>
        <div v-if="!loading.flashSales" class="game-grid">
          <v-card
            v-for="game in flashSales"
            :key="game.id"
            :to="'/games/' + game.id"
            class="game-card"
            elevation="0"
          >
            <v-img
              :src="game.cover_image || '/images/placeholder-game.jpg'"
              :alt="game.title"
              class="game-card-image"
            >
              <template v-slot:placeholder>
                <VRow class="fill-height ma-0" align="center" justify="center">
                  <VProgressCircular indeterminate color="grey-lighten-5"></VProgressCircular>
                </VRow>
              </template>
            </v-img>
            <v-card-title>{{ game.title }}</v-card-title>
            <v-card-text>
              <div class="price-container">
                <span v-if="game.discount > 0" class="discount-badge">-{{ game.discount }}%</span>
                <div class="text-right">
                  <span v-if="game.discount > 0" class="original-price">${{ game.price.toFixed(2) }}</span>
                  <span class="final-price">${{ getDiscountedPrice(game.price, game.discount).toFixed(2) }}</span>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </div>
        <v-skeleton-loader
          v-else
          type="article, article, article, article"
          class="mt-4"
        ></v-skeleton-loader>
      </v-container>
    </section>

    <!-- Browse By Category -->
    <section class="section-container">
      <v-container>
        <h2 class="section-title">Browse by Category</h2>
        <div v-if="!loading.categories" class="categories-grid">
          <v-card
            v-for="category in categories"
            :key="category.name"
            :to="'/browse-games?category=' + category.name"
            class="category-card"
            elevation="0"
          >
            <v-icon color="primary" size="x-large">
              {{ category.icon }}
            </v-icon>
            <div class="category-title">{{ category.name }}</div>
          </v-card>
        </div>
        <v-skeleton-loader
          v-else
          type="article, article, article, article, article"
          class="mt-4"
        ></v-skeleton-loader>
      </v-container>
    </section>

    <!-- Top Rated Products -->
    <section class="section-container dark-section">
      <v-container>
        <h2 class="section-title">Top Rated</h2>
        <div v-if="!loading.bestSelling" class="game-grid">
          <v-card
            v-for="game in bestSelling"
            :key="game.id"
            :to="'/games/' + game.id"
            class="game-card"
            elevation="0"
          >
            <v-img
              :src="game.cover_image || '/images/placeholder-game.jpg'"
              :alt="game.title"
              class="game-card-image"
            >
              <template v-slot:placeholder>
                <VRow class="fill-height ma-0" align="center" justify="center">
                  <VProgressCircular indeterminate color="grey-lighten-5"></VProgressCircular>
                </VRow>
              </template>
            </v-img>
            <v-card-title>{{ game.title }}</v-card-title>
            <v-card-text>
              <div class="price-container">
                <div class="d-flex align-center">
                  <v-rating
                    :model-value="game.rating"
                    color="amber"
                    density="compact"
                  size="small"
                    readonly
                  ></v-rating>
                </div>
                <div class="text-right">
                  <span v-if="game.discount > 0" class="original-price">${{ game.price.toFixed(2) }}</span>
                  <span class="final-price">${{ getDiscountedPrice(game.price, game.discount).toFixed(2) }}</span>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </div>
        <v-skeleton-loader
          v-else
          type="article, article, article, article"
          class="mt-4"
        ></v-skeleton-loader>
      </v-container>
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
              src="/images/placeholder.jpg"
              width="150"
            />
          </VCol>
        </VRow>
      </VCard>
    </section>

    <!-- Explore Products -->
    <section class="section-container">
      <v-container>
        <h2 class="section-title">Explore Our Products</h2>
        <div v-if="!loading.explore" class="game-grid">
          <v-card
            v-for="game in exploreProducts"
            :key="game.id"
            :to="'/games/' + game.id"
            class="game-card"
            elevation="0"
          >
            <v-img
              :src="game.cover_image || '/images/placeholder-game.jpg'"
              :alt="game.title"
              class="game-card-image"
            >
              <template v-slot:placeholder>
                <VRow class="fill-height ma-0" align="center" justify="center">
                  <VProgressCircular indeterminate color="grey-lighten-5"></VProgressCircular>
                </VRow>
              </template>
            </v-img>
            <v-card-title>{{ game.title }}</v-card-title>
            <v-card-text>
              <div class="price-container">
                <div class="d-flex align-center">
                  <v-rating
                    :model-value="game.rating"
                    color="amber"
                    density="compact"
                  size="small"
                    readonly
                  ></v-rating>
                </div>
                <div class="text-right">
                  <span v-if="game.discount > 0" class="original-price">RM{{ game.price.toFixed(2) }}</span>
                  <span class="final-price">RM{{ getDiscountedPrice(game.price, game.discount).toFixed(2) }}</span>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </div>
        <v-skeleton-loader
          v-else
          type="article, article, article, article"
          class="mt-4"
        ></v-skeleton-loader>
      </v-container>
    </section>

    <!-- New Arrivals -->
    <!-- <section class="mb-8">
      <div class="d-flex align-center mb-4">
        <div class="bg-primary py-1 px-2">
          <span class="text-white font-weight-bold">New Arrival</span>
        </div>
      </div>

      <VRow>
        <VCol
          cols="12"
          md="4"
        >
          <VCard
            height="300"
            class="position-relative"
          >
            <VImg
              src="/images/placeholder.jpg"
              cover
              height="300"
            >
              <div class="position-absolute bottom-0 start-0 pa-4">
                <h3 class="text-h5 text-white">
                  PlayStation 5
                </h3>
                <p class="text-white">
                  Next-gen console for advanced gaming
                </p>
                <VBtn
                  color="transparent"
                  variant="outlined"
                  class="text-white"
                >
                  Shop Now
                </VBtn>
              </div>
            </VImg>
          </VCard>
        </VCol>
        <VCol
          cols="12"
          md="8"
        >
          <VRow>
            <VCol
              cols="12"
              md="6"
            >
              <VCard
                height="300"
                class="position-relative"
              >
                <VImg
                  src="/images/placeholder.jpg"
                  cover
                  height="300"
                >
                  <div class="position-absolute bottom-0 start-0 pa-4">
                    <h3 class="text-h5 text-white">
                      Xbox Game Pass
                    </h3>
                    <p class="text-white">
                      Access to hundreds of high-quality games
                    </p>
                    <VBtn
                      color="transparent"
                      variant="outlined"
                      class="text-white"
                    >
                      Subscribe Now
                    </VBtn>
                  </div>
                </VImg>
              </VCard>
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <VRow>
                <VCol cols="12">
                  <VCard
                    height="145"
                    class="position-relative"
                  >
                    <VImg
                      src="/images/placeholder.jpg"
                      cover
                      height="145"
                    >
                      <div class="position-absolute bottom-0 start-0 pa-2">
                        <h3 class="text-subtitle-1 text-white">
                          Gaming Headsets
                        </h3>
                        <VBtn
                          size="small"
                          color="transparent"
                          variant="outlined"
                          class="text-white"
                        >
                          Shop Now
                        </VBtn>
                      </div>
                    </VImg>
                  </VCard>
                </VCol>
                <VCol cols="12">
                  <VCard
                    height="145"
                    class="position-relative"
                  >
                    <VImg
                      src="/images/placeholder.jpg"
                      cover
                      height="145"
                    >
                      <div class="position-absolute bottom-0 start-0 pa-2">
                        <h3 class="text-subtitle-1 text-white">
                          Nintendo Switch
                        </h3>
                        <VBtn
                          size="small"
                          color="transparent"
                          variant="outlined"
                          class="text-white"
                        >
                          Shop Now
                        </VBtn>
                      </div>
                    </VImg>
                  </VCard>
                </VCol>
              </VRow>
            </VCol>
          </VRow>
        </VCol>
      </VRow>
    </section> -->

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
import axios from 'axios';
import { onMounted, ref } from 'vue';

const loading = ref({
  featured: true,
  flashSales: true,
  categories: true,
  bestSelling: true,
  explore: true
});

const featuredGames = ref([]);
const flashSales = ref([]);
const categories = ref([]);
const bestSelling = ref([]);
const exploreProducts = ref([]);

// Computed function for calculating discounted price
const getDiscountedPrice = (price, discount) => {
  return price - (price * (discount / 100));
};

const fetchFeaturedGames = async () => {
  try {
    loading.value.featured = true;
    const response = await axios.get('/api/store/featured');
    console.log('Featured games response:', response.data); // Debug log

    featuredGames.value = response.data.map(game => {
      const bannerImage = game.g_mainImage ? `/storage/${game.g_mainImage}` : null;
      console.log('Processing game:', game.g_title, 'Image path:', bannerImage); // Debug log
      
      return {
        id: game.g_id,
        title: game.g_title,
        short_description: game.g_description,
        banner_image: bannerImage,
        cover_image: bannerImage,
        price: game.g_price,
        discount: game.g_discount
      };
    });

    console.log('Processed featured games:', featuredGames.value); // Debug log
  } catch (error) {
    console.error('Error fetching featured games:', error);
    featuredGames.value = []; // Reset on error
  } finally {
    loading.value.featured = false;
  }
};

const fetchFlashSales = async () => {
  try {
    loading.value.flashSales = true;
    const response = await axios.get('/api/store/flash-sales');
    flashSales.value = response.data.map(game => ({
      id: game.g_id,
      title: game.g_title,
      price: game.g_price,
      discount: game.discount,
      cover_image: game.g_mainImage ? `/storage/${game.g_mainImage}` : null
    }));
  } catch (error) {
    console.error('Error fetching flash sales:', error);
  } finally {
    loading.value.flashSales = false;
  }
};

const fetchCategories = async () => {
  try {
    loading.value.categories = true;
    const response = await axios.get('/api/store/categories');
    categories.value = response.data;
  } catch (error) {
    console.error('Error fetching categories:', error);
  } finally {
    loading.value.categories = false;
  }
};

const fetchBestSelling = async () => {
  try {
    loading.value.bestSelling = true;
    const response = await axios.get('/api/store/best-selling');
    bestSelling.value = response.data.map(game => ({
      id: game.g_id,
      title: game.g_title,
      price: game.g_price,
      rating: game.g_overallRate || 0,
      discount: game.g_discount || 0,
      cover_image: game.g_mainImage ? `/storage/${game.g_mainImage}` : null
    }));
  } catch (error) {
    console.error('Error fetching top rated games:', error);
  } finally {
    loading.value.bestSelling = false;
  }
};

const fetchExploreProducts = async () => {
  try {
    loading.value.explore = true;
    const response = await axios.get('/api/store/explore');
    exploreProducts.value = response.data.map(game => ({
      id: game.g_id,
      title: game.g_title,
      price: game.g_price,
      rating: game.g_overallRate || 0,
      discount: game.g_discount || 0,
      cover_image: game.g_mainImage ? `/storage/${game.g_mainImage}` : null
    }));
  } catch (error) {
    console.error('Error fetching explore products:', error);
  } finally {
    loading.value.explore = false;
  }
};

onMounted(() => {
  fetchFeaturedGames();
  fetchFlashSales();
  fetchCategories();
  fetchBestSelling();
  fetchExploreProducts();
});

// Countdown data for Flash Sale
const countdown = ref({
  'Days': '03',
  'Hours': '23',
  'Min': '19',
  'Sec': '56',
})
</script>

<style scoped>
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
}

.dark-section {
  background-color: rgb(30, 31, 40);
}

/* Update the grid layout for game cards */
.game-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 24px;
  width: 100%;
}

.categories-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 24px;
  width: 100%;
}

@media (max-width: 1264px) {
  .categories-grid {
    grid-template-columns: repeat(4, 1fr);
  }
}

@media (max-width: 960px) {
  .categories-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 600px) {
  .categories-grid {
    grid-template-columns: repeat(2, 1fr);
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
</style>
