import Image from '@tiptap/extension-image'
import { Plugin } from '@tiptap/pm/state'
import { VueNodeViewRenderer } from '@tiptap/vue-3'
// import { generateThumbnail } from '@/utils/imageManipulation' // Not needed - blur placeholders generated server-side
import ResizableImageNodeView from '@/components/tiptap/ResizableImageNodeView.vue'

/**
 * Enhanced TipTap Image Extension
 *
 * Features:
 * - Paste images from clipboard (Ctrl+V)
 * - Drag & drop images into editor
 * - Automatic upload with progress tracking
 * - Blur placeholder support
 * - Lightbox on click
 * - Image deletion tracking
 * - Loading states with blur-up effect
 */
export const EnhancedImageExtension = Image.extend({
  name: 'enhancedImage',

  addOptions() {
    return {
      ...this.parent?.(),
      uploadFunction: null, // Function to handle image upload
      deleteFunction: null, // Function to handle image deletion
      onUploadStart: null,  // Callback when upload starts
      onUploadComplete: null, // Callback when upload completes
      onUploadError: null,  // Callback when upload fails
      onUploadProgress: null, // Callback for upload progress updates
      onImageDelete: null,  // Callback when image is deleted from editor
      onImageClick: null, // Callback when image is clicked
      onImageRightClick: null, // Callback when image is right-clicked
      enableLightbox: true, // Enable lightbox on image click
      enableContextMenu: true, // Enable context menu on right-click
      // generatePlaceholder: true, // No longer needed - blur placeholders generated server-side
    }
  },

  addStorage() {
    return {
      uploadedImages: new Map(), // Track uploaded images: URL -> { mediaId, blurPlaceholder }
      uploadProgress: new Map(), // Track upload progress: tempUrl -> { loaded, total, percentage }
    }
  },

  addAttributes() {
    return {
      ...this.parent?.(),
      src: {
        default: null,
      },
      alt: {
        default: null,
      },
      title: {
        default: null,
      },
      width: {
        default: null,
        parseHTML: element => {
          const width = element.getAttribute('width')
          return width ? parseInt(width) : null
        },
        renderHTML: attributes => {
          if (!attributes.width) {
            return {}
          }
          return {
            width: attributes.width
          }
        }
      },
      'data-blur-placeholder': {
        default: null,
        parseHTML: element => element.getAttribute('data-blur-placeholder'),
        renderHTML: attributes => {
          if (!attributes['data-blur-placeholder']) {
            return {}
          }
          return {
            'data-blur-placeholder': attributes['data-blur-placeholder']
          }
        }
      },
      'data-media-id': {
        default: null,
        parseHTML: element => element.getAttribute('data-media-id'),
        renderHTML: attributes => {
          if (!attributes['data-media-id']) {
            return {}
          }
          return {
            'data-media-id': attributes['data-media-id']
          }
        }
      },
      'data-loading': {
        default: null,
        parseHTML: element => element.getAttribute('data-loading'),
        renderHTML: attributes => {
          if (!attributes['data-loading']) {
            return {}
          }
          return {
            'data-loading': attributes['data-loading']
          }
        }
      },
      class: {
        default: null,
      }
    }
  },

  addNodeView() {
    return VueNodeViewRenderer(ResizableImageNodeView)
  },

  addProseMirrorPlugins() {
    const uploadFunction = this.options.uploadFunction
    const deleteFunction = this.options.deleteFunction
    const onUploadStart = this.options.onUploadStart
    const onUploadComplete = this.options.onUploadComplete
    const onUploadError = this.options.onUploadError
    const onUploadProgress = this.options.onUploadProgress
    const onImageDelete = this.options.onImageDelete
    const onImageClick = this.options.onImageClick
    const onImageRightClick = this.options.onImageRightClick
    const enableLightbox = this.options.enableLightbox
    const enableContextMenu = this.options.enableContextMenu
    // const generatePlaceholder = this.options.generatePlaceholder // Not needed anymore
    const storage = this.storage

    return [
      // Paste handler
      new Plugin({
        props: {
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
                handleImageUpload(
                  file,
                  view,
                  uploadFunction,
                  onUploadStart,
                  onUploadComplete,
                  onUploadError,
                  onUploadProgress,
                  undefined,
                  storage,
                )
              }
            })

            return true
          },

          // Drop handler
          handleDrop: (view, event) => {
            // Check if this is an internal drag (moving existing content within editor)
            // Internal drags have effectAllowed = 'move' and may have HTML data
            const isInternalDrag = event.dataTransfer.effectAllowed === 'move' ||
              event.dataTransfer.types.includes('text/html')

            // If it's an internal drag, let TipTap handle it (don't upload)
            if (isInternalDrag) {
              return false
            }

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
              handleImageUpload(
                file,
                view,
                uploadFunction,
                onUploadStart,
                onUploadComplete,
                onUploadError,
                onUploadProgress,
                coordinates?.pos,
                storage,
              )
            })

            return true
          },

          // Click handler for lightbox
          handleClick: (view, pos, event) => {
            const node = view.state.doc.nodeAt(pos)

            if (node && node.type.name === 'enhancedImage') {
              const src = node.attrs.src
              const alt = node.attrs.alt
              const mediaId = node.attrs['data-media-id']

              if (enableLightbox && onImageClick) {
                event.preventDefault()
                onImageClick({ src, alt, mediaId, node, pos })
                return true
              }
            }

            return false
          },

          // Right-click handler for context menu
          handleContextMenu: (view, pos, event) => {
            const node = view.state.doc.nodeAt(pos)

            if (node && node.type.name === 'enhancedImage') {
              const src = node.attrs.src
              const alt = node.attrs.alt
              const mediaId = node.attrs['data-media-id']

              if (enableContextMenu && onImageRightClick) {
                event.preventDefault()
                onImageRightClick({ src, alt, mediaId, node, pos, event })
                return true
              }
            }

            return false
          }
        }
      }),

      // DOM-based context menu handler (for better right-click support)
      new Plugin({
        props: {
          handleDOMEvents: {
            contextmenu: (view, event) => {
              const pos = view.posAtCoords({ left: event.clientX, top: event.clientY })
              if (!pos) return false

              const node = view.state.doc.nodeAt(pos.pos)
              if (node && node.type.name === 'enhancedImage') {
                const src = node.attrs.src
                const alt = node.attrs.alt
                const mediaId = node.attrs['data-media-id']

                if (enableContextMenu && onImageRightClick) {
                  event.preventDefault()
                  onImageRightClick({ src, alt, mediaId, node, pos: pos.pos, event })
                  return true
                }
              }

              return false
            }
          }
        }
      }),

      // Image deletion tracker
      new Plugin({
        appendTransaction(transactions, oldState, newState) {
          if (!deleteFunction || typeof deleteFunction !== 'function') {
            return null
          }

          const oldImages = new Set()
          const newImages = new Set()

          oldState.doc.descendants((node) => {
            if (node.type.name === 'enhancedImage' && node.attrs.src) {
              if (!node.attrs.src.startsWith('blob:')) {
                oldImages.add(node.attrs.src)
              }
            }
          })

          newState.doc.descendants((node) => {
            if (node.type.name === 'enhancedImage' && node.attrs.src) {
              if (!node.attrs.src.startsWith('blob:')) {
                newImages.add(node.attrs.src)
              }
            }
          })

          const deletedImages = [...oldImages].filter(url => !newImages.has(url))

          deletedImages.forEach(url => {
            const imageData = storage.uploadedImages.get(url)
            if (imageData?.mediaId) {
              handleImageDelete(url, imageData.mediaId, deleteFunction, onImageDelete, storage)
            }
          })

          return null
        }
      })
    ]
  }
})

// Helper: Generate unique filename
function generateUniqueFilename(file) {
  const extension = file.name.split('.').pop() || 'png'
  const timestamp = Date.now()
  const randomString = Math.random().toString(36).substring(2, 8)
  return `image-${timestamp}-${randomString}.${extension}`
}

// Helper: Rename file
function renameFile(file, newName) {
  return new File([file], newName, {
    type: file.type,
    lastModified: file.lastModified,
  })
}

// Helper: Handle image deletion
function handleImageDelete(url, mediaId, deleteFunction, onImageDelete, storage) {
  console.log('Deleting image:', { url, mediaId })

  deleteFunction(mediaId)
    .then(() => {
      storage.uploadedImages.delete(url)

      if (onImageDelete) {
        onImageDelete({ url, mediaId })
      }

      console.log('Image deleted successfully:', mediaId)
    })
    .catch(error => {
      console.error('Failed to delete image:', error)
    })
}

// Helper: Handle image upload with progress
async function handleImageUpload(
  file,
  view,
  uploadFunction,
  onUploadStart,
  onUploadComplete,
  onUploadError,
  onUploadProgress,
  pos,
  storage,
) {
  if (!uploadFunction || typeof uploadFunction !== 'function') {
    console.error('EnhancedImageExtension: uploadFunction not provided')
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
  const maxSize = 10 * 1024 * 1024
  if (file.size > maxSize) {
    const error = new Error('Image size must be less than 10MB')
    if (onUploadError) {
      onUploadError(error)
    }
    return
  }

  // Generate unique filename
  const uniqueFilename = generateUniqueFilename(file)
  const renamedFile = renameFile(file, uniqueFilename)

  // Create temporary blob URL
  const tempUrl = URL.createObjectURL(renamedFile)

  // Don't generate client-side placeholder - we'll use server-generated one
  // This avoids base64 strings in the database

  // Insert image with loading state (blur placeholder will be added after upload)
  const insertPos = pos !== undefined ? pos : view.state.selection.from
  const node = view.state.schema.nodes.enhancedImage.create({
    src: tempUrl,
    'data-blur-placeholder': '', // Empty initially, will be filled by server response
    'data-loading': 'true',
    class: 'tiptap-image-uploading'
  })

  const transaction = view.state.tr.insert(insertPos, node)
  view.dispatch(transaction)

  // Notify upload start
  if (onUploadStart) {
    onUploadStart(renamedFile)
  }

  // Initialize progress tracking
  if (storage) {
    storage.uploadProgress.set(tempUrl, { loaded: 0, total: file.size, percentage: 0 })
  }

  try {
    // Upload with progress tracking
    const response = await uploadFunction(renamedFile, {
      onUploadProgress: (progressEvent) => {
        const { loaded, total } = progressEvent
        const percentage = total > 0 ? Math.round((loaded / total) * 100) : 0

        // Update storage
        if (storage) {
          storage.uploadProgress.set(tempUrl, { loaded, total, percentage })
        }

        // Notify progress callback
        if (onUploadProgress) {
          onUploadProgress({ tempUrl, loaded, total, percentage })
        }

        // Update image attributes with progress
        updateImageProgress(view, tempUrl, percentage)
      }
    })

    // Get permanent URL and metadata from response
    const permanentUrl = response.url || response.data?.url
    const blurPlaceholder = response.blur_placeholder_url || response.blur_placeholder || response.data?.blur_placeholder_url || response.data?.blur_placeholder
    const mediaId = response.id || response.data?.id

    if (!permanentUrl) {
      throw new Error('No URL returned from upload')
    }

    // Store image data
    if (mediaId && storage) {
      storage.uploadedImages.set(permanentUrl, {
        mediaId,
        blurPlaceholder: blurPlaceholder || ''
      })
      console.log('Stored media data:', {
        url: permanentUrl,
        mediaId,
        hasPlaceholder: !!blurPlaceholder,
        blurPlaceholderUrl: blurPlaceholder
      })
    }

    // Replace temporary image with permanent one
    replaceImageUrl(view, tempUrl, permanentUrl, {
      'data-media-id': mediaId,
      'data-blur-placeholder': blurPlaceholder || '',
      'data-loading': null,
      class: null
    })

    // Cleanup
    URL.revokeObjectURL(tempUrl)
    if (storage) {
      storage.uploadProgress.delete(tempUrl)
    }

    // Notify upload complete
    if (onUploadComplete) {
      onUploadComplete(response)
    }
  } catch (error) {
    console.error('Image upload failed:', error)

    // Remove failed image
    removeImageByUrl(view, tempUrl)

    // Cleanup
    URL.revokeObjectURL(tempUrl)
    if (storage) {
      storage.uploadProgress.delete(tempUrl)
    }

    // Notify error
    if (onUploadError) {
      onUploadError(error)
    }
  }
}

// Helper: Update image progress
function updateImageProgress(view, url, percentage) {
  const { state } = view
  const { tr, doc } = state
  let modified = false

  doc.descendants((node, pos) => {
    if (node.type.name === 'enhancedImage' && node.attrs.src === url) {
      const attrs = {
        ...node.attrs,
        title: `Uploading... ${percentage}%`
      }

      tr.setNodeMarkup(pos, null, attrs)
      modified = true
    }
  })

  if (modified) {
    view.dispatch(tr)
  }
}

// Helper: Replace image URL and attributes
function replaceImageUrl(view, oldUrl, newUrl, additionalAttrs = {}) {
  const { state } = view
  const { tr, doc } = state
  let modified = false

  doc.descendants((node, pos) => {
    if (node.type.name === 'enhancedImage' && node.attrs.src === oldUrl) {
      const attrs = {
        ...node.attrs,
        src: newUrl,
        ...additionalAttrs,
        title: null // Remove progress title
      }

      tr.setNodeMarkup(pos, null, attrs)
      modified = true
    }
  })

  if (modified) {
    view.dispatch(tr)
  }
}

// Helper: Remove image by URL
function removeImageByUrl(view, url) {
  const { state } = view
  const { tr, doc } = state
  let modified = false

  doc.descendants((node, pos) => {
    if (node.type.name === 'enhancedImage' && node.attrs.src === url) {
      tr.delete(pos, pos + node.nodeSize)
      modified = true
    }
  })

  if (modified) {
    view.dispatch(tr)
  }
}
