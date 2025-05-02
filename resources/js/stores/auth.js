import axios from '@/utils/axios'
import { setCookie } from '@/utils/cookie'
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

        // Save email and password as cookies if "remember" is checked
        if (remember) {
          setCookie('email', email, 7) // Save for 7 days
          setCookie('password', password, 7) // Save for 7 days
        }

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

        // Do NOT delete email and password cookies to keep them for relogin
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

    async refreshUserData() {
      try {
        const response = await axios.get('/api/profile')
        const userData = response.data
        
        // Update the user data in the store
        this.user = {
          ...this.user, // Keep existing properties
          ...userData, // Update with new properties from the API
        }
        
        // Update stored user data
        localStorage.setItem('user', JSON.stringify(this.user))
        
        return userData
      } catch (error) {
        console.error('Failed to refresh user data:', error)
        throw error
      }
    },
  },
})
