<template>
  <div>
    <VBreadcrumbs
      :items="breadcrumbs"
      class="pa-0 mb-6"
    />
    
    <h1 class="text-h3 font-weight-bold mb-6">
      Shopping Cart
    </h1>
    
    <VRow v-if="cartItems.length > 0">
      <VCol
        cols="12"
        md="8"
      >
        <VCard>
          <VCardText>
            <h2 class="text-h5 font-weight-bold mb-4">
              Cart Items ({{ cartItems.length }})
            </h2>
            
            <VTable>
              <thead>
                <tr>
                  <th style="width: 120px">
                    Product
                  </th>
                  <th>Name</th>
                  <th>Price</th>
                  <th>Discount</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="item in cartItems"
                  :key="item.id"
                >
                  <td>
                    <VImg
                      :src="item.game.g_mainImage ? `/storage/${item.game.g_mainImage}` : '/images/placeholder.jpg'"
                      height="80"
                      width="80"
                      cover
                    />
                  </td>
                  <td>
                    <div class="font-weight-medium">
                      {{ item.game.g_title }}
                    </div>
                  </td>
                  <td>${{ item.c_price }}</td>
                  <td>{{ item.c_discount }}%</td>
                  <td>
                    <VBtn 
                      icon 
                      variant="text" 
                      color="error" 
                      size="small"
                      @click="removeItem(item.id)"
                    >
                      <VIcon icon="bx-trash" />
                    </VBtn>
                  </td>
                </tr>
              </tbody>
            </VTable>
          </VCardText>
          
          <VDivider />
          
          <VCardActions class="pa-4">
            <VBtn 
              variant="text" 
              color="primary" 
              prepend-icon="bx-chevron-left"
              to="/game-store"
            >
              Continue Shopping
            </VBtn>
            <VSpacer />
            <VBtn
              color="error"
              variant="text"
              @click="clearCart"
            >
              Clear Cart
            </VBtn>
          </VCardActions>
        </VCard>
      </VCol>
      
      <VCol
        cols="12"
        md="4"
      >
        <VCard>
          <VCardText>
            <h2 class="text-h5 font-weight-bold mb-4">
              Order Summary
            </h2>
            
            <div class="d-flex justify-space-between mb-2">
              <div>Original Subtotal</div>
              <div>${{ originalSubtotal.toFixed(2) }}</div>
            </div>
            
            <div class="d-flex justify-space-between mb-2">
              <div>Total Discount</div>
              <div>-${{ totalDiscount.toFixed(2) }}</div>
            </div>
            
            <VDivider class="mb-4" />
            
            <div class="d-flex justify-space-between mb-6">
              <div class="text-h6 font-weight-bold">
                Final Total
              </div>
              <div class="text-h6 font-weight-bold">
                ${{ finalTotal.toFixed(2) }}
              </div>
            </div>
            
            <VBtn 
              block 
              color="primary" 
              size="large"
              @click="proceedToCheckout"
            >
              Proceed to Checkout
            </VBtn>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
    
    <VCard
      v-else
      class="pa-6 text-center"
    >
      <VIcon
        icon="bx-cart"
        size="64"
        color="grey-lighten-1"
      />
      <h2 class="text-h5 font-weight-bold mt-4 mb-2">
        Your cart is empty
      </h2>
      <p class="text-body-1 mb-6">
        Looks like you haven't added any games to your cart yet.
      </p>
      <VBtn 
        color="primary" 
        to="/browse-games"
        prepend-icon="bx-shopping-bag"
      >
        Browse Games
      </VBtn>
    </VCard>
  </div>
</template>

<script setup>
import { calculateFinalTotal, calculateOriginalSubtotal, calculateTotalDiscount } from '@/utils/priceCalculations'
import axios from 'axios'
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// Breadcrumbs
const breadcrumbs = ref([
  {
    title: 'Home',
    disabled: false,
    to: '/',
  },
  {
    title: 'Shopping Cart',
    disabled: true,
  },
])

// Cart items - now fetched from API
const cartItems = ref([])
const isLoading = ref(false)
const error = ref(null)

// Cart calculations
const originalSubtotal = computed(() => calculateOriginalSubtotal(cartItems.value))
const totalDiscount = computed(() => calculateTotalDiscount(cartItems.value))
const finalTotal = computed(() => calculateFinalTotal(originalSubtotal.value, totalDiscount.value))

// Fetch cart items
const fetchCartItems = async () => {
  isLoading.value = true
  error.value = null
  
  try {
    const response = await axios.get('/api/cart')

    cartItems.value = response.data.cartItems
  } catch (err) {
    error.value = 'Failed to load cart items'
    console.error(err)
  } finally {
    isLoading.value = false
  }
}

// Remove item from cart
const removeItem = async id => {
  try {
    await axios.delete(`/api/cart/${id}`)
    await fetchCartItems() 
  } catch (err) {
    console.error('Failed to remove item', err)
  }
}

// Clear all cart items
const clearCart = async () => {
  try {
    // Try to use the standard endpoint
    try {
      const response = await axios.delete('/api/cart/clear', {
        withCredentials: true,
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      })
      
      cartItems.value = []
      
      return
    } catch (mainError) {
      console.error('Main clear cart failed, trying test endpoint:', mainError)
      
    }
  } catch (err) {
    console.error('Failed to clear cart:', err.response?.data || err.message || err)
    alert('Failed to clear cart. Please try again.')
  }
}

// Navigate to checkout
const proceedToCheckout = () => {
  if (cartItems.value.length === 0) {
    alert('Your cart is empty.')
    
    return
  }
  
  router.push('/checkout')
}

// Load cart items when component mounts
onMounted(fetchCartItems)
</script> 
