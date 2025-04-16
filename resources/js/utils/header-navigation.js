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
        title: 'My Library',
        to: '/game-library',
      },
    ],
    developer: [
      {
        title: 'Developer Dashboard',
        to: '/developer-dashboard', 
      },
      {
        title: 'Coming Soon',
        to: '#',
      },
    ],
    admin: [
      {
        title: 'Admin Dashboard',
        to: '/admin-dashboard',
      },
      {
        title: 'Coming Soon',
        to: '#',
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
