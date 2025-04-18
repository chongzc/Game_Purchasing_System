export const getHeaderNavigationItems = user => {
  const navigationConfig = {
    public: [
      {
        title: 'Game Store',
        to: '/game-store',
      },
      {
        title: 'Browse', 
        to: '/browse-games',
      },
    ],
    user: [
      {
        title: 'Library',
        to: '/game-library',
      },
      {
        title: 'Wishlist',
        to: '/user-wishlist',
      },
      {
        title: 'Cart',
        to: '/cart',
      },
    ],
    developer: [
      {
        title: 'Developer Dashboard',
        to: '/developer-dashboard', 
      },
      {
        title: 'Developer Create Game',
        to: '/create-game', 
      },
    ],
    admin: [
      {
        title: 'Admin Dashboard',
        to: '/admin-dashboard',
      },
    ],
  }

  if (!user) {
    return navigationConfig.public
  }

  const role = user.u_role
  
  if (role === 'user') {
    return [...navigationConfig.public, ...navigationConfig.user]
  }

  return navigationConfig[role] || []
}
