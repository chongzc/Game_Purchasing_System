export const getHeaderNavigationItems = user => {
  const items = [
    {
      title: 'Game Store',
      to: '/game-store',
    },
    {
      title: 'Game Store',
      to: '/browse-games',
    },
  ]

  if (user) {
    items.push({
      title: 'My Library',
      to: '/game-library',
    })

    if (user.role === 'developer') {
      items.push({
        title: 'Developer Dashboard',
        to: '/developer-dashboard',
      })
    }

    if (user.role === 'admin') {
      items.push({
        title: 'Admin Dashboard',
        to: '/admin-dashboard',
      })
    }
  }

  return items
}
