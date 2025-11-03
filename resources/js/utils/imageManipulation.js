/**
 * Image Manipulation Utilities
 * Pure functions for client-side image processing
 */

/**
 * Rotate image by degrees
 * @param {File|Blob} file - Image file
 * @param {number} degrees - Rotation angle (90, 180, 270, -90)
 * @returns {Promise<Blob>} Rotated image blob
 */
export async function rotateImage(file, degrees) {
  return new Promise((resolve, reject) => {
    const img = new Image()
    const canvas = document.createElement('canvas')
    const ctx = canvas.getContext('2d')

    img.onload = () => {
      // Normalize degrees to 0-360 range
      const normalizedDegrees = ((degrees % 360) + 360) % 360

      // For 90° and 270°, swap width and height
      if (normalizedDegrees === 90 || normalizedDegrees === 270) {
        canvas.width = img.height
        canvas.height = img.width
      } else {
        canvas.width = img.width
        canvas.height = img.height
      }

      // Move to center, rotate, draw
      ctx.save()

      // Translate to center
      ctx.translate(canvas.width / 2, canvas.height / 2)

      // Rotate
      ctx.rotate((normalizedDegrees * Math.PI) / 180)

      // Draw image centered
      ctx.drawImage(img, -img.width / 2, -img.height / 2)

      ctx.restore()

      // Convert canvas to blob
      canvas.toBlob(
        (blob) => {
          if (blob) {
            resolve(blob)
          } else {
            reject(new Error('Failed to create blob from canvas'))
          }
        },
        file.type || 'image/jpeg',
        0.95
      )
    }

    img.onerror = () => {
      reject(new Error('Failed to load image'))
    }

    // Load image from file
    const reader = new FileReader()
    reader.onload = (e) => {
      img.src = e.target.result
    }
    reader.onerror = () => {
      reject(new Error('Failed to read file'))
    }
    reader.readAsDataURL(file)
  })
}

/**
 * Flip image horizontally
 * @param {File|Blob} file - Image file
 * @returns {Promise<Blob>} Flipped image blob
 */
export async function flipHorizontal(file) {
  return new Promise((resolve, reject) => {
    const img = new Image()
    const canvas = document.createElement('canvas')
    const ctx = canvas.getContext('2d')

    img.onload = () => {
      canvas.width = img.width
      canvas.height = img.height

      ctx.save()

      // Flip horizontally
      ctx.translate(canvas.width, 0)
      ctx.scale(-1, 1)

      ctx.drawImage(img, 0, 0)

      ctx.restore()

      canvas.toBlob(
        (blob) => {
          if (blob) {
            resolve(blob)
          } else {
            reject(new Error('Failed to create blob from canvas'))
          }
        },
        file.type || 'image/jpeg',
        0.95
      )
    }

    img.onerror = () => {
      reject(new Error('Failed to load image'))
    }

    const reader = new FileReader()
    reader.onload = (e) => {
      img.src = e.target.result
    }
    reader.onerror = () => {
      reject(new Error('Failed to read file'))
    }
    reader.readAsDataURL(file)
  })
}

/**
 * Flip image vertically
 * @param {File|Blob} file - Image file
 * @returns {Promise<Blob>} Flipped image blob
 */
export async function flipVertical(file) {
  return new Promise((resolve, reject) => {
    const img = new Image()
    const canvas = document.createElement('canvas')
    const ctx = canvas.getContext('2d')

    img.onload = () => {
      canvas.width = img.width
      canvas.height = img.height

      ctx.save()

      // Flip vertically
      ctx.translate(0, canvas.height)
      ctx.scale(1, -1)

      ctx.drawImage(img, 0, 0)

      ctx.restore()

      canvas.toBlob(
        (blob) => {
          if (blob) {
            resolve(blob)
          } else {
            reject(new Error('Failed to create blob from canvas'))
          }
        },
        file.type || 'image/jpeg',
        0.95
      )
    }

    img.onerror = () => {
      reject(new Error('Failed to load image'))
    }

    const reader = new FileReader()
    reader.onload = (e) => {
      img.src = e.target.result
    }
    reader.onerror = () => {
      reject(new Error('Failed to read file'))
    }
    reader.readAsDataURL(file)
  })
}

/**
 * Resize image to fit within max dimensions while maintaining aspect ratio
 * @param {File|Blob} file - Image file
 * @param {number} maxWidth - Maximum width
 * @param {number} maxHeight - Maximum height
 * @returns {Promise<Blob>} Resized image blob
 */
export async function resizeImage(file, maxWidth, maxHeight) {
  return new Promise((resolve, reject) => {
    const img = new Image()
    const canvas = document.createElement('canvas')
    const ctx = canvas.getContext('2d')

    img.onload = () => {
      let { width, height } = img

      // Calculate new dimensions maintaining aspect ratio
      if (width > maxWidth || height > maxHeight) {
        const aspectRatio = width / height

        if (width / maxWidth > height / maxHeight) {
          width = maxWidth
          height = width / aspectRatio
        } else {
          height = maxHeight
          width = height * aspectRatio
        }
      }

      canvas.width = width
      canvas.height = height

      // Enable smoothing for better quality
      ctx.imageSmoothingEnabled = true
      ctx.imageSmoothingQuality = 'high'

      ctx.drawImage(img, 0, 0, width, height)

      canvas.toBlob(
        (blob) => {
          if (blob) {
            resolve(blob)
          } else {
            reject(new Error('Failed to create blob from canvas'))
          }
        },
        file.type || 'image/jpeg',
        0.95
      )
    }

    img.onerror = () => {
      reject(new Error('Failed to load image'))
    }

    const reader = new FileReader()
    reader.onload = (e) => {
      img.src = e.target.result
    }
    reader.onerror = () => {
      reject(new Error('Failed to read file'))
    }
    reader.readAsDataURL(file)
  })
}

/**
 * Get image dimensions
 * @param {File|Blob} file - Image file
 * @returns {Promise<{width: number, height: number}>} Image dimensions
 */
export async function getImageDimensions(file) {
  return new Promise((resolve, reject) => {
    const img = new Image()

    img.onload = () => {
      resolve({
        width: img.width,
        height: img.height
      })
    }

    img.onerror = () => {
      reject(new Error('Failed to load image'))
    }

    const reader = new FileReader()
    reader.onload = (e) => {
      img.src = e.target.result
    }
    reader.onerror = () => {
      reject(new Error('Failed to read file'))
    }
    reader.readAsDataURL(file)
  })
}

/**
 * Convert blob to file
 * @param {Blob} blob - Image blob
 * @param {string} filename - Desired filename
 * @returns {File} File object
 */
export function blobToFile(blob, filename = `image-${Date.now()}.jpg`) {
  return new File([blob], filename, {
    type: blob.type || 'image/jpeg',
    lastModified: Date.now()
  })
}

/**
 * Create object URL for preview
 * @param {File|Blob} file - Image file
 * @returns {string} Object URL
 */
export function createPreviewUrl(file) {
  if (!file) return null
  return URL.createObjectURL(file)
}

/**
 * Revoke object URL
 * @param {string} url - Object URL to revoke
 */
export function revokePreviewUrl(url) {
  if (url && url.startsWith('blob:')) {
    URL.revokeObjectURL(url)
  }
}

/**
 * Compress image to reduce file size
 * @param {File|Blob} file - Image file
 * @param {number} quality - Quality (0-1, default 0.8)
 * @param {number} maxSizeMB - Max file size in MB (optional)
 * @returns {Promise<Blob>} Compressed image blob
 */
export async function compressImage(file, quality = 0.8, maxSizeMB = null) {
  return new Promise((resolve, reject) => {
    const img = new Image()
    const canvas = document.createElement('canvas')
    const ctx = canvas.getContext('2d')

    img.onload = () => {
      canvas.width = img.width
      canvas.height = img.height

      ctx.imageSmoothingEnabled = true
      ctx.imageSmoothingQuality = 'high'

      ctx.drawImage(img, 0, 0)

      canvas.toBlob(
        (blob) => {
          if (blob) {
            // Check if we need to reduce quality further
            if (maxSizeMB && blob.size > maxSizeMB * 1024 * 1024 && quality > 0.1) {
              // Recursively compress with lower quality
              compressImage(blob, quality * 0.8, maxSizeMB).then(resolve).catch(reject)
            } else {
              resolve(blob)
            }
          } else {
            reject(new Error('Failed to create blob from canvas'))
          }
        },
        'image/jpeg', // Always use JPEG for compression
        quality
      )
    }

    img.onerror = () => {
      reject(new Error('Failed to load image'))
    }

    const reader = new FileReader()
    reader.onload = (e) => {
      img.src = e.target.result
    }
    reader.onerror = () => {
      reject(new Error('Failed to read file'))
    }
    reader.readAsDataURL(file)
  })
}

/**
 * Generate thumbnail from image
 * @param {File|Blob} file - Image file
 * @param {number} size - Thumbnail size (width/height)
 * @returns {Promise<string>} Base64 data URL of thumbnail
 */
export async function generateThumbnail(file, size = 20) {
  return new Promise((resolve, reject) => {
    const img = new Image()
    const canvas = document.createElement('canvas')
    const ctx = canvas.getContext('2d')

    img.onload = () => {
      // Calculate thumbnail dimensions maintaining aspect ratio
      const aspectRatio = img.width / img.height
      let width, height

      if (aspectRatio > 1) {
        width = size
        height = size / aspectRatio
      } else {
        height = size
        width = size * aspectRatio
      }

      canvas.width = width
      canvas.height = height

      ctx.imageSmoothingEnabled = true
      ctx.imageSmoothingQuality = 'medium'

      ctx.drawImage(img, 0, 0, width, height)

      // Return as data URL
      resolve(canvas.toDataURL('image/jpeg', 0.5))
    }

    img.onerror = () => {
      reject(new Error('Failed to load image'))
    }

    const reader = new FileReader()
    reader.onload = (e) => {
      img.src = e.target.result
    }
    reader.onerror = () => {
      reject(new Error('Failed to read file'))
    }
    reader.readAsDataURL(file)
  })
}

/**
 * Validate image file
 * @param {File} file - File to validate
 * @param {Object} options - Validation options
 * @returns {{valid: boolean, error: string|null}}
 */
export function validateImageFile(file, options = {}) {
  const {
    maxSizeMB = 10,
    allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'],
    maxWidth = null,
    maxHeight = null
  } = options

  // Check file type
  if (!file.type.startsWith('image/')) {
    return { valid: false, error: 'File must be an image' }
  }

  // Check allowed types
  if (!allowedTypes.includes(file.type)) {
    return {
      valid: false,
      error: `Image type must be one of: ${allowedTypes.map((t) => t.replace('image/', '')).join(', ')}`
    }
  }

  // Check file size
  const maxSizeBytes = maxSizeMB * 1024 * 1024
  if (file.size > maxSizeBytes) {
    return { valid: false, error: `Image size must be less than ${maxSizeMB}MB` }
  }

  return { valid: true, error: null }
}
