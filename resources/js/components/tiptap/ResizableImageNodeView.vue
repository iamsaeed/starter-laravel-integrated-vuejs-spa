<template>
  <NodeViewWrapper
    class="resizable-image-container"
    :class="{ 'is-selected': selected }"
    data-drag-handle
  >
    <div
      class="image-wrapper"
      :style="{ width: currentWidth + 'px' }"
      ref="wrapperRef"
      data-drag-handle
    >
      <img
        :src="node.attrs.src"
        :alt="node.attrs.alt"
        :title="node.attrs.title"
        :data-blur-placeholder="node.attrs['data-blur-placeholder']"
        :data-media-id="node.attrs['data-media-id']"
        :data-loading="node.attrs['data-loading']"
        :class="node.attrs.class"
        class="drag-handle"
        data-drag-handle
        ref="imageRef"
        @load="onImageLoad"
      />

      <!-- Resize handle - only show when selected and editable -->
      <div
        v-if="selected && editor.isEditable"
        class="resize-handle"
        @mousedown.stop="startResize"
        @touchstart.stop="startResize"
        @dragstart.prevent
      >
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M15 1L1 15M15 6L10 11M15 11L11 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
      </div>
    </div>
  </NodeViewWrapper>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { nodeViewProps, NodeViewWrapper } from '@tiptap/vue-3'

const props = defineProps(nodeViewProps)

const imageRef = ref(null)
const wrapperRef = ref(null)
const currentWidth = ref(null)
const isResizing = ref(false)
const imageLoaded = ref(false)

const selected = computed(() => props.selected)

// Initialize dimensions when image loads
const onImageLoad = () => {
  if (!imageLoaded.value && imageRef.value) {
    imageLoaded.value = true

    // If no width is set, use natural width (up to max 600px)
    if (!props.node.attrs.width) {
      const naturalWidth = imageRef.value.naturalWidth
      const maxWidth = Math.min(naturalWidth, 600)
      currentWidth.value = maxWidth

      // Update node attributes
      props.updateAttributes({
        width: maxWidth
      })
    } else {
      currentWidth.value = props.node.attrs.width
    }
  }
}

// Initialize width from node attributes
onMounted(() => {
  if (props.node.attrs.width) {
    currentWidth.value = props.node.attrs.width
  }
})

// Resize functionality
let startX = 0
let startWidth = 0

const startResize = (event) => {
  // Prevent default behavior and stop propagation to prevent drag
  event.preventDefault()
  event.stopPropagation()

  // Determine if it's a touch or mouse event
  const clientX = event.touches ? event.touches[0].clientX : event.clientX

  isResizing.value = true
  startX = clientX
  startWidth = currentWidth.value || wrapperRef.value.offsetWidth

  // Disable dragging while resizing
  if (wrapperRef.value) {
    wrapperRef.value.setAttribute('draggable', 'false')
  }

  // Add event listeners
  if (event.touches) {
    document.addEventListener('touchmove', handleResize, { passive: false })
    document.addEventListener('touchend', stopResize)
  } else {
    document.addEventListener('mousemove', handleResize)
    document.addEventListener('mouseup', stopResize)
  }

  // Prevent text selection during resize
  document.body.style.userSelect = 'none'
  document.body.style.cursor = 'nwse-resize'
}

const handleResize = (event) => {
  if (!isResizing.value) return

  event.preventDefault()

  const clientX = event.touches ? event.touches[0].clientX : event.clientX
  const deltaX = clientX - startX

  // Calculate new width with smooth update
  let newWidth = startWidth + deltaX

  // Constrain to min/max widths
  const minWidth = 100
  const maxWidth = 1200
  newWidth = Math.max(minWidth, Math.min(maxWidth, newWidth))

  // Update width immediately for smooth visual feedback
  currentWidth.value = Math.round(newWidth)
}

const stopResize = () => {
  if (!isResizing.value) return

  // Clean up event listeners
  document.removeEventListener('mousemove', handleResize)
  document.removeEventListener('mouseup', stopResize)
  document.removeEventListener('touchmove', handleResize)
  document.removeEventListener('touchend', stopResize)

  // Restore cursor and text selection
  document.body.style.userSelect = ''
  document.body.style.cursor = ''

  // Re-enable dragging
  if (wrapperRef.value) {
    wrapperRef.value.removeAttribute('draggable')
  }

  isResizing.value = false

  // Save the final width to node attributes
  props.updateAttributes({
    width: currentWidth.value
  })
}
</script>

<style scoped>
.resizable-image-container {
  display: block;
  margin: 1rem 0;
  line-height: 0;
}

.image-wrapper {
  position: relative;
  display: inline-block;
  max-width: 100%;
  transition: outline 0.15s ease;
}

.is-selected .image-wrapper {
  outline: 3px solid rgb(59 130 246);
  outline-offset: 2px;
  border-radius: 0.25rem;
}

img {
  width: 100%;
  height: auto;
  display: block;
  border-radius: 0.25rem;
  cursor: grab;
}

img:active {
  cursor: grabbing;
}

/* Resize Handle */
.resize-handle {
  position: absolute;
  bottom: -8px;
  right: -8px;
  width: 32px;
  height: 32px;
  background: rgb(59 130 246);
  border: 2px solid white;
  border-radius: 50%;
  cursor: nwse-resize;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
  transition: all 0.2s ease;
  z-index: 10;
  opacity: 0;
  transform: scale(0.8);
}

.is-selected .resize-handle {
  opacity: 1;
  transform: scale(1);
}

.resize-handle:hover {
  background: rgb(37 99 235);
  transform: scale(1.1);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.resize-handle:active {
  transform: scale(0.95);
}

.resize-handle svg {
  width: 14px;
  height: 14px;
}

/* Dark mode support */
.dark .is-selected .image-wrapper {
  outline-color: rgb(96 165 250);
}

.dark .resize-handle {
  background: rgb(96 165 250);
  border-color: rgb(30 41 59);
}

.dark .resize-handle:hover {
  background: rgb(59 130 246);
  box-shadow: 0 4px 12px rgba(96, 165, 250, 0.4);
}

/* Loading state */
img[data-loading="true"] {
  opacity: 0.6;
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 0.6;
  }
  50% {
    opacity: 0.4;
  }
}

/* Blur placeholder effect */
img[data-blur-placeholder] {
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
}

img[data-blur-placeholder]:not([src]) {
  background-image: attr(data-blur-placeholder);
}
</style>
