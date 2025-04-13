import axios from 'axios'
import { defineStore } from 'pinia'

export const useUserProfileStore = defineStore('userProfile', {
  state: () => ({
    loading: false,
    error: null,
    success: null,
    profile: null,
  }),

  actions: {
    async fetchProfile() {
      this.loading = true
      this.error = null
      
      try {
        const response = await axios.get('/api/profile')
        
        // Normalize the data to use consistent field names
        this.profile = {
          name: response.data.name || response.data.u_name,
          email: response.data.email || response.data.u_email,
          birthdate: response.data.birthdate || response.data.u_birthdate,
          role: response.data.role || response.data.u_role,
          profilePic: response.data.profilePic,
        }

        return this.profile
      } catch (error) {
        this.error = error.response?.data?.message || 'An error occurred while fetching profile'
        throw error
      } finally {
        this.loading = false
      }
    },

    async updateProfile(profileData) {
      this.loading = true
      this.error = null
      this.success = null
      
      try {
        const formData = new FormData()
        
        if (profileData.profile_picture instanceof File) {
          formData.append('profile_picture', profileData.profile_picture)
        }
        
        if (profileData.name) formData.append('name', profileData.name)
        if (profileData.email) formData.append('email', profileData.email)
        if (profileData.birthdate) formData.append('birthdate', profileData.birthdate)
        
        const response = await axios.post('/api/profile', formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
          },
        })
        
        this.success = 'Profile updated successfully'
        await this.fetchProfile() // Refresh profile data after update

        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'An error occurred while updating profile'
        throw error
      } finally {
        this.loading = false
      }
    },

    async updatePassword(passwordData) {
      this.loading = true
      this.error = null
      this.success = null
      
      try {
        const response = await axios.post('/api/profile', {
          current_password: passwordData.currentPassword,
          new_password: passwordData.newPassword,
        })
        
        this.success = 'Password updated successfully'

        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'An error occurred while updating password'
        throw error
      } finally {
        this.loading = false
      }
    },
  },
})
