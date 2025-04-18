import axios from 'axios'

// Create an axios instance with credentials support
const axiosInstance = axios.create({
  baseURL: '/',
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
})

// Add a request interceptor to ensure XSRF token is included
axiosInstance.interceptors.request.use(
  config => {
    // Get the XSRF token from cookies if present
    const token = document.cookie
      .split('; ')
      .find(row => row.startsWith('XSRF-TOKEN='))
      ?.split('=')[1]

    if (token) {
      config.headers['X-XSRF-TOKEN'] = decodeURIComponent(token)
    }
    
    // Add X-Requested-With header for all requests
    config.headers['X-Requested-With'] = 'XMLHttpRequest'
    
    // Log request for debugging
    console.log(`Request to ${config.url}:`, { 
      headers: config.headers,
      method: config.method,
      data: config.data,
    })
    
    return config
  },
  error => {
    return Promise.reject(error)
  },
)

// Response interceptor for handling common errors
axiosInstance.interceptors.response.use(
  response => response,
  error => {
    // Handle 401 errors (Unauthenticated)
    if (error.response && error.response.status === 401) {
      console.error('Authentication error. Please log in again.')

      // You could redirect to login or dispatch an action to clear auth state
      localStorage.removeItem('user')
      window.location.href = '/login'
    }
    
    return Promise.reject(error)
  },
)

export default axiosInstance 
