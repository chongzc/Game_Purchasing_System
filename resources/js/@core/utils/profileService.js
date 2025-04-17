import { apiClient } from './axiosClient'

/**
 * Service for handling profile-related API operations
 */
export const profileService = {

  async getProfile() {
    return apiClient.get('/api/profile')
  },
  
  /**
   * Update user profile
   * @param {Object} profileData - Profile data to update
   * @returns {Promise} Updated profile data
   */
  async updateProfile(profileData) {
    const formData = new FormData()
    
    // Handle profile picture if it's a File object
    if (profileData.profilePicture instanceof File) {
      formData.append('profile_picture', profileData.profilePicture)
    }
    
    // Add other profile fields
    if (profileData.name) formData.append('name', profileData.name)
    if (profileData.email) formData.append('email', profileData.email)
    if (profileData.birthdate) formData.append('birthdate', profileData.birthdate)
    
    return apiClient.post('/api/profile', formData)
  },
  

  async getAllUsers() {
    return apiClient.get('/api/users')
  },
  

  async getUserById(userId) {
    return apiClient.get(`/api/users/${userId}`)
  },
} 
