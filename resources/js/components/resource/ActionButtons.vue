<template>
  <div v-if="actions && actions.length > 0" class="action-buttons">
    <!-- Actions Dropdown -->
    <div class="relative inline-block text-left">
      <button
        @click="toggleDropdown"
        :disabled="!hasSelection"
        class="action-trigger px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg font-medium transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
      >
        <Icon name="more-vertical" :size="18" />
        <span>Actions</span>
        <Icon
          name="chevron-down"
          :size="16"
          class="transition-transform duration-200"
          :class="{ 'rotate-180': isOpen }"
        />
      </button>

      <!-- Dropdown Menu -->
      <Transition
        enter-active-class="transition ease-out duration-100"
        enter-from-class="transform opacity-0 scale-95"
        enter-to-class="transform opacity-100 scale-100"
        leave-active-class="transition ease-in duration-75"
        leave-from-class="transform opacity-100 scale-100"
        leave-to-class="transform opacity-0 scale-95"
      >
        <div
          v-if="isOpen"
          class="action-dropdown absolute right-0 z-10 mt-2 w-56 origin-top-right rounded-lg bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
        >
          <div class="py-1">
            <button
              v-for="action in actions"
              :key="action.key"
              @click="handleAction(action)"
              :disabled="executing"
              class="action-item w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-150 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
            >
              <Icon v-if="action.icon" :name="action.icon" :size="16" />
              <span>{{ action.label }}</span>
            </button>
          </div>
        </div>
      </Transition>
    </div>

    <!-- Confirmation Modal -->
    <Teleport to="body">
      <div
        v-if="showConfirmation"
        class="fixed inset-0 z-50 overflow-y-auto"
        @click="cancelAction"
      >
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
          <!-- Background overlay -->
          <div class="fixed inset-0 transition-opacity bg-gray-500 dark:bg-gray-900 bg-opacity-75 dark:bg-opacity-75" />

          <!-- Modal panel -->
          <div
            class="relative inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-gray-800 shadow-xl rounded-lg"
            @click.stop
          >
            <div class="space-y-4">
              <!-- Icon -->
              <div class="flex items-center justify-center w-12 h-12 mx-auto bg-yellow-100 dark:bg-yellow-900/30 rounded-full">
                <Icon name="alert-triangle" :size="24" class="text-yellow-600 dark:text-yellow-500" />
              </div>

              <!-- Title -->
              <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 text-center">
                {{ pendingAction?.confirmationTitle || 'Confirm Action' }}
              </h3>

              <!-- Message -->
              <p class="text-sm text-gray-500 dark:text-gray-400 text-center">
                {{ pendingAction?.confirmationMessage || `Are you sure you want to run this action on ${selectedCount} item(s)?` }}
              </p>

              <!-- Additional Fields (if any) -->
              <div v-if="pendingAction?.fields && pendingAction.fields.length > 0" class="space-y-3">
                <div
                  v-for="field in pendingAction.fields"
                  :key="field.attribute"
                  class="form-group"
                >
                  <label
                    :for="field.attribute"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                  >
                    {{ field.label }}
                    <span v-if="field.required" class="text-red-500">*</span>
                  </label>

                  <!-- Text/Email Input -->
                  <input
                    v-if="field.type === 'text' || field.type === 'email'"
                    :id="field.attribute"
                    v-model="actionData[field.attribute]"
                    :type="field.type"
                    :required="field.required"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />

                  <!-- Select -->
                  <select
                    v-else-if="field.type === 'select'"
                    :id="field.attribute"
                    v-model="actionData[field.attribute]"
                    :required="field.required"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  >
                    <option value="">Select {{ field.label }}</option>
                    <option
                      v-for="option in field.options"
                      :key="option.value"
                      :value="option.value"
                    >
                      {{ option.label }}
                    </option>
                  </select>

                  <!-- Textarea -->
                  <textarea
                    v-else-if="field.type === 'textarea'"
                    :id="field.attribute"
                    v-model="actionData[field.attribute]"
                    :required="field.required"
                    :rows="field.rows || 3"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
              </div>

              <!-- Actions -->
              <div class="flex items-center justify-end gap-3 pt-2">
                <button
                  @click="cancelAction"
                  :disabled="executing"
                  class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 font-medium transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  Cancel
                </button>
                <button
                  @click="confirmAction"
                  :disabled="executing"
                  class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                >
                  <div v-if="executing" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white" />
                  <span>{{ executing ? 'Running...' : 'Confirm' }}</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import Icon from '@/components/common/Icon.vue'
import { resourceService } from '@/services/resourceService'

const props = defineProps({
  resource: {
    type: String,
    required: true
  },
  actions: {
    type: Array,
    default: () => []
  },
  selectedIds: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['success', 'error'])

// State
const isOpen = ref(false)
const showConfirmation = ref(false)
const pendingAction = ref(null)
const actionData = ref({})
const executing = ref(false)

// Computed
const hasSelection = computed(() => props.selectedIds.length > 0)
const selectedCount = computed(() => props.selectedIds.length)

// Methods
function toggleDropdown() {
  if (hasSelection.value) {
    isOpen.value = !isOpen.value
  }
}

function handleAction(action) {
  isOpen.value = false
  pendingAction.value = action
  actionData.value = {}

  // Initialize action data with defaults
  if (action.fields) {
    action.fields.forEach(field => {
      if (field.default !== null && field.default !== undefined) {
        actionData.value[field.attribute] = field.default
      }
    })
  }

  // Show confirmation if required or has fields
  if (action.requiresConfirmation !== false || (action.fields && action.fields.length > 0)) {
    showConfirmation.value = true
  } else {
    confirmAction()
  }
}

async function confirmAction() {
  if (!pendingAction.value) return

  executing.value = true
  try {
    const response = await resourceService.runAction(
      props.resource,
      pendingAction.value.key,
      props.selectedIds,
      actionData.value
    )

    emit('success', {
      action: pendingAction.value.key,
      message: response.message || `Action "${pendingAction.value.label}" completed successfully`,
      data: response.data
    })

    showConfirmation.value = false
    pendingAction.value = null
    actionData.value = {}
  } catch (error) {
    emit('error', {
      action: pendingAction.value.key,
      message: error.response?.data?.message || `Failed to execute action "${pendingAction.value.label}"`,
      error
    })
  } finally {
    executing.value = false
  }
}

function cancelAction() {
  showConfirmation.value = false
  pendingAction.value = null
  actionData.value = {}
}

// Close dropdown when clicking outside
if (typeof window !== 'undefined') {
  document.addEventListener('click', (e) => {
    if (isOpen.value && !e.target.closest('.action-buttons')) {
      isOpen.value = false
    }
  })
}
</script>
