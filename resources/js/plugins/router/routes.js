export const routes = [
  { path: '/', redirect: '/game-store' },
  {
    path: '/',
    component: () => import('@/layouts/default.vue'),
    children: [
      {
        path: 'game-store',
        component: () => import('@/pages/GameStore.vue'),
      },
      {
        path: 'games/:id',
        component: () => import('@/pages/GameDetails.vue'),
      },
      {
        path: 'cart',
        component: () => import('@/pages/ShoppingCart.vue'),
        meta: { requiresAuth: true, roles: ['user', 'developer', 'admin'] },
      },
      {
        path: 'checkout',
        component: () => import('@/pages/Checkout.vue'),
        meta: { requiresAuth: true, roles: ['user', 'developer', 'admin'] },
      },
      {
        path: 'game-library',
        component: () => import('@/pages/GameLibrary.vue'),
        meta: { requiresAuth: true, roles: ['user', 'developer', 'admin'] },
      },
      {
        path: 'developer-dashboard',
        component: () => import('@/pages/developer-dashboard.vue'),
        meta: { requiresAuth: true, roles: ['developer'] },
      },
      {
        path: 'admin-dashboard',
        component: () => import('@/pages/admin-dashboard.vue'),
        meta: { requiresAuth: true, roles: ['admin'] },
      },
      {
        path: 'admin',
        meta: { requiresAuth: true, roles: ['admin'] },
        children: [
          {
            path: 'games',
            component: () => import('@/pages/admin/GameManagement.vue'),
          },

          // More admin routes can be added here
        ],
      },
      {
        path: 'dashboard',
        component: () => import('@/pages/dashboard.vue'),
        meta: { requiresAuth: true },
      },
      {
        path: 'account-settings',
        component: () => import('@/pages/account-settings.vue'),
        meta: { requiresAuth: true },
      },
      {
        path: 'typography',
        component: () => import('@/pages/typography.vue'),
      },
      {
        path: 'icons',
        component: () => import('@/pages/icons.vue'),
      },
      {
        path: 'cards',
        component: () => import('@/pages/cards.vue'),
      },
      {
        path: 'tables',
        component: () => import('@/pages/tables.vue'),
      },
      {
        path: 'form-layouts',
        component: () => import('@/pages/form-layouts.vue'),
      },
    ],
  },
  {
    path: '/',
    component: () => import('@/layouts/blank.vue'),
    children: [
      {
        path: 'login',
        component: () => import('@/pages/login.vue'),
        meta: { guestOnly: true },
      },
      {
        path: 'register',
        component: () => import('@/pages/Register.vue'),
        meta: { guestOnly: true },
      },
      {
        path: '/:pathMatch(.*)*',
        component: () => import('@/pages/[...error].vue'),
      },
    ],
  },
]
