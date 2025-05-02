<template>
  <div>
    <VBreadcrumbs
      :items="breadcrumbs"
      class="pa-0 mb-6"
    />
    
    <h1 class="text-h3 font-weight-bold mb-6">
      Checkout
    </h1>
    
    <VRow>
      <VCol
        cols="12"
        md="8"
      >
        <!-- Payment Method -->
        <VCard class="mb-6">
          <VCardText>
            <h2 class="text-h5 font-weight-bold mb-4">
              Payment Method
            </h2>
            
            <div class="d-flex align-center mb-4">
              <VIcon
                icon="bx-credit-card"
                style="margin-right: 8px;"
              />
              <span class="text-subtitle-1 font-weight-medium">Credit Card</span>
            </div>
            
            <div class="mt-4">
              <VRow>
                <VCol cols="12">
                  <VTextField
                    v-model="creditCardForm.number"
                    label="Card Number"
                    placeholder="0000 0000 0000 0000"
                    variant="outlined"
                    required
                  />
                </VCol>
              </VRow>
              <VRow>
                <VCol
                  cols="12"
                  md="6"
                >
                  <VTextField
                    v-model="creditCardForm.name"
                    label="Cardholder Name"
                    placeholder="John Doe"
                    variant="outlined"
                    required
                  />
                </VCol>
                <VCol
                  cols="6"
                  md="3"
                >
                  <VTextField
                    v-model="creditCardForm.expiry"
                    label="Expiry Date"
                    placeholder="MM/YY"
                    variant="outlined"
                    required
                  />
                </VCol>
                <VCol
                  cols="6"
                  md="3"
                >
                  <VTextField
                    v-model="creditCardForm.cvv"
                    label="CVV"
                    placeholder="123"
                    variant="outlined"
                    required
                    type="password"
                  />
                </VCol>
              </VRow>
            </div>
          </VCardText>
        </VCard>
        
        <!-- Billing Information -->
        <VCard class="mb-6">
          <VCardText>
            <h2 class="text-h5 font-weight-bold mb-4">
              Billing Information
            </h2>
            
            <VRow>
              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="billingInfo.firstName"
                  label="First Name"
                  variant="outlined"
                  required
                />
              </VCol>
              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="billingInfo.lastName"
                  label="Last Name"
                  variant="outlined"
                  required
                />
              </VCol>
            </VRow>
            
            <VRow>
              <VCol cols="12">
                <VTextField
                  v-model="billingInfo.email"
                  label="Email"
                  variant="outlined"
                  required
                  type="email"
                />
              </VCol>
            </VRow>
            
            <VRow>
              <VCol cols="12">
                <VTextField
                  v-model="billingInfo.address"
                  label="Address"
                  variant="outlined"
                  required
                />
              </VCol>
            </VRow>
            
            <VRow>
              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="billingInfo.city"
                  label="City"
                  variant="outlined"
                  required
                />
              </VCol>
              <VCol
                cols="12"
                md="3"
              >
                <VTextField
                  v-model="billingInfo.state"
                  label="State/Province"
                  variant="outlined"
                  required
                />
              </VCol>
              <VCol
                cols="12"
                md="3"
              >
                <VTextField
                  v-model="billingInfo.zip"
                  label="ZIP/Postal Code"
                  variant="outlined"
                  required
                />
              </VCol>
            </VRow>
            
            <VRow>
              <VCol cols="12">
                <VSelect
                  v-model="billingInfo.country"
                  label="Country"
                  variant="outlined"
                  :items="countries"
                  required
                />
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
        
        <!-- Special Instructions -->
        <VCard>
          <VCardText>
            <h2 class="text-h5 font-weight-bold mb-4">
              Additional Information
            </h2>
            
            <VTextarea
              v-model="additionalInfo"
              label="Special Instructions (Optional)"
              variant="outlined"
              rows="3"
            />
            
            <VCheckbox
              v-model="termsAccepted"
              label="I agree to the Terms of Service and Privacy Policy"
              required
              class="mt-4"
            />
          </VCardText>
        </VCard>
      </VCol>
      
      <VCol
        cols="12"
        md="4"
      >
        <!-- Order Summary -->
        <VCard sticky>
          <VCardText>
            <h2 class="text-h5 font-weight-bold mb-4">
              Order Summary
            </h2>
            
            <div
              v-for="(item, index) in cartItems"
              :key="index"
              class="mb-4"
            >
              <div class="d-flex">
                <div
                  class="image-container"
                  style="width: 80px; height: 60px; overflow: hidden; border-radius: 4px; margin-right: 12px;"
                >
                  <VImg
                    :src="item.game?.g_mainImage ? `/storage/${item.game?.g_mainImage}` : item.image || '/images/placeholder.jpg'"
                    cover
                    width="100%"
                    height="100%"
                  />
                </div>
                <div>
                  <div class="font-weight-medium">
                    {{ item.game?.g_title || item.name }}
                  </div>
                  <div class="d-flex align-center">
                    <div class="text-caption">
                      ${{ (parseFloat(item.c_price || item.price)).toFixed(2) }}
                      <span
                        v-if="parseFloat(item.c_discount || item.discount || item.game?.g_discount || 0) > 0" 
                        class="text-error ms-1"
                      >
                        (-{{ (item.c_discount || item.discount || item.game?.g_discount || 0) }}%)
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <VDivider class="my-4" />
            
            <div class="d-flex justify-space-between mb-2">
              <div>Subtotal</div>
              <div>${{ cartTotal.toFixed(2) }}</div>
            </div>
            
            <div
              v-if="totalDiscount > 0"
              class="d-flex justify-space-between mb-2"
            >
              <div>Discount</div>
              <div class="text-error">
                -${{ totalDiscount.toFixed(2) }}
              </div>
            </div>
            
            <VDivider class="mb-4" />
            
            <div class="d-flex justify-space-between mb-6">
              <div class="text-h6 font-weight-bold">
                Total
              </div>
              <div class="text-h6 font-weight-bold">
                ${{ orderTotal.toFixed(2) }}
              </div>
            </div>
            
            <VBtn
              block
              color="primary"
              size="large"
              :disabled="!formValid"
              :loading="isProcessing"
              @click="placeOrder"
            >
              Place Order
            </VBtn>
            
            <div class="text-center mt-4">
              <VBtn 
                variant="text" 
                color="primary" 
                prepend-icon="bx-chevron-left"
                to="/cart"
              >
                Back to Cart
              </VBtn>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
    
    <!-- Order Confirmation Dialog -->
    <VDialog
      v-model="orderDialog"
      max-width="500"
    >
      <VCard>
        <VCardItem>
          <VCardTitle>Order Confirmation</VCardTitle>
        </VCardItem>
        
        <VCardText class="text-center pa-6">
          <VIcon
            icon="bx-check-circle"
            color="success"
            size="64"
          />
          
          <h3 class="text-h5 font-weight-bold mt-4">
            Thank you for your order!
          </h3>
          
          <p class="mt-2">
            Your order has been placed successfully.
          </p>
          <p class="text-subtitle-1 font-weight-bold mt-2">
            Order Number: {{ orderNumber }}
          </p>
          
          <p class="mt-4">
            A confirmation email has been sent to <span class="font-weight-bold">{{ billingInfo.email }}</span>.
          </p>
        </VCardText>
        
        <VCardActions class="pb-6 px-6">
          <VSpacer />
          <VBtn
            color="primary"
            to="/game-library"
          >
            Go to Game Library
          </VBtn>
          <VBtn
            color="error"
            variant="text"
            to="/game-store"
          >
            Continue Shopping
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>

<script setup>
import { getCookie, setCookie } from '@/utils/cookie'
import { calculateDiscountedPrice, calculateFinalTotal, calculateOriginalSubtotal, calculateTotalDiscount } from '@/utils/priceCalculations'
import axios from 'axios'
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const router = useRouter()
const route = useRoute()

// Session timeout handling
const sessionTimeout = ref(null)
const sessionDuration = 500000 // 5 seconds
const lastVisitedPath = ref(null)

// Store the current route as the last visited (before inactivity)
watch(route, newRoute => {
  lastVisitedPath.value = newRoute.fullPath
}, { immediate: true })

const resetSessionTimeout = () => {
  if (sessionTimeout.value) {
    clearTimeout(sessionTimeout.value)
  }

  sessionTimeout.value = setTimeout(() => {
    alert('Session expired due to inactivity. Redirecting to the game store.')
    router.push('/game-store')
  }, sessionDuration)
}

const attachActivityListeners = () => {
  const events = ['mousemove', 'keydown', 'click', 'scroll', 'touchstart']

  events.forEach(event => {
    window.addEventListener(event, resetSessionTimeout)
  })
}

const detachActivityListeners = () => {
  const events = ['mousemove', 'keydown', 'click', 'scroll', 'touchstart']

  events.forEach(event => {
    window.removeEventListener(event, resetSessionTimeout)
  })
}

onMounted(() => {
  resetSessionTimeout()
  attachActivityListeners()
})

onUnmounted(() => {
  if (sessionTimeout.value) {
    clearTimeout(sessionTimeout.value)
  }
  detachActivityListeners()
})

// Breadcrumbs
const breadcrumbs = ref([
  {
    title: 'Home',
    disabled: false,
    to: '/',
  },
  {
    title: 'Cart',
    disabled: false,
    to: '/cart',
  },
  {
    title: 'Checkout',
    disabled: true,
  },
])

const cartItems = ref([])
const isLoading = ref(false)
const error = ref(null)

// Check if this is a direct purchase from game details page
const isDirectPurchase = computed(() => route.query.directPurchase === 'true')

// Fetch cart items or handle direct purchase
const fetchCartItems = async () => {
  isLoading.value = true
  error.value = null
  
  try {
    // If this is a direct purchase, create a cart item from query params
    if (isDirectPurchase.value) {
      const gameId = route.query.gameId
      const gameTitle = route.query.gameTitle
      const gamePrice = parseFloat(route.query.gamePrice)
      const gameDiscount = parseFloat(route.query.gameDiscount || 0)
      const gameImage = route.query.gameImage
      
      // Create a single item cart with the game details
      cartItems.value = [{
        c_gameId: gameId,
        c_price: gamePrice,
        c_discount: gameDiscount,  
        game: {
          g_id: gameId,
          g_title: gameTitle,
          g_mainImage: gameImage,
          g_price: gamePrice,
          g_discount: gameDiscount,  
        },
      }]
    } else {
      // Regular cart checkout - fetch from API
      const response = await axios.get('/api/cart')

      cartItems.value = response.data.cartItems
    }
  } catch (err) {
    error.value = 'Failed to load cart items'
    console.error(err)
  } finally {
    isLoading.value = false
  }
}

// Payment method from cookies
const paymentMethod = ref(getCookie('paymentMethod') || 'credit_card')

// Load cart items when component mounts
onMounted(fetchCartItems)

// Credit card form
const creditCardForm = ref({
  number: getCookie('cardNumber') || '',
  name: getCookie('cardName') || '',
  expiry: getCookie('cardExpiry') || '',
  cvv: getCookie('cardCVV') || '',
})

// Billing information
const billingInfo = ref({
  firstName: getCookie('billingFirstName') || '',
  lastName: getCookie('billingLastName') || '',
  email: getCookie('billingEmail') || '',
  address: getCookie('billingAddress') || '',
  city: getCookie('billingCity') || '',
  state: getCookie('billingState') || '',
  zip: getCookie('billingZip') || '',
  country: getCookie('billingCountry') || 'United States',
})

// Countries list (shortened for brevity)
const countries = [
  'United States',
  'Canada',
  'United Kingdom',
  'Australia',
  'Germany',
  'France',
  'Japan',

  // Add more countries as needed
]

// Additional information
const additionalInfo = ref('')

// Terms acceptance
const termsAccepted = ref(false)

// Order processing state
const isProcessing = ref(false)
const orderDialog = ref(false)
const orderNumber = ref('')

// Save checkout data to cookies
const saveCheckoutDataToCookies = () => {
  setCookie('paymentMethod', paymentMethod.value, 30)
  setCookie('cardNumber', creditCardForm.value.number, 30)
  setCookie('cardName', creditCardForm.value.name, 30)
  setCookie('cardExpiry', creditCardForm.value.expiry, 30)
  setCookie('cardCVV', creditCardForm.value.cvv, 30)
  setCookie('billingFirstName', billingInfo.value.firstName, 30)
  setCookie('billingLastName', billingInfo.value.lastName, 30)
  setCookie('billingEmail', billingInfo.value.email, 30)
  setCookie('billingAddress', billingInfo.value.address, 30)
  setCookie('billingCity', billingInfo.value.city, 30)
  setCookie('billingState', billingInfo.value.state, 30)
  setCookie('billingZip', billingInfo.value.zip, 30)
  setCookie('billingCountry', billingInfo.value.country, 30)
}

// Order calculations
const cartTotal = computed(() => calculateOriginalSubtotal(cartItems.value))
const totalDiscount = computed(() => calculateTotalDiscount(cartItems.value))
const orderTotal = computed(() => calculateFinalTotal(cartTotal.value, totalDiscount.value))

// Form validation
const formValid = computed(() => {
  // Check terms acceptance
  if (!termsAccepted.value) return false
  
  // Basic validation for required fields
  const { firstName, lastName, email, address, city, state, zip, country } = billingInfo.value
  const hasRequiredFields = firstName && lastName && email && address && city && state && zip && country
  
  // Validate payment method based on cookie selection
  const { number, name, expiry, cvv } = creditCardForm.value
  const hasValidCardInfo = paymentMethod.value && number && name && expiry && cvv
  
  return hasRequiredFields && hasValidCardInfo
})

// Place order method
const placeOrder = async () => {
  if (!formValid.value) {
    alert('Please fill out all required fields.')
    
    return
  }
	
  isProcessing.value = true
  
  try {
    // Save data to cookies from CookieForLogin branch
    saveCheckoutDataToCookies()
    
    // Generate order number/receipt number 
    const receiptNumber = 'REC-' + Math.random().toString(36).substring(2, 12)
    const currentDate = new Date().toISOString() // Full ISO format with time
    
    // Create batch of purchase records for all cart items
    const purchaseBatch = cartItems.value.map(item => ({
      'p_gameId': item.c_gameId || item.game?.g_id,
      'p_gameName': item.game?.g_title || 'Unknown Game',
      'p_purchasePrice': calculateDiscountedPrice(item), 
      'p_purchaseDate': currentDate,
      'p_receiptNumber': receiptNumber,
    }))
    
    // Create all purchase records in a single request
    const response = await axios.post('/api/purchases', { purchases: purchaseBatch }, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      withCredentials: true,
    })
    
    // Use receipt number from response if available, otherwise use the generated one
    if (response.data && response.data.receiptNumber) {
      orderNumber.value = response.data.receiptNumber
    } else {
      orderNumber.value = receiptNumber
    }
    
    // Add purchased games to user library
    const libraryEntries = cartItems.value.map(item => ({
      'ul_gameId': item.c_gameId || item.game?.g_id,
      'ul_name': item.game?.g_title || 'Unknown Game',
      'ul_status': 'owned',  // Default status is 'owned' when purchased
    }))
    
    // Add to user library
    await axios.post('/api/user-library', { games: libraryEntries }, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      withCredentials: true,
    })
    
    // Clear the cart after successful purchase
    await axios.delete('/api/cart/clear', {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      withCredentials: true,
    })
    
    // Update the UI
    isProcessing.value = false
    orderDialog.value = true
  } catch (error) {
    console.error('Error processing order:', error?.response?.data || error)
    
    // Handle authentication errors
    if (error?.response?.status === 401) {
      alert('You need to be logged in to complete this purchase. Please log in and try again.')
      
      // Optionally redirect to login page
      // router.push('/login');
    } else {
      alert('There was an error processing your order. Please try again.')
    }
    
    isProcessing.value = false
  }
}
</script>

<style scoped>
.sticky {
  position: sticky;
  top: 20px;
}
</style>
