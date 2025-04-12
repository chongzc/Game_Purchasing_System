import { useAuthStore } from '@/stores/auth'
import { createRouter, createWebHistory } from 'vue-router'
import { routes } from './routes'

export default function (app) {
  const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes,
    scrollBehavior() {
      return { top: 0 }
    },
  })
  
  // Navigation Guards
  router.beforeEach(async (to, from, next) => {
    const authStore = useAuthStore()
    
    // Try to get the current authentication status
    await authStore.checkAuth()
    
    // Check for protected routes (routes that require authentication)
    if (to.matched.some(record => record.meta.requiresAuth)) {
      // If not logged in, redirect to login page
      if (!authStore.isLoggedIn) {
        next({
          path: '/login',
          query: { redirect: to.fullPath }, // Store the path for redirect after login
        })
        
        return
      }
      
      // Check role authorization
      if (to.matched.some(record => record.meta.roles)) {
        const allowedRoles = to.matched.find(record => record.meta.roles)?.meta.roles
        
        if (allowedRoles && !allowedRoles.includes(authStore.userRole)) {
          // Redirect based on role
          if (authStore.isAdmin) {
            next('/admin-dashboard')
          } else if (authStore.isDeveloper) {
            next('/developer-dashboard')
          } else {
            next('/game-store')
          }
          
          return
        }
      }
    }
    
    // Guest-only routes (login, register) - redirect if already logged in
    if (to.matched.some(record => record.meta.guestOnly) && authStore.isLoggedIn) {
      // Redirect based on role
      if (authStore.isAdmin) {
        next('/admin-dashboard')
      } else if (authStore.isDeveloper) {
        next('/developer-dashboard')
      } else {
        next('/game-store')
      }
      
      return
    }
    
    next()
  })
  
  app.use(router)
}

