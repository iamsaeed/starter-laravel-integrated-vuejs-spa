<template>
  <div class="rich-text-editor">
    <!-- Toolbar -->
    <div v-if="editor" class="editor-toolbar">
      <!-- Text Formatting -->
      <div class="toolbar-group">
        <button
          type="button"
          @click="editor.chain().focus().toggleBold().run()"
          :class="{ 'is-active': editor.isActive('bold') }"
          class="toolbar-button"
          v-tooltip="'Bold (Ctrl+B)'"
        >
          <Icon name="bold" class="w-4 h-4" />
        </button>
        <button
          type="button"
          @click="editor.chain().focus().toggleItalic().run()"
          :class="{ 'is-active': editor.isActive('italic') }"
          class="toolbar-button"
          v-tooltip="'Italic (Ctrl+I)'"
        >
          <Icon name="italic" class="w-4 h-4" />
        </button>
        <button
          type="button"
          @click="editor.chain().focus().toggleUnderline().run()"
          :class="{ 'is-active': editor.isActive('underline') }"
          class="toolbar-button"
          v-tooltip="'Underline (Ctrl+U)'"
        >
          <Icon name="underline" class="w-4 h-4" />
        </button>
        <button
          type="button"
          @click="editor.chain().focus().toggleStrike().run()"
          :class="{ 'is-active': editor.isActive('strike') }"
          class="toolbar-button"
          v-tooltip="'Strikethrough'"
        >
          <Icon name="strikethrough" class="w-4 h-4" />
        </button>
        <button
          type="button"
          @click="editor.chain().focus().toggleCode().run()"
          :class="{ 'is-active': editor.isActive('code') }"
          class="toolbar-button"
          v-tooltip="'Inline Code'"
        >
          <Icon name="code" class="w-4 h-4" />
        </button>
      </div>

      <div class="toolbar-divider"></div>

      <!-- Headings -->
      <div class="toolbar-group">
        <button
          type="button"
          @click="editor.chain().focus().toggleHeading({ level: 1 }).run()"
          :class="{ 'is-active': editor.isActive('heading', { level: 1 }) }"
          class="toolbar-button"
          v-tooltip="'Heading 1'"
        >
          <span class="text-sm font-semibold">H1</span>
        </button>
        <button
          type="button"
          @click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
          :class="{ 'is-active': editor.isActive('heading', { level: 2 }) }"
          class="toolbar-button"
          v-tooltip="'Heading 2'"
        >
          <span class="text-sm font-semibold">H2</span>
        </button>
        <button
          type="button"
          @click="editor.chain().focus().toggleHeading({ level: 3 }).run()"
          :class="{ 'is-active': editor.isActive('heading', { level: 3 }) }"
          class="toolbar-button"
          v-tooltip="'Heading 3'"
        >
          <span class="text-sm font-semibold">H3</span>
        </button>
      </div>

      <div class="toolbar-divider"></div>

      <!-- Lists -->
      <div class="toolbar-group">
        <button
          type="button"
          @click="editor.chain().focus().toggleBulletList().run()"
          :class="{ 'is-active': editor.isActive('bulletList') }"
          class="toolbar-button"
          v-tooltip="'Bullet List'"
        >
          <Icon name="list" class="w-4 h-4" />
        </button>
        <button
          type="button"
          @click="editor.chain().focus().toggleOrderedList().run()"
          :class="{ 'is-active': editor.isActive('orderedList') }"
          class="toolbar-button"
          v-tooltip="'Numbered List'"
        >
          <Icon name="list-ordered" class="w-4 h-4" />
        </button>
        <button
          type="button"
          @click="editor.chain().focus().toggleTaskList().run()"
          :class="{ 'is-active': editor.isActive('taskList') }"
          class="toolbar-button"
          v-tooltip="'Checklist'"
        >
          <Icon name="list-checks" class="w-4 h-4" />
        </button>
      </div>

      <div class="toolbar-divider"></div>

      <!-- Links & Media -->
      <div class="toolbar-group">
        <button
          type="button"
          @click="setLink"
          :class="{ 'is-active': editor.isActive('link') }"
          class="toolbar-button"
          v-tooltip="'Add Link (Ctrl+K)'"
        >
          <Icon name="link" class="w-4 h-4" />
        </button>
        <button
          v-if="canUpload"
          type="button"
          @click="triggerFileInput"
          class="toolbar-button"
          :disabled="isUploading"
          v-tooltip="'Upload Image'"
        >
          <Icon name="image" class="w-4 h-4" />
        </button>
        <button
          type="button"
          @click="addImageFromUrl"
          class="toolbar-button"
          v-tooltip="'Add Image URL'"
        >
          <Icon name="link" class="w-4 h-4" />
        </button>
        <button
          type="button"
          @click="editor.chain().focus().toggleCodeBlock().run()"
          :class="{ 'is-active': editor.isActive('codeBlock') }"
          class="toolbar-button"
          v-tooltip="'Code Block'"
        >
          <Icon name="code-2" class="w-4 h-4" />
        </button>
      </div>

      <!-- Hidden file input for image upload -->
      <input
        ref="fileInputRef"
        type="file"
        accept="image/*"
        class="hidden"
        @change="handleFileInputChange"
      />

      <div class="toolbar-divider"></div>

      <!-- Alignment & Formatting -->
      <div class="toolbar-group">
        <button
          type="button"
          @click="editor.chain().focus().setTextAlign('left').run()"
          :class="{ 'is-active': editor.isActive({ textAlign: 'left' }) }"
          class="toolbar-button"
          v-tooltip="'Align Left'"
        >
          <Icon name="align-left" class="w-4 h-4" />
        </button>
        <button
          type="button"
          @click="editor.chain().focus().setTextAlign('center').run()"
          :class="{ 'is-active': editor.isActive({ textAlign: 'center' }) }"
          class="toolbar-button"
          v-tooltip="'Align Center'"
        >
          <Icon name="align-center" class="w-4 h-4" />
        </button>
        <button
          type="button"
          @click="editor.chain().focus().setTextAlign('right').run()"
          :class="{ 'is-active': editor.isActive({ textAlign: 'right' }) }"
          class="toolbar-button"
          v-tooltip="'Align Right'"
        >
          <Icon name="align-right" class="w-4 h-4" />
        </button>
        <button
          type="button"
          @click="editor.chain().focus().toggleBlockquote().run()"
          :class="{ 'is-active': editor.isActive('blockquote') }"
          class="toolbar-button"
          v-tooltip="'Quote'"
        >
          <Icon name="quote" class="w-4 h-4" />
        </button>
      </div>

      <div class="toolbar-divider"></div>

      <!-- Undo/Redo & Clear -->
      <div class="toolbar-group">
        <button
          type="button"
          @click="editor.chain().focus().undo().run()"
          :disabled="!editor.can().undo()"
          class="toolbar-button"
          v-tooltip="'Undo (Ctrl+Z)'"
        >
          <Icon name="undo" class="w-4 h-4" />
        </button>
        <button
          type="button"
          @click="editor.chain().focus().redo().run()"
          :disabled="!editor.can().redo()"
          class="toolbar-button"
          v-tooltip="'Redo (Ctrl+Y)'"
        >
          <Icon name="redo" class="w-4 h-4" />
        </button>
        <button
          type="button"
          @click="editor.chain().focus().clearNodes().unsetAllMarks().run()"
          class="toolbar-button"
          v-tooltip="'Clear Formatting'"
        >
          <Icon name="eraser" class="w-4 h-4" />
        </button>
      </div>

    </div>

    <!-- Editor Content -->
    <div
      :class="[
        'editor-content-wrapper',
        { 'has-error': error }
      ]"
    >
      <editor-content :editor="editor" class="editor-content" />

      <!-- Character Count -->
      <div v-if="showCharacterCount && editor" class="editor-footer">
        <span class="text-xs text-gray-500 dark:text-gray-400">
          {{ editor.storage.characterCount.characters() }} characters
          <template v-if="maxCharacters">
            / {{ maxCharacters }}
          </template>
        </span>
      </div>
    </div>

    <!-- Error Message -->
    <div v-if="error" class="error-message">
      {{ error }}
    </div>

    <!-- Image Lightbox -->
    <ImageLightbox
      v-model="lightboxOpen"
      :images="lightboxImages"
      :initial-index="lightboxInitialIndex"
    />

    <!-- Upload Progress -->
    <div v-if="activeUploads.length > 0" class="upload-progress-container">
      <UploadProgressBar
        v-for="upload in activeUploads"
        :key="upload.id"
        :file-name="upload.fileName"
        :file-size="upload.fileSize"
        :progress="upload.progress"
        :speed="upload.speed"
        :eta="upload.eta"
        :is-completed="upload.isCompleted"
        :has-error="upload.hasError"
        :error-message="upload.errorMessage"
        @cancel="cancelUpload(upload.id)"
      />
    </div>

  </div>
</template>

<script setup>
import { ref, watch, onMounted, onBeforeUnmount, computed } from 'vue'
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import Placeholder from '@tiptap/extension-placeholder'
import Link from '@tiptap/extension-link'
import TaskList from '@tiptap/extension-task-list'
import TaskItem from '@tiptap/extension-task-item'
import Underline from '@tiptap/extension-underline'
import TextAlign from '@tiptap/extension-text-align'
import CharacterCount from '@tiptap/extension-character-count'
import Image from '@tiptap/extension-image'
import { mediaService } from '@/services/mediaService'
import Icon from '@/components/common/Icon.vue'
import { useToast } from '@/composables/useToast'
import { useLightbox } from '@/composables/useLightbox'
import { useUploadProgress, generateUploadId } from '@/composables/useUploadProgress'
import ImageLightbox from '@/components/common/ImageLightbox.vue'
import UploadProgressBar from '@/components/common/UploadProgressBar.vue'

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: 'Write a description...'
  },
  editable: {
    type: Boolean,
    default: true
  },
  showCharacterCount: {
    type: Boolean,
    default: true
  },
  maxCharacters: {
    type: Number,
    default: null
  },
  error: {
    type: String,
    default: null
  },
  autofocus: {
    type: Boolean,
    default: false
  },
  // Upload configuration
  uploadEnabled: {
    type: Boolean,
    default: false
  },
  modelType: {
    type: String,
    default: null
  },
  modelId: {
    type: [Number, String],
    default: null
  },
  mediaCollection: {
    type: String,
    default: 'description-images'
  }
})

const emit = defineEmits(['update:modelValue', 'focus', 'blur', 'image-uploaded', 'image-deleted'])

const toast = useToast()
const isUploading = ref(false)
const fileInputRef = ref(null)
const tempImageIds = ref([]) // Track temporary uploaded image IDs

// Lightbox support
const {
  show: showLightbox,
  isOpen: lightboxOpen,
  images: lightboxImages,
  initialIndex: lightboxInitialIndex
} = useLightbox()

// Upload progress tracking
const {
  createUpload,
  updateProgress,
  completeUpload,
  failUpload,
  getActiveUploads,
  cancelUpload
} = useUploadProgress()

const activeUploads = computed(() => getActiveUploads())

// Check if upload is properly configured
const canUpload = computed(() => {
  return props.uploadEnabled
})

// Convert plain text to HTML if needed
const convertToHTML = (content) => {
  if (!content) return ''

  // Check if content is already HTML
  if (content.trim().startsWith('<')) {
    return content
  }

  // Convert plain text to HTML paragraphs
  return content
    .split('\n\n')
    .map(paragraph => {
      if (paragraph.trim()) {
        return `<p>${paragraph.replace(/\n/g, '<br>')}</p>`
      }
      return ''
    })
    .filter(p => p)
    .join('')
}

// Handle image upload - use temp upload (no model ID required)
const handleImageUpload = async (file, options = {}) => {
  try {
    isUploading.value = true

    // Upload to temp endpoint (NO model ID required!)
    const response = await mediaService.uploadTempImage(file)

    // Track temp image ID for later transfer
    tempImageIds.value.push(response.id)

    console.log('Temp image uploaded:', {
      id: response.id,
      url: response.url,
      blurPlaceholderUrl: response.blur_placeholder_url
    })

    // Return response with both URLs
    return {
      id: response.id,
      url: response.url,
      blur_placeholder_url: response.blur_placeholder_url
    }
  } catch (error) {
    console.error('Image upload failed:', error)
    throw error
  } finally {
    isUploading.value = false
  }
}

// Handle image deletion
const handleImageDelete = async (mediaId) => {
  try {
    await mediaService.delete(mediaId)
    console.log('Image deleted from server:', mediaId)
    // Emit event for parent component to update tracking
    emit('image-deleted', { mediaId })
  } catch (error) {
    console.error('Failed to delete image:', error)
    // Don't throw - deletion from editor should continue even if API fails
  }
}

// Upload callbacks
const onUploadStart = () => {
  isUploading.value = true
}

const onUploadComplete = (response) => {
  isUploading.value = false
  toast.success('Image uploaded successfully')
  // Emit event with temp image info
  emit('image-uploaded', {
    mediaId: response.id,
    url: response.url,
    blurPlaceholderUrl: response.blur_placeholder_url
  })
}

const onUploadError = (error) => {
  isUploading.value = false
  toast.error(error.message || 'Failed to upload image')
}

// Wrapper for upload with progress tracking
const handleImageUploadWithProgress = async (file, options = {}) => {
  const uploadId = generateUploadId()
  createUpload(uploadId, file)

  try {
    const response = await handleImageUpload(file, {
      ...options,
      onUploadProgress: (progressEvent) => {
        updateProgress(uploadId, progressEvent)
        if (options.onUploadProgress) {
          options.onUploadProgress(progressEvent)
        }
      }
    })

    completeUpload(uploadId)
    return response
  } catch (error) {
    failUpload(uploadId, error.message)
    throw error
  }
}

// Handle progress updates from extension
const handleProgressUpdate = ({ tempUrl, loaded, total, percentage }) => {
  console.log(`Upload progress: ${percentage}%`)
}

const editor = useEditor({
  content: convertToHTML(props.modelValue),
  editable: props.editable,
  autofocus: props.autofocus,
  extensions: [
    StarterKit.configure({
      heading: {
        levels: [1, 2, 3]
      },
      // Disable built-in extensions we configure separately to avoid duplicates
      // Note: StarterKit doesn't include link or underline by default, but listing for clarity
    }),
    Placeholder.configure({
      placeholder: props.placeholder
    }),
    Link.configure({
      openOnClick: false,
      HTMLAttributes: {
        class: 'text-primary-600 dark:text-primary-400 underline cursor-pointer hover:text-primary-700'
      }
    }),
    Image.configure({
      HTMLAttributes: {
        class: 'max-w-full h-auto rounded-lg'
      },
      inline: false,
      allowBase64: true
    }),
    TaskList,
    TaskItem.configure({
      nested: true
    }),
    Underline,
    TextAlign.configure({
      types: ['heading', 'paragraph']
    }),
    CharacterCount.configure({
      limit: props.maxCharacters
    })
  ],
  onUpdate: ({ editor }) => {
    const html = editor.getHTML()
    emit('update:modelValue', html)
  },
  onFocus: () => {
    emit('focus')
  },
  onBlur: () => {
    emit('blur')
  }
})

// Watch for external changes to modelValue
watch(() => props.modelValue, (newValue) => {
  if (editor.value) {
    const currentContent = editor.value.getHTML()
    const newContent = convertToHTML(newValue)

    // Only update if content is different to avoid cursor jumping
    if (currentContent !== newContent) {
      editor.value.commands.setContent(newContent, false)
    }
  }
})

// Watch editable prop
watch(() => props.editable, (newValue) => {
  if (editor.value) {
    editor.value.setEditable(newValue)
  }
})

// Link functionality
const setLink = () => {
  const previousUrl = editor.value.getAttributes('link').href
  const url = window.prompt('Enter URL:', previousUrl)

  if (url === null) {
    return
  }

  if (url === '') {
    editor.value.chain().focus().extendMarkRange('link').unsetLink().run()
    return
  }

  editor.value
    .chain()
    .focus()
    .extendMarkRange('link')
    .setLink({ href: url })
    .run()
}

// Image functionality - Add image from URL
const addImageFromUrl = () => {
  const url = window.prompt('Enter image URL:')

  if (url) {
    editor.value.chain().focus().setImage({ src: url }).run()
  }
}

// Trigger file input for image upload
const triggerFileInput = () => {
  fileInputRef.value?.click()
}

// Handle file input change (manual upload)
const handleFileInputChange = async (event) => {
  const file = event.target.files?.[0]
  if (!file) return

  // Validate file type
  if (!file.type.startsWith('image/')) {
    toast.error('Only image files are allowed')
    return
  }

  // Validate file size (10MB max)
  const maxSize = 10 * 1024 * 1024
  if (file.size > maxSize) {
    toast.error('Image size must be less than 10MB')
    return
  }

  try {
    isUploading.value = true
    const response = await handleImageUpload(file)

    // Insert the image at cursor position
    editor.value.chain().focus().setImage({ src: response.url }).run()

    toast.success('Image uploaded successfully')
  } catch (error) {
    toast.error(error.message || 'Failed to upload image')
  } finally {
    isUploading.value = false
    // Reset file input
    event.target.value = ''
  }
}

// Cleanup
onBeforeUnmount(() => {
  if (editor.value) {
    editor.value.destroy()
  }
})

// Public methods
defineExpose({
  focus: () => editor.value?.commands.focus(),
  blur: () => editor.value?.commands.blur(),
  clear: () => editor.value?.commands.clearContent(),
  getTempImageIds: () => tempImageIds.value,
  clearTempImageIds: () => { tempImageIds.value = [] }
})
</script>

<style scoped>
.rich-text-editor {
  width: 100%;
}

.editor-toolbar {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem;
  background-color: rgb(249 250 251);
  border: 1px solid rgb(209 213 219);
  border-radius: 0.375rem 0.375rem 0 0;
  flex-wrap: wrap;
}

.dark .editor-toolbar {
  background-color: rgb(31 41 55);
  border-color: rgb(75 85 99);
}

.toolbar-group {
  display: flex;
  align-items: center;
  gap: 0.125rem;
}

.toolbar-divider {
  width: 1px;
  height: 1.5rem;
  background-color: rgb(209 213 219);
  margin: 0 0.25rem;
}

.dark .toolbar-divider {
  background-color: rgb(75 85 99);
}

.toolbar-button {
  padding: 0.5rem;
  border-radius: 0.375rem;
  transition: colors 0.2s;
  color: rgb(55 65 81);
}

.dark .toolbar-button {
  color: rgb(209 213 219);
}

.toolbar-button:hover {
  background-color: rgb(229 231 235);
  color: rgb(17 24 39);
}

.dark .toolbar-button:hover {
  background-color: rgb(55 65 81);
  color: rgb(243 244 246);
}

.toolbar-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.toolbar-button.is-active {
  background-color: rgb(229 231 235);
  color: rgb(37 99 235);
}

.dark .toolbar-button.is-active {
  background-color: rgb(55 65 81);
  color: rgb(96 165 250);
}

.editor-content-wrapper {
  border: 1px solid rgb(209 213 219);
  border-top: 0;
  border-radius: 0 0 0.375rem 0.375rem;
  background-color: white;
}

.dark .editor-content-wrapper {
  border-color: rgb(75 85 99);
  background-color: rgb(17 24 39);
}

.editor-content-wrapper.has-error {
  border-color: rgb(239 68 68);
}

.dark .editor-content-wrapper.has-error {
  border-color: rgb(248 113 113);
}

.editor-content {
  min-height: 200px;
  max-height: 450px;
  overflow-y: auto;
}

.editor-footer {
  padding: 0.5rem 0.75rem;
  border-top: 1px solid rgb(229 231 235);
  background-color: rgb(249 250 251);
}

.dark .editor-footer {
  border-color: rgb(55 65 81);
  background-color: rgb(31 41 55);
}

.error-message {
  margin-top: 0.25rem;
  font-size: 0.875rem;
  color: rgb(220 38 38);
}

.dark .error-message {
  color: rgb(248 113 113);
}

.upload-progress-container {
  position: fixed;
  bottom: 1rem;
  right: 1rem;
  z-index: 50;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  max-width: 400px;
}
</style>

<style>
/* TipTap Editor Styles */
.ProseMirror {
  padding: 1rem;
  outline: none;
  min-height: 200px;
  color: rgb(17 24 39);
}

.dark .ProseMirror {
  color: rgb(243 244 246);
}

.ProseMirror-focused {
  outline: none;
}

/* Placeholder */
.ProseMirror p.is-editor-empty:first-child::before {
  color: rgb(156 163 175);
  content: attr(data-placeholder);
  float: left;
  height: 0;
  pointer-events: none;
}

.dark .ProseMirror p.is-editor-empty:first-child::before {
  color: rgb(107 114 128);
}

/* Typography */
.ProseMirror h1,
.ProseMirror h2,
.ProseMirror h3 {
  color: rgb(17 24 39);
}

.dark .ProseMirror h1,
.dark .ProseMirror h2,
.dark .ProseMirror h3 {
  color: rgb(243 244 246);
}

.ProseMirror h1 {
  font-size: 1.875rem;
  font-weight: bold;
  margin-bottom: 1rem;
  margin-top: 1.5rem;
}

.ProseMirror h2 {
  font-size: 1.5rem;
  font-weight: bold;
  margin-bottom: 0.75rem;
  margin-top: 1.25rem;
}

.ProseMirror h3 {
  font-size: 1.25rem;
  font-weight: bold;
  margin-bottom: 0.5rem;
  margin-top: 1rem;
}

.ProseMirror p {
  margin-bottom: 0.75rem;
}

.ProseMirror strong {
  font-weight: bold;
}

.ProseMirror em {
  font-style: italic;
}

.ProseMirror u {
  text-decoration: underline;
}

.ProseMirror s {
  text-decoration: line-through;
}

.ProseMirror code {
  background-color: rgb(243 244 246);
  color: rgb(220 38 38);
  padding: 0.125rem 0.375rem;
  border-radius: 0.25rem;
  font-size: 0.875rem;
  font-family: monospace;
}

.dark .ProseMirror code {
  background-color: rgb(31 41 55);
  color: rgb(248 113 113);
}

/* Lists */
.ProseMirror ul,
.ProseMirror ol {
  padding-left: 1.5rem;
  margin-bottom: 0.75rem;
}

.ProseMirror ul {
  list-style-type: disc;
}

.ProseMirror ol {
  list-style-type: decimal;
}

.ProseMirror li {
  margin-bottom: 0.25rem;
  color: rgb(17 24 39);
}

.dark .ProseMirror li {
  color: rgb(243 244 246);
}

.ProseMirror li > p {
  margin-bottom: 0;
  color: inherit;
}

/* Task List */
.ProseMirror ul[data-type="taskList"] {
  list-style: none;
  padding-left: 0;
  margin: 1rem 0;
}

.ProseMirror ul[data-type="taskList"] li {
  display: flex;
  align-items: flex-start;
  gap: 0.5rem;
  margin-bottom: 0.5rem;
}

.ProseMirror ul[data-type="taskList"] li > label {
  display: flex;
  align-items: flex-start;
  gap: 0.5rem;
  cursor: pointer;
  color: rgb(17 24 39);
  width: auto;
  flex: 0 0 auto;
}

.dark .ProseMirror ul[data-type="taskList"] li > label {
  color: rgb(243 244 246);
}

.ProseMirror ul[data-type="taskList"] li > label input[type="checkbox"] {
  cursor: pointer;
  width: 1rem;
  height: 1rem;
  min-width: 1rem;
  border-radius: 0.25rem;
  border: 1px solid rgb(209 213 219);
  background-color: white;
  margin-top: 0.25rem;
  flex-shrink: 0;
}

.dark .ProseMirror ul[data-type="taskList"] li > label input[type="checkbox"] {
  border-color: rgb(75 85 99);
  background-color: rgb(55 65 81);
}

.ProseMirror ul[data-type="taskList"] li > label > span,
.ProseMirror ul[data-type="taskList"] li > label > div {
  color: inherit;
  flex: 1;
}

.ProseMirror ul[data-type="taskList"] li[data-checked="true"] > label {
  text-decoration: line-through;
  color: rgb(107 114 128);
}

.dark .ProseMirror ul[data-type="taskList"] li[data-checked="true"] > label {
  color: rgb(156 163 175);
}

/* Task list content wrapper */
.ProseMirror ul[data-type="taskList"] li > div {
  flex: 1;
  color: rgb(17 24 39);
}

.dark .ProseMirror ul[data-type="taskList"] li > div {
  color: rgb(243 244 246);
}

/* Blockquote */
.ProseMirror blockquote {
  border-left: 4px solid rgb(209 213 219);
  padding-left: 1rem;
  font-style: italic;
  margin: 1rem 0;
  color: rgb(55 65 81);
}

.dark .ProseMirror blockquote {
  border-color: rgb(75 85 99);
  color: rgb(209 213 219);
}

/* Code Block */
.ProseMirror pre {
  background-color: rgb(17 24 39);
  color: rgb(243 244 246);
  padding: 1rem;
  border-radius: 0.5rem;
  margin-bottom: 1rem;
  overflow-x: auto;
}

.dark .ProseMirror pre {
  background-color: rgb(3 7 18);
}

.ProseMirror pre code {
  background: transparent;
  color: rgb(243 244 246);
  padding: 0;
  font-size: 0.875rem;
  font-family: monospace;
}

/* Horizontal Rule */
.ProseMirror hr {
  border-color: rgb(209 213 219);
  margin: 1.5rem 0;
}

.dark .ProseMirror hr {
  border-color: rgb(75 85 99);
}

/* Links */
.ProseMirror a {
  color: rgb(37 99 235);
  text-decoration: underline;
  cursor: pointer;
}

.ProseMirror a:hover {
  color: rgb(29 78 216);
}

.dark .ProseMirror a {
  color: rgb(96 165 250);
}

.dark .ProseMirror a:hover {
  color: rgb(147 197 253);
}

/* Images */
.ProseMirror img {
  max-width: 100%;
  height: auto;
  border-radius: 0.5rem;
  margin: 1rem 0;
}

/* Uploading Image State */
.ProseMirror img.tiptap-image-uploading {
  opacity: 0.5;
  filter: grayscale(100%);
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 0.5;
  }
  50% {
    opacity: 0.3;
  }
}

/* Text Alignment */
.ProseMirror [data-text-align="left"] {
  text-align: left;
}

.ProseMirror [data-text-align="center"] {
  text-align: center;
}

.ProseMirror [data-text-align="right"] {
  text-align: right;
}

/* Image with blur placeholder - loading state */
.ProseMirror img[data-loading="true"] {
  opacity: 0.5;
  filter: grayscale(100%);
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Image with blur placeholder - show placeholder */
.ProseMirror img[data-blur-placeholder] {
  background-image: var(--blur-placeholder);
  background-size: cover;
  background-position: center;
}

/* Remove blur effect once image loads */
.ProseMirror img:not([data-loading]) {
  filter: none;
  animation: none;
}
</style>
