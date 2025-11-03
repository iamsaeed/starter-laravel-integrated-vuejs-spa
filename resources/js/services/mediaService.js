/**
 * Media Service
 * Handles all API calls for media uploads and management
 */

export const mediaService = {
  /**
   * Upload a single file
   * @param {File} file - The file to upload
   * @param {string} modelType - Laravel model class (e.g., 'App\\Models\\Comment')
   * @param {number} modelId - Model instance ID
   * @param {string} collection - Spatie media collection name
   * @param {Object} options - Upload options
   * @param {Function} options.onUploadProgress - Progress callback
   * @param {Object} options.cancelToken - Axios cancel token
   */
  async upload(file, modelType, modelId, collection = 'default', options = {}) {
    const formData = new FormData()
    formData.append('file', file)
    formData.append('model_type', modelType)
    formData.append('model_id', modelId)
    formData.append('collection', collection)

    const response = await window.axios.post('/api/media/upload', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      },
      onUploadProgress: options.onUploadProgress,
      cancelToken: options.cancelToken
    })

    return response.data
  },

  /**
   * Upload multiple files
   * @param {File[]} files - Array of files to upload
   * @param {string} modelType - Laravel model class
   * @param {number} modelId - Model instance ID
   * @param {string} collection - Spatie media collection name
   * @param {Object} options - Upload options
   * @param {Function} options.onUploadProgress - Progress callback
   * @param {Object} options.cancelToken - Axios cancel token
   */
  async uploadMultiple(files, modelType, modelId, collection = 'default', options = {}) {
    const formData = new FormData()

    files.forEach(file => {
      formData.append('files[]', file)
    })

    formData.append('model_type', modelType)
    formData.append('model_id', modelId)
    formData.append('collection', collection)

    const response = await window.axios.post('/api/media/upload-multiple', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      },
      onUploadProgress: options.onUploadProgress,
      cancelToken: options.cancelToken
    })

    return response.data
  },

  /**
   * Delete a media file
   * @param {number} mediaId - Media ID to delete
   */
  async delete(mediaId) {
    const response = await window.axios.delete(`/api/media/${mediaId}`)
    return response.data
  },

  /**
   * Get media URL with optional conversion
   */
  getMediaUrl(media, conversion = null) {
    if (!media) return null

    if (conversion && media[conversion]) {
      return media[conversion]
    }

    return media.url || media.thumbnail || null
  },

  /**
   * Process image (used internally by components)
   * Converts blob to file for upload
   */
  blobToFile(blob, filename = `image-${Date.now()}.jpg`) {
    return new File([blob], filename, { type: blob.type || 'image/jpeg' })
  },

  /**
   * Create object URL for preview
   */
  createPreviewUrl(file) {
    if (!file) return null
    return URL.createObjectURL(file)
  },

  /**
   * Revoke object URL
   */
  revokePreviewUrl(url) {
    if (url && url.startsWith('blob:')) {
      URL.revokeObjectURL(url)
    }
  },

  /**
   * Upload image from blob (for paste/drag-drop)
   * @param {Blob} blob - Image blob
   * @param {string} filename - Filename for the blob
   * @param {string} modelType - Laravel model class
   * @param {number} modelId - Model instance ID
   * @param {string} collection - Spatie media collection name
   */
  async uploadFromBlob(blob, filename, modelType, modelId, collection = 'default') {
    const file = this.blobToFile(blob, filename)
    return this.upload(file, modelType, modelId, collection)
  },

  /**
   * Upload image for rich text editor
   * Simplified method specifically for RichTextEditor component
   * @param {File} file - Image file
   * @param {string} modelType - Laravel model class
   * @param {number} modelId - Model instance ID
   * @param {string} collection - Spatie media collection name
   * @param {Object} options - Upload options (onUploadProgress, cancelToken)
   */
  async uploadImageForEditor(file, modelType, modelId, collection = 'description-images', options = {}) {
    return this.upload(file, modelType, modelId, collection, options)
  },

  /**
   * Upload a temporary image for rich text editor
   * @param {File} file - Image file
   * @returns {Promise<{id: number, url: string, blur_placeholder_url: string}>}
   */
  async uploadTempImage(file) {
    const formData = new FormData()
    formData.append('image', file)

    const response = await window.axios.post(
      '/api/temp-images/upload',
      formData,
      {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      }
    )

    return response.data.data // Returns { id, url, blur_placeholder_url, ... }
  },

  /**
   * Cleanup temporary images for the authenticated user
   * @param {number[]|null} tempImageIds - Array of temp image IDs to cleanup (null = all)
   */
  async cleanupTempImages(tempImageIds = null) {
    const data = tempImageIds ? { temp_image_ids: tempImageIds } : {}
    const response = await window.axios.post(
      '/api/temp-images/cleanup',
      data
    )
    return response.data
  }
}
