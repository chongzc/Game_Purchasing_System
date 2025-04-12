<script setup>
import Footer from '@/layouts/components/Footer.vue'
import Header from '@/layouts/components/Header.vue'
</script>

<template>
  <Header />
  <div class="slot-wrapper">
    <div class="content-container">
      <slot />
    </div>
  </div>
  <Footer />
</template>

<style lang="scss" scoped>
.slot-wrapper {
  margin: 30px;
}

.content-container {
  max-width: 1920px;
  margin: 0 auto;
  width: 100%;
}
</style>

