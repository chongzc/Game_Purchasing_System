<script setup>
import UsersTable from '@/components/users/UsersTable.vue'
</script>

<template>
  <VCard title="Users Management">
    <VCardText>
      <h2 class="text-h5 mb-4">
        System Users
      </h2>
      <p class="mb-6">
        This page allows administrators to view all users registered in the system.
      </p>
      
      <!-- Users Table Component with TanStack Query integration -->
      <UsersTable />
    </VCardText>
  </VCard>
</template> 
