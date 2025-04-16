import axios from '@/utils/axios'
import { defineStore } from 'pinia'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('user')) || null,
    isLoggedIn: !!localStorage.getItem('user'),
    loading: false,
    error: null,
  }),
  
  getters: {
    isUser: state => state.user?.u_role === 'user',
    isDeveloper: state => state.user?.u_role === 'developer',
    isAdmin: state => state.user?.u_role === 'admin',
    userRole: state => state.user?.u_role || null,
  },
  
  actions: {
    async login(email, password, remember) {
      this.loading = true
      this.error = null
      
      try {
        // Get CSRF cookie first
        await axios.get('/sanctum/csrf-cookie')
        
        const response = await axios.post('/api/login', {
          email,
          password,
          remember,
        })
        
        this.user = response.data.user
        this.isLoggedIn = true
        localStorage.setItem('user', JSON.stringify(response.data.user))
        
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Login failed'
        throw error
      } finally {
        this.loading = false  
      }
    },
    
    async register(userData) {
      this.loading = true
      this.error = null
      
      try {
        // Get CSRF cookie first
        await axios.get('/sanctum/csrf-cookie')
        
        const response = await axios.post('/api/register', userData)
        
        this.user = response.data.user
        this.isLoggedIn = true
        localStorage.setItem('user', JSON.stringify(response.data.user))
        
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Registration failed'
        throw error
      } finally {
        this.loading = false
      }
    },
    
    async logout() {
      this.loading = true
      
      try {
        await axios.post('/api/logout')
      } catch (error) {
        console.error('Logout error:', error)
      } finally {
        this.user = null
        this.isLoggedIn = false
        localStorage.removeItem('user')
        this.loading = false
      }
    },
    
    async checkAuth() {
      if (this.isLoggedIn) return
      
      try {
        const response = await axios.get('/api/user')
        
        if (response.data.isLoggedIn && response.data.user) {
          this.user = response.data.user
          this.isLoggedIn = true
          localStorage.setItem('user', JSON.stringify(response.data.user))
        }
      } catch (error) {
        console.error('Auth check error:', error)
        this.user = null
        this.isLoggedIn = false
        localStorage.removeItem('user')
      }
    },
    
    async getUserData() {
      this.loading = true
      this.error = null
      
      try {
        const response = await axios.get('/api/user/profile')
        
        // Log the response
        console.log('getUserData response:', response.data)
        
        // Update the user data in the store and localStorage
        if (response.data) {
          this.user = response.data
          this.isLoggedIn = true
          localStorage.setItem('user', JSON.stringify(response.data))
        }
        
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch user data'
        console.error('Error fetching user data:', error)
        throw error
      } finally {
        this.loading = false
      }
    },
  },
}) 
