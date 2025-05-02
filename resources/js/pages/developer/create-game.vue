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
  'Horror',
  'Fighting',
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
      errorMessage.value = 'Failed to load game data for editing'
    }
  }
})

const handleImageUpload = (event, type) => {
  if (type === 'cover') {
    gameData.value.coverImage = event.target.files[0]
  } else if (type === 'screenshots' && event.target.files.length > 0) {
    // Clear the screenshots array first if it's a new selection
    gameData.value.screenshots = []

    // Add all selected files to the screenshots array
    for (let i = 0; i < event.target.files.length; i++) {
      gameData.value.screenshots.push(event.target.files[i])
    }
  }
}

// Add message state refs
const successMessage = ref('')
const errorMessage = ref('')

const handleSubmit = async () => {
  // Clear previous messages
  successMessage.value = ''
  errorMessage.value = ''
  
  // Check if all required fields are filled
  if (!gameData.value.title || 
      !gameData.value.description || 
      !gameData.value.price || 
      !gameData.value.genre || 
      !gameData.value.language ||
      (!isEditing.value && !gameData.value.coverImage)) {
    errorMessage.value = 'Please fill in all required fields'
    
    return
  }

  try {
    await axios.get('/sanctum/csrf-cookie')
    
    const formData = new FormData()
    
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
    
    const developerId = 1

    formData.append('g_developerId', developerId)
    
    const categoryId = genres.indexOf(gameData.value.genre) + 1

    formData.append('categories[]', categoryId)
    
    // Only append the coverImage file if it actually exists and is a File object
    if (gameData.value.coverImage && gameData.value.coverImage instanceof File) {
      console.log('Appending cover image:', gameData.value.coverImage.name)
      formData.append('g_mainImage', gameData.value.coverImage)
    }
    
    // Handle screenshots
    if (gameData.value.screenshots.length > 0) {
      gameData.value.screenshots.forEach((file, index) => {
        if (file instanceof File) {
          console.log(`Appending screenshot ${index + 1}:`, file.name)
          formData.append(`g_exImg${index + 1}`, file)
        }
      })
    }
    
    let response
    
    if (isEditing.value) {
      formData.append('_method', 'PUT')
      response = await axios.post(`/api/games/${gameId.value}`, formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      })
      successMessage.value = 'Game updated successfully! It will be reviewed by an admin.'
    } else {
      response = await axios.post('/api/games', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      })
      successMessage.value = 'Game submitted successfully! It will be reviewed by an admin.'
    }
    
    console.log('Server response:', response.data)
    
    setTimeout(() => {
      router.push('/developer-dashboard')
    }, 2000)
  } catch (error) {
    console.error('Error:', error)
    errorMessage.value = error.response?.data?.error || 
                       error.response?.data?.message || 
                       error.message
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
            <!-- Success and Error Messages -->
            <VAlert
              v-if="successMessage"
              type="success"
              variant="tonal"
              closable
              class="mb-4"
            >
              {{ successMessage }}
            </VAlert>
            
            <VAlert
              v-if="errorMessage"
              type="error"
              variant="tonal"
              closable
              class="mb-4"
            >
              {{ errorMessage }}
            </VAlert>
            
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
                    prefix="$"
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
                    :rules="[v => isEditing ? true : (v.length > 0 && v.length <= 1) || 'Cover image is required']"
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
                    :rules="[v => isEditing ? true : (v.length <= 3) || 'Maximum 3 screenshots are allowed']"
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


