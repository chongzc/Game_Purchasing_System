import { getCsrfToken } from './csrfToken'

/**
 * Utility for making API requests with consistent error handling and CSRF token
 */
export const apiService = {
  /**
   * Make a fetch request with proper headers and error handling
   * @param {string} url - API endpoint URL
   * @param {Object} options - Fetch options
   * @returns {Promise} - Response data
   */
  async fetch(url, options = {}) {
    // Get CSRF token
    const csrfToken = getCsrfToken()
    
    // Prepare headers with CSRF token
    const headers = {
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'application/json',
      ...(csrfToken ? { 'X-XSRF-TOKEN': csrfToken } : {}),
      ...(options.headers || {}),
    }
    
    try {
      const response = await fetch(url, {
        credentials: 'same-origin',
        ...options,
        headers,
      })
      
      if (!response.ok) {
        // Try to parse error response
        let errorMessage
        try {
          const errorData = await response.json()

          errorMessage = errorData.message || `${response.status} ${response.statusText}`
        } catch (e) {
          errorMessage = `${response.status} ${response.statusText}`
        }
        
        throw new Error(errorMessage)
      }
      
      // Check if response is empty
      const contentType = response.headers.get('content-type')
      if (contentType && contentType.includes('application/json')) {
        return await response.json()
      }
      
      return await response.text()
    } catch (error) {
      console.error('API request failed:', error)
      throw error
    }
  },
  

  async get(url, options = {}) {
    return this.fetch(url, {
      method: 'GET',
      ...options,
    })
  },
  

  async post(url, data, options = {}) {
    const headers = { ...options.headers }
    let body = data
    
    // Handle JSON data
    if (!(data instanceof FormData)) {
      headers['Content-Type'] = 'application/json'
      body = JSON.stringify(data)
    }
    
    return this.fetch(url, {
      method: 'POST',
      body,
      ...options,
      headers,
    })
  },
  

  async put(url, data, options = {}) {
    const headers = { ...options.headers }
    let body = data
    
    // Handle JSON data
    if (!(data instanceof FormData)) {
      headers['Content-Type'] = 'application/json'
      body = JSON.stringify(data)
    }
    
    return this.fetch(url, {
      method: 'PUT',
      body,
      ...options,
      headers,
    })
  },
  
  async delete(url, options = {}) {
    return this.fetch(url, {
      method: 'DELETE',
      ...options,
    })
  },
} 
