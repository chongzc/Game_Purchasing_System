<template>
  <div class="h-100 d-flex align-center justify-md-space-between justify-center">
    <!-- 👉 Footer: left content -->
    <span class="d-flex align-center">
      &copy;
      {{ new Date().getFullYear() }}
      Game Store. All rights reserved.
    </span>
    <!-- 👉 Footer: right content -->
    <span class="d-md-flex gap-x-4 text-primary d-none">
      <a
        href="/terms"
        target="_self"
      >Terms of Service</a>
      <a
        href="/privacy"
        target="_self"
      >Privacy Policy</a>
      <a
        href="/about"
        target="_self"
      >About Us</a>
      <a
        href="/contact"
        target="_self"
      >Contact Support</a>
    </span>
  </div>
</template>
