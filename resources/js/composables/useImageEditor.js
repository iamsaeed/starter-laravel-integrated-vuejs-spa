import { ref } from 'vue'

/**
 * Image Editor Composable
 * Provides a clean API for opening the image editor modal
 *
 * @returns {Object} Image editor functions and state
 */
export function useImageEditor() {
  const isOpen = ref(false)
  const currentImage = ref(null)
  const currentOptions = ref({})
  const resolveCallback = ref(null)
  const rejectCallback = ref(null)

  /**
   * Open image editor with a file or URL
   *
   * @param {File|Blob|String} image - Image file, blob, or URL
   * @param {Object} options - Editor options
   * @param {number|null} options.aspectRatio - Fixed aspect ratio (null for free crop)
   * @param {number} options.minWidth - Minimum width
   * @param {number} options.minHeight - Minimum height
   * @param {number} options.quality - JPEG quality (0-1)
   * @returns {Promise<{blob: Blob, file: File, canvas: HTMLCanvasElement}>}
   */
  function openEditor(image, options = {}) {
    return new Promise((resolve, reject) => {
      if (!image) {
        reject(new Error('No image provided'))
        return
      }

      // If image is a File or Blob, create object URL
      if (image instanceof File || image instanceof Blob) {
        currentImage.value = URL.createObjectURL(image)
      } else if (typeof image === 'string') {
        currentImage.value = image
      } else {
        reject(new Error('Invalid image type'))
        return
      }

      currentOptions.value = {
        aspectRatio: options.aspectRatio || null,
        minWidth: options.minWidth || null,
        minHeight: options.minHeight || null,
        quality: options.quality || 0.9,
        ...options
      }

      resolveCallback.value = resolve
      rejectCallback.value = reject
      isOpen.value = true
    })
  }

  /**
   * Edit image from file
   *
   * @param {File} file - Image file to edit
   * @param {Object} options - Editor options
   * @returns {Promise<{blob: Blob, file: File}>}
   */
  async function editFile(file, options = {}) {
    if (!file || !(file instanceof File)) {
      throw new Error('Invalid file provided')
    }

    return openEditor(file, options)
  }

  /**
   * Edit image from URL
   *
   * @param {String} url - Image URL to edit
   * @param {Object} options - Editor options
   * @returns {Promise<{blob: Blob, file: File}>}
   */
  async function editUrl(url, options = {}) {
    if (!url || typeof url !== 'string') {
      throw new Error('Invalid URL provided')
    }

    return openEditor(url, options)
  }

  /**
   * Crop image to specific aspect ratio
   *
   * @param {File|String} image - Image to crop
   * @param {number} aspectRatio - Aspect ratio (e.g., 1 for square, 16/9 for widescreen)
   * @returns {Promise<{blob: Blob, file: File}>}
   */
  async function cropToAspectRatio(image, aspectRatio) {
    return openEditor(image, { aspectRatio })
  }

  /**
   * Crop image to square
   *
   * @param {File|String} image - Image to crop
   * @returns {Promise<{blob: Blob, file: File}>}
   */
  async function cropToSquare(image) {
    return cropToAspectRatio(image, 1)
  }

  /**
   * Handle editor save
   *
   * @param {Object} result - Editor result
   */
  function handleSave(result) {
    if (resolveCallback.value) {
      resolveCallback.value(result)
    }
    close()
  }

  /**
   * Handle editor cancel
   */
  function handleCancel() {
    if (rejectCallback.value) {
      rejectCallback.value(new Error('User cancelled'))
    }
    close()
  }

  /**
   * Close editor and cleanup
   */
  function close() {
    // Revoke object URL if it was created
    if (currentImage.value && currentImage.value.startsWith('blob:')) {
      URL.revokeObjectURL(currentImage.value)
    }

    isOpen.value = false
    currentImage.value = null
    currentOptions.value = {}
    resolveCallback.value = null
    rejectCallback.value = null
  }

  return {
    // State
    isOpen,
    currentImage,
    currentOptions,

    // Actions
    openEditor,
    editFile,
    editUrl,
    cropToAspectRatio,
    cropToSquare,
    handleSave,
    handleCancel,
    close
  }
}
