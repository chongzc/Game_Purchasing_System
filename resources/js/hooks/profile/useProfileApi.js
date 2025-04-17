import { profileService } from '@/@core/utils/profileService'
import { useMutation, useQuery } from '@tanstack/vue-query'
import { ref } from 'vue'

/**
 * Hook for fetching current user's profile
 */
export const useProfileQuery = () => {
  return useQuery(
    'profile',
    () => profileService.getProfile(),
    {
      enabled: true,
    },
  )
}

/**
 * Hook for fetching all users (admin)
 */
export const useAllUsersQuery = (options = {}) => {
  const enabled = ref(false)
  
  const query = useQuery(
    'users',
    () => profileService.getAllUsers(),
    {
      enabled: enabled.value,
      ...options,
    },
  )
  
  // Function to trigger the query when needed
  const fetchUsers = () => {
    enabled.value = true
  }
  
  return {
    ...query,
    fetchUsers,
  }
}

/**
 * Hook for fetching a specific user by ID
 */
export const useUserByIdQuery = (userId, options = {}) => {
  return useQuery(
    ['user', userId],
    () => profileService.getUserById(userId),
    options,
  )
}

/**
 * Hook for updating user profile
 */
export const useUpdateProfileMutation = () => {
  return useMutation(
    profileData => profileService.updateProfile(profileData),
    {
      invalidateQueries: 'profile', 
    },
  )
} 
