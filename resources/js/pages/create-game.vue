<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const gameData = ref({
  title: '',
  description: '',
  price: '',
  genre: '',
  releaseDate: '',
  platform: [],
  features: '',
  systemRequirements: {
    minimum: {
      os: '',
      processor: '',
      memory: '',
      graphics: '',
      storage: '',
    },
    recommended: {
      os: '',
      processor: '',
      memory: '',
      graphics: '',
      storage: '',
    },
  },
  coverImage: null,
  screenshots: [],
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

const platforms = [
  'Windows',
  'macOS',
  'Linux',
  'Android',
  'iOS',
]

const handleImageUpload = (event, type) => {
  const file = event.target.files[0]
  if (type === 'cover') {
    gameData.value.coverImage = file
  } else if (type === 'screenshots') {
    gameData.value.screenshots.push(file)
  }
}

const handleSubmit = async () => {
  try {
    // TODO: Implement API call to save game
    console.log('Game data:', gameData.value)
    router.push('/developer-dashboard')
  } catch (error) {
    console.error('Error creating game:', error)
  }
}
</script>

<template>
  <VContainer>
    <VRow>
      <VCol cols="12">
        <VCard>
          <VCardTitle class="text-h5 mb-4">
            Create New Game
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
                    prefix="$"
                    required
                    :rules="[v => !!v || 'Price is required']"
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
                  <VTextField
                    v-model="gameData.releaseDate"
                    label="Release Date"
                    type="date"
                    required
                    :rules="[v => !!v || 'Release date is required']"
                  />
                </VCol>
                
                <VCol cols="12">
                  <VSelect
                    v-model="gameData.platform"
                    :items="platforms"
                    label="Platforms"
                    multiple
                    chips
                    required
                    :rules="[v => v.length > 0 || 'At least one platform is required']"
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
                
                <VCol cols="12">
                  <VTextarea
                    v-model="gameData.features"
                    label="Key Features"
                    required
                    :rules="[v => !!v || 'Features are required']"
                    rows="4"
                  />
                </VCol>
                
                <!-- System Requirements -->
                <VCol cols="12">
                  <VCard variant="outlined">
                    <VCardTitle>System Requirements</VCardTitle>
                    <VCardText>
                      <VRow>
                        <VCol
                          cols="12"
                          md="6"
                        >
                          <VCard variant="outlined">
                            <VCardTitle class="text-subtitle-1">
                              Minimum Requirements
                            </VCardTitle>
                            <VCardText>
                              <VTextField
                                v-model="gameData.systemRequirements.minimum.os"
                                label="Operating System"
                                required
                              />
                              <VTextField
                                v-model="gameData.systemRequirements.minimum.processor"
                                label="Processor"
                                required
                              />
                              <VTextField
                                v-model="gameData.systemRequirements.minimum.memory"
                                label="Memory"
                                required
                              />
                              <VTextField
                                v-model="gameData.systemRequirements.minimum.graphics"
                                label="Graphics"
                                required
                              />
                              <VTextField
                                v-model="gameData.systemRequirements.minimum.storage"
                                label="Storage"
                                required
                              />
                            </VCardText>
                          </VCard>
                        </VCol>
                        
                        <VCol
                          cols="12"
                          md="6"
                        >
                          <VCard variant="outlined">
                            <VCardTitle class="text-subtitle-1">
                              Recommended Requirements
                            </VCardTitle>
                            <VCardText>
                              <VTextField
                                v-model="gameData.systemRequirements.recommended.os"
                                label="Operating System"
                                required
                              />
                              <VTextField
                                v-model="gameData.systemRequirements.recommended.processor"
                                label="Processor"
                                required
                              />
                              <VTextField
                                v-model="gameData.systemRequirements.recommended.memory"
                                label="Memory"
                                required
                              />
                              <VTextField
                                v-model="gameData.systemRequirements.recommended.graphics"
                                label="Graphics"
                                required
                              />
                              <VTextField
                                v-model="gameData.systemRequirements.recommended.storage"
                                label="Storage"
                                required
                              />
                            </VCardText>
                          </VCard>
                        </VCol>
                      </VRow>
                    </VCardText>
                  </VCard>
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
                    required
                    :rules="[v => !!v || 'Cover image is required']"
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
                    required
                    :rules="[v => v.length > 0 || 'At least one screenshot is required']"
                    @change="handleImageUpload($event, 'screenshots')"
                  />
                </VCol>
                
                <!-- Submit Button -->
                <VCol
                  cols="12"
                  class="d-flex justify-end"
                >
                  <VBtn
                    color="primary"
                    type="submit"
                    size="large"
                  >
                    Create Game
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
