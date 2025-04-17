<template>
  <v-btn
    :icon="icon"
    :color="isWishlisted ? 'red' : undefined"
    :loading="loading"
    @click="toggleWishlist"
    :disabled="!isAuthenticated"
    :title="buttonTitle"
  >
    <v-icon>
      {{ isWishlisted ? 'mdi-heart' : 'mdi-heart-outline' }}
    </v-icon>
  </v-btn>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth';
import axios from 'axios';
import { computed, onMounted, ref } from 'vue';

const props = defineProps({
  gameId: {
    type: Number,
    required: true
  },
  icon: {
    type: Boolean,
    default: true
  }
});

const emit = defineEmits(['removed']);

const authStore = useAuthStore();
const isAuthenticated = computed(() => authStore.isAuthenticated);
const loading = ref(false);
const isWishlisted = ref(false);

const buttonTitle = computed(() => {
  if (!isAuthenticated.value) return 'Please login to add to wishlist';
  return isWishlisted.value ? 'Remove from wishlist' : 'Add to wishlist';
});

const checkWishlistStatus = async () => {
  if (!isAuthenticated.value) return;
  
  try {
    const response = await axios.get(`/api/games/${props.gameId}/wishlist-status`);
    if (response.data.success) {
      isWishlisted.value = response.data.isWishlisted;
    }
  } catch (error) {
    console.error('Error checking wishlist status:', error);
  }
};

const toggleWishlist = async () => {
  if (!isAuthenticated.value) return;
  
  loading.value = true;
  try {
    if (isWishlisted.value) {
      const response = await axios.delete(`/api/wishlist/${props.gameId}`);
      if (response.data.success) {
        isWishlisted.value = false;
        emit('removed');
      }
    } else {
      const response = await axios.post(`/api/wishlist/${props.gameId}`);
      if (response.data.success) {
        isWishlisted.value = true;
      }
    }
  } catch (error) {
    console.error('Error toggling wishlist:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  checkWishlistStatus();
});
</script> 
