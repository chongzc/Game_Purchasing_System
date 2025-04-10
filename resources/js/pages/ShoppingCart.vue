<template>
  <div>
    <VBreadcrumbs :items="breadcrumbs" class="pa-0 mb-6"></VBreadcrumbs>
    
    <h1 class="text-h3 font-weight-bold mb-6">Shopping Cart</h1>
    
    <VRow v-if="cartItems.length > 0">
      <VCol cols="12" md="8">
        <VCard>
          <VCardText>
            <h2 class="text-h5 font-weight-bold mb-4">Cart Items ({{ cartItems.length }})</h2>
            
            <VTable>
              <thead>
                <tr>
                  <th style="width: 120px">Product</th>
                  <th>Name</th>
                  <th>Price</th>
                  <th>Quantity</th>
                  <th>Total</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in cartItems" :key="index">
                  <td>
                    <VImg :src="item.image || '/images/placeholder.jpg'" height="80" width="80" cover />
                  </td>
                  <td>
                    <div class="font-weight-medium">{{ item.name }}</div>
                    <div v-if="item.platform" class="text-caption text-disabled">{{ item.platform }}</div>
                  </td>
                  <td>${{ item.price.toFixed(2) }}</td>
                  <td>
                    <div class="d-flex align-center">
                      <VBtn 
                        size="small" 
                        icon 
                        variant="text" 
                        :disabled="item.quantity <= 1"
                        @click="updateQuantity(index, -1)"
                      >
                        <VIcon icon="bx-minus" />
                      </VBtn>
                      <div class="mx-2">{{ item.quantity }}</div>
                      <VBtn 
                        size="small" 
                        icon 
                        variant="text" 
                        @click="updateQuantity(index, 1)"
                      >
                        <VIcon icon="bx-plus" />
                      </VBtn>
                    </div>
                  </td>
                  <td>${{ (item.price * item.quantity).toFixed(2) }}</td>
                  <td>
                    <VBtn 
                      icon 
                      variant="text" 
                      color="error" 
                      size="small"
                      @click="removeItem(index)"
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
      
      <VCol cols="12" md="4">
        <VCard>
          <VCardText>
            <h2 class="text-h5 font-weight-bold mb-4">Order Summary</h2>
            
            <div class="d-flex justify-space-between mb-2">
              <div>Subtotal</div>
              <div>${{ cartTotal.toFixed(2) }}</div>
            </div>
            
            <div class="d-flex justify-space-between mb-2">
              <div>Discount</div>
              <div>-${{ discount.toFixed(2) }}</div>
            </div>
            
            <div class="d-flex justify-space-between mb-4">
              <div>Tax</div>
              <div>${{ tax.toFixed(2) }}</div>
            </div>
            
            <VDivider class="mb-4" />
            
            <div class="d-flex justify-space-between mb-6">
              <div class="text-h6 font-weight-bold">Total</div>
              <div class="text-h6 font-weight-bold">${{ orderTotal.toFixed(2) }}</div>
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
        
        <VCard class="mt-4">
          <VCardText>
            <h3 class="text-h6 font-weight-bold mb-2">Have a Coupon?</h3>
            <div class="d-flex">
              <VTextField
                v-model="couponCode"
                placeholder="Enter coupon code"
                variant="outlined"
                density="compact"
                hide-details
                class="mr-2"
              />
              <VBtn 
                color="primary"
                @click="applyCoupon"
              >
                Apply
              </VBtn>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
    
    <VCard v-else class="pa-6 text-center">
      <VIcon icon="bx-cart" size="64" color="grey-lighten-1" />
      <h2 class="text-h5 font-weight-bold mt-4 mb-2">Your cart is empty</h2>
      <p class="text-body-1 mb-6">Looks like you haven't added any games to your cart yet.</p>
      <VBtn 
        color="primary" 
        to="/game-store"
        prepend-icon="bx-shopping-bag"
      >
        Browse Games
      </VBtn>
    </VCard>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// Breadcrumbs
const breadcrumbs = ref([
  {
    title: 'Home',
    disabled: false,
    to: '/'
  },
  {
    title: 'Shopping Cart',
    disabled: true
  }
])

// Cart items - in a real app, this would be fetched from a store or API
const cartItems = ref([
  {
    id: 1,
    name: 'Elden Ring',
    price: 59.99,
    originalPrice: 69.99,
    quantity: 1,
    platform: 'PC Digital Download',
    image: '/images/placeholder.jpg'
  },
  {
    id: 3,
    name: 'FIFA 23',
    price: 44.99,
    originalPrice: 59.99,
    quantity: 1,
    platform: 'PS5 Digital Download',
    image: '/images/placeholder.jpg'
  }
])

// Coupon code
const couponCode = ref('')

// Cart calculations
const cartTotal = computed(() => {
  return cartItems.value.reduce((total, item) => {
    return total + (item.price * item.quantity)
  }, 0)
})

const discount = ref(10) // Example discount amount
const tax = computed(() => cartTotal.value * 0.08) // Example 8% tax
const orderTotal = computed(() => cartTotal.value - discount.value + tax.value)

// Methods
const updateQuantity = (index, change) => {
  const newQuantity = cartItems.value[index].quantity + change
  if (newQuantity > 0) {
    cartItems.value[index].quantity = newQuantity
  }
}

const removeItem = (index) => {
  cartItems.value.splice(index, 1)
}

const clearCart = () => {
  cartItems.value = []
}

const applyCoupon = () => {
  if (couponCode.value.toUpperCase() === 'SAVE10') {
    discount.value = Math.min(cartTotal.value * 0.1, 20) // 10% discount, max $20
    alert('Coupon applied successfully!')
  } else {
    alert('Invalid coupon code.')
  }
  couponCode.value = ''
}

const proceedToCheckout = () => {
  if (cartItems.value.length === 0) {
    alert('Your cart is empty.')
    return
  }
  
  // Navigate to checkout page
  router.push('/checkout')
}
</script> 
