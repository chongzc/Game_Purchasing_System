<script setup>
import axios from 'axios'
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const router = useRouter()
const route = useRoute()
const isEditing = ref(false)
const gameId = ref(null)

// At the beginning of your script section, configure axios globally
axios.defaults.withCredentials = true

const gameData = ref({
  title: '',
  description: '',
  price: '',
  discount: '',
  genre: '',
  language: '',
  coverImage: null,
  screenshots: [],
  status: 'pending',
})

const genres = [
  'Action',
  'Adventure',
  'RPG',
  'Strategy',
  'Sports',
  'Racing',
  'Simulation',
  'Puzzle',
  'Platformer',
  'Fighting',
  'Shooter',
]

const languages = [
  'English',
  'Spanish',
  'French',
  'German',
  'Chinese',
  'Japanese',
  'Korean',
  'Portuguese',
  'Russian',
  'Multiple Languages',
]

// Load game data if editing
onMounted(async () => {
  // Check if we're in edit mode by looking for an id parameter
  if (route.params.id) {
    gameId.value = route.params.id
    isEditing.value = true
    
    try {
      const response = await axios.get(`/api/games/${gameId.value}/edit`)
      if (response.data.success) {
        const game = response.data.game
        
        // Map the game data to the form
        gameData.value.title = game.title
        gameData.value.description = game.description
        gameData.value.price = game.price
        gameData.value.discount = game.discount
        gameData.value.genre = game.genre
        gameData.value.language = game.language
        gameData.value.status = game.status
        
        // We don't set the cover image and screenshots because they're files
        // and we can't load them back into input elements
      }
    } catch (error) {
      console.error('Failed to load game data:', error)
      alert('Failed to load game data for editing')
    }
  }
})

const handleImageUpload = (event, type) => {
  const file = event.target.files[0]
  if (type === 'cover') {
    gameData.value.coverImage = file
  } else if (type === 'screenshots') {
    gameData.value.screenshots.append(file)
  }
}

const handleSubmit = async () => {
  try {
    console.log('Game data:', gameData.value)
    
    // Get a fresh CSRF token before submitting
    await axios.get('/sanctum/csrf-cookie')
    
    // Create FormData object to handle file uploads
    const formData = new FormData()
    
    // Use the correct field names that match the backend expectations
    formData.append('g_title', gameData.value.title)
    formData.append('g_description', gameData.value.description)
    formData.append('g_price', gameData.value.price)
    formData.append('g_discount', gameData.value.discount || 0)
    formData.append('g_language', gameData.value.language)
  
    if (!isEditing.value || gameData.value.status !== 'verified') {
      formData.append('g_status', 'pending')
    } else {
      formData.append('g_status', gameData.value.status)
    }
    
    formData.append('g_category', gameData.value.genre)
    
    // Get the authenticated user's ID from the session/auth system
    // Since we're using a hardcoded value for now, make sure it's sent as a number, not null
    const developerId = 1 // In a real app, this would come from the auth system

    formData.append('g_developerId', developerId)
    
    // Category for the relationship table
    const categoryId = genres.indexOf(gameData.value.genre) + 1

    formData.append('categories[]', categoryId)
    
    // Add cover image
    if (gameData.value.coverImage) {
      formData.append('g_mainImage', gameData.value.coverImage)
    }
    
    // Add screenshots as extra images
    if (gameData.value.screenshots.length > 0) {
      if (gameData.value.screenshots[0]) {
        formData.append('g_exImg1', gameData.value.screenshots[0])
      }
      if (gameData.value.screenshots[1]) {
        formData.append('g_exImg2', gameData.value.screenshots[1])
      }
      if (gameData.value.screenshots[2]) {
        formData.append('g_exImg3', gameData.value.screenshots[2])
      }
    }
    
    console.log('Game data ready for submission:', Object.fromEntries(formData.entries()))
    
    try {
      let response
      
      if (isEditing.value) {
        // Add a _method field to simulate a PUT request
        formData.append('_method', 'PUT')
        
        // Send to update endpoint
        response = await axios.post(`/api/games/${gameId.value}`, formData)
        alert('Game updated successfully! It will be reviewed by an admin.')
      } else {
        // Create new game
        response = await axios.post('/api/games', formData)
        alert('Game submitted successfully! It will be reviewed by an admin.')
      }
      
      console.log('Game action completed successfully:', response.data)
      
      // Redirect to developer dashboard
      router.push('/developer-dashboard')
    } catch (axiosError) {
      console.error('Server response:', axiosError.response?.data)
      
      const errorMessage = axiosError.response?.data?.error || 
                          axiosError.response?.data?.message || 
                          axiosError.message
      
      alert(`Failed to ${isEditing.value ? 'update' : 'create'} game: ${errorMessage}`)
    }
  } catch (error) {
    console.error('General error:', error)
    alert(`Error: ${error.message}`)
  }
}

const navigateToDashboard = () => {
  router.push('/developer-dashboard')
}
</script>

<template>
  <VContainer>
    <VRow>
      <VCol cols="12">
        <VCard>
          <VCardTitle
            v-if="!isEditing"
            class="text-h5 mb-4"
          >
            Create New Game
          </VCardTitle>
          <VCardTitle
            v-else
            class="text-h5 mb-4"
          >
            Edit Game
          </VCardTitle>

          <VCardText>
            <VForm @submit.prevent="handleSubmit">
              <VRow>
                <!-- Basic Information -->
                <VCol
                  cols="12"
                  md="6"
                >
                  <VTextField
                    v-model="gameData.title"
                    label="Game Title"
                    required
                    :rules="[v => !!v || 'Title is required']"
                  />
                </VCol>
                
                <VCol
                  cols="12"
                  md="6"
                >
                  <VTextField
                    v-model="gameData.price"
                    label="Price"
                    type="number"
                    prefix="RM"
                    required
                    :rules="[v => !!v || 'Price is required']"
                  />
                </VCol>
                
                <VCol
                  cols="12"
                  md="6"
                >
                  <VTextField
                    v-model="gameData.discount"
                    label="Discount"
                    type="number"
                    suffix="%"
                    min="0"
                    max="100"
                    hint="Enter a discount percentage (0-100)"
                  />
                </VCol>
                
                <VCol
                  cols="12"
                  md="6"
                >
                  <VSelect
                    v-model="gameData.genre"
                    :items="genres"
                    label="Genre"
                    required
                    :rules="[v => !!v || 'Genre is required']"
                  />
                </VCol>
                
                <VCol
                  cols="12"
                  md="6"
                >
                  <VSelect
                    v-model="gameData.language"
                    :items="languages"
                    label="Language"
                    required
                    :rules="[v => !!v || 'Language is required']"
                  />
                </VCol>
                             
                <VCol cols="12">
                  <VTextarea
                    v-model="gameData.description"
                    label="Game Description"
                    required
                    :rules="[v => !!v || 'Description is required']"
                    rows="4"
                  />
                </VCol>
                
                <!-- Media Upload -->
                <VCol
                  cols="12"
                  md="6"
                >
                  <VFileInput
                    v-model="gameData.coverImage"
                    label="Cover Image"
                    accept="image/*"
                    :required="!isEditing"
                    :rules="[v => isEditing ? true : !!v || 'Cover image is required']"
                    @change="handleImageUpload($event, 'cover')"
                  />
                </VCol>
                
                <VCol
                  cols="12"
                  md="6"
                >
                  <VFileInput
                    v-model="gameData.screenshots"
                    label="Screenshots"
                    accept="image/*"
                    multiple
                    :required="!isEditing"
                    :rules="[v => isEditing ? true : v.length > 0 || 'At least one screenshot is required']"
                    @change="handleImageUpload($event, 'screenshots')"
                  />
                </VCol>
                
                <!-- Submit Button -->
                <VCol
                  cols="12"
                  class="d-flex justify-end gap-3"
                >
                  <VBtn
                    color="secondary"
                    size="large"
                    @click="navigateToDashboard"
                  >
                    Cancel
                  </VBtn>
                  <VBtn
                    color="primary"
                    type="submit"
                    size="large"
                  >
                    {{ isEditing ? 'Update Game' : 'Create Game' }}
                  </VBtn>
                </VCol>
              </VRow>
            </VForm>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </VContainer>
</template> 


