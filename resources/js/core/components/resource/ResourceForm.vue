<template>
  <div class="resource-form bg-transparent">
    <!-- Form Header -->
    <div class="mb-6">
      <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
        {{ itemId ? 'Edit' : 'Create' }} {{ meta?.singularLabel || 'Record' }}
      </h2>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center py-12 bg-transparent">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600" />
    </div>

    <!-- Form -->
    <form v-else-if="meta" @submit.prevent="handleSubmit" class="space-y-6 bg-transparent">
      <!-- Grid Container for Sections/Fields -->
      <div class="grid grid-cols-12 gap-4 md:gap-6">
        <template v-for="(item, index) in formStructure" :key="index">
          <!-- Section -->
          <div v-if="item.type === 'section' && isItemVisible(item)" :class="item.cols || 'col-span-12'">
            <div
              :class="[
                'space-y-4',
                item.containerClasses || 'bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700'
              ]"
            >
              <!-- Section Header -->
              <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-3">
                <div class="flex items-center space-x-2">
                  <Icon v-if="item.icon" :name="item.icon" :size="20" class="text-gray-500 dark:text-gray-400" />
                  <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                      {{ item.title }}
                    </h3>
                    <p v-if="item.description" class="text-sm text-gray-500 dark:text-gray-400">
                      {{ item.description }}
                    </p>
                  </div>
                </div>
                <button
                  v-if="item.collapsible"
                  type="button"
                  @click="toggleSection(index)"
                  class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                >
                  <Icon :name="sectionCollapsed[index] ? 'chevron-down' : 'chevron-up'" :size="20" />
                </button>
              </div>

              <!-- Section Fields -->
              <div v-show="!sectionCollapsed[index]" :class="['grid grid-cols-12', item.gap || 'gap-4']">
                <template v-for="fieldItem in item.fields" :key="fieldItem.attribute || fieldItem.label">
                  <!-- Group within Section -->
                  <div v-if="fieldItem.type === 'group' && isItemVisible(fieldItem)" :class="fieldItem.cols || 'col-span-12'">
                    <div v-if="fieldItem.label" class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">
                      {{ fieldItem.label }}
                    </div>
                    <div :class="['grid grid-cols-12', fieldItem.gap || 'gap-4']">
                      <div
                        v-for="groupField in fieldItem.fields"
                        :key="groupField.attribute"
                        :class="[groupField.cols || 'col-span-12', groupField.containerClasses]"
                      >
                        <FieldRenderer
                          :field="groupField"
                          :model-value="formData"
                          :errors="errors"
                          :relation-options="relationOptions"
                          :item-id="itemId"
                          :meta="meta"
                          @quick-create="openQuickCreate"
                        />
                      </div>
                    </div>
                  </div>

                  <!-- Regular Field within Section -->
                  <div v-else :class="[fieldItem.cols || 'col-span-12', fieldItem.containerClasses]">
                    <FieldRenderer
                      :field="fieldItem"
                      :model-value="formData"
                      :errors="errors"
                      :relation-options="relationOptions"
                      :item-id="itemId"
                      :meta="meta"
                      @quick-create="openQuickCreate"
                    />
                  </div>
                </template>
              </div>
            </div>
          </div>

          <!-- Group (top-level, no section) -->
          <div v-else-if="item.type === 'group' && isItemVisible(item)" :class="item.cols || 'col-span-12'">
            <div v-if="item.label" class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">
              {{ item.label }}
            </div>
            <div :class="['grid grid-cols-12', item.gap || 'gap-4']">
              <div
                v-for="groupField in item.fields"
                :key="groupField.attribute"
                :class="[groupField.cols || 'col-span-12', groupField.containerClasses]"
              >
                <FieldRenderer
                  :field="groupField"
                  :model-value="formData"
                  :errors="errors"
                  :relation-options="relationOptions"
                  :item-id="itemId"
                  :meta="meta"
                  @quick-create="openQuickCreate"
                />
              </div>
            </div>
          </div>

          <!-- Regular Field (no section or group) -->
          <div v-else :class="[item.cols || 'col-span-12', item.containerClasses]">
            <FieldRenderer
              :field="item"
              :model-value="formData"
              :errors="errors"
              :relation-options="relationOptions"
              :item-id="itemId"
              :meta="meta"
              @quick-create="openQuickCreate"
            />
          </div>
        </template>
      </div>

      <!-- Form Actions -->
      <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
        <button
          type="button"
          @click="$emit('cancel')"
          class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 font-medium transition-colors duration-200"
        >
          Cancel
        </button>
        <button
          type="submit"
          :disabled="submitting"
          class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-medium transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
        >
          <div v-if="submitting" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white" />
          <span>{{ itemId ? 'Update' : 'Create' }}</span>
        </button>
      </div>
    </form>

    <!-- Quick Create Modal -->
    <QuickCreateModal
      v-if="quickCreateField"
      :show="showQuickCreate"
      :resource="quickCreateField.meta?.resource"
      :title="quickCreateField.label"
      @created="handleQuickCreateSuccess"
      @cancel="handleQuickCreateCancel"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { resourceService } from '@/core/services/resourceService'
import FieldRenderer from './FieldRenderer.vue'
import QuickCreateModal from './QuickCreateModal.vue'
import Icon from '@/components/common/Icon.vue'

const props = defineProps({
  resource: {
    type: String,
    required: true
  },
  itemId: {
    type: [Number, String],
    default: null
  }
})

const emit = defineEmits(['success', 'cancel'])

// State
const meta = ref(null)
const formData = ref({})
const errors = ref({})
const loading = ref(false)
const submitting = ref(false)
const relationOptions = ref({})
const quickCreateField = ref(null)
const showQuickCreate = ref(false)
const sectionCollapsed = ref({})

// Computed
const formStructure = computed(() => {
  if (!meta.value) return []
  // Meta.fields can now contain Section/Group objects or regular fields
  return meta.value.fields || []
})

const formFields = computed(() => {
  if (!meta.value) return []
  // Flatten structure to get all fields for initialization
  return flattenFields(meta.value.fields)
})

// Methods
function flattenFields(items) {
  const flattened = []

  for (const item of items) {
    if (item.type === 'section') {
      flattened.push(...flattenFields(item.fields))
    } else if (item.type === 'group') {
      flattened.push(...item.fields)
    } else {
      flattened.push(item)
    }
  }

  return flattened
}

function toggleSection(index) {
  sectionCollapsed.value[index] = !sectionCollapsed.value[index]
}

function isItemVisible(item) {
  // Check dependsOn condition
  if (item.dependsOn) {
    const { attribute, value, operator = '=' } = item.dependsOn
    const actualValue = formData.value[attribute]
    return evaluateCondition(actualValue, value, operator)
  }

  // Check showWhen structured condition
  if (item.showWhen) {
    // Skip if it's a callback (frontend: false)
    if (item.showWhen.frontend === false) {
      return true // Backend-only callbacks always visible on frontend
    }

    return evaluateStructuredCondition(item.showWhen)
  }

  // Default: visible
  return true
}

function evaluateStructuredCondition(condition) {
  if (condition.type === 'all') {
    // AND logic
    return condition.conditions.every(cond => {
      const actualValue = formData.value[cond.attribute]
      return evaluateCondition(actualValue, cond.value, cond.operator || '=')
    })
  } else if (condition.type === 'any') {
    // OR logic
    return condition.conditions.some(cond => {
      const actualValue = formData.value[cond.attribute]
      return evaluateCondition(actualValue, cond.value, cond.operator || '=')
    })
  }

  return true
}

function evaluateCondition(actualValue, expectedValue, operator) {
  switch (operator) {
    case '=':
      return actualValue == expectedValue
    case '!=':
      return actualValue != expectedValue
    case '>':
      return actualValue > expectedValue
    case '>=':
      return actualValue >= expectedValue
    case '<':
      return actualValue < expectedValue
    case '<=':
      return actualValue <= expectedValue
    case 'in':
      return Array.isArray(expectedValue) && expectedValue.includes(actualValue)
    case 'not_in':
      return Array.isArray(expectedValue) && !expectedValue.includes(actualValue)
    case 'contains':
      return Array.isArray(actualValue) && actualValue.includes(expectedValue)
    case 'not_contains':
      return Array.isArray(actualValue) && !actualValue.includes(expectedValue)
    case 'empty':
      return !actualValue || actualValue === '' || (Array.isArray(actualValue) && actualValue.length === 0)
    case 'not_empty':
      return actualValue && actualValue !== '' && (!Array.isArray(actualValue) || actualValue.length > 0)
    default:
      return false
  }
}

async function fetchMeta() {
  try {
    const response = await resourceService.getMeta(props.resource, 'form')
    meta.value = response

    // Initialize collapsed state for sections
    formStructure.value.forEach((item, index) => {
      if (item.type === 'section' && item.collapsed) {
        sectionCollapsed.value[index] = true
      }
    })

    // Initialize form data with defaults
    formFields.value.forEach(field => {
      if (field.default !== null && field.default !== undefined) {
        formData.value[field.attribute] = field.default
      } else if (field.type === 'boolean') {
        formData.value[field.attribute] = false
      } else if (field.type === 'belongsToMany' || field.type === 'belongs-to-many') {
        formData.value[field.attribute] = []
      } else if (field.type === 'select' && field.meta?.multiple) {
        formData.value[field.attribute] = []
      }
    })

    // Load relation options for belongsToMany fields
    await loadRelationOptions()
  } catch (error) {
    console.error('Failed to fetch resource meta:', error)
  }
}

async function fetchItem() {
  if (!props.itemId) return

  loading.value = true
  try {
    const response = await resourceService.show(props.resource, props.itemId)

    // Populate form with existing data for all form fields
    formFields.value.forEach(field => {
      if (response.data.hasOwnProperty(field.attribute)) {
        let value = response.data[field.attribute]

        // For BelongsToMany fields, extract IDs from the relationship data
        if ((field.type === 'belongsToMany' || field.type === 'belongs-to-many') && Array.isArray(value)) {
          value = value.map(item => item.id)
        }
        // For Select with multiple and resource, extract IDs from the relationship data
        else if (field.type === 'select' && field.meta?.multiple && field.meta?.resource && Array.isArray(value)) {
          value = value.map(item => item.id)
        }
        // For BelongsTo fields, extract ID from the relationship data
        else if ((field.type === 'belongsTo' || field.type === 'belongs-to') && value && typeof value === 'object' && value.id) {
          value = value.id
        }

        formData.value[field.attribute] = value
      }
    })
  } catch (error) {
    console.error('Failed to fetch item:', error)
  } finally {
    loading.value = false
  }
}

async function loadRelationOptions() {
  const relationFields = formFields.value.filter(f =>
    f.type === 'belongsToMany' ||
    f.type === 'belongs-to-many' ||
    f.type === 'belongsTo' ||
    f.type === 'belongs-to' ||
    (f.type === 'select' && f.meta?.resource && !f.meta?.serverSide) // Skip server-side selects
  )

  for (const field of relationFields) {
    if (field.meta?.resource) {
      try {
        // Fetch options from related resource
        const response = await resourceService.index(field.meta.resource, { perPage: 100 })
        relationOptions.value[field.attribute] = response.data.map(item => ({
          value: item.id,
          label: item[field.meta.titleAttribute || 'name'] || item.id
        }))
      } catch (error) {
        console.error(`Failed to load options for ${field.attribute}:`, error)
      }
    }
  }
}

async function handleSubmit() {
  errors.value = {}
  submitting.value = true

  try {
    let response

    if (props.itemId) {
      response = await resourceService.update(props.resource, props.itemId, formData.value)
    } else {
      response = await resourceService.store(props.resource, formData.value)
    }

    emit('success', response.data)
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      console.error('Failed to submit form:', error)
    }
  } finally {
    submitting.value = false
  }
}

function openQuickCreate(field) {
  quickCreateField.value = field
  showQuickCreate.value = true
}

async function handleQuickCreateSuccess(data) {
  const field = quickCreateField.value

  // Close modal immediately for better UX
  showQuickCreate.value = false
  quickCreateField.value = null

  // Refresh options for this field in the background
  await refreshRelationOptions(field)

  // Auto-select the newly created item
  if (field.type === 'belongsToMany' || field.type === 'belongs-to-many') {
    // Multi-select: add to array
    if (!formData.value[field.attribute]) {
      formData.value[field.attribute] = []
    }
    if (!formData.value[field.attribute].includes(data.data.id)) {
      formData.value[field.attribute].push(data.data.id)
    }
  } else if (field.type === 'belongsTo' || field.type === 'belongs-to') {
    // Single select: set value
    formData.value[field.attribute] = data.data.id
  }
}

function handleQuickCreateCancel() {
  showQuickCreate.value = false
  quickCreateField.value = null
}

async function refreshRelationOptions(field) {
  if (field.meta?.resource) {
    try {
      const response = await resourceService.index(field.meta.resource, { perPage: 100 })
      relationOptions.value[field.attribute] = response.data.map(item => ({
        value: item.id,
        label: item[field.meta.titleAttribute || 'name'] || item.id
      }))
    } catch (error) {
      console.error('Failed to refresh options:', error)
    }
  }
}

// Lifecycle
onMounted(async () => {
  await fetchMeta()
  await fetchItem()
})
</script>
