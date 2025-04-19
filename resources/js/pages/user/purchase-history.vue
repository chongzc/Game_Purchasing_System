<template>
  <div>
    <VBreadcrumbs
      :items="breadcrumbs"
      class="pa-0 mb-6"
    />
    
    <div class="d-flex align-center mb-6">
      <h1 class="text-h3 font-weight-bold">
        Purchase History
      </h1>
      <VSpacer />
    </div>
    
    <!-- Search and Filter -->
    <VCard class="mb-6">
      <VCardText>
        <VRow>
          <VCol
            cols="12"
            md="6"
          >
            <VTextField
              v-model="searchQuery"
              label="Search purchases"
              placeholder="Enter game title or receipt number"
              variant="outlined"
              density="compact"
              prepend-inner-icon="bx-search"
              hide-details
            />
          </VCol>
          <VCol
            cols="12"
            md="3"
          >
            <VSelect
              v-model="sortOption"
              label="Sort by"
              :items="sortOptions"
              variant="outlined"
              density="compact"
              hide-details
            />
          </VCol>
          <VCol
            cols="12"
            md="3"
          >
            <VBtn
              color="primary"
              variant="outlined"
              prepend-icon="bx-reset"
              block
              @click="clearFilters"
            >
              Clear Filters
            </VBtn>
          </VCol>
        </VRow>
      </VCardText>
    </VCard>
    
    <!-- Loading State -->
    <VCard
      v-if="loading"
      class="pa-6 text-center mx-auto"
      max-width="1080"
    >
      <VProgressCircular
        indeterminate
        color="primary"
      />
      <div class="text-body-1 mt-4">
        Loading your purchase history...
      </div>
    </VCard>
    
    <!-- Empty State -->
    <VCard
      v-else-if="!loading && filteredPurchases.length === 0"
      class="pa-6 text-center mx-auto"
      max-width="1080"
    >
      <VIcon
        icon="bx-receipt"
        size="64"
        color="grey-lighten-1"
      />
      <h2 class="text-h5 font-weight-bold mt-4 mb-2">
        No Purchase Records Found
      </h2>
      <p class="text-body-1 mb-6">
        {{ getEmptyStateMessage }}
      </p>
      <VBtn
        color="primary"
        to="/game-store"
        prepend-icon="bx-shopping-bag"
      >
        Browse Games
      </VBtn>
    </VCard>
    
    <!-- Purchase History -->
    <div
      v-else
      class="mx-auto"
      style="max-width: 1080px;"
    >
      <VCard
        v-for="(receipt, receiptNumber) in filteredPurchases"
        :key="receiptNumber"
        class="mb-6"
      >
        <VCardItem>
          <template #prepend>
            <VIcon
              icon="bx-receipt"
              size="large"
              color="primary"
              class="me-4"
            />
          </template>
          
          <VCardTitle>
            Receipt: {{ receiptNumber }}
          </VCardTitle>
          
          <VCardSubtitle>
            {{ formatDate(receipt[0].p_purchaseDate) }}
          </VCardSubtitle>
          
          <template #append>
            <VChip
              color="primary"
              size="small"
            >
              {{ receipt.length }} Items
            </VChip>
          </template>
        </VCardItem>
        
        <VDivider />
        
        <VCardText>
          <VTable class="purchase-table">
            <thead>
              <tr>
                <th class="game-column">
                  Game
                </th>
                <th class="price-column text-right">
                  Price
                </th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="purchase in receipt"
                :key="purchase.p_id"
              >
                <td class="game-column">
                  <div class="d-flex align-center">
                    <VAvatar
                      v-if="purchase.game && purchase.game.g_image"
                      :image="purchase.game.g_image"
                      size="36"
                      class="me-3"
                    >
                      <template #fallback>
                        <VIcon icon="bx-game" />
                      </template>
                    </VAvatar>
                    <div>
                      <div class="font-weight-medium">
                        {{ purchase.p_gameName }}
                      </div>
                      <div 
                        v-if="purchase.game && purchase.game.developer"
                        class="text-caption text-medium-emphasis"
                      >
                        {{ purchase.game.developer.u_name }}
                      </div>
                    </div>
                  </div>
                </td>
                <td class="price-column text-right">
                  ${{ formatPrice(purchase.p_purchasePrice) }}
                </td>
              </tr>
            </tbody>
            <tfoot>
              <tr>
                <td class="text-right font-weight-bold">
                  Total:
                </td>
                <td class="price-column text-right font-weight-bold">
                  ${{ calculateReceiptTotal(receipt) }}
                </td>
              </tr>
            </tfoot>
          </VTable>
        </VCardText>
      </VCard>
    </div>
  </div>
</template>

<script setup>
import axios from 'axios'
import { computed, onMounted, ref } from 'vue'

// Breadcrumbs
const breadcrumbs = ref([
  {
    title: 'Home',
    disabled: false,
    to: '/',
  },
  {
    title: 'Purchase History',
    disabled: true,
  },
])

// Purchase history data
const purchases = ref({})
const loading = ref(true)
const errorMessage = ref('')

// Search and filtering
const searchQuery = ref('')
const sortOption = ref('date-desc')

// Sort options
const sortOptions = [
  { title: 'Date (Newest First)', value: 'date-desc' },
  { title: 'Date (Oldest First)', value: 'date-asc' },
  { title: 'Price (High to Low)', value: 'price-desc' },
  { title: 'Price (Low to High)', value: 'price-asc' },
]

// Filter and sort purchases
const filteredPurchases = computed(() => {
  // Start with all purchases
  let result = { ...purchases.value }
  
  // Apply search filter if provided
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    const filteredResult = {}
    
    // Filter by receipt number or game name
    Object.entries(result).forEach(([receiptNumber, items]) => {
      if (receiptNumber.toLowerCase().includes(query)) {
        // Receipt number matches, include all items
        filteredResult[receiptNumber] = items
      } else {
        // Check if any game name matches the query
        const matchingItems = items.filter(item => 
          item.p_gameName.toLowerCase().includes(query),
        )
        
        if (matchingItems.length > 0) {
          filteredResult[receiptNumber] = matchingItems
        }
      }
    })
    
    result = filteredResult
  }
  
  // Convert to array of [receiptNumber, items] for sorting
  let sortableResult = Object.entries(result)
  
  // Apply sorting
  sortableResult.sort(([receiptA, itemsA], [receiptB, itemsB]) => {
    switch (sortOption.value) {
    case 'date-desc':
      // Newest first (using first item's purchase date)
      return new Date(itemsB[0].p_purchaseDate) - new Date(itemsA[0].p_purchaseDate)
    case 'date-asc':
      // Oldest first
      return new Date(itemsA[0].p_purchaseDate) - new Date(itemsB[0].p_purchaseDate)
    case 'price-desc':
      // Highest total first
      return calculateReceiptTotal(itemsB) - calculateReceiptTotal(itemsA)
    case 'price-asc':
      // Lowest total first
      return calculateReceiptTotal(itemsA) - calculateReceiptTotal(itemsB)
    default:
      return 0
    }
  })
  
  // Convert back to object
  return Object.fromEntries(sortableResult)
})

// Methods
const fetchPurchaseHistory = async () => {
  try {
    loading.value = true
    
    const response = await axios.get('/api/purchases')

    purchases.value = response.data.purchases || {}
    
    console.log('Purchase history:', purchases.value)
  } catch (error) {
    console.error('Error fetching purchase history:', error)
    errorMessage.value = 'Failed to load purchase history. Please try again.'
    purchases.value = {}
  } finally {
    loading.value = false
  }
}

const formatDate = date => {
  if (!date) return 'N/A'
  
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const formatPrice = price => {
  return parseFloat(price).toFixed(2)
}

const calculateReceiptTotal = items => {
  const total = items.reduce((sum, item) => sum + parseFloat(item.p_purchasePrice), 0)
  
  return total.toFixed(2)
}

const clearFilters = () => {
  searchQuery.value = ''
  sortOption.value = 'date-desc'
}



// Computed property for empty state message
const getEmptyStateMessage = computed(() => {
  if (Object.keys(purchases.value).length === 0) {
    return "You haven't made any purchases yet."
  }
  
  if (Object.keys(filteredPurchases.value).length === 0) {
    return "No purchases match your search criteria."
  }
  
  return ""
})

// Load purchase history on mount
onMounted(() => {
  fetchPurchaseHistory()
})
</script>

<style scoped>
.purchase-table {
  width: 100%;
  border-collapse: collapse;
}

.purchase-table th {
  font-weight: 600;
  color: rgba(var(--v-theme-on-surface), var(--v-medium-emphasis-opacity));
  text-align: left;
  padding: 0.5rem;
}

.purchase-table td {
  padding: 0.75rem 0.5rem;
  border-bottom: thin solid rgba(var(--v-border-opacity), var(--v-border-opacity));
}

.purchase-table tfoot tr td {
  border-bottom: none;
  padding-top: 1rem;
}

.game-column {
  width: 70%;
}

.price-column {
  width: 30%;
  text-align: right;
}
</style>

