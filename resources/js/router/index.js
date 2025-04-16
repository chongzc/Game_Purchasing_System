import { useAuthStore } from '@/stores/auth'
import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  {
    path: '/game-store',
    component: () => import('@/pages/GameStore.vue'),
  },
  {
    path: '/game-library',
    component: () => import('@/pages/GameLibrary.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/developer-dashboard',
    component: () => import('@/pages/developer-dashboard.vue'),
    meta: { requiresAuth: true, role: 'developer' },
  },
  {
    path: '/create-game',
    component: () => import('@/pages/create-game.vue'),
    meta: { requiresAuth: true, role: 'developer' },
  },
  {
    path: '/admin-dashboard',
    component: () => import('@/pages/AdminDashboard.vue'),
    meta: { requiresAuth: true, role: 'admin' },
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, from, next) => {
  const auth = useAuthStore()
  
  if (to.meta.requiresAuth && !auth.user) {
    next('/login')
  } else if (to.meta.role && auth.user?.role !== to.meta.role) {
    next('/game-store')
  } else {
    next()
  }
})

export default router
