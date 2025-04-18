import { authRequest } from '@/utils/auth-helper'
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
        const response = await authRequest('get', '/api/profile')
        
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
        
        if (profileData.profilePicture instanceof File) {
          formData.append('profile_picture', profileData.profilePicture)
        }
        
        if (profileData.name) formData.append('name', profileData.name)
        if (profileData.email) formData.append('email', profileData.email)
        if (profileData.birthdate) formData.append('birthdate', profileData.birthdate)
        
        // Use authRequest with multipart/form-data config
        const response = await authRequest('post', '/api/profile', formData, {
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
        // Fetch current user data if profile is not already loaded
        if (!this.profile) {
          await this.fetchProfile()
        }
        
        // Include required profile fields to satisfy validation
        const response = await authRequest('post', '/api/profile', {
          name: this.profile.name,
          email: this.profile.email,
          birthdate: this.profile.birthdate,
          // eslint-disable-next-line camelcase
          current_password: passwordData.currentPassword,
          // eslint-disable-next-line camelcase
          new_password: passwordData.newPassword,
        })
        
        this.success = 'Password updated successfully'

        return response.data
      } catch (error) {
        if (error.response?.data?.errors?.current_password) {
          this.error = error.response.data.errors.current_password[0]
        } else {
          this.error = error.response?.data?.message || 'An error occurred while updating password'
        }
        throw error
      } finally {
        this.loading = false
      }
    },
  },
})
