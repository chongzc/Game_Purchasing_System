<template>
  <VBtn
    :icon="true"
    :color="isWishlisted ? 'warning' : 'default'"
    :loading="loading"
    variant="text"
    size="small"
    class="wishlist-btn"
    @click="toggleWishlist"
  >
    <VIcon :color="isWishlisted ? 'warning-darken-2' : 'grey'">
      {{ isWishlisted ? 'bxs-star' : 'bx-star' }}
    </VIcon>

    <VTooltip
      activator="parent"
      location="top"
    >
      {{ buttonTitle }}
    </VTooltip>
  </VBtn>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth'
import axios from 'axios'
import { computed, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'

const props = defineProps({
  gameId: {
    type: Number,
    required: true,
  },
})

const emit = defineEmits(['update:wishlist'])

const router = useRouter()
const authStore = useAuthStore()
const loading = ref(false)
const isWishlisted = ref(false)

const isLoggedIn = computed(() => authStore.user !== null && authStore.isLoggedIn)

const buttonTitle = computed(() => {
  if (!isLoggedIn.value) return 'Please log in to add to wishlist'
  
  return isWishlisted.value ? 'Remove from wishlist' : 'Add to wishlist'
})

const checkWishlistStatus = async () => {
  if (!isLoggedIn.value) return

  try {
    loading.value = true

    const response = await axios.get(`/api/games/${props.gameId}/wishlist-status`)
    if (response.data.success) {
      isWishlisted.value = response.data.isWishlisted
    }
  } catch (error) {
    console.error('Error checking wishlist status:', error)
  } finally {
    loading.value = false
  }
}

const toggleWishlist = async () => {
  if (!isLoggedIn.value) {
    router.push('/login')
    
    return
  }

  try {
    loading.value = true
    
    if (isWishlisted.value) {
      const response = await axios.delete(`/api/wishlist/${props.gameId}`)
      if (response.data.success) {
        isWishlisted.value = false
        emit('update:wishlist', false)
      }
    } else {
      const response = await axios.post(`/api/wishlist/${props.gameId}`)
      if (response.data.success) {
        isWishlisted.value = true
        emit('update:wishlist', true)
      }
    }
  } catch (error) {
    console.error('Error toggling wishlist:', error)
  } finally {
    loading.value = false
  }
}

// Watch for auth state changes
watch(() => isLoggedIn.value, newValue => {
  if (newValue) {
    checkWishlistStatus()
  } else {
    isWishlisted.value = false
  }
})

onMounted(() => {
  if (isLoggedIn.value) {
    checkWishlistStatus()
  }
})
</script>

<style scoped>
.wishlist-btn {
  transition: all 0.3s ease;
}

.wishlist-btn:hover {
  transform: scale(1.1);
}

.wishlist-btn:hover .v-icon {
  filter: brightness(1.2);
}
</style> 
