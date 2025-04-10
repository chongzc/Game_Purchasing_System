<script setup>
import Header from '@/layouts/components/Header.vue'
import Footer from '@/layouts/components/Footer.vue'
</script>

<template>
  <Header />
  <div class="slot-wrapper">
    <slot />
  </div>

  <Footer />
</template>



<style lang="scss" scoped>
.slot-wrapper {
  margin: 30px;
}
</style>

