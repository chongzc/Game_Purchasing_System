export const getHeaderNavigationItems = user => {
  let items = []
  
  // If user is not logged in or has user role, show Game Store and Browse
  if (!user || user.u_role === 'user') {
    items = [
      {
        title: 'Game Store',
        to: '/game-store',
      },
      {
        title: 'Browse',
        to: '/browse-games',
      },
    ]
  }

  // Additional items for logged in users based on role
  if (user) {
    if (user.u_role === 'user') {
      items.push({
        title: 'My Library',
        to: '/game-library',
      })
    }

    if (user.u_role === 'developer') {
      items = [
        {
          title: 'Developer Dashboard',
          to: '/developer-dashboard',
        },
        {
          title: 'Coming Soon',
          to: '#',
        },
      ]
    }

    if (user.u_role === 'admin') {
      items = [
        {
          title: 'Admin Dashboard',
          to: '/admin-dashboard',
        },
        {
          title: 'Coming Soon',
          to: '#',
        },
      ]
    }
  }

  return items
}
