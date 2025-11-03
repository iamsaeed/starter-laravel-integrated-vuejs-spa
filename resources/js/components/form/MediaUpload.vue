<template>
  <div class="media-upload">
    <!-- Label -->
    <label v-if="label" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>

    <!-- Current Media Display -->
    <div v-if="currentMedia && !uploading" class="mb-4">
      <div class="relative inline-block">
        <img
          :src="currentMedia.thumbnail || currentMedia.url"
          :alt="label"
          :class="[
            'object-cover border-2 border-gray-300 dark:border-gray-600',
            rounded ? 'rounded-full' : 'rounded-lg',
            sizeClass
          ]"
        />
        <button
          v-if="!disabled && editable"
          @click="handleEdit"
          type="button"
          class="absolute -bottom-2 -left-2 bg-primary-600 hover:bg-primary-700 text-white rounded-full p-1 shadow-lg transition-colors duration-200"
          title="Edit Image"
        >
          <Icon name="edit" :size="16" />
        </button>
        <button
          v-if="!disabled"
          @click="handleRemove"
          type="button"
          class="absolute -top-2 -right-2 bg-red-500 hover:bg-red-600 text-white rounded-full p-1 shadow-lg transition-colors duration-200"
        >
          <Icon name="close" :size="16" />
        </button>
      </div>
    </div>

    <!-- Upload Area -->
    <div
      v-if="!currentMedia || uploading"
      @click="triggerFileInput"
      @dragover.prevent="dragOver = true"
      @dragleave.prevent="dragOver = false"
      @drop.prevent="handleDrop"
      :class="[
        'border-2 border-dashed rounded-lg p-6 text-center cursor-pointer transition-all duration-200',
        dragOver
          ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20'
          : 'border-gray-300 dark:border-gray-600 hover:border-primary-400 dark:hover:border-primary-500',
        disabled ? 'opacity-50 cursor-not-allowed' : '',
        uploading ? 'pointer-events-none' : ''
      ]"
    >
      <input
        ref="fileInput"
        type="file"
        :accept="acceptedTypes.join(',')"
        :multiple="multiple"
        @change="handleFileSelect"
        class="hidden"
        :disabled="disabled"
      />

      <div v-if="uploading" class="flex flex-col items-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600 mb-3"></div>
        <p class="text-sm text-gray-600 dark:text-gray-400">Uploading...</p>
      </div>

      <div v-else class="flex flex-col items-center">
        <Icon name="upload" :size="48" class="text-gray-400 dark:text-gray-500 mb-3" />
        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
          {{ multiple ? 'Drop files here or click to browse' : 'Drop file here or click to browse' }}
        </p>
        <p class="text-xs text-gray-500 dark:text-gray-400">
          {{ acceptedTypesText }}
          <span v-if="maxFileSize">(max {{ maxFileSize }}MB)</span>
        </p>
      </div>
    </div>

    <!-- Help Text -->
    <p v-if="helpText" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
      {{ helpText }}
    </p>

    <!-- Error Message -->
    <p v-if="error" class="mt-1 text-sm text-red-600 dark:text-red-400">
      {{ error }}
    </p>

    <!-- Image Editor Modal -->
    <ImageEditor
      v-if="editable"
      :show="showEditor"
      :image-src="editorImageSrc"
      :options="editorOptions"
      @close="showEditor = false"
      @save="handleEditorSave"
    />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { mediaService } from '@/services/mediaService'
import Icon from '@/components/common/Icon.vue'
import ImageEditor from '@/components/common/ImageEditor.vue'

const props = defineProps({
  modelValue: {
    type: [Object, Array, null],
    default: null
  },
  label: {
    type: String,
    default: ''
  },
  helpText: {
    type: String,
    default: ''
  },
  required: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  multiple: {
    type: Boolean,
    default: false
  },
  collection: {
    type: String,
    default: 'default'
  },
  acceptedTypes: {
    type: Array,
    default: () => ['image/*']
  },
  maxFileSize: {
    type: Number,
    default: null // in MB
  },
  rounded: {
    type: Boolean,
    default: false
  },
  previewWidth: {
    type: Number,
    default: 128
  },
  previewHeight: {
    type: Number,
    default: 128
  },
  modelType: {
    type: String,
    required: true
  },
  modelId: {
    type: [Number, String],
    required: true
  },
  editable: {
    type: Boolean,
    default: false
  },
  editorOptions: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['update:modelValue', 'uploaded', 'removed', 'error'])

const fileInput = ref(null)
const dragOver = ref(false)
const uploading = ref(false)
const error = ref(null)
const showEditor = ref(false)
const editorImageSrc = ref('')
const pendingFile = ref(null)

const currentMedia = computed(() => props.modelValue)

const sizeClass = computed(() => {
  return `w-[${props.previewWidth}px] h-[${props.previewHeight}px]`
})

const acceptedTypesText = computed(() => {
  const types = props.acceptedTypes.map(type => {
    if (type === 'image/*') return 'Images'
    if (type.startsWith('image/')) return type.split('/')[1].toUpperCase()
    if (type === 'application/pdf') return 'PDF'
    return type
  })
  return types.join(', ')
})

function triggerFileInput() {
  if (!props.disabled && !uploading.value) {
    fileInput.value?.click()
  }
}

function handleFileSelect(event) {
  const files = Array.from(event.target.files)
  if (files.length > 0) {
    if (props.editable && files.length === 1 && files[0].type.startsWith('image/')) {
      openEditor(files[0])
    } else {
      uploadFiles(files)
    }
  }
}

function handleDrop(event) {
  dragOver.value = false
  if (props.disabled || uploading.value) return

  const files = Array.from(event.dataTransfer.files)
  if (files.length > 0) {
    if (props.editable && files.length === 1 && files[0].type.startsWith('image/')) {
      openEditor(files[0])
    } else {
      uploadFiles(files)
    }
  }
}

function openEditor(file) {
  pendingFile.value = file

  const reader = new FileReader()
  reader.onload = (e) => {
    editorImageSrc.value = e.target.result
    showEditor.value = true
  }
  reader.readAsDataURL(file)
}

function handleEdit() {
  if (!currentMedia.value?.url) return

  editorImageSrc.value = currentMedia.value.url
  showEditor.value = true
}

async function handleEditorSave({ blob, options }) {
  showEditor.value = false

  // Convert blob to file with original filename
  const filename = pendingFile.value?.name || `edited-${Date.now()}.jpg`
  const file = new File([blob], filename, { type: 'image/jpeg' })

  // Upload the edited image
  await uploadFiles([file])

  pendingFile.value = null
}

async function uploadFiles(files) {
  error.value = null

  // Validate file size
  if (props.maxFileSize) {
    const maxBytes = props.maxFileSize * 1024 * 1024
    for (const file of files) {
      if (file.size > maxBytes) {
        error.value = `File size must be less than ${props.maxFileSize}MB`
        emit('error', error.value)
        return
      }
    }
  }

  // Validate file type
  for (const file of files) {
    const isValidType = props.acceptedTypes.some(type => {
      if (type.endsWith('/*')) {
        const baseType = type.split('/')[0]
        return file.type.startsWith(baseType + '/')
      }
      return file.type === type
    })

    if (!isValidType) {
      error.value = `Invalid file type. Accepted types: ${acceptedTypesText.value}`
      emit('error', error.value)
      return
    }
  }

  uploading.value = true

  try {
    if (props.multiple) {
      const result = await mediaService.uploadMultiple(
        files,
        props.modelType,
        props.modelId,
        props.collection
      )
      emit('update:modelValue', result.data)
      emit('uploaded', result.data)
    } else {
      const result = await mediaService.upload(
        files[0],
        props.modelType,
        props.modelId,
        props.collection
      )
      emit('update:modelValue', result.data)
      emit('uploaded', result.data)
    }

    // Reset file input
    if (fileInput.value) {
      fileInput.value.value = ''
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Upload failed'
    emit('error', error.value)
  } finally {
    uploading.value = false
  }
}

async function handleRemove() {
  if (!currentMedia.value?.id) {
    emit('update:modelValue', null)
    emit('removed', null)
    return
  }

  try {
    await mediaService.delete(currentMedia.value.id)
    emit('update:modelValue', null)
    emit('removed', currentMedia.value.id)
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to delete file'
    emit('error', error.value)
  }
}
</script>
