import axios from 'axios'

// Create an axios instance with credentials support
const axiosInstance = axios.create({
  baseURL: '/',
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'Content-Type': 'application/json',
  },
})

export default axiosInstance 
