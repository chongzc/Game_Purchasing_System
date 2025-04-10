/**
 * Utility to hide/show the vertical navigation bar
 */

// Hide the vertical navigation bar
export function hideVerticalNav() {
  document.querySelector('.layout-wrapper').classList.add('hide-vertical-nav')
}

// Show the vertical navigation bar
export function showVerticalNav() {
  document.querySelector('.layout-wrapper').classList.remove('hide-vertical-nav')
}

// Toggle the vertical navigation bar visibility
export function toggleVerticalNav() {
  const layoutWrapper = document.querySelector('.layout-wrapper')
  if (layoutWrapper.classList.contains('hide-vertical-nav')) {
    layoutWrapper.classList.remove('hide-vertical-nav')
  } else {
    layoutWrapper.classList.add('hide-vertical-nav')
  }
} 
