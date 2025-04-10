import { createRouter, createWebHistory } from 'vue-router'
import { routes } from './routes'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

// Navigation guard to check authentication
router.beforeEach((to, from, next) => {
  // Define which routes require authentication
  const requiresAuth = ['cart', 'game-library', 'checkout', 'account-settings'].some(
    path => to.path.includes(path),
  )

  // Check if user is authenticated
  const isAuthenticated = document.cookie.includes('user_authenticated=true')

  if (requiresAuth && !isAuthenticated) {
    // Redirect to login if authentication is required but user is not authenticated
    next('/login')
  } else {
    // Continue navigation
    next()
  }
})

export default function (app) {
  app.use(router)
}
export { router }

