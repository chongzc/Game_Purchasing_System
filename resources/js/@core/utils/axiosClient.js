import axios from 'axios'
import { getCsrfToken } from './csrfToken'

// Create axios instance with default config
const axiosClient = axios.create({
  baseURL: window.location.origin,
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
})

// Request interceptor - adds CSRF token to all requests
axiosClient.interceptors.request.use(config => {
  const csrfToken = getCsrfToken()
  
  if (csrfToken) {
    config.headers['X-XSRF-TOKEN'] = csrfToken
  }
  
  // If FormData is being sent, let axios set the content type automatically
  if (config.data instanceof FormData) {
    delete config.headers['Content-Type']
  }
  
  return config
}, error => {
  return Promise.reject(error)
})

// Response interceptor - standardizes error handling
axiosClient.interceptors.response.use(
  response => response.data,
  error => {
    const errorMessage = 
      error.response?.data?.message || 
      error.message || 
      'An unknown error occurred'
    
    // Create a standardized error object
    const enhancedError = new Error(errorMessage)

    enhancedError.status = error.response?.status
    enhancedError.data = error.response?.data
    enhancedError.originalError = error
    
    // Log errors in development
    if (process.env.NODE_ENV !== 'production') {
      console.error('API Error:', {
        message: errorMessage,
        status: error.response?.status,
        data: error.response?.data,
        url: error.config?.url,
        method: error.config?.method,
      })
    }
    
    return Promise.reject(enhancedError)
  },
)

// API service methods
export const apiClient = {
  /**
   * Make a GET request
   * @param {string} url - API endpoint URL
   * @param {Object} params - Query parameters
   * @param {Object} options - Additional axios options
   * @returns {Promise} - Response data
   */
  get: (url, params = {}, options = {}) => {
    return axiosClient.get(url, { params, ...options })
  },
  
  /**
   * Make a POST request
   * @param {string} url - API endpoint URL
   * @param {Object|FormData} data - Request payload
   * @param {Object} options - Additional axios options
   * @returns {Promise} - Response data
   */
  post: (url, data = {}, options = {}) => {
    return axiosClient.post(url, data, options)
  },
  
  /**
   * Make a PUT request
   * @param {string} url - API endpoint URL
   * @param {Object|FormData} data - Request payload
   * @param {Object} options - Additional axios options
   * @returns {Promise} - Response data
   */
  put: (url, data = {}, options = {}) => {
    return axiosClient.put(url, data, options)
  },
  
  /**
   * Make a PATCH request
   * @param {string} url - API endpoint URL
   * @param {Object|FormData} data - Request payload
   * @param {Object} options - Additional axios options
   * @returns {Promise} - Response data
   */
  patch: (url, data = {}, options = {}) => {
    return axiosClient.patch(url, data, options)
  },
  
  /**
   * Make a DELETE request
   * @param {string} url - API endpoint URL
   * @param {Object} options - Additional axios options
   * @returns {Promise} - Response data
   */
  delete: (url, options = {}) => {
    return axiosClient.delete(url, options)
  },
}

// Export the axios instance for direct use if needed
export default axiosClient 
