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
    
    async getUser() {
      this.loading = true
      
      try {
        // Get user profile data with profile image
        const response = await axios.get('/api/profile')
        
        // Update user data
        if (this.user) {
          this.user = {
            ...this.user,
            u_name: response.data.name,
            u_email: response.data.email,
            u_birthdate: response.data.birthdate,
            u_role: response.data.role,
            u_profilePic: response.data.profilePic,
          }
          
          localStorage.setItem('user', JSON.stringify(this.user))
        }
        
        return this.user
      } catch (error) {
        console.error('Get user error:', error)
        throw error
      } finally {
        this.loading = false
      }
    },
  },
}) 
