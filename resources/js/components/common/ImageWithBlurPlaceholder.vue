<template>
  <div class="image-with-blur-placeholder" :class="containerClass">
    <!-- Blur Placeholder (shown while loading) -->
    <img
      v-if="blurPlaceholder && !isLoaded"
      :src="blurPlaceholder"
      :alt="alt"
      class="blur-placeholder"
      :class="placeholderClass"
      aria-hidden="true"
    />

    <!-- Actual Image -->
    <img
      ref="imageRef"
      :src="src"
      :alt="alt"
      :class="imageClass"
      :style="imageStyle"
      @load="handleLoad"
      @error="handleError"
    />

    <!-- Loading Skeleton (fallback if no blur placeholder) -->
    <div
      v-if="!blurPlaceholder && !isLoaded && !hasError"
      class="loading-skeleton"
      :class="skeletonClass"
    >
      <Icon name="image" class="w-8 h-8 text-gray-400 dark:text-gray-600" />
    </div>

    <!-- Error State -->
    <div
      v-if="hasError"
      class="error-state"
      :class="errorClass"
    >
      <Icon name="image-off" class="w-8 h-8 text-gray-400 dark:text-gray-600" />
      <span v-if="showErrorMessage" class="error-message">Failed to load image</span>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import Icon from '@/components/common/Icon.vue'

const props = defineProps({
  // Image source
  src: {
    type: String,
    required: true
  },

  // Blur placeholder (base64 data URI or URL)
  blurPlaceholder: {
    type: String,
    default: null
  },

  // Alt text for accessibility
  alt: {
    type: String,
    default: ''
  },

  // Transition duration in ms
  transitionDuration: {
    type: Number,
    default: 300
  },

  // Custom classes
  containerClass: {
    type: String,
    default: ''
  },
  imageClass: {
    type: String,
    default: ''
  },
  placeholderClass: {
    type: String,
    default: ''
  },
  skeletonClass: {
    type: String,
    default: ''
  },
  errorClass: {
    type: String,
    default: ''
  },

  // Options
  showErrorMessage: {
    type: Boolean,
    default: true
  },
  eager: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['load', 'error'])

const imageRef = ref(null)
const isLoaded = ref(false)
const hasError = ref(false)

const imageStyle = computed(() => {
  return {
    opacity: isLoaded.value ? 1 : 0,
    transition: `opacity ${props.transitionDuration}ms ease-in-out`
  }
})

// Watch for src changes to reset state
watch(() => props.src, () => {
  isLoaded.value = false
  hasError.value = false
})

function handleLoad() {
  isLoaded.value = true
  emit('load')
}

function handleError(event) {
  hasError.value = true
  emit('error', event)
}

onMounted(() => {
  // If image is already cached, it might load before the component mounts
  if (imageRef.value?.complete && imageRef.value?.naturalHeight !== 0) {
    handleLoad()
  }
})
</script>

<style scoped>
.image-with-blur-placeholder {
  position: relative;
  overflow: hidden;
  display: inline-block;
}

.blur-placeholder {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  filter: blur(20px);
  transform: scale(1.1); /* Prevent blur edge artifacts */
}

.loading-skeleton {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: rgb(243 244 246);
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

.dark .loading-skeleton {
  background-color: rgb(31 41 55);
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

.error-state {
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  background-color: rgb(243 244 246);
}

.dark .error-state {
  background-color: rgb(31 41 55);
}

.error-message {
  font-size: 0.875rem;
  color: rgb(107 114 128);
}

.dark .error-message {
  color: rgb(156 163 175);
}
</style>
