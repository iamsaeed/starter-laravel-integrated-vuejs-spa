<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { emailTemplateService } from '@/services/emailTemplateService'
import { useToast } from '@/composables/useToast'
import MonacoEditor from '@/components/common/MonacoEditor.vue'
import Icon from '@/components/common/Icon.vue'

const router = useRouter()
const route = useRoute()
const toast = useToast()

const template = ref({
  key: '',
  name: '',
  subject_template: '',
  body_content: `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Template</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f4f4f5;
        }
    </style>
</head>
<body>
    <div style="max-width: 600px; margin: 40px auto; background: #ffffff; padding: 20px; border-radius: 8px;">
        <h1>Email Title</h1>
        <p>Email content goes here...</p>
    </div>
</body>
</html>`,
  is_active: true
})

const loading = ref(false)
const saving = ref(false)
const previewing = ref(false)
const sendingTest = ref(false)
const previewHtml = ref('')
const previewSubject = ref('')
const variables = ref({})
const testEmails = ref('')
const showPreview = ref(false)
const deviceView = ref('desktop') // desktop | mobile
const editorRef = ref(null)
const syntaxErrors = ref([])
const activeTab = ref('variables') // variables | snippets
const showMetadataModal = ref(false)
const showTestEmailModal = ref(false)

const isEditMode = computed(() => !!route.params.id)
const pageTitle = computed(() => isEditMode.value ? 'Edit Email Template' : 'Create Email Template')

function openMetadataModal() {
  showMetadataModal.value = true
}

async function closeMetadataModal() {
  showMetadataModal.value = false

  // Auto-save when closing modal if template has required fields
  if (!isEditMode.value && template.value.key && template.value.name) {
    await saveTemplate()
  }
}

function openTestEmailModal() {
  showTestEmailModal.value = true
}

function closeTestEmailModal() {
  showTestEmailModal.value = false
  testEmails.value = ''
}

// Debounce timers
let previewDebounceTimer = null
let autosaveDebounceTimer = null

async function loadTemplate() {
  if (!route.params.id) {
    // For new templates, keep the boilerplate
    loading.value = false
    return
  }

  loading.value = true
  try {
    const response = await emailTemplateService.getTemplate(route.params.id)
    template.value = response.data
    await loadVariables()
    await updatePreview()
  } catch (error) {
    toast.error('Failed to load template')
    router.push({ name: 'admin.email-templates.index' })
  } finally {
    loading.value = false
  }
}

async function loadVariables() {
  if (!template.value.id) return

  try {
    const response = await emailTemplateService.getVariables(template.value.id)
    variables.value = response.data
  } catch (error) {
    // Variables are optional
    variables.value = {}
  }
}

async function saveTemplate() {
  // If new template without required fields, open metadata modal
  if (!isEditMode.value && (!template.value.key || !template.value.name)) {
    openMetadataModal()
    return
  }

  // Clear syntax errors first
  syntaxErrors.value = []

  saving.value = true
  try {
    if (isEditMode.value) {
      await emailTemplateService.updateTemplate(template.value.id, template.value)
      toast.success('Template updated successfully')
      // Reload to get updated preview
      await loadVariables()
      await updatePreview()
    } else {
      const response = await emailTemplateService.createTemplate(template.value)
      toast.success('Template created successfully')
      router.push({ name: 'admin.email-templates.edit', params: { id: response.data.id } })
    }
  } catch (error) {
    const message = error.response?.data?.message || 'Failed to save template'
    toast.error(message)

    // Extract validation errors if any
    if (error.response?.data?.errors) {
      const errors = error.response.data.errors
      if (errors.subject_template || errors.body_content) {
        syntaxErrors.value = [
          { line: 1, message: errors.subject_template?.[0] || errors.body_content?.[0] }
        ]
      }
    }
  } finally {
    saving.value = false
  }
}

async function updatePreview() {
  if (!template.value.id) {
    showPreview.value = false
    return
  }

  previewing.value = true
  syntaxErrors.value = []

  try {
    const response = await emailTemplateService.previewTemplate(template.value.id)
    previewHtml.value = response.html
    previewSubject.value = response.subject
    showPreview.value = true
  } catch (error) {
    const message = error.response?.data?.message || 'Failed to generate preview'
    toast.error(message)
    showPreview.value = false

    // Check for syntax errors
    if (error.response?.data?.errors) {
      syntaxErrors.value = [
        { line: 1, message: error.response.data.message }
      ]
    }
  } finally {
    previewing.value = false
  }
}

async function sendTestEmail() {
  if (!testEmails.value.trim()) {
    toast.error('Please enter at least one email address')
    return
  }

  const emails = testEmails.value.split(',').map(e => e.trim()).filter(e => e)

  // Validate email format
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  const invalidEmails = emails.filter(email => !emailRegex.test(email))
  if (invalidEmails.length > 0) {
    toast.error(`Invalid email format: ${invalidEmails.join(', ')}`)
    return
  }

  sendingTest.value = true
  try {
    await emailTemplateService.sendTestEmail(template.value.id, emails)
    toast.success(`Test email sent successfully to ${emails.length} recipient(s)!`)
    closeTestEmailModal()
  } catch (error) {
    toast.error('Failed to send test email')
  } finally {
    sendingTest.value = false
  }
}

function insertVariable(example) {
  if (editorRef.value) {
    editorRef.value.insertText(example)
    nextTick(() => {
      editorRef.value.focus()
    })
  }
}

function insertSnippet(snippet) {
  if (editorRef.value) {
    editorRef.value.insertText(snippet)
    nextTick(() => {
      editorRef.value.focus()
    })
  }
}

function cancel() {
  router.push({ name: 'admin.email-templates.index' })
}

// Auto-save on content change (debounced)
async function autosaveTemplate() {
  if (!isEditMode.value || !template.value.id) return

  saving.value = true
  syntaxErrors.value = []

  try {
    await emailTemplateService.updateTemplate(template.value.id, template.value)
    // Silent save - no toast notification to avoid interrupting user
    // Reload variables and preview
    await loadVariables()
    await updatePreview()
  } catch (error) {
    // Only show error if it's a validation issue
    if (error.response?.data?.errors) {
      const errors = error.response.data.errors
      if (errors.subject_template || errors.body_content) {
        syntaxErrors.value = [
          { line: 1, message: errors.subject_template?.[0] || errors.body_content?.[0] }
        ]
      }
    }
  } finally {
    saving.value = false
  }
}

// Auto-preview on content change (debounced)
function handleContentChange() {
  if (!isEditMode.value) return

  // Clear existing timers
  if (previewDebounceTimer) {
    clearTimeout(previewDebounceTimer)
  }
  if (autosaveDebounceTimer) {
    clearTimeout(autosaveDebounceTimer)
  }

  // Autosave after 400ms of inactivity
  autosaveDebounceTimer = setTimeout(() => {
    autosaveTemplate()
  }, 400)

  // Preview updates after autosave completes (1.5s total)
  previewDebounceTimer = setTimeout(() => {
    if (!saving.value) {
      updatePreview()
    }
  }, 1500)
}

// Watch for template changes
watch(() => [template.value.subject_template, template.value.body_content], handleContentChange, { deep: true })

// Close modal on Escape key
function handleEscape(e) {
  if (e.key === 'Escape') {
    if (showMetadataModal.value) {
      closeMetadataModal()
    }
    if (showTestEmailModal.value) {
      closeTestEmailModal()
    }
  }
}

onMounted(() => {
  loadTemplate()
  window.addEventListener('keydown', handleEscape)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleEscape)
})
</script>

<template>

  <div class="h-[85vh] flex flex-col bg-gray-50 dark:bg-gray-900">
    <!-- Compact Header -->
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 py-2">
      <div class="flex items-center justify-between">
        <!-- Left: Back & Title -->
        <div class="flex items-center gap-3">
          <button
            @click="cancel"
            class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded transition-colors"
            v-tooltip="'Back to templates'"
          >
            <Icon name="arrow-left" class="w-5 h-5 text-gray-600 dark:text-gray-400" />
          </button>
          <h1 class="text-sm font-medium text-title">
            {{ template.name || pageTitle }}
          </h1>
        </div>

        <!-- Right: Action Icons -->
        <div class="flex items-center gap-2">
          <!-- Settings -->
          <button
            @click="openMetadataModal"
            class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded transition-colors"
            v-tooltip="'Template settings & test email'"
          >
            <Icon name="document-text" class="w-5 h-5 text-gray-600 dark:text-gray-400" />
          </button>

          <div class="w-px h-6 bg-gray-300 dark:bg-gray-600 mx-1"></div>

          <!-- Device Toggle -->
          <button
            @click="deviceView = 'desktop'"
            :class="[
              'p-2 rounded transition-colors',
              deviceView === 'desktop'
                ? 'bg-primary-600 text-white'
                : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'
            ]"
            v-tooltip="'Desktop preview'"
          >
            <Icon name="monitor" class="w-5 h-5" />
          </button>
          <button
            @click="deviceView = 'mobile'"
            :class="[
              'p-2 rounded transition-colors',
              deviceView === 'mobile'
                ? 'bg-primary-600 text-white'
                : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'
            ]"
            v-tooltip="'Mobile preview'"
          >
            <Icon name="device-mobile" class="w-5 h-5" />
          </button>

          <div class="w-px h-6 bg-gray-300 dark:bg-gray-600 mx-1"></div>

          <!-- Refresh Preview -->
          <button
            v-if="isEditMode"
            @click="updatePreview"
            :disabled="previewing"
            class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded transition-colors disabled:opacity-50"
            v-tooltip="'Refresh preview'"
          >
            <Icon name="refresh" class="w-5 h-5 text-gray-600 dark:text-gray-400" :class="{ 'animate-spin': previewing }" />
          </button>

          <!-- Send Test Email -->
          <button
            v-if="isEditMode"
            @click="openTestEmailModal"
            class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded transition-colors"
            v-tooltip="'Send test email'"
          >
            <Icon name="mail" class="w-5 h-5 text-gray-600 dark:text-gray-400" />
          </button>

          <div v-if="isEditMode" class="w-px h-6 bg-gray-300 dark:bg-gray-600 mx-1"></div>

          <!-- Save Button -->
          <button
            @click="saveTemplate"
            :disabled="saving"
            class="px-4 py-2 bg-primary-600 hover:bg-primary-700 disabled:opacity-50 text-white text-sm font-medium rounded transition-colors flex items-center gap-2"
            v-tooltip="saving ? 'Saving template...' : (isEditMode ? 'Save template' : 'Fill template details to save')"
          >
            <Icon v-if="saving" name="refresh" class="w-5 h-5 animate-spin" />
            <Icon v-else name="check" class="w-5 h-5" />
            <span>{{ saving ? 'Saving...' : 'Save' }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex-1 flex justify-center items-center">
      <div class="text-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600 mx-auto mb-4"></div>
        <p class="text-gray-600 dark:text-gray-400">Loading template...</p>
      </div>
    </div>

    <!-- Main Split Layout - Maximum Space -->
    <div v-else class="flex-1 flex overflow-hidden" style="height: calc(85vh - 48px);">
      <!-- Left Panel: Editor -->
      <div class="w-1/2 flex flex-col bg-white dark:bg-gray-900 relative">
        <!-- New Template Overlay -->
        <div
          v-if="!isEditMode && !template.key"
          class="absolute inset-0 bg-white/95 dark:bg-gray-900/95 flex items-center justify-center z-10"
        >
          <div class="text-center max-w-md px-6">
            <div class="mb-6">
              <Icon name="document-text" class="w-20 h-20 text-gray-400 dark:text-gray-600 mx-auto mb-4" />
              <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-200 mb-2">Create New Email Template</h2>
              <p class="text-gray-600 dark:text-gray-400 text-sm">
                Get started by filling in the template details, subject, and metadata
              </p>
            </div>
            <button
              @click="openMetadataModal"
              class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors flex items-center gap-2 mx-auto"
            >
              <Icon name="document-text" class="w-5 h-5" />
              <span>Fill Template Details</span>
            </button>
          </div>
        </div>

        <!-- Body Editor -->
        <div class="h-full overflow-hidden">
          <MonacoEditor
            ref="editorRef"
            v-model="template.body_content"
            language="blade"
            theme="auto"
            height="100%"
            :errors="syntaxErrors"
            :options="{
              fontSize: 14,
              minimap: { enabled: true },
              wordWrap: 'on',
              lineNumbers: 'on',
              scrollBeyondLastLine: false,
              folding: true,
              padding: { top: 16 },
            }"
            @change="handleContentChange"
          />
        </div>
      </div>

      <!-- Right Panel: Preview -->
      <div class="w-1/2 flex flex-col bg-white dark:bg-gray-800 relative">
        <!-- New Template Overlay for Preview -->
        <div
          v-if="!isEditMode && !template.key"
          class="absolute inset-0 bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 flex items-center justify-center z-10"
        >
          <div class="text-center max-w-md px-6">
            <Icon name="eye" class="w-20 h-20 text-gray-300 dark:text-gray-700 mx-auto mb-4" />
            <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300 mb-2">Live Preview</h3>
            <p class="text-gray-500 dark:text-gray-400 text-sm">
              Your email template preview will appear here once you fill in the details and start coding
            </p>
          </div>
        </div>

        <!-- Preview Content -->
        <div class="h-full overflow-hidden bg-gray-100 dark:bg-gray-900 p-4">
          <!-- Preview iframe -->
          <div
            v-if="showPreview && isEditMode"
            :class="[
              'mx-auto bg-white shadow-lg rounded overflow-hidden transition-all duration-300 h-full',
              deviceView === 'mobile' ? 'max-w-[375px]' : 'w-full'
            ]"
          >
            <iframe
              :srcdoc="previewHtml"
              class="w-full h-full border-0"
              sandbox="allow-same-origin"
            />
          </div>

          <!-- No Preview Message -->
          <div v-else-if="isEditMode" class="flex items-center justify-center h-full text-center text-muted">
            <div>
              <Icon name="eye-slash" class="w-16 h-16 mx-auto mb-3 opacity-30" />
              <p class="text-sm">Preview will appear here</p>
            </div>
          </div>
        </div>

        <!-- Bottom Tabs: Variables & Snippets -->
        <div v-if="isEditMode || template.key" class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
          <!-- Tab Headers -->
          <div class="flex border-b border-gray-200 dark:border-gray-700">
            <button
              @click="activeTab = 'variables'"
              :class="[
                'px-4 py-2 text-sm font-medium border-b-2 transition-colors',
                activeTab === 'variables'
                  ? 'border-primary-600 text-primary-600 dark:text-primary-400'
                  : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200'
              ]"
            >
              Variables ({{ Object.keys(variables).length }})
            </button>
            <button
              @click="activeTab = 'snippets'"
              :class="[
                'px-4 py-2 text-sm font-medium border-b-2 transition-colors',
                activeTab === 'snippets'
                  ? 'border-primary-600 text-primary-600 dark:text-primary-400'
                  : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200'
              ]"
            >
              Blade Snippets
            </button>
          </div>

          <!-- Tab Content -->
          <div class="h-48 overflow-y-auto p-4">
            <!-- Variables Tab -->
            <div v-show="activeTab === 'variables'">
              <div v-if="Object.keys(variables).length > 0" class="space-y-2">
                <div
                  v-for="(variable, key) in variables"
                  :key="key"
                  class="p-2 bg-gray-50 dark:bg-gray-700 rounded hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer transition-colors"
                  @click="insertVariable(variable.example)"
                >
                  <div class="flex items-center justify-between">
                    <div class="flex-1">
                      <div class="text-sm font-medium text-title">{{ variable.label }}</div>
                      <code class="text-xs text-primary-600 dark:text-primary-400">{{ variable.example }}</code>
                    </div>
                    <Icon name="code" class="w-4 h-4 text-gray-400" />
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-8 text-sm text-muted">
                <Icon name="cube-transparent" class="w-12 h-12 mx-auto mb-2 opacity-30" />
                <p>No variables available</p>
              </div>
            </div>

            <!-- Snippets Tab -->
            <div v-show="activeTab === 'snippets'" class="space-y-2">
              <button
                @click="insertSnippet('{{ $variable }}')"
                class="w-full text-left p-2 bg-gray-50 dark:bg-gray-700 rounded hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors"
              >
                <code class="text-sm text-title block">{{ "\{\{ $variable \}\}" }}</code>
                <span class="text-xs text-muted">Output variable</span>
              </button>

              <button
                @click="insertSnippet('@if($condition)\n\t\n@endif')"
                class="w-full text-left p-2 bg-gray-50 dark:bg-gray-700 rounded hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors"
              >
                <code class="text-sm text-title block">@if(...) @endif</code>
                <span class="text-xs text-muted">Conditional</span>
              </button>

              <button
                @click="insertSnippet('@foreach($items as $item)\n\t\n@endforeach')"
                class="w-full text-left p-2 bg-gray-50 dark:bg-gray-700 rounded hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors"
              >
                <code class="text-sm text-title block">@foreach(...) @endforeach</code>
                <span class="text-xs text-muted">Loop</span>
              </button>

              <div class="mt-3 p-2 bg-red-50 dark:bg-red-900/20 rounded border border-red-200 dark:border-red-800">
                <div class="flex items-start gap-2 text-xs text-red-600 dark:text-red-400">
                  <Icon name="shield-exclamation" class="w-4 h-4 flex-shrink-0 mt-0.5" />
                  <div>
                    <strong>Security:</strong> @php, @include, @extends are blocked
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Test Email Modal -->
    <Teleport to="body">
      <Transition name="modal">
        <div
          v-if="showTestEmailModal"
          class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
          @click.self="closeTestEmailModal"
        >
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full mx-4">
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
              <div>
                <h2 class="text-lg font-semibold text-title">Send Test Email</h2>
                <p class="text-sm text-muted mt-1">Test your email template in real email clients</p>
              </div>
              <button
                @click="closeTestEmailModal"
                class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                v-tooltip="'Close'"
              >
                <Icon name="x-mark" class="w-5 h-5 text-gray-600 dark:text-gray-400" />
              </button>
            </div>

            <!-- Modal Body -->
            <div class="px-6 py-4">
              <div class="space-y-4">
                <div>
                  <label class="form-label">
                    Email Recipients
                    <span class="text-xs text-muted ml-1">(comma-separated for multiple)</span>
                  </label>
                  <textarea
                    v-model="testEmails"
                    class="form-input min-h-[100px] font-mono text-sm"
                    placeholder="test@example.com, another@example.com"
                    :disabled="sendingTest"
                  />
                  <p class="text-xs text-muted mt-2">
                    Enter one or more email addresses separated by commas. The template will be rendered with sample data.
                  </p>
                </div>

                <!-- Preview Info -->
                <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                  <div class="flex items-start gap-3">
                    <Icon name="info-circle" class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" />
                    <div class="text-sm">
                      <p class="font-medium text-blue-900 dark:text-blue-100 mb-1">
                        Template: {{ template.name }}
                      </p>
                      <p class="text-blue-700 dark:text-blue-300">
                        Subject: {{ template.subject_template || 'No subject' }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
              <button
                @click="closeTestEmailModal"
                class="form-button-secondary"
                :disabled="sendingTest"
              >
                Cancel
              </button>
              <button
                @click="sendTestEmail"
                :disabled="sendingTest || !testEmails.trim()"
                class="form-button-primary"
              >
                <Icon v-if="sendingTest" name="refresh" class="w-4 h-4 animate-spin" />
                <Icon v-else name="mail" class="w-4 h-4" />
                <span>{{ sendingTest ? 'Sending...' : 'Send Test Email' }}</span>
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Metadata Modal -->
    <Teleport to="body">
      <Transition name="modal">
        <div
          v-if="showMetadataModal"
          class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
          @click.self="closeMetadataModal"
        >
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full mx-4">
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
              <h2 class="text-lg font-semibold text-title">Template Settings & Test Email</h2>
              <button
                @click="closeMetadataModal"
                class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                v-tooltip="'Close'"
              >
                <Icon name="x-mark" class="w-5 h-5 text-gray-600 dark:text-gray-400" />
              </button>
            </div>

            <!-- Modal Body -->
            <div class="px-6 py-4 space-y-4">
              <div>
                <label class="form-label">
                  Template Key
                  <span class="text-xs text-muted ml-1">(unique identifier)</span>
                </label>
                <input
                  v-model="template.key"
                  type="text"
                  class="form-input"
                  :class="{ 'form-input-disabled': isEditMode }"
                  placeholder="e.g., user_welcome"
                  :disabled="isEditMode"
                  required
                />
                <p v-if="isEditMode" class="text-xs text-muted mt-1">
                  Template key cannot be changed after creation
                </p>
              </div>

              <div>
                <label class="form-label">
                  Template Name
                  <span class="text-xs text-muted ml-1">(display name)</span>
                </label>
                <input
                  v-model="template.name"
                  type="text"
                  class="form-input"
                  placeholder="e.g., User Welcome Email"
                  required
                />
              </div>

              <div>
                <label class="form-label">
                  Subject Template
                  <span class="text-xs text-muted ml-1">(supports Blade syntax)</span>
                </label>
                <input
                  v-model="template.subject_template"
                  type="text"
                  class="form-input font-mono text-sm"
                  placeholder="e.g., Welcome to {{ config('app.name') }}, {{ $user->name }}!"
                />
              </div>

              <div>
                <label class="flex items-center gap-2 cursor-pointer">
                  <input
                    v-model="template.is_active"
                    type="checkbox"
                    class="form-checkbox"
                  />
                  <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    Active Template
                  </span>
                </label>
                <p class="text-xs text-muted mt-1 ml-6">
                  Only active templates can be used for sending emails
                </p>
              </div>

              <!-- Test Email Section -->
              <div v-if="isEditMode" class="pt-4 border-t border-gray-200 dark:border-gray-700">
                <label class="form-label">
                  Send Test Email
                  <span class="text-xs text-muted ml-1">(comma-separated)</span>
                </label>
                <div class="flex gap-2">
                  <input
                    v-model="testEmails"
                    type="text"
                    class="form-input flex-1"
                    placeholder="test@example.com, another@example.com"
                  />
                  <button
                    @click="sendTestEmail"
                    :disabled="sendingTest || !testEmails.trim()"
                    class="form-button-primary whitespace-nowrap"
                  >
                    <Icon v-if="sendingTest" name="refresh" class="w-4 h-4 animate-spin" />
                    <Icon v-else name="paper-airplane" class="w-4 h-4" />
                    <span>{{ sendingTest ? 'Sending...' : 'Send' }}</span>
                  </button>
                </div>
              </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
              <button
                @click="closeMetadataModal"
                class="form-button-secondary"
              >
                Close
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<style scoped>
/* Custom scrollbar for sidebar */
.overflow-y-auto {
  scrollbar-width: thin;
  scrollbar-color: rgba(156, 163, 175, 0.5) transparent;
}

.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: transparent;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background-color: rgba(156, 163, 175, 0.5);
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background-color: rgba(156, 163, 175, 0.7);
}

/* Modal transitions */
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.2s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-active > div,
.modal-leave-active > div {
  transition: transform 0.2s ease;
}

.modal-enter-from > div,
.modal-leave-to > div {
  transform: scale(0.95);
}
</style>
