export const routes = [
  { path: '/', redirect: '/game-store' },
  {
    path: '/',
    component: () => import('@/layouts/default.vue'),
    children: [
      {
        path: 'game-store',
        component: () => import('@/pages/game-store.vue'),
        meta: { requiresAuth: false, roles: ['user'] },
      },
      {
        path: 'purchase-history',
        component: () => import('@/pages/user/purchase-history.vue'),
        meta: { requiresAuth: true, roles: ['user'] },
      },
      {
        path: 'browse-games',
        component: () => import('@/pages/browse-games.vue'),
      },
      {
        path: 'games/:id',
        component: () => import('@/pages/user/game-details.vue'),
      },
      {
        path: 'cart',
        component: () => import('@/pages/user/cart.vue'),
        meta: { requiresAuth: true, roles: ['user'] },
      },
      {
        path: 'checkout',
        component: () => import('@/pages/user/checkout.vue'),
        meta: { requiresAuth: true, roles: ['user'] },
      },
      {
        path: 'game-library',
        component: () => import('@/pages/user/user-library.vue'),
        meta: { requiresAuth: true, roles: ['user', 'developer', 'admin'] },
      },
      {
        path: 'user-wishlist',
        component: () => import('@/pages/user/user-wishlist.vue'),
        meta: { requiresAuth: true, roles: ['user', 'developer', 'admin'] },
      },
      {
        path: 'developer-dashboard',
        component: () => import('@/pages/developer/developer-dashboard.vue'),
        meta: { requiresAuth: true, roles: ['developer'] },
      },
      {
        path: 'create-game',
        component: () => import('@/pages/developer/create-game.vue'),
        meta: { requiresAuth: true, roles: ['developer'] },
      },
      {
        path: '/game-edit/:id',
        component: () => import('@/pages/developer/create-game.vue'),
        meta: { requiresAuth: true, role: 'developer' },
      },
      {
        path: 'admin-dashboard',
        component: () => import('@/pages/admin/admin-dashboard.vue'),
        meta: { requiresAuth: true, roles: ['admin'] },
      },
      {
        path: 'admin',
        meta: { requiresAuth: true, roles: ['admin'] },
        children: [
          {
            path: 'games',
            component: () => import('@/pages/admin/game-management.vue'),
          },
          {
            path: 'users',
            component: () => import('@/pages/admin/user-management.vue'),
          },

        ],
      },
      {
        path: 'account-settings',
        component: () => import('@/pages/profile/account-settings.vue'),
        meta: { requiresAuth: true },
      },
    ],
  },
  {
    path: '/',
    component: () => import('@/layouts/blank.vue'),
    children: [
      {
        path: 'login',
        component: () => import('@/pages/session/login.vue'),
        meta: { guestOnly: true },
      },
      {
        path: 'register',
        component: () => import('@/pages/session/register.vue'),
        meta: { guestOnly: true },
      },
      {
        path: '/:pathMatch(.*)*',
        component: () => import('@/pages/[...error].vue'),
      },
    ],
  },
]
