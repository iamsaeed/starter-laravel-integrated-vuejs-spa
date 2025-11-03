<template>
  <div class="page-container">
    <!-- Filters and Actions -->
    <div class="card p-6 mb-6">
      <div class="flex flex-col sm:flex-row gap-4">
        <!-- Search -->
        <div class="flex-1">
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <Icon name="magnifying-glass" class="w-5 h-5 text-gray-400" />
            </div>
            <input
              v-model="search"
              type="text"
              placeholder="Search templates by name or key..."
              class="form-input pl-10"
              @input="loadTemplates"
            />
          </div>
        </div>

        <!-- Status Filter -->
        <div class="w-full sm:w-56">
          <select
            v-model="filterActive"
            @change="loadTemplates"
            class="form-select"
          >
            <option
              v-for="option in statusOptions"
              :key="option.value"
              :value="option.value"
            >
              {{ option.label }}
            </option>
          </select>
        </div>

        <!-- New Template Button -->
        <button @click="createTemplate" class="resource-button-create whitespace-nowrap">
          <Icon name="plus" class="w-5 h-5" />
          <span>New Template</span>
        </button>
      </div>
    </div>


    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-24">
      <div class="text-center">
        <div class="animate-spin rounded-full h-16 w-16 border-4 border-primary-200 border-t-primary-600 mx-auto mb-4"></div>
        <p class="text-muted font-medium">Loading templates...</p>
      </div>
    </div>

    <!-- Templates Grid -->
    <div v-else-if="templates.length > 0" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
      <div
        v-for="template in templates"
        :key="template.id"
        class="card group hover:shadow-xl transition-all duration-300 overflow-hidden"
      >
        <!-- Template Header with Icon -->
        <div
          @click="editTemplate(template)"
          class="relative h-40 bg-gradient-to-br from-primary-50 to-secondary-50 dark:from-primary-900/20 dark:to-secondary-900/20 flex items-center justify-center cursor-pointer overflow-hidden group-hover:scale-105 transition-transform duration-300"
        >
          <!-- Decorative Background Pattern -->
          <div class="absolute inset-0 opacity-10">
            <div class="absolute top-0 left-0 w-32 h-32 bg-primary-500 rounded-full -translate-x-16 -translate-y-16"></div>
            <div class="absolute bottom-0 right-0 w-40 h-40 bg-secondary-500 rounded-full translate-x-20 translate-y-20"></div>
          </div>

          <!-- Icon and Key -->
          <div class="relative text-center z-10">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-primary-500 to-secondary-500 rounded-2xl shadow-lg mb-3 group-hover:scale-110 transition-transform duration-300">
              <Icon name="mail" class="w-10 h-10 text-white" />
            </div>
            <div class="px-4">
              <span class="inline-block px-3 py-1 bg-white dark:bg-gray-800 text-primary-600 dark:text-primary-400 text-xs font-mono font-semibold rounded-full shadow-sm">
                {{ template.key }}
              </span>
            </div>
          </div>

          <!-- Status Badge -->
          <div class="absolute top-4 right-4">
            <span
              v-if="template.is_active"
              class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 text-xs font-semibold rounded-full shadow-sm"
            >
              <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>
              Active
            </span>
            <span v-else class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 text-xs font-semibold rounded-full shadow-sm">
              <span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span>
              Inactive
            </span>
          </div>
        </div>

        <!-- Template Info -->
        <div @click="editTemplate(template)" class="p-5 cursor-pointer">
          <h3 class="text-lg font-bold text-title mb-2 line-clamp-1 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors duration-200">
            {{ template.name }}
          </h3>
          <p class="text-sm text-muted mb-3 line-clamp-2 min-h-[2.5rem]">
            {{ template.subject_template || 'No subject defined' }}
          </p>
          <div class="flex items-center justify-between text-xs text-muted">
            <div class="flex items-center gap-1.5">
              <Icon name="clock" class="w-3.5 h-3.5" />
              <span>{{ new Date(template.updated_at).toLocaleDateString() }}</span>
            </div>
            <div class="flex items-center gap-1.5">
              <Icon name="user" class="w-3.5 h-3.5" />
              <span>{{ template.updated_by || 'System' }}</span>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="px-5 pb-5 pt-2 flex items-center gap-2 border-t border-gray-100 dark:border-gray-700">
          <!-- Edit Button -->
          <button
            @click.stop="editTemplate(template)"
            class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 bg-primary-50 hover:bg-primary-100 dark:bg-primary-900/20 dark:hover:bg-primary-900/30 text-primary-700 dark:text-primary-300 font-medium text-sm rounded-lg transition-all duration-200 group/btn"
          >
            <Icon name="pencil" class="w-4 h-4 group-hover/btn:scale-110 transition-transform" />
            Edit
          </button>

          <!-- Toggle Active -->
          <button
            @click.stop="toggleActive(template)"
            class="p-2.5 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400 rounded-lg transition-all duration-200"
            :title="template.is_active ? 'Deactivate' : 'Activate'"
          >
            <ToggleSwitch :model-value="template.is_active" @click.stop />
          </button>

          <!-- Duplicate Button -->
          <button
            @click.stop="duplicateTemplate(template)"
            class="p-2.5 hover:bg-secondary-50 dark:hover:bg-secondary-900/20 text-secondary-600 dark:text-secondary-400 rounded-lg transition-all duration-200"
            title="Duplicate template"
          >
            <Icon name="document-duplicate" class="w-4 h-4" />
          </button>

          <!-- Delete Button -->
          <button
            @click.stop="deleteTemplate(template)"
            class="p-2.5 hover:bg-red-50 dark:hover:bg-red-900/20 text-red-600 dark:text-red-400 rounded-lg transition-all duration-200"
            title="Delete template"
          >
            <Icon name="trash" class="w-4 h-4" />
          </button>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="card">
      <div class="text-center py-16 px-6">
        <!-- Empty State Icon -->
        <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-800 dark:to-gray-700 rounded-full mb-6">
          <Icon name="mail" class="w-12 h-12 text-gray-400 dark:text-gray-500" />
        </div>

        <h3 class="text-2xl font-bold text-title mb-3">No Email Templates</h3>
        <p class="text-muted max-w-md mx-auto mb-8">
          Get started by creating your first email template. Templates use Blade syntax for dynamic content.
        </p>

        <button @click="createTemplate" class="resource-button-create">
          <Icon name="plus" class="w-5 h-5" />
          <span>Create First Template</span>
        </button>

        <!-- Quick Tips -->
        <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6 text-left max-w-4xl mx-auto">
          <div class="p-4 bg-primary-50 dark:bg-primary-900/10 rounded-lg">
            <div class="flex items-start gap-3">
              <div class="p-2 bg-primary-100 dark:bg-primary-900/30 rounded-lg">
                <Icon name="code-bracket" class="w-5 h-5 text-primary-600 dark:text-primary-400" />
              </div>
              <div>
                <h4 class="font-semibold text-title text-sm mb-1">Blade Syntax</h4>
                <p class="text-xs text-muted">Use Laravel Blade directives for dynamic content</p>
              </div>
            </div>
          </div>

          <div class="p-4 bg-secondary-50 dark:bg-secondary-900/10 rounded-lg">
            <div class="flex items-start gap-3">
              <div class="p-2 bg-secondary-100 dark:bg-secondary-900/30 rounded-lg">
                <Icon name="eye" class="w-5 h-5 text-secondary-600 dark:text-secondary-400" />
              </div>
              <div>
                <h4 class="font-semibold text-title text-sm mb-1">Live Preview</h4>
                <p class="text-xs text-muted">Preview templates with sample data in real-time</p>
              </div>
            </div>
          </div>

          <div class="p-4 bg-green-50 dark:bg-green-900/10 rounded-lg">
            <div class="flex items-start gap-3">
              <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-lg">
                <Icon name="shield-check" class="w-5 h-5 text-green-600 dark:text-green-400" />
              </div>
              <div>
                <h4 class="font-semibold text-title text-sm mb-1">Secure</h4>
                <p class="text-xs text-muted">Dangerous directives are automatically blocked</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { emailTemplateService } from '@/services/emailTemplateService'
import { useToast } from '@/composables/useToast'
import { useDialog } from '@/composables/useDialog'
import Icon from '@/components/common/Icon.vue'
import ToggleSwitch from '@/components/common/ToggleSwitch.vue'

const router = useRouter()
const toast = useToast()
const { confirmDanger } = useDialog()

const templates = ref([])
const loading = ref(false)
const search = ref('')
const filterActive = ref(null)
const meta = ref({})

const filteredTemplates = computed(() => {
  return templates.value
})

const statusOptions = [
  { value: null, label: 'All Status' },
  { value: 1, label: 'Active Only' },
  { value: 0, label: 'Inactive Only' }
]

async function loadTemplates() {
  loading.value = true
  try {
    const params = {
      search: search.value || undefined,
      filter_active: filterActive.value,
    }
    const response = await emailTemplateService.getTemplates(params)
    templates.value = response.data
    meta.value = response.meta
  } catch (error) {
    toast.error('Failed to load email templates')
  } finally {
    loading.value = false
  }
}

async function toggleActive(template) {
  try {
    await emailTemplateService.updateTemplate(template.id, {
      ...template,
      is_active: !template.is_active
    })
    toast.success(`Template ${template.is_active ? 'deactivated' : 'activated'} successfully`)
    loadTemplates()
  } catch (error) {
    toast.error('Failed to update template status')
  }
}

async function duplicateTemplate(template) {
  try {
    await emailTemplateService.duplicateTemplate(template.id)
    toast.success('Template duplicated successfully')
    loadTemplates()
  } catch (error) {
    toast.error('Failed to duplicate template')
  }
}

async function deleteTemplate(template) {
  const confirmed = await confirmDanger(
    `Are you sure you want to delete "${template.name}"?`,
    'This action cannot be undone.'
  )

  if (confirmed) {
    try {
      await emailTemplateService.deleteTemplate(template.id)
      toast.success('Template deleted successfully')
      loadTemplates()
    } catch (error) {
      toast.error('Failed to delete template')
    }
  }
}

function createTemplate() {
  router.push({ name: 'admin.email-templates.create' })
}

function editTemplate(template) {
  router.push({ name: 'admin.email-templates.edit', params: { id: template.id } })
}

onMounted(() => {
  loadTemplates()
})
</script>

<style scoped>
.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
