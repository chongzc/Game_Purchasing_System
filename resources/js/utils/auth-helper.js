import axios from '@/utils/axios'

/**
 * Helper function to verify authentication status and refresh CSRF token
 * Use this before making authenticated API calls
 */
export const verifyAuth = async () => {
  // Get fresh CSRF token
  await axios.get('/sanctum/csrf-cookie')
  
  // Verify authentication status
  try {
    const response = await axios.get('/api/user')
    if (!response.data.isLoggedIn) {
      throw new Error('User not authenticated')
    }
    
    return true
  } catch (error) {
    console.error('Authentication verification failed:', error)

    // Clear authentication state and redirect to login
    localStorage.removeItem('user')
    window.location.href = '/login'
    
    return false
  }
}

/**
 * Unified method to make authenticated API calls
 * @param {string} method - HTTP method (get, post, put, delete)
 * @param {string} url - API endpoint
 * @param {object} data - Request data (for POST/PUT)
 * @param {object} config - Additional axios config
 */
export const authRequest = async (method, url, data = null, config = {}) => {
  await verifyAuth()
  
  try {
    switch (method.toLowerCase()) {
    case 'get':
      return await axios.get(url, config)
    case 'post':
      return await axios.post(url, data, config)
    case 'put':
      return await axios.put(url, data, config)
    case 'delete':
      return await axios.delete(url, config)
    default:
      throw new Error(`Unsupported method: ${method}`)
    }
  } catch (error) {
    console.error(`API request failed (${method.toUpperCase()} ${url}):`, error)
    throw error
  }
} 
