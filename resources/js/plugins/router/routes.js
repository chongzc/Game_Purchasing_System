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
      },
      {
        path: 'checkout',
        component: () => import('@/pages/Checkout.vue'),
      },
      {
        path: 'game-library',
        component: () => import('@/pages/GameLibrary.vue'),
      },
      {
        path: 'admin',
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
      },
      {
        path: 'account-settings',
        component: () => import('@/pages/account-settings.vue'),
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
      },
      {
        path: 'register',
        component: () => import('@/pages/Register.vue'),
      },
      {
        path: '/:pathMatch(.*)*',
        component: () => import('@/pages/[...error].vue'),
      },
    ],
  },
]
