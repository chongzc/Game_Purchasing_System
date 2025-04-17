// Function to get CSRF token from cookie
export const getCsrfToken = () => {
  const cookies = document.cookie.split(';')
  
  const csrfToken = cookies
    .find(cookie => cookie.trim().startsWith('XSRF-TOKEN='))
    ?.split('=')[1]
    
  return csrfToken ? decodeURIComponent(csrfToken) : null
}
