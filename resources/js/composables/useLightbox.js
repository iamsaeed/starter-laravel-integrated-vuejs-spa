import { ref } from 'vue'

/**
 * Lightbox Composable
 * Provides a clean API for showing images in a lightbox
 *
 * @returns {Object} Lightbox functions and state
 */
export function useLightbox() {
  const isOpen = ref(false)
  const images = ref([])
  const initialIndex = ref(0)

  /**
   * Open lightbox with a single image
   *
   * @param {string|Object} image - Image URL or object with url/src property
   * @param {Object} options - Additional options
   */
  function show(image, options = {}) {
    if (typeof image === 'string') {
      images.value = [{ url: image, name: options.name || 'Image' }]
    } else {
      images.value = [image]
    }

    initialIndex.value = 0
    isOpen.value = true
  }

  /**
   * Open lightbox with multiple images (gallery)
   *
   * @param {Array} imageArray - Array of image URLs or objects
   * @param {number} startIndex - Index of image to show first
   */
  function showGallery(imageArray, startIndex = 0) {
    if (!Array.isArray(imageArray) || imageArray.length === 0) {
      console.warn('showGallery requires a non-empty array of images')
      return
    }

    // Normalize images to objects with url property
    images.value = imageArray.map((img) => {
      if (typeof img === 'string') {
        return { url: img }
      }
      return img
    })

    initialIndex.value = Math.max(0, Math.min(startIndex, imageArray.length - 1))
    isOpen.value = true
  }

  /**
   * Open lightbox from media collection
   *
   * @param {Array} mediaArray - Array of media objects from API
   * @param {number} startIndex - Index of image to show first
   */
  function showMediaGallery(mediaArray, startIndex = 0) {
    if (!Array.isArray(mediaArray) || mediaArray.length === 0) {
      console.warn('showMediaGallery requires a non-empty array of media')
      return
    }

    images.value = mediaArray.map((media) => ({
      url: media.url || media.src,
      name: media.name || media.file_name || 'Image',
      alt: media.alt || media.name || '',
      ...media
    }))

    initialIndex.value = Math.max(0, Math.min(startIndex, mediaArray.length - 1))
    isOpen.value = true
  }

  /**
   * Close lightbox
   */
  function close() {
    isOpen.value = false
  }

  /**
   * Clear images
   */
  function clear() {
    images.value = []
    initialIndex.value = 0
  }

  return {
    // State
    isOpen,
    images,
    initialIndex,

    // Actions
    show,
    showGallery,
    showMediaGallery,
    close,
    clear
  }
}
