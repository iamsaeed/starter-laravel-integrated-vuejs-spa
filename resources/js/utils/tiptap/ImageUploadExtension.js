import Image from '@tiptap/extension-image'
import { Plugin } from '@tiptap/pm/state'

/**
 * Custom TipTap Image Extension with upload support
 *
 * Features:
 * - Paste images from clipboard (Ctrl+V)
 * - Drag & drop images into editor
 * - Automatic upload to Spatie Media Library
 * - Replace blob URLs with permanent URLs after upload
 * - Loading states and error handling
 */
export const ImageUploadExtension = Image.extend({
  addOptions() {
    return {
      ...this.parent?.(),
      uploadFunction: null, // Function to handle image upload
      deleteFunction: null, // Function to handle image deletion
      onUploadStart: null,  // Callback when upload starts
      onUploadComplete: null, // Callback when upload completes
      onUploadError: null,  // Callback when upload fails
      onImageDelete: null,  // Callback when image is deleted from editor
    }
  },

  addStorage() {
    return {
      uploadedImages: new Map(), // Track uploaded images: URL -> mediaId
    }
  },

  addProseMirrorPlugins() {
    const uploadFunction = this.options.uploadFunction
    const deleteFunction = this.options.deleteFunction
    const onUploadStart = this.options.onUploadStart
    const onUploadComplete = this.options.onUploadComplete
    const onUploadError = this.options.onUploadError
    const onImageDelete = this.options.onImageDelete
    const storage = this.storage

    return [
      new Plugin({
        props: {
          // Handle paste events
          handlePaste: (view, event) => {
            const items = Array.from(event.clipboardData?.items || [])
            const imageItems = items.filter(item => item.type.indexOf('image') !== -1)

            if (imageItems.length === 0) {
              return false
            }

            event.preventDefault()

            imageItems.forEach(item => {
              const file = item.getAsFile()
              if (file) {
                handleImageUpload(file, view, uploadFunction, onUploadStart, onUploadComplete, onUploadError, undefined, storage)
              }
            })

            return true
          },

          // Handle drop events
          handleDrop: (view, event) => {
            const hasFiles = event.dataTransfer &&
              event.dataTransfer.files &&
              event.dataTransfer.files.length > 0

            if (!hasFiles) {
              return false
            }

            const images = Array.from(event.dataTransfer.files).filter(file =>
              file.type.indexOf('image') !== -1
            )

            if (images.length === 0) {
              return false
            }

            event.preventDefault()

            const coordinates = view.posAtCoords({
              left: event.clientX,
              top: event.clientY
            })

            images.forEach(file => {
              handleImageUpload(file, view, uploadFunction, onUploadStart, onUploadComplete, onUploadError, coordinates?.pos, storage)
            })

            return true
          }
        }
      }),

      // Plugin to track image deletions
      new Plugin({
        appendTransaction(transactions, oldState, newState) {
          if (!deleteFunction || typeof deleteFunction !== 'function') {
            return null
          }

          // Get all image URLs from old and new states
          const oldImages = new Set()
          const newImages = new Set()

          oldState.doc.descendants((node) => {
            if (node.type.name === 'image' && node.attrs.src) {
              // Only track uploaded images (not blob URLs)
              if (!node.attrs.src.startsWith('blob:')) {
                oldImages.add(node.attrs.src)
              }
            }
          })

          newState.doc.descendants((node) => {
            if (node.type.name === 'image' && node.attrs.src) {
              if (!node.attrs.src.startsWith('blob:')) {
                newImages.add(node.attrs.src)
              }
            }
          })

          // Find deleted images
          const deletedImages = [...oldImages].filter(url => !newImages.has(url))

          // Delete images that were removed from editor
          deletedImages.forEach(url => {
            const mediaId = storage.uploadedImages.get(url)
            if (mediaId) {
              handleImageDelete(url, mediaId, deleteFunction, onImageDelete, storage)
            }
          })

          return null
        }
      })
    ]
  }
})

// Helper function to generate unique filename
function generateUniqueFilename(file) {
  // Get file extension
  const extension = file.name.split('.').pop() || 'png'

  // Generate unique filename with timestamp and random string
  const timestamp = Date.now()
  const randomString = Math.random().toString(36).substring(2, 8)

  return `image-${timestamp}-${randomString}.${extension}`
}

// Helper function to rename file
function renameFile(file, newName) {
  return new File([file], newName, {
    type: file.type,
    lastModified: file.lastModified,
  })
}

// Helper function to handle image deletion
function handleImageDelete(url, mediaId, deleteFunction, onImageDelete, storage) {
  console.log('Deleting image:', { url, mediaId })

  deleteFunction(mediaId)
    .then(() => {
      // Remove from tracking
      storage.uploadedImages.delete(url)

      // Notify callback
      if (onImageDelete) {
        onImageDelete({ url, mediaId })
      }

      console.log('Image deleted successfully:', mediaId)
    })
    .catch(error => {
      console.error('Failed to delete image:', error)
      // Keep in tracking in case of failure
    })
}

// Helper function to handle image upload (outside the extension)
function handleImageUpload(file, view, uploadFunction, onUploadStart, onUploadComplete, onUploadError, pos, storage) {
  // Validate upload function exists
  if (!uploadFunction || typeof uploadFunction !== 'function') {
    console.error('ImageUploadExtension: uploadFunction not provided')
    if (onUploadError) {
      onUploadError(new Error('Upload function not configured'))
    }
    return
  }

  // Validate file type
  if (!file.type.startsWith('image/')) {
    const error = new Error('Only image files are allowed')
    if (onUploadError) {
      onUploadError(error)
    }
    return
  }

  // Validate file size (10MB max)
  const maxSize = 10 * 1024 * 1024 // 10MB
  if (file.size > maxSize) {
    const error = new Error('Image size must be less than 10MB')
    if (onUploadError) {
      onUploadError(error)
    }
    return
  }

  // Generate unique filename for the file
  const uniqueFilename = generateUniqueFilename(file)
  const renamedFile = renameFile(file, uniqueFilename)

  // Create temporary blob URL for immediate preview
  const tempUrl = URL.createObjectURL(renamedFile)
  const loadingClass = 'tiptap-image-uploading'

  // Insert image with temporary URL and loading class
  const insertPos = pos !== undefined ? pos : view.state.selection.from
  const node = view.state.schema.nodes.image.create({
    src: tempUrl,
    class: loadingClass,
    'data-blur-placeholder': '', // Will be updated after upload
    'data-media-id': '' // Will be updated after upload
  })

  const transaction = view.state.tr.insert(insertPos, node)
  view.dispatch(transaction)

  // Notify upload start
  if (onUploadStart) {
    onUploadStart(renamedFile)
  }

  // Upload the image with unique filename
  uploadFunction(renamedFile)
    .then(response => {
      // Get the permanent URL from response
      const permanentUrl = response.url || response.data?.url

      if (!permanentUrl) {
        throw new Error('No URL returned from upload')
      }

      // Get blur placeholder URL and media ID
      const blurPlaceholderUrl = response.blur_placeholder_url || response.data?.blur_placeholder_url || ''
      const mediaId = response.id || response.data?.id

      // Store media ID for deletion tracking
      if (mediaId && storage) {
        storage.uploadedImages.set(permanentUrl, mediaId)
        console.log('Stored media ID for tracking:', { url: permanentUrl, mediaId, blurPlaceholderUrl })
      }

      // Find and replace the temporary image with permanent URL and blur placeholder
      replaceImageUrl(view, tempUrl, permanentUrl, blurPlaceholderUrl, mediaId)

      // Revoke the temporary blob URL
      URL.revokeObjectURL(tempUrl)

      // Notify upload complete
      if (onUploadComplete) {
        onUploadComplete(response)
      }
    })
    .catch(error => {
      console.error('Image upload failed:', error)

      // Remove the failed image
      removeImageByUrl(view, tempUrl)

      // Revoke the temporary blob URL
      URL.revokeObjectURL(tempUrl)

      // Notify upload error
      if (onUploadError) {
        onUploadError(error)
      }
    })
}

// Helper function to replace image URL in the document
function replaceImageUrl(view, oldUrl, newUrl, blurPlaceholderUrl, mediaId) {
  const { state } = view
  const { tr, doc } = state
  let modified = false

  doc.descendants((node, pos) => {
    if (node.type.name === 'image' && node.attrs.src === oldUrl) {
      // Remove loading class and update URL, blur placeholder, and media ID
      const attrs = {
        ...node.attrs,
        src: newUrl,
        'data-blur-placeholder': blurPlaceholderUrl || '',
        'data-media-id': mediaId || '',
        class: node.attrs.class?.replace('tiptap-image-uploading', '').trim() || null
      }

      tr.setNodeMarkup(pos, null, attrs)
      modified = true
    }
  })

  if (modified) {
    view.dispatch(tr)
  }
}

// Helper function to remove image by URL
function removeImageByUrl(view, url) {
  const { state } = view
  const { tr, doc } = state
  let modified = false

  doc.descendants((node, pos) => {
    if (node.type.name === 'image' && node.attrs.src === url) {
      tr.delete(pos, pos + node.nodeSize)
      modified = true
    }
  })

  if (modified) {
    view.dispatch(tr)
  }
}
