import { ref, computed } from 'vue'

/**
 * Upload Progress Tracker Composable
 * Manages multiple concurrent upload progress tracking
 *
 * @returns {Object} Upload progress tracking functions and state
 */
export function useUploadProgress() {
  // Upload tracking map: uploadId -> progress data
  const uploads = ref(new Map())

  /**
   * Create a new upload tracker
   *
   * @param {string} uploadId - Unique upload identifier
   * @param {File} file - File being uploaded
   * @returns {Object} Upload progress tracker
   */
  function createUpload(uploadId, file) {
    const uploadData = {
      id: uploadId,
      file,
      fileName: file.name,
      fileSize: file.size,
      progress: 0,
      loaded: 0,
      total: file.size,
      speed: 0,
      eta: 0,
      startTime: Date.now(),
      lastUpdateTime: Date.now(),
      lastLoaded: 0,
      isCompleted: false,
      hasError: false,
      errorMessage: null,
      cancelToken: null
    }

    uploads.value.set(uploadId, uploadData)

    return uploadData
  }

  /**
   * Update upload progress
   *
   * @param {string} uploadId - Upload identifier
   * @param {ProgressEvent} progressEvent - Axios progress event
   */
  function updateProgress(uploadId, progressEvent) {
    const upload = uploads.value.get(uploadId)
    if (!upload) return

    const { loaded, total } = progressEvent
    const now = Date.now()

    // Calculate progress percentage
    upload.progress = total > 0 ? Math.round((loaded / total) * 100) : 0
    upload.loaded = loaded
    upload.total = total

    // Calculate upload speed (bytes per second)
    const timeDiff = (now - upload.lastUpdateTime) / 1000 // Convert to seconds
    const loadedDiff = loaded - upload.lastLoaded

    if (timeDiff > 0) {
      // Calculate instantaneous speed
      const instantSpeed = loadedDiff / timeDiff

      // Smooth speed using exponential moving average
      upload.speed = upload.speed === 0
        ? instantSpeed
        : upload.speed * 0.7 + instantSpeed * 0.3
    }

    // Calculate ETA (estimated time of arrival)
    if (upload.speed > 0) {
      const remaining = total - loaded
      upload.eta = remaining / upload.speed
    }

    // Update tracking values
    upload.lastUpdateTime = now
    upload.lastLoaded = loaded
  }

  /**
   * Mark upload as completed
   *
   * @param {string} uploadId - Upload identifier
   */
  function completeUpload(uploadId) {
    const upload = uploads.value.get(uploadId)
    if (!upload) return

    upload.isCompleted = true
    upload.progress = 100
    upload.loaded = upload.total
    upload.eta = 0
  }

  /**
   * Mark upload as failed
   *
   * @param {string} uploadId - Upload identifier
   * @param {string} errorMessage - Error message
   */
  function failUpload(uploadId, errorMessage = 'Upload failed') {
    const upload = uploads.value.get(uploadId)
    if (!upload) return

    upload.hasError = true
    upload.errorMessage = errorMessage
    upload.eta = 0
  }

  /**
   * Cancel upload
   *
   * @param {string} uploadId - Upload identifier
   */
  function cancelUpload(uploadId) {
    const upload = uploads.value.get(uploadId)
    if (!upload) return

    if (upload.cancelToken) {
      upload.cancelToken.cancel('Upload cancelled by user')
    }

    removeUpload(uploadId)
  }

  /**
   * Remove upload from tracking
   *
   * @param {string} uploadId - Upload identifier
   */
  function removeUpload(uploadId) {
    uploads.value.delete(uploadId)
  }

  /**
   * Get upload data
   *
   * @param {string} uploadId - Upload identifier
   * @returns {Object|null} Upload data
   */
  function getUpload(uploadId) {
    return uploads.value.get(uploadId) || null
  }

  /**
   * Get all active uploads
   *
   * @returns {Array} Array of active uploads
   */
  function getActiveUploads() {
    return Array.from(uploads.value.values()).filter(
      (upload) => !upload.isCompleted && !upload.hasError
    )
  }

  /**
   * Get all completed uploads
   *
   * @returns {Array} Array of completed uploads
   */
  function getCompletedUploads() {
    return Array.from(uploads.value.values()).filter((upload) => upload.isCompleted)
  }

  /**
   * Get all failed uploads
   *
   * @returns {Array} Array of failed uploads
   */
  function getFailedUploads() {
    return Array.from(uploads.value.values()).filter((upload) => upload.hasError)
  }

  /**
   * Clear completed uploads
   */
  function clearCompletedUploads() {
    const completed = getCompletedUploads()
    completed.forEach((upload) => {
      uploads.value.delete(upload.id)
    })
  }

  /**
   * Clear all uploads
   */
  function clearAllUploads() {
    uploads.value.clear()
  }

  /**
   * Set cancel token for upload
   *
   * @param {string} uploadId - Upload identifier
   * @param {Object} cancelToken - Axios cancel token
   */
  function setCancelToken(uploadId, cancelToken) {
    const upload = uploads.value.get(uploadId)
    if (upload) {
      upload.cancelToken = cancelToken
    }
  }

  // Computed properties
  const activeUploadsCount = computed(() => getActiveUploads().length)
  const completedUploadsCount = computed(() => getCompletedUploads().length)
  const failedUploadsCount = computed(() => getFailedUploads().length)
  const totalUploadsCount = computed(() => uploads.value.size)
  const hasActiveUploads = computed(() => activeUploadsCount.value > 0)

  /**
   * Get overall progress across all active uploads
   */
  const overallProgress = computed(() => {
    const active = getActiveUploads()
    if (active.length === 0) return 0

    const totalProgress = active.reduce((sum, upload) => sum + upload.progress, 0)
    return Math.round(totalProgress / active.length)
  })

  /**
   * Get total upload speed across all active uploads
   */
  const totalSpeed = computed(() => {
    const active = getActiveUploads()
    return active.reduce((sum, upload) => sum + upload.speed, 0)
  })

  return {
    // State
    uploads,
    activeUploadsCount,
    completedUploadsCount,
    failedUploadsCount,
    totalUploadsCount,
    hasActiveUploads,
    overallProgress,
    totalSpeed,

    // Actions
    createUpload,
    updateProgress,
    completeUpload,
    failUpload,
    cancelUpload,
    removeUpload,
    getUpload,
    getActiveUploads,
    getCompletedUploads,
    getFailedUploads,
    clearCompletedUploads,
    clearAllUploads,
    setCancelToken
  }
}

/**
 * Helper function to generate unique upload ID
 *
 * @returns {string} Unique upload ID
 */
export function generateUploadId() {
  return `upload-${Date.now()}-${Math.random().toString(36).substring(2, 9)}`
}
