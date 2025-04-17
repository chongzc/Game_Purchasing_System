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
            Admin Dashboard
          </VCardTitle>
          <VCardText>
            <h2 class="text-h6 font-weight-medium mb-6">
              Welcome back, {{ authStore.user?.u_name || 'Admin' }}!
            </h2>
            <p>From here you can manage the game store platform, users, and view statistics.</p>
          </VCardText>
        </VCard>
      </VCol>
      
      <VCol
        cols="12"
        md="6"
      >
        <VCard height="100%">
          <VCardTitle>
            Users Overview
          </VCardTitle>
          <VCardText>
            <div class="d-flex justify-space-between align-center">
              <div>
                <h3 class="text-h6">
                  Total Users
                </h3>
                <p class="text-h4">
                  0
                </p>
              </div>
              <VDivider
                vertical
                class="mx-4"
              />
              <div>
                <h3 class="text-h6">
                  Developers
                </h3>
                <p class="text-h4">
                  0
                </p>
              </div>
              <VDivider
                vertical
                class="mx-4"
              />
              <div>
                <h3 class="text-h6">
                  Customers
                </h3>
                <p class="text-h4">
                  0
                </p>
              </div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
      
      <VCol
        cols="12"
        md="6"
      >
        <VCard height="100%">
          <VCardTitle>
            Games Overview
          </VCardTitle>
          <VCardText>
            <div class="d-flex justify-space-between align-center">
              <div>
                <h3 class="text-h6">
                  Total Games
                </h3>
                <p class="text-h4">
                  0
                </p>
              </div>
              <VDivider
                vertical
                class="mx-4"
              />
              <div>
                <h3 class="text-h6">
                  Sales Today
                </h3>
                <p class="text-h4">
                  $0
                </p>
              </div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
      
      <VCol cols="12">
        <VCard>
          <VCardTitle>
            Quick Actions
          </VCardTitle>
          <VDivider />
          <VCardText>
            <VRow>
              <VCol
                cols="12"
                md="4"
              >
                <VBtn
                  prepend-icon="mdi-account-group"
                  block
                  variant="flat"
                  color="primary"
                  class="mb-4"
                >
                  Manage Users
                </VBtn>
              </VCol>
              <VCol
                cols="12"
                md="4"
              >
                <VBtn
                  prepend-icon="mdi-gamepad-variant"
                  block
                  variant="flat"
                  color="primary"
                  class="mb-4"
                >
                  Manage Games
                </VBtn>
              </VCol>
              <VCol
                cols="12"
                md="4"
              >
                <VBtn
                  prepend-icon="mdi-chart-bar"
                  block
                  variant="flat"
                  color="primary"
                  class="mb-4"
                >
                  View Reports
                </VBtn>
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </VContainer>
</template> 
