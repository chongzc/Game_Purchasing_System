<script setup>
import UserProfile from '@/layouts/components/UserProfile.vue'
import { ref } from 'vue'

const isLoggedIn = ref(false)
</script>

<template>
  <header class="header">
    <div class="container">
      <div class="logo">
        Logo
      </div>

      <nav class="navigation">
        <ul>
          <li><a href="#">Home</a></li>
          <li><a href="#">My Library</a></li>
          <li><a href="#">Cart</a></li>
          <li><a href="#">Hi</a></li>
        </ul>
      </nav>

      <div class="auth-buttons">
        <VBtn class="sign-in">
          Sign In
        </VBtn>
        <VBtn
          color="secondary"
          class="log-in"
        >
          Log In
        </VBtn>
        <UserProfile />
      </div>
    </div>
  </header>
</template>

<style scoped>
.header {
  background-color: white;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  width: 100%;
}

.container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 2rem;
  width: 100%;
  max-width: 1920px;
  min-width: 450px;
  margin: 0 auto;
  box-sizing: border-box;
}

.logo {
  font-size: 1.5rem;
  font-weight: bold;
  white-space: nowrap;
}

.navigation {
  flex-grow: 1;
  text-align: center;
}

.navigation ul {
  display: flex;
  list-style: none;
  gap: 2rem;
  margin: 0;
  padding: 0;
  justify-content: center;
  white-space: nowrap;
}

.navigation a {
  text-decoration: none;
  color: #333;
}

.auth-buttons {
  display: flex;
  gap: 0.5rem;
  align-items: center;
  white-space: nowrap;
}

@media (max-width: 768px) {
  .container {
    gap: 1rem;
  }
  
  .navigation ul {
    gap: 1rem;
  }
}

@media (max-width: 576px) {
  .container {
    padding: 1rem;
  }
  
  .sign-in, .log-in {
    font-size: 0.9rem;
    padding: 0.25rem 0.5rem;
  }
}

@media (max-width: 450px) {
  /* 
  At 450px and below, we maintain the layout as is
  since the container has a min-width of 450px.
  The layout won't shrink further, and users will need to scroll horizontally.
  */
  .container {
    min-width: 450px;
  }
}
</style>
