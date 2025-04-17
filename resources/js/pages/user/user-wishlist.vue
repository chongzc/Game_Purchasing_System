<template>
  <v-container>
    <v-row>
      <v-col cols="12">
        <h1 class="text-h4 mb-6">My Wishlist</h1>
      </v-col>
    </v-row>

    <v-row v-if="loading">
      <v-col cols="12" class="text-center">
        <v-progress-circular indeterminate color="primary"></v-progress-circular>
      </v-col>
    </v-row>

    <template v-else>
      <v-row v-if="wishlist.length === 0">
        <v-col cols="12" class="text-center">
          <v-icon size="64" color="grey">mdi-heart-outline</v-icon>
          <p class="text-h6 mt-4">Your wishlist is empty</p>
          <p class="text-body-1 text-grey">Browse our game collection and add games to your wishlist</p>
          <v-btn
            color="primary"
            class="mt-4"
            :to="{ name: 'browse-games' }"
            prepend-icon="mdi-store"
          >
            Browse Games
          </v-btn>
        </v-col>
      </v-row>

      <v-row v-else>
        <v-col
          v-for="game in wishlist"
          :key="game.id"
          cols="12"
          sm="6"
          md="4"
          lg="3"
        >
          <v-card class="h-100">
            <v-img
              :src="game.image_url"
              :alt="game.title"
              height="200"
              cover
            ></v-img>

            <v-card-title class="text-truncate">
              {{ game.title }}
            </v-card-title>

            <v-card-text>
              <div class="d-flex align-center justify-space-between">
                <div class="text-h6">
                  {{ formatPrice(game.price) }}
                </div>
                <div>
                  <WishlistButton :game-id="game.id" @removed="removeFromList(game.id)" />
                </div>
              </div>
            </v-card-text>

            <v-card-actions>
              <v-btn
                block
                color="primary"
                :to="{ name: 'game-details', params: { id: game.id }}"
              >
                View Details
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-col>
      </v-row>
    </template>
  </v-container>
</template>

<script setup>
import WishlistButton from '@/components/WishlistButton.vue';
import axios from 'axios';
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const wishlist = ref([]);
const loading = ref(true);

const fetchWishlist = async () => {
  try {
    const response = await axios.get('/api/wishlist');
    wishlist.value = response.data;
  } catch (error) {
    console.error('Error fetching wishlist:', error);
  } finally {
    loading.value = false;
  }
};

const removeFromList = (gameId) => {
  wishlist.value = wishlist.value.filter(game => game.id !== gameId);
};

const formatPrice = (price) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(price);
};

onMounted(() => {
  fetchWishlist();
});
</script> 
