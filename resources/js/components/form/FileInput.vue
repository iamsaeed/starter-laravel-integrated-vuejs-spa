<template>
  <Field
    :id="id"
    :name="name"
    v-slot="{ field, errorMessage, meta, handleChange }"
    :rules="rules"
  >
    <div class="space-y-3">
      <!-- File Drop Zone -->
      <div
        :class="[
          'border-2 border-dashed rounded-lg p-6 text-center transition-all duration-200',
          {
            'border-gray-300 dark:border-gray-600 hover:border-primary-400 dark:hover:border-primary-500': !isDragActive && !errorMessage,
            'border-primary-500 bg-primary-50 dark:bg-primary-900/20': isDragActive,
            'border-red-500 bg-red-50 dark:bg-red-900/20': errorMessage,
            'border-green-500 bg-green-50 dark:bg-green-900/20': meta.valid && meta.touched && selectedFiles.length > 0,
            'opacity-50 cursor-not-allowed': disabled
          }
        ]"
        @dragover.prevent="handleDragOver"
        @dragleave.prevent="handleDragLeave"
        @drop.prevent="handleDrop"
        @click="!disabled && $refs.fileInput?.click()"
      >
        <input
          ref="fileInput"
          type="file"
          :accept="accept"
          :multiple="multiple"
          :disabled="disabled"
          class="hidden"
          @change="handleFileSelect"
        />
        
        <div class="space-y-2">
          <!-- Upload icon -->
          <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
          
          <div>
            <p class="text-sm text-gray-600 dark:text-gray-300">
              <span class="font-medium text-primary-600 dark:text-primary-400 hover:text-primary-500 cursor-pointer">
                Click to upload
              </span>
              or drag and drop
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
              {{ acceptText }}
              <span v-if="maxSize">(max {{ formatFileSize(maxSize) }})</span>
            </p>
          </div>
        </div>
      </div>

      <!-- Selected Files Display -->
      <div v-if="selectedFiles.length > 0" class="space-y-2">
        <h4 class="text-sm font-medium text-gray-900 dark:text-white">
          Selected Files ({{ selectedFiles.length }})
        </h4>
        
        <div class="space-y-2 max-h-40 overflow-y-auto">
          <div
            v-for="(file, index) in selectedFiles"
            :key="`${file.name}-${index}`"
            class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg"
          >
            <div class="flex items-center space-x-3 flex-1 min-w-0">
              <!-- File preview or icon -->
              <div class="flex-shrink-0">
                <img
                  v-if="file.preview && isImage(file)"
                  :src="file.preview"
                  :alt="file.name"
                  class="w-10 h-10 object-cover rounded"
                />
                <div
                  v-else
                  class="w-10 h-10 bg-gray-200 dark:bg-gray-600 rounded flex items-center justify-center"
                >
                  <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                </div>
              </div>
              
              <!-- File info -->
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                  {{ file.name }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                  {{ formatFileSize(file.size) }}
                </p>
              </div>
            </div>
            
            <!-- Remove button -->
            <button
              type="button"
              @click="removeFile(index)"
              class="ml-2 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-200 transition-colors duration-200"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <FormHelpText v-if="helpText" :text="helpText" />
    <FormError :error="errorMessage" />
  </Field>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Field } from 'vee-validate'
import FormError from './FormError.vue'
import FormHelpText from './FormHelpText.vue'

const props = defineProps({
  id: {
    type: String,
    required: false,
    default: null
  },
  name: {
    type: String,
    required: true
  },
  disabled: {
    type: Boolean,
    default: false
  },
  rules: {
    type: [String, Function, Object],
    default: null
  },
  helpText: {
    type: String,
    default: ''
  },
  accept: {
    type: String,
    default: '*'
  },
  multiple: {
    type: Boolean,
    default: false
  },
  maxSize: {
    type: Number,
    default: null // in bytes
  },
  preview: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['filesSelected', 'fileRemoved'])

// State
const selectedFiles = ref([])
const isDragActive = ref(false)
const fileInput = ref(null)

// Computed
const acceptText = computed(() => {
  if (props.accept === '*') return 'Any file type'
  
  const types = props.accept.split(',').map(type => type.trim())
  if (types.includes('image/*')) return 'Images'
  if (types.includes('.pdf')) return 'PDF files'
  
  return types.join(', ')
})

// Methods
const handleDragOver = () => {
  if (!props.disabled) {
    isDragActive.value = true
  }
}

const handleDragLeave = () => {
  isDragActive.value = false
}

const handleDrop = (event) => {
  isDragActive.value = false
  if (props.disabled) return
  
  const files = Array.from(event.dataTransfer.files)
  processFiles(files)
}

const handleFileSelect = (event) => {
  const files = Array.from(event.target.files)
  processFiles(files)
}

const processFiles = async (files) => {
  const validFiles = []
  const previewPromises = []
  
  // Process files in parallel for better performance
  const fileProcessingPromises = files.map(async (file) => {
    // Validate file size
    if (props.maxSize && file.size > props.maxSize) {
      return null // Skip files that are too large
    }
    
    // Create file object
    const fileObj = {
      name: file.name,
      size: file.size,
      type: file.type,
      file: file,
      id: `${file.name}-${file.size}-${Date.now()}` // Unique ID for tracking
    }
    
    // Queue preview generation for images (non-blocking)
    if (props.preview && isImage(file)) {
      const previewPromise = generatePreview(file)
        .then(preview => {
          fileObj.preview = preview
          return fileObj
        })
        .catch(error => {
          console.warn(`Failed to generate preview for ${file.name}:`, error)
          return fileObj
        })
      
      previewPromises.push(previewPromise)
      return previewPromise
    }
    
    return fileObj
  })
  
  // Wait for all file processing to complete
  const processedFiles = await Promise.all(fileProcessingPromises)
  const validProcessedFiles = processedFiles.filter(file => file !== null)
  
  // Update selected files
  if (props.multiple) {
    selectedFiles.value = [...selectedFiles.value, ...validProcessedFiles]
  } else {
    selectedFiles.value = validProcessedFiles.slice(0, 1)
  }
  
  emit('filesSelected', selectedFiles.value)
  
  // If there are preview promises still pending, update previews as they complete
  if (previewPromises.length > 0) {
    Promise.allSettled(previewPromises).then(results => {
      // Force reactivity update for any previews that completed after initial render
      selectedFiles.value = [...selectedFiles.value]
    })
  }
}

const removeFile = (index) => {
  const removedFile = selectedFiles.value[index]
  selectedFiles.value.splice(index, 1)
  emit('fileRemoved', removedFile, selectedFiles.value)
}

const isImage = (file) => {
  return file.type && file.type.startsWith('image/')
}

// Optimized preview generation with caching and resizing
const previewCache = new Map()
const generatePreview = (file) => {
  // Check cache first
  const cacheKey = `${file.name}-${file.size}-${file.lastModified}`
  if (previewCache.has(cacheKey)) {
    return Promise.resolve(previewCache.get(cacheKey))
  }

  return new Promise((resolve, reject) => {
    const reader = new FileReader()
    
    reader.onload = (e) => {
      const img = new Image()
      img.onload = () => {
        // Create canvas for resizing
        const canvas = document.createElement('canvas')
        const ctx = canvas.getContext('2d')
        
        // Calculate dimensions for thumbnail (max 200x200)
        const maxSize = 200
        let { width, height } = img
        
        if (width > height) {
          if (width > maxSize) {
            height = (height * maxSize) / width
            width = maxSize
          }
        } else {
          if (height > maxSize) {
            width = (width * maxSize) / height
            height = maxSize
          }
        }
        
        canvas.width = width
        canvas.height = height
        
        // Draw resized image
        ctx.drawImage(img, 0, 0, width, height)
        
        // Get optimized data URL
        const preview = canvas.toDataURL('image/jpeg', 0.8)
        
        // Cache the result
        previewCache.set(cacheKey, preview)
        
        // Cleanup
        canvas.remove()
        
        resolve(preview)
      }
      
      img.onerror = () => reject(new Error('Failed to load image'))
      img.src = e.target.result
    }
    
    reader.onerror = () => reject(new Error('Failed to read file'))
    reader.readAsDataURL(file)
  })
}

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}
</script>