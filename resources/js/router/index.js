import { useAuthStore } from '@/stores/auth'
import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  {
    path: '/game-store',
    component: () => import('@/pages/game-store.vue'),
  },
  {
    path: '/game-library',
    component: () => import('@/pages/user/user-library.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/developer-dashboard',
    component: () => import('@/pages/developer/developer-dashboard.vue'),
    meta: { requiresAuth: true, role: 'developer' },
  },
  {
    path: '/create-game',
    component: () => import('@/pages/developer/create-game.vue'),
    meta: { requiresAuth: true, role: 'developer' },
  },
  {
    path: '/game-edit/:id',
    component: () => import('@/pages/developer/create-game.vue'),
    meta: { requiresAuth: true, role: 'developer' },
  },
  {
    path: '/admin-dashboard',
    component: () => import('@/pages/admin/admin-dashboard.vue'),
    meta: { requiresAuth: true, role: 'admin' },
  },
  {
    path: '/admin/game-management',
    component: () => import('@/pages/admin/game-management.vue'),
    meta: { requiresAuth: true, role: 'admin' },
  },
  {
    path: '/admin/user-management',
    component: () => import('@/pages/admin/user-management.vue'),
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
