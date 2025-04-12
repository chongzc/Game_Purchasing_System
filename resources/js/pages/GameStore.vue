<template>
  <div class="game-store">
    <!-- Header Banner with Game Promotion -->
    <VCard class="mb-6">
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
                SHOP NOW
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
    </VCard>

    <!-- Flash Sales Section -->
    <section class="mb-8">
      <div class="d-flex align-center mb-4">
        <div class="bg-primary py-1 px-2 mr-4">
          <span class="text-white font-weight-bold">Flash Sales</span>
        </div>
        <div class="ml-auto d-flex align-center">
          <div
            v-for="(value, key) in countdown"
            :key="key"
            class="countdown-item mx-2"
          >
            <div class="text-center">
              <span class="text-h5 font-weight-bold">{{ value }}</span>
              <div class="text-caption">
                {{ key }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <VRow>
        <VCol
          v-for="item in flashSales"
          :key="item.id"
          cols="12"
          sm="6"
          md="3"
        >
          <VCard>
            <VCardItem class="pa-0">
              <div class="position-relative">
                <VImg
                  src="/images/placeholder.jpg"
                  height="180"
                  cover
                />
                <div class="position-absolute bottom-0 start-0 bg-primary px-1">
                  <span class="text-white text-caption">-{{ item.discount }}%</span>
                </div>
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

            <VCardText class="pt-2 pb-0">
              <div class="text-subtitle-2 mb-1">
                <RouterLink
                  :to="`/games/${item.id}`"
                  class="text-decoration-none"
                >
                  {{ item.name }}
                </RouterLink>
              </div>
              <div class="d-flex align-center">
                <div class="text-decoration-line-through text-disabled me-2">
                  ${{ item.originalPrice }}
                </div>
                <div class="text-primary font-weight-medium">
                  ${{ item.price }}
                </div>
                <div class="ms-auto">
                  <VRating
                    :model-value="item.rating"
                    color
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
                :to="`/games/${item.id}`"
              >
                VIEW DETAILS
              </VBtn>
            </VCardActions>
          </VCard>
        </VCol>
      </VRow>
    </section>

    <!-- Browse By Category -->
    <section class="mb-8">
      <div class="d-flex align-center mb-4">
        <div class="bg-primary py-1 px-2">
          <span class="text-white font-weight-bold">Browse By Category</span>
        </div>
        <div class="ml-auto">
          <VBtn
            size="small"
            icon
            variant="text"
          >
            <VIcon icon="bx-chevron-left" />
          </VBtn>
          <VBtn
            size="small"
            icon
            variant="text"
          >
            <VIcon icon="bx-chevron-right" />
          </VBtn>
        </div>
      </div>

      <VRow>
        <VCol
          v-for="category in categories"
          :key="category.id"
          cols="4"
          sm="4"
          md="2"
        >
          <VCard
            flat
            class="text-center pa-2"
          >
            <VIcon
              :icon="category.icon"
              size="36"
              color="primary"
            />
            <div class="mt-2">
              {{ category.name }}
            </div>
          </VCard>
        </VCol>
      </VRow>
    </section>

    <!-- Best Selling Products -->
    <section class="mb-8">
      <div class="d-flex align-center mb-4">
        <div class="bg-primary py-1 px-2">
          <span class="text-white font-weight-bold">Best Selling Products</span>
        </div>
        <div class="ml-auto">
          <VBtn
            color="primary"
            variant="outlined"
            size="small"
          >
            VIEW ALL
          </VBtn>
        </div>
      </div>

      <VRow>
        <VCol
          v-for="product in bestSelling"
          :key="product.id"
          cols="12"
          sm="6"
          md="3"
        >
          <VCard>
            <VCardItem class="pa-0">
              <div class="position-relative">
                <VImg
                  src="/images/placeholder.jpg"
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
                <RouterLink
                  :to="`/games/${product.id}`"
                  class="text-decoration-none"
                >
                  {{ product.name }}
                </RouterLink>
              </div>
              <div class="d-flex align-center">
                <div class="text-decoration-line-through text-disabled me-2">
                  ${{ product.originalPrice }}
                </div>
                <div class="text-primary font-weight-medium">
                  ${{ product.price }}
                </div>
                <div class="ms-auto">
                  <VRating
                    :model-value="product.rating"
                    size="x-small"
                    readonly
                    dense
                  />
                </div>
              </div>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>
    </section>

    <!-- Featured Section - Music Experience -->
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
              Enhance Your Gaming Experience
            </h3>
            <div class="d-flex mb-4">
              <VIcon
                icon="bx-headphone"
                class="rounded-circle bg-white text-black p-2 mr-2"
              />
              <VIcon
                icon="bx-joystick"
                class="rounded-circle bg-white text-black p-2 mr-2"
              />
              <VIcon
                icon="bx-desktop"
                class="rounded-circle bg-white text-black p-2 mr-2"
              />
              <VIcon
                icon="bx-mouse"
                class="rounded-circle bg-white text-black p-2"
              />
            </div>
            <VBtn
              color="primary"
              class="rounded-0"
            >
              SHOP GAMING GEAR
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

    <!-- Explore Our Products -->
    <section class="mb-8">
      <div class="d-flex align-center mb-4">
        <div class="bg-primary py-1 px-2">
          <span class="text-white font-weight-bold">Explore Our Products</span>
        </div>
        <div class="ml-auto">
          <VBtn
            size="small"
            icon
            variant="text"
          >
            <VIcon icon="bx-chevron-left" />
          </VBtn>
          <VBtn
            size="small"
            icon
            variant="text"
          >
            <VIcon icon="bx-chevron-right" />
          </VBtn>
        </div>
      </div>

      <VRow>
        <VCol
          v-for="product in exploreProducts"
          :key="product.id"
          cols="12"
          sm="6"
          md="3"
        >
          <VCard>
            <VCardItem class="pa-0">
              <div class="position-relative">
                <VImg
                  src="/images/placeholder.jpg"
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
                <RouterLink
                  :to="`/games/${product.id}`"
                  class="text-decoration-none"
                >
                  {{ product.name }}
                </RouterLink>
              </div>
              <div class="d-flex align-center">
                <div class="ms-auto">
                  <VRating
                    :model-value="product.rating"
                    size="x-small"
                    readonly
                    dense
                  />
                </div>
              </div>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>

      <div class="text-center">
        <VBtn
          color="primary"
          variant="flat"
          class="mt-4"
        >
          SHOW ALL PRODUCTS
        </VBtn>
      </div>
    </section>

    <!-- New Arrivals -->
    <section class="mb-8">
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
    </section>

    <!-- Features Section -->
    <section class="mb-8">
      <VCard
        flat
        class="bg-grey-lighten-5 pa-4"
      >
        <VRow>
          <VCol
            cols="12"
            md="4"
            class="d-flex align-center"
          >
            <VIcon
              icon="bx-package"
              size="x-large"
              class="mr-2"
            />
            <div>
              <div class="font-weight-bold">
                FREE AND FAST DELIVERY
              </div>
              <div class="text-caption">
                Free delivery for all orders over $100
              </div>
            </div>
          </VCol>
          <VCol
            cols="12"
            md="4"
            class="d-flex align-center"
          >
            <VIcon
              icon="bx-headphone"
              size="x-large"
              class="mr-2"
            />
            <div>
              <div class="font-weight-bold">
                24/7 CUSTOMER SERVICE
              </div>
              <div class="text-caption">
                Friendly 24/7 customer support
              </div>
            </div>
          </VCol>
          <VCol
            cols="12"
            md="4"
            class="d-flex align-center"
          >
            <VIcon
              icon="bx-shield-quarter"
              size="x-large"
              class="mr-2"
            />
            <div>
              <div class="font-weight-bold">
                MONEY BACK GUARANTEE
              </div>
              <div class="text-caption">
                We return money within 30 days
              </div>
            </div>
          </VCol>
        </VRow>
      </VCard>
    </section>
  </div>
</template>

<script setup>
import { ref } from 'vue'

// Countdown data for Flash Sale
const countdown = ref({
  'Days': '03',
  'Hours': '23',
  'Min': '19',
  'Sec': '56',
})

// Categories
const categories = ref([
  { id: 1, name: 'Action', icon: 'bx-run' },
  { id: 2, name: 'Adventure', icon: 'bx-map' },
  { id: 3, name: 'RPG', icon: 'bx-diamond' },
  { id: 4, name: 'Strategy', icon: 'bx-chess' },
  { id: 5, name: 'Sports', icon: 'bx-baseball' },
  { id: 6, name: 'Simulation', icon: 'bx-city' },
])

// Best Selling Products
const bestSelling = ref([
  { 
    id: 1, 
    name: 'Cyberpunk 2077', 
    originalPrice: '59.99', 
    price: '39.99', 
    rating: 4.5,
    image: 'placeholder.jpg',
  },
  { 
    id: 2, 
    name: 'Elden Ring', 
    originalPrice: '69.99', 
    price: '59.99', 
    rating: 5,
    image: 'placeholder.jpg',
  },
  { 
    id: 3, 
    name: 'FIFA 23', 
    originalPrice: '59.99', 
    price: '44.99', 
    rating: 4,
    image: 'placeholder.jpg',
  },
  { 
    id: 4, 
    name: 'Minecraft', 
    originalPrice: '29.99', 
    price: '24.99', 
    rating: 5,
    image: 'placeholder.jpg',
  },
])

// Explore Products
const exploreProducts = ref([
  { id: 1, name: 'Call of Duty: Modern Warfare III', rating: 4.5, image: 'placeholder.jpg' },
  { id: 2, name: 'The Legend of Zelda: Tears of the Kingdom', rating: 5, image: 'placeholder.jpg' },
  { id: 3, name: 'Starfield', rating: 4, image: 'placeholder.jpg' },
  { id: 4, name: 'Baldur\'s Gate 3', rating: 5, image: 'placeholder.jpg' },
  { id: 5, name: 'Hogwarts Legacy', rating: 4.5, image: 'placeholder.jpg' },
  { id: 6, name: 'Red Dead Redemption 2', rating: 5, image: 'placeholder.jpg' },
  { id: 7, name: 'Diablo IV', rating: 4, image: 'placeholder.jpg' },
  { id: 8, name: 'The Witcher 3: Wild Hunt', rating: 5, image: 'placeholder.jpg' },
])

// Flash Sales data
const flashSales = ref([
  { 
    id: 1, 
    name: 'God of War Ragnarök', 
    originalPrice: '69.99', 
    price: '49.99', 
    discount: 29, 
    rating: 5,
    image: 'placeholder.jpg',
  },
  { 
    id: 2, 
    name: 'Assassin\'s Creed Valhalla', 
    originalPrice: '59.99', 
    price: '29.99', 
    discount: 50, 
    rating: 4,
    image: 'placeholder.jpg',
  },
  { 
    id: 3, 
    name: 'Resident Evil 4 Remake', 
    originalPrice: '59.99', 
    price: '39.99', 
    discount: 33, 
    rating: 5,
    image: 'placeholder.jpg',
  },
  { 
    id: 4, 
    name: 'Spider-Man 2', 
    originalPrice: '69.99', 
    price: '49.99', 
    discount: 29, 
    rating: 4.5,
    image: 'placeholder.jpg',
  },
])
</script>

<style scoped>
.countdown-item {
  text-align: center;
  min-width: 40px;
}

.game-store {
  max-width: 1920px;
  margin: 0 auto;
}
</style>
