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
            
            <VRadioGroup
              v-model="paymentMethod"
              inline
            >
              <VRadio
                value="credit_card"
                label="Credit Card"
              />
              <VRadio
                value="paypal"
                label="PayPal"
              />
              <VRadio
                value="crypto"
                label="Cryptocurrency"
              />
            </VRadioGroup>
            
            <!-- Credit Card Form -->
            <VExpandTransition>
              <div
                v-if="paymentMethod === 'credit_card'"
                class="mt-4"
              >
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
            </VExpandTransition>
            
            <!-- PayPal Info -->
            <VExpandTransition>
              <div
                v-if="paymentMethod === 'paypal'"
                class="mt-4"
              >
                <VAlert
                  type="info"
                  border="start"
                  prominent
                >
                  You will be redirected to PayPal to complete your payment.
                </VAlert>
              </div>
            </VExpandTransition>
            
            <!-- Crypto Info -->
            <VExpandTransition>
              <div
                v-if="paymentMethod === 'crypto'"
                class="mt-4"
              >
                <VAlert
                  type="info"
                  border="start"
                  prominent
                >
                  You will be redirected to our cryptocurrency payment processor.
                </VAlert>
              </div>
            </VExpandTransition>
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
                <VImg
                  :src="item.image || '/images/placeholder.jpg'"
                  width="60"
                  height="60"
                  cover
                  class="rounded mr-3"
                />
                <div>
                  <div class="font-weight-medium">
                    {{ item.name }}
                  </div>
                  <div class="d-flex align-center">
                    <div class="text-caption text-disabled">
                      Qty: {{ item.quantity }}
                    </div>
                    <div class="text-caption ml-2">
                      ${{ (item.price * item.quantity).toFixed(2) }}
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
import { ref, computed } from 'vue'
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
    title: 'Cart',
    disabled: false,
    to: '/cart',
  },
  {
    title: 'Checkout',
    disabled: true,
  },
])

// Cart items (in a real app, this would be fetched from a store or API)
const cartItems = ref([
  {
    id: 1,
    name: 'Elden Ring',
    price: 59.99,
    quantity: 1,
    platform: 'PC Digital Download',
    image: '/images/placeholder.jpg',
  },
  {
    id: 3,
    name: 'FIFA 23',
    price: 44.99,
    quantity: 1,
    platform: 'PS5 Digital Download',
    image: '/images/placeholder.jpg',
  },
])

// Payment method
const paymentMethod = ref('credit_card')

// Credit card form
const creditCardForm = ref({
  number: '',
  name: '',
  expiry: '',
  cvv: '',
})

// Billing information
const billingInfo = ref({
  firstName: '',
  lastName: '',
  email: '',
  address: '',
  city: '',
  state: '',
  zip: '',
  country: 'United States',
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

// Order calculations
const cartTotal = computed(() => {
  return cartItems.value.reduce((total, item) => {
    return total + (item.price * item.quantity)
  }, 0)
})

const discount = ref(10) // Example discount amount
const tax = computed(() => cartTotal.value * 0.08) // Example 8% tax
const orderTotal = computed(() => cartTotal.value - discount.value + tax.value)

// Form validation
const formValid = computed(() => {
  if (!termsAccepted.value) return false
  
  // Basic validation for required fields
  const { firstName, lastName, email, address, city, state, zip, country } = billingInfo.value
  if (!firstName || !lastName || !email || !address || !city || !state || !zip || !country) {
    return false
  }
  
  // If credit card is selected, validate credit card info
  if (paymentMethod.value === 'credit_card') {
    const { number, name, expiry, cvv } = creditCardForm.value
    if (!number || !name || !expiry || !cvv) {
      return false
    }
  }
  
  return true
})

// Place order method
const placeOrder = () => {
  if (!formValid.value) {
    alert('Please fill out all required fields.')
    
    return
  }
  
  isProcessing.value = true
  
  // Simulate API call with a timeout
  setTimeout(() => {
    // Generate random order number
    orderNumber.value = 'ORD-' + Date.now().toString().substring(3) + '-' + Math.floor(Math.random() * 1000)
    
    isProcessing.value = false
    orderDialog.value = true
    
    // In a real app, you would clear the cart here or redirect to a confirmation page
  }, 2000)
}
</script>

<style scoped>
.sticky {
  position: sticky;
  top: 20px;
}
</style> 
