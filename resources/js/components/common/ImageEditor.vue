<template>
  <div
    v-if="show"
    class="fixed inset-0 z-50 overflow-y-auto"
    @keydown.esc="handleCancel"
  >
    <!-- Backdrop -->
    <div
      class="fixed inset-0 bg-black bg-opacity-75 transition-opacity"
      @click="handleCancel"
    ></div>

    <!-- Modal -->
    <div class="flex min-h-screen items-center justify-center p-4">
      <div
        class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-5xl w-full"
        @click.stop
      >
        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            Edit Image
          </h3>
          <button
            @click="handleCancel"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors"
            type="button"
          >
            <Icon name="close" :size="24" />
          </button>
        </div>

        <!-- Toolbar -->
        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 gap-3 flex-wrap">
          <!-- Aspect Ratio -->
          <div class="flex items-center gap-2">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Aspect:</span>
            <div class="flex gap-1">
              <button
                v-for="ratio in aspectRatios"
                :key="ratio.value"
                @click="setAspectRatio(ratio.value)"
                :class="[
                  'px-3 py-1 text-xs font-medium rounded transition-colors',
                  selectedAspectRatio === ratio.value
                    ? 'bg-primary-600 text-white'
                    : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700'
                ]"
                type="button"
              >
                {{ ratio.label }}
              </button>
            </div>
          </div>

          <!-- Transform Tools -->
          <div class="flex items-center gap-2">
            <button
              @click="rotate(-90)"
              class="p-2 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
              title="Rotate Left"
              type="button"
            >
              <Icon name="rotate-left" :size="20" />
            </button>
            <button
              @click="rotate(90)"
              class="p-2 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
              title="Rotate Right"
              type="button"
            >
              <Icon name="rotate-right" :size="20" />
            </button>
            <button
              @click="flip(true, false)"
              class="p-2 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
              title="Flip Horizontal"
              type="button"
            >
              <Icon name="flip-horizontal" :size="20" />
            </button>
            <button
              @click="flip(false, true)"
              class="p-2 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
              title="Flip Vertical"
              type="button"
            >
              <Icon name="flip-vertical" :size="20" />
            </button>
            <button
              @click="zoom(0.1)"
              class="p-2 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
              title="Zoom In"
              type="button"
            >
              <Icon name="zoom-in" :size="20" />
            </button>
            <button
              @click="zoom(-0.1)"
              class="p-2 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
              title="Zoom Out"
              type="button"
            >
              <Icon name="zoom-out" :size="20" />
            </button>
            <button
              @click="reset"
              class="p-2 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
              title="Reset"
              type="button"
            >
              <Icon name="refresh" :size="20" />
            </button>
          </div>

          <!-- Undo/Redo -->
          <div class="flex items-center gap-2 ml-2">
            <button
              @click="handleUndo"
              :disabled="!canUndo"
              class="p-2 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              title="Undo"
              type="button"
            >
              <Icon name="undo" :size="20" />
            </button>
            <button
              @click="handleRedo"
              :disabled="!canRedo"
              class="p-2 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              title="Redo"
              type="button"
            >
              <Icon name="redo" :size="20" />
            </button>
          </div>
        </div>

        <!-- Cropper -->
        <div class="p-4 bg-gray-100 dark:bg-gray-950">
          <div class="cropper-wrapper">
            <Cropper
              ref="cropper"
              :src="currentImageSrc"
              :stencil-props="stencilProps"
              :default-size="defaultSize"
              class="cropper"
              @change="onChange"
            />
          </div>
        </div>

        <!-- Footer -->
        <div class="flex items-center justify-end gap-3 p-4 border-t border-gray-200 dark:border-gray-700">
          <button
            @click="handleCancel"
            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
            type="button"
          >
            Cancel
          </button>
          <button
            @click="handleSave"
            :disabled="saving"
            class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
            type="button"
          >
            <span v-if="saving" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></span>
            {{ saving ? 'Processing...' : 'Save Changes' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue'
import { Cropper } from 'vue-advanced-cropper'
import 'vue-advanced-cropper/dist/style.css'
import Icon from '@/components/common/Icon.vue'
import { useImageEditHistory } from '@/composables/useImageEditHistory'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  imageSrc: {
    type: String,
    required: true
  },
  options: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['close', 'save'])

const cropper = ref(null)
const saving = ref(false)
const selectedAspectRatio = ref(props.options.aspectRatio || null)
const currentRotation = ref(0)
const currentFlip = ref({ horizontal: false, vertical: false })
const currentImageSrc = ref(props.imageSrc)

// Edit history
const {
  canUndo,
  canRedo,
  historyPosition,
  pushState: pushHistoryState,
  undo: undoHistory,
  redo: redoHistory,
  reset: resetHistory,
  clearHistory
} = useImageEditHistory()

const aspectRatios = computed(() => {
  const ratios = [
    { label: 'Free', value: null },
    { label: '1:1', value: 1 },
    { label: '16:9', value: 16 / 9 },
    { label: '4:3', value: 4 / 3 },
    { label: '3:2', value: 3 / 2 }
  ]

  // If a fixed aspect ratio is specified in options, only show that one
  if (props.options.aspectRatio && typeof props.options.aspectRatio === 'number') {
    const fixed = ratios.find(r => r.value === props.options.aspectRatio)
    if (fixed) {
      selectedAspectRatio.value = fixed.value
      return [fixed]
    }
  }

  return ratios
})

const stencilProps = computed(() => {
  const props = {}

  if (selectedAspectRatio.value !== null) {
    props.aspectRatio = selectedAspectRatio.value
  }

  return props
})

const defaultSize = computed(() => {
  const size = {}

  if (props.options.minWidth) {
    size.width = props.options.minWidth
  }

  if (props.options.minHeight) {
    size.height = props.options.minHeight
  }

  return Object.keys(size).length > 0 ? size : undefined
})

function setAspectRatio(ratio) {
  selectedAspectRatio.value = ratio
}

function rotate(angle) {
  if (!cropper.value) return

  currentRotation.value = (currentRotation.value + angle) % 360
  cropper.value.rotate(angle)
}

function flip(horizontal, vertical) {
  if (!cropper.value) return

  if (horizontal) {
    currentFlip.value.horizontal = !currentFlip.value.horizontal
    cropper.value.flip(true, false)
  }

  if (vertical) {
    currentFlip.value.vertical = !currentFlip.value.vertical
    cropper.value.flip(false, true)
  }
}

function zoom(factor) {
  if (!cropper.value) return
  cropper.value.zoom(factor)
}

function reset() {
  if (!cropper.value) return

  currentRotation.value = 0
  currentFlip.value = { horizontal: false, vertical: false }
  cropper.value.reset()
}

function onChange({ coordinates, canvas }) {
  // This is called when the crop area changes
  // We can use this for real-time preview if needed
}

// Save current cropper state to history
async function saveStateToHistory() {
  if (!cropper.value) return

  try {
    const { canvas } = cropper.value.getResult()
    if (!canvas) return

    const blob = await new Promise((resolve) => {
      canvas.toBlob((blob) => {
        resolve(blob)
      }, 'image/jpeg', 0.9)
    })

    if (blob) {
      pushHistoryState(blob)
    }
  } catch (error) {
    console.error('Failed to save state to history:', error)
  }
}

// Undo handler
async function handleUndo() {
  const previousState = undoHistory()
  if (previousState && cropper.value) {
    // Revoke old blob URL if it exists
    if (currentImageSrc.value && currentImageSrc.value.startsWith('blob:')) {
      URL.revokeObjectURL(currentImageSrc.value)
    }

    // Create new blob URL from history state
    const url = URL.createObjectURL(previousState)
    currentImageSrc.value = url

    // Wait for cropper to reload with new src
    await nextTick()

    // Reset cropper to show full image
    if (cropper.value) {
      cropper.value.reset()
    }
  }
}

// Redo handler
async function handleRedo() {
  const nextState = redoHistory()
  if (nextState && cropper.value) {
    // Revoke old blob URL if it exists
    if (currentImageSrc.value && currentImageSrc.value.startsWith('blob:')) {
      URL.revokeObjectURL(currentImageSrc.value)
    }

    // Create new blob URL from history state
    const url = URL.createObjectURL(nextState)
    currentImageSrc.value = url

    // Wait for cropper to reload with new src
    await nextTick()

    // Reset cropper to show full image
    if (cropper.value) {
      cropper.value.reset()
    }
  }
}

async function handleSave() {
  if (!cropper.value) return

  saving.value = true

  try {
    const { canvas } = cropper.value.getResult()

    if (!canvas) {
      throw new Error('Failed to get cropped image')
    }

    // Convert canvas to blob
    const blob = await new Promise((resolve) => {
      canvas.toBlob((blob) => {
        resolve(blob)
      }, 'image/jpeg', 0.9) // 90% quality JPEG
    })

    // Emit the edited image blob
    emit('save', {
      blob,
      canvas,
      options: {
        rotation: currentRotation.value,
        flip: currentFlip.value,
        aspectRatio: selectedAspectRatio.value
      }
    })
  } catch (error) {
    console.error('Error saving edited image:', error)
    alert('Failed to save edited image. Please try again.')
  } finally {
    saving.value = false
  }
}

function handleCancel() {
  if (!saving.value) {
    emit('close')
  }
}

// Reset state when modal is opened
watch(() => props.show, (newVal) => {
  if (newVal) {
    selectedAspectRatio.value = props.options.aspectRatio || null
    currentRotation.value = 0
    currentFlip.value = { horizontal: false, vertical: false }
    currentImageSrc.value = props.imageSrc
    clearHistory()
  }
})

// Watch for imageSrc changes
watch(() => props.imageSrc, (newSrc) => {
  currentImageSrc.value = newSrc
})
</script>

<style scoped>
.cropper-wrapper {
  max-height: 60vh;
  background: #000;
  border-radius: 0.5rem;
  overflow: hidden;
}

.cropper {
  max-height: 60vh;
}

/* Override cropper default styles for dark mode */
:deep(.vue-advanced-cropper__background) {
  background: #000;
}

:deep(.vue-advanced-cropper__foreground) {
  background: rgba(0, 0, 0, 0.5);
}
</style>
