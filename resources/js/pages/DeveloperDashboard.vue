<script setup>
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
</script>

<template>
  <VContainer>
    <VRow>
      <VCol cols="12">
        <VCard>
          <VCardTitle>
            Developer Dashboard
          </VCardTitle>
          <VCardText>
            <h2 class="text-h6 font-weight-medium mb-6">
              Welcome back, {{ authStore.user?.u_name || 'Developer' }}!
            </h2>
            <p>From here you can manage your published games, add new ones, and view statistics.</p>
          </VCardText>
        </VCard>
      </VCol>
      
      <VCol
        cols="12"
        md="6"
      >
        <VCard height="100%">
          <VCardTitle class="d-flex align-center justify-space-between">
            My Games
            <VBtn
              color="primary"
              variant="flat"
              size="small"
            >
              Add New Game
            </VBtn>
          </VCardTitle>
          <VCardText>
            <p>No games published yet.</p>
          </VCardText>
        </VCard>
      </VCol>
      
      <VCol
        cols="12"
        md="6"
      >
        <VCard height="100%">
          <VCardTitle>
            Sales Overview
          </VCardTitle>
          <VCardText>
            <p>No sales data available.</p>
          </VCardText>
        </VCard>
      </VCol>
      
      <VCol
        cols="12"
        md="6"
      >
        <VCard height="100%">
          <VCardTitle>
            Latest Reviews
          </VCardTitle>
          <VCardText>
            <p>No reviews yet.</p>
          </VCardText>
        </VCard>
      </VCol>
      
      <VCol
        cols="12"
        md="6"
      >
        <VCard height="100%">
          <VCardTitle>
            Quick Actions
          </VCardTitle>
          <VDivider />
          <VList>
            <VListItem
              title="Create New Game"
              prepend-icon="mdi-plus"
            />
            <VListItem
              title="View Sales Reports"
              prepend-icon="mdi-chart-line"
            />
            <VListItem
              title="Update Profile"
              prepend-icon="mdi-account-edit"
            />
          </VList>
        </VCard>
      </VCol>
    </VRow>
  </VContainer>
</template> 
