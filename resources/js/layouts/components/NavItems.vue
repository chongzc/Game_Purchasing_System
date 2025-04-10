<script setup>
import VerticalNavSectionTitle from '@/@layouts/components/VerticalNavSectionTitle.vue'
import VerticalNavGroup from '@layouts/components/VerticalNavGroup.vue'
import VerticalNavLink from '@layouts/components/VerticalNavLink.vue'
</script>

<template>
  <!-- 👉 Game Store -->
  <VerticalNavLink
    :item="{
      title: 'Game Store',
      icon: 'bx-joystick',
      to: '/game-store',
    }"
  />

  <!-- 👉 Customer Pages -->
  <VerticalNavSectionTitle
    :item="{
      heading: 'Customer',
    }"
  />
  
  <VerticalNavLink
    :item="{
      title: 'Shopping Cart',
      icon: 'bx-cart',
      to: '/cart',
    }"
  />
  
  <VerticalNavLink
    :item="{
      title: 'Game Library',
      icon: 'bx-library',
      to: '/game-library',
    }"
  />
  
  <!-- 👉 Admin -->
  <VerticalNavSectionTitle
    :item="{
      heading: 'Admin',
    }"
  />
  
  <VerticalNavLink
    :item="{
      title: 'Game Management',
      icon: 'bx-game',
      to: '/admin/games',
    }"
  />

</template>
