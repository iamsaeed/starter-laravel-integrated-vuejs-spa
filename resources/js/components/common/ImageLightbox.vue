<template>
  <Teleport to="body">
    <Transition name="lightbox">
      <div
        v-if="isOpen"
        class="lightbox-overlay"
        @click="handleOverlayClick"
        @keydown.esc="close"
        @keydown.left="previous"
        @keydown.right="next"
        tabindex="0"
        ref="lightboxRef"
      >
        <!-- Close Button -->
        <button
          class="lightbox-close-btn"
          @click="close"
          type="button"
          v-tooltip="'Close (Esc)'"
        >
          <Icon name="x" class="w-6 h-6" />
        </button>

        <!-- Navigation Buttons (for galleries) -->
        <button
          v-if="showNavigation && hasPrevious"
          class="lightbox-nav-btn lightbox-nav-prev"
          @click.stop="previous"
          type="button"
          v-tooltip="'Previous (←)'"
        >
          <Icon name="chevron-left" class="w-8 h-8" />
        </button>

        <button
          v-if="showNavigation && hasNext"
          class="lightbox-nav-btn lightbox-nav-next"
          @click.stop="next"
          type="button"
          v-tooltip="'Next (→)'"
        >
          <Icon name="chevron-right" class="w-8 h-8" />
        </button>

        <!-- Image Container -->
        <div class="lightbox-content" @click.stop>
          <!-- Zoom Controls -->
          <div class="lightbox-controls">
            <button
              @click="zoomIn"
              class="lightbox-control-btn"
              type="button"
              v-tooltip="'Zoom In (+)'"
            >
              <Icon name="zoom-in" class="w-5 h-5" />
            </button>
            <button
              @click="zoomOut"
              class="lightbox-control-btn"
              type="button"
              v-tooltip="'Zoom Out (-)'"
            >
              <Icon name="zoom-out" class="w-5 h-5" />
            </button>
            <button
              @click="resetZoom"
              class="lightbox-control-btn"
              type="button"
              v-tooltip="'Reset Zoom (0)'"
            >
              <Icon name="maximize" class="w-5 h-5" />
            </button>
            <button
              v-if="showDownload"
              @click="download"
              class="lightbox-control-btn"
              type="button"
              v-tooltip="'Download'"
            >
              <Icon name="download" class="w-5 h-5" />
            </button>
          </div>

          <!-- Image -->
          <div class="lightbox-image-container" ref="imageContainerRef">
            <img
              :src="currentImageUrl"
              :alt="currentImageAlt"
              class="lightbox-image"
              :style="imageStyle"
              @load="handleImageLoad"
              @error="handleImageError"
              ref="imageRef"
            />

            <!-- Loading State -->
            <div v-if="isLoading" class="lightbox-loading">
              <Icon name="loader" class="w-12 h-12 animate-spin text-white" />
            </div>

            <!-- Error State -->
            <div v-if="hasError" class="lightbox-error">
              <Icon name="alert-circle" class="w-12 h-12 text-white" />
              <p class="mt-4 text-white">Failed to load image</p>
            </div>
          </div>

          <!-- Image Info -->
          <div v-if="showInfo && currentImage" class="lightbox-info">
            <span class="lightbox-info-text">{{ currentImage.name || 'Image' }}</span>
            <span v-if="images.length > 1" class="lightbox-info-text">
              {{ currentIndex + 1 }} / {{ images.length }}
            </span>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue'
import Icon from '@/components/common/Icon.vue'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  images: {
    type: Array,
    default: () => []
  },
  initialIndex: {
    type: Number,
    default: 0
  },
  showNavigation: {
    type: Boolean,
    default: true
  },
  showInfo: {
    type: Boolean,
    default: true
  },
  showDownload: {
    type: Boolean,
    default: true
  },
  zoomStep: {
    type: Number,
    default: 0.2
  },
  minZoom: {
    type: Number,
    default: 0.5
  },
  maxZoom: {
    type: Number,
    default: 3
  }
})

const emit = defineEmits(['update:modelValue', 'close', 'change'])

const lightboxRef = ref(null)
const imageRef = ref(null)
const imageContainerRef = ref(null)
const currentIndex = ref(props.initialIndex)
const zoom = ref(1)
const isLoading = ref(false)
const hasError = ref(false)
const isDragging = ref(false)
const dragStart = ref({ x: 0, y: 0 })
const imagePosition = ref({ x: 0, y: 0 })

const isOpen = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

const currentImage = computed(() => {
  return props.images[currentIndex.value] || null
})

const currentImageUrl = computed(() => {
  if (!currentImage.value) return ''
  return currentImage.value.url || currentImage.value.src || currentImage.value
})

const currentImageAlt = computed(() => {
  if (!currentImage.value) return ''
  return currentImage.value.alt || currentImage.value.name || 'Image'
})

const hasPrevious = computed(() => {
  return currentIndex.value > 0
})

const hasNext = computed(() => {
  return currentIndex.value < props.images.length - 1
})

const imageStyle = computed(() => {
  return {
    transform: `scale(${zoom.value}) translate(${imagePosition.value.x}px, ${imagePosition.value.y}px)`,
    cursor: zoom.value > 1 ? (isDragging.value ? 'grabbing' : 'grab') : 'default'
  }
})

// Watch for image changes
watch(currentIndex, () => {
  resetZoom()
  isLoading.value = true
  hasError.value = false
  emit('change', currentIndex.value)
})

watch(isOpen, (newValue) => {
  if (newValue) {
    document.body.style.overflow = 'hidden'
    nextTick(() => {
      lightboxRef.value?.focus()
    })
  } else {
    document.body.style.overflow = ''
  }
})

function close() {
  isOpen.value = false
  emit('close')
}

function handleOverlayClick() {
  close()
}

function previous() {
  if (hasPrevious.value) {
    currentIndex.value--
  }
}

function next() {
  if (hasNext.value) {
    currentIndex.value++
  }
}

function zoomIn() {
  zoom.value = Math.min(zoom.value + props.zoomStep, props.maxZoom)
}

function zoomOut() {
  zoom.value = Math.max(zoom.value - props.zoomStep, props.minZoom)

  // Reset position if zooming out to 1 or less
  if (zoom.value <= 1) {
    imagePosition.value = { x: 0, y: 0 }
  }
}

function resetZoom() {
  zoom.value = 1
  imagePosition.value = { x: 0, y: 0 }
}

function download() {
  if (!currentImageUrl.value) return

  const link = document.createElement('a')
  link.href = currentImageUrl.value
  link.download = currentImage.value?.name || 'image'
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

function handleImageLoad() {
  isLoading.value = false
  hasError.value = false
}

function handleImageError() {
  isLoading.value = false
  hasError.value = true
}

// Keyboard shortcuts
function handleKeydown(event) {
  if (!isOpen.value) return

  switch (event.key) {
    case '+':
    case '=':
      event.preventDefault()
      zoomIn()
      break
    case '-':
    case '_':
      event.preventDefault()
      zoomOut()
      break
    case '0':
      event.preventDefault()
      resetZoom()
      break
  }
}

// Mouse drag for panning when zoomed
function handleMouseDown(event) {
  if (zoom.value <= 1) return

  isDragging.value = true
  dragStart.value = {
    x: event.clientX - imagePosition.value.x,
    y: event.clientY - imagePosition.value.y
  }
}

function handleMouseMove(event) {
  if (!isDragging.value || zoom.value <= 1) return

  imagePosition.value = {
    x: event.clientX - dragStart.value.x,
    y: event.clientY - dragStart.value.y
  }
}

function handleMouseUp() {
  isDragging.value = false
}

// Touch support for mobile
function handleTouchStart(event) {
  if (event.touches.length === 1 && zoom.value > 1) {
    isDragging.value = true
    dragStart.value = {
      x: event.touches[0].clientX - imagePosition.value.x,
      y: event.touches[0].clientY - imagePosition.value.y
    }
  }
}

function handleTouchMove(event) {
  if (!isDragging.value || zoom.value <= 1 || event.touches.length !== 1) return

  event.preventDefault()
  imagePosition.value = {
    x: event.touches[0].clientX - dragStart.value.x,
    y: event.touches[0].clientY - dragStart.value.y
  }
}

function handleTouchEnd() {
  isDragging.value = false
}

onMounted(() => {
  document.addEventListener('keydown', handleKeydown)

  if (imageContainerRef.value) {
    imageContainerRef.value.addEventListener('mousedown', handleMouseDown)
    imageContainerRef.value.addEventListener('touchstart', handleTouchStart, { passive: false })
  }

  document.addEventListener('mousemove', handleMouseMove)
  document.addEventListener('mouseup', handleMouseUp)
  document.addEventListener('touchmove', handleTouchMove, { passive: false })
  document.addEventListener('touchend', handleTouchEnd)
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown)
  document.removeEventListener('mousemove', handleMouseMove)
  document.removeEventListener('mouseup', handleMouseUp)
  document.removeEventListener('touchmove', handleTouchMove)
  document.removeEventListener('touchend', handleTouchEnd)
  document.body.style.overflow = ''
})
</script>

<style scoped>
.lightbox-overlay {
  position: fixed;
  inset: 0;
  z-index: 9999;
  background-color: rgba(0, 0, 0, 0.95);
  display: flex;
  align-items: center;
  justify-content: center;
  outline: none;
}

.lightbox-close-btn {
  position: absolute;
  top: 1rem;
  right: 1rem;
  z-index: 10;
  padding: 0.75rem;
  background-color: rgba(0, 0, 0, 0.5);
  color: white;
  border-radius: 0.5rem;
  transition: background-color 0.2s;
}

.lightbox-close-btn:hover {
  background-color: rgba(0, 0, 0, 0.7);
}

.lightbox-nav-btn {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  z-index: 10;
  padding: 1rem;
  background-color: rgba(0, 0, 0, 0.5);
  color: white;
  border-radius: 0.5rem;
  transition: background-color 0.2s;
}

.lightbox-nav-btn:hover {
  background-color: rgba(0, 0, 0, 0.7);
}

.lightbox-nav-prev {
  left: 1rem;
}

.lightbox-nav-next {
  right: 1rem;
}

.lightbox-content {
  position: relative;
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2rem;
}

.lightbox-controls {
  position: absolute;
  bottom: 2rem;
  left: 50%;
  transform: translateX(-50%);
  z-index: 10;
  display: flex;
  gap: 0.5rem;
  background-color: rgba(0, 0, 0, 0.7);
  padding: 0.5rem;
  border-radius: 0.5rem;
}

.lightbox-control-btn {
  padding: 0.75rem;
  background-color: rgba(255, 255, 255, 0.1);
  color: white;
  border-radius: 0.375rem;
  transition: background-color 0.2s;
}

.lightbox-control-btn:hover {
  background-color: rgba(255, 255, 255, 0.2);
}

.lightbox-image-container {
  position: relative;
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.lightbox-image {
  max-width: 90%;
  max-height: 90%;
  object-fit: contain;
  transition: transform 0.2s ease-out;
  user-select: none;
}

.lightbox-loading,
.lightbox-error {
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.lightbox-info {
  position: absolute;
  top: 1rem;
  left: 1rem;
  z-index: 10;
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  background-color: rgba(0, 0, 0, 0.7);
  padding: 0.75rem 1rem;
  border-radius: 0.5rem;
}

.lightbox-info-text {
  font-size: 0.875rem;
  color: white;
}

/* Transitions */
.lightbox-enter-active,
.lightbox-leave-active {
  transition: opacity 0.3s ease;
}

.lightbox-enter-from,
.lightbox-leave-to {
  opacity: 0;
}
</style>
