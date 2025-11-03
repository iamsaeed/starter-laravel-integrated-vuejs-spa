<template>
  <div class="resource-table">
    <!-- Initial Loading State -->
    <div v-if="loading && !meta" class="flex justify-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
    </div>

    <!-- Main Content -->
    <div v-if="meta" class="space-y-5">
      <!-- Header with Search and Actions -->
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <!-- Search -->
        <div v-if="meta.searchable" class="relative flex-1 max-w-md">
          <Icon name="search" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 dark:text-gray-500" :size="20" />
          <input
            v-model="search"
            type="text"
            placeholder="Search records..."
            class="w-full pl-10 pr-10 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent focus:bg-white dark:focus:bg-gray-800 transition-colors duration-200"
            @input="debounceSearch"
          />
          <button
            v-if="search"
            @click="search = ''; debounceSearch()"
            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
          >
            <Icon name="close" :size="18" />
          </button>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-3">
          <button @click="$emit('create')" class="resource-button-create">
            <Icon name="add" :size="20" />
            <span>Create New</span>
          </button>
        </div>
      </div>

      <!-- Filters -->
      <FilterBar
        v-if="meta.filters && meta.filters.length"
        :filters="meta.filters"
        v-model="filters"
        @change="handleFilterChange"
      />

      <!-- Bulk Selection Bar -->
      <div v-if="selectedIds.length > 0" class="flex items-center justify-between gap-4 p-3 bg-primary-50 dark:bg-primary-900/20 rounded-lg">
        <div class="flex items-center gap-3">
          <span class="text-sm font-medium text-primary-900 dark:text-primary-100">
            {{ selectedIds.length }} selected
          </span>
          <ActionButtons
            v-if="meta.actions && meta.actions.length"
            :resource="resource"
            :actions="meta.actions"
            :selected-ids="selectedIds"
            @success="handleActionSuccess"
            @error="handleActionError"
          />
        </div>
        <div class="flex items-center gap-2">
          <button
            @click="handleBulkDelete"
            class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded text-sm font-medium transition-colors duration-200"
          >
            Delete
          </button>
          <button
            @click="clearSelection"
            class="px-3 py-1 bg-gray-600 hover:bg-gray-700 text-white rounded text-sm font-medium transition-colors duration-200"
          >
            Clear
          </button>
        </div>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-lg shadow relative">
        <!-- Loading Overlay (only covers table) -->
        <div
          v-if="loading"
          class="absolute inset-0 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm z-50 flex items-center justify-center rounded-lg transition-opacity duration-200"
        >
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
        </div>

        <table class="w-full">
          <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
            <tr>
              <th class="px-4 py-3 text-left w-12">
                <input
                  type="checkbox"
                  :checked="isAllSelected"
                  @change="toggleSelectAll"
                  class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                />
              </th>
              <th
                v-for="field in visibleFields"
                :key="field.attribute"
                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                :class="{ 'cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600': field.sortable }"
                @click="field.sortable && handleSort(field.attribute)"
              >
                <div class="flex items-center gap-2">
                  <span>{{ field.label }}</span>
                  <Icon
                    v-if="field.sortable && sortBy === field.attribute"
                    :name="sortDirection === 'asc' ? 'arrow-up' : 'arrow-down'"
                    :size="14"
                  />
                </div>
              </th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-if="!data || data.length === 0">
              <td :colspan="visibleFields.length + 2" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                No records found
              </td>
            </tr>
            <tr
              v-for="item in data"
              :key="item.id"
              class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150"
            >
              <td class="px-4 py-3">
                <input
                  type="checkbox"
                  :checked="selectedIds.includes(item.id)"
                  @change="toggleSelection(item.id)"
                  class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                />
              </td>
              <td
                v-for="field in visibleFields"
                :key="field.attribute"
                class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100"
              >
                <!-- Toggle for toggleable fields (boolean or status with toggle enabled) -->
                <ToggleSwitch
                  v-if="isToggleableField(field)"
                  :model-value="getToggleValue(item[field.attribute], field)"
                  @change="handleToggle(item, field, $event)"
                />
                <!-- Image field display -->
                <div v-else-if="field.type === 'image' && item[field.attribute]" class="flex items-center">
                  <!-- SVG Display -->
                  <div
                    v-if="field.meta.displayType === 'svg'"
                    v-html="item[field.attribute]"
                    :style="{
                      width: field.meta.width ? `${field.meta.width}px` : 'auto',
                      height: field.meta.height ? `${field.meta.height}px` : 'auto'
                    }"
                    class="inline-block"
                    :class="{ 'rounded-full': field.meta.rounded }"
                  />
                  <!-- URL Display -->
                  <img
                    v-else-if="field.meta.displayType === 'url'"
                    :src="item[field.attribute]"
                    :alt="field.meta.alt || field.label"
                    :width="field.meta.width"
                    :height="field.meta.height"
                    class="object-cover"
                    :class="{ 'rounded-full': field.meta.rounded }"
                    @error="handleImageError($event, field)"
                  />
                  <!-- Base64 Display -->
                  <img
                    v-else-if="field.meta.displayType === 'base64'"
                    :src="`data:image/png;base64,${item[field.attribute]}`"
                    :alt="field.meta.alt || field.label"
                    :width="field.meta.width"
                    :height="field.meta.height"
                    class="object-cover"
                    :class="{ 'rounded-full': field.meta.rounded }"
                    @error="handleImageError($event, field)"
                  />
                </div>
                <!-- Empty state for image fields -->
                <span v-else-if="field.type === 'image' && !item[field.attribute]" class="text-gray-400">
                  -
                </span>
                <!-- Media field display -->
                <div v-else-if="field.type === 'media'" class="flex items-center">
                  <img
                    v-if="item[field.attribute]"
                    :src="item[field.attribute].thumbnail || item[field.attribute].url"
                    :alt="field.label"
                    :width="field.meta.previewWidth || 48"
                    :height="field.meta.previewHeight || 48"
                    class="object-cover"
                    :class="{ 'rounded-full': field.meta.rounded, 'rounded': !field.meta.rounded }"
                  />
                  <!-- Empty state with placeholder icon -->
                  <div
                    v-else
                    :style="{
                      width: `${field.meta.previewWidth || 48}px`,
                      height: `${field.meta.previewHeight || 48}px`
                    }"
                    class="bg-gray-200 dark:bg-gray-700 flex items-center justify-center"
                    :class="{ 'rounded-full': field.meta.rounded, 'rounded': !field.meta.rounded }"
                  >
                    <Icon name="user" :size="Math.floor((field.meta.previewWidth || 48) / 2)" class="text-gray-400 dark:text-gray-500" />
                  </div>
                </div>
                <!-- Regular field display -->
                <span v-else>
                  {{ formatValue(item[field.attribute], field) }}
                </span>
              </td>
              <td class="px-4 py-3 text-right text-sm">
                <div class="flex items-center justify-end gap-2">
                  <!-- Impersonate button -->
                  <button
                    v-if="canImpersonateUser(item)"
                    @click="$emit('impersonate', item)"
                    v-tooltip="'Impersonate User'"
                    class="p-1.5 text-purple-600 hover:text-purple-900 dark:text-purple-400 dark:hover:text-purple-300 hover:bg-purple-50 dark:hover:bg-purple-900/20 rounded transition-colors duration-200"
                  >
                    <Icon name="person" :size="18" />
                  </button>
                  <button
                    @click="$emit('edit', item)"
                    v-tooltip="'Edit'"
                    class="resource-button-edit"
                  >
                    <Icon name="edit" :size="18" />
                  </button>
                  <button
                    @click="handleDelete(item.id)"
                    v-tooltip="'Delete'"
                    class="resource-button-delete"
                  >
                    <Icon name="delete" :size="18" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="shouldShowPagination" class="flex items-center justify-between">
        <div class="text-sm text-gray-700 dark:text-gray-300">
          Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} results
        </div>
        <div class="flex gap-2">
          <button
            v-for="page in paginationPages"
            :key="page"
            @click="changePage(page)"
            :disabled="page === pagination.current_page"
            class="px-3 py-1 rounded border transition-colors duration-200"
            :class="
              page === pagination.current_page
                ? 'bg-primary-600 text-white border-primary-600'
                : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700'
            "
          >
            {{ page }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { resourceService } from '@/core/services/resourceService'
import { useDialog } from '@/composables/useDialog'
import { useAuthStore } from '@/stores/auth'
import Icon from '@/components/common/Icon.vue'
import ToggleSwitch from '@/components/common/ToggleSwitch.vue'
import FilterBar from './FilterBar.vue'
import ActionButtons from './ActionButtons.vue'

const props = defineProps({
  resource: {
    type: String,
    required: true
  },
  defaultPerPage: {
    type: Number,
    default: 15
  },
  enableExport: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['create', 'edit', 'view', 'deleted', 'impersonate'])

const authStore = useAuthStore()

const route = useRoute()
const router = useRouter()

// State
const meta = ref(null)
const data = ref([])
const loading = ref(false)
const search = ref('')
const sortBy = ref('')
const sortDirection = ref('asc')
const filters = ref({})
const selectedIds = ref([])
const pagination = ref(null)
const currentPage = ref(1)
const isInitializing = ref(true) // Prevent updating URL during initialization

// Computed
const visibleFields = computed(() => {
  if (!meta.value) return []
  return meta.value.fields
})

const isAllSelected = computed(() => {
  return data.value.length > 0 && selectedIds.value.length === data.value.length
})

const paginationPages = computed(() => {
  if (!pagination.value) return []
  const pages = []
  const total = pagination.value.last_page
  const current = pagination.value.current_page

  // Simple pagination: show 5 pages around current
  let start = Math.max(1, current - 2)
  let end = Math.min(total, current + 2)

  for (let i = start; i <= end; i++) {
    pages.push(i)
  }

  return pages
})

const shouldShowPagination = computed(() => {
  if (!pagination.value) return false
  // Hide pagination if there's only one page (all data fits on single page)
  return pagination.value.last_page > 1
})

/**
 * Check if the impersonate button should be shown for a user
 * Only show if:
 * 1. Resource is 'users'
 * 2. Current user is an admin
 * 3. Target user is not the current user (can't impersonate yourself)
 * 4. Target user is not an admin (can't impersonate other admins)
 * 5. Target user has the 'user' role (is_user = true)
 */
function canImpersonateUser(user) {
  // Only available on users resource
  if (props.resource !== 'users') {
    return false
  }

  // Current user must be an admin
  if (!authStore.user?.can_access_admin_panel) {
    return false
  }

  // Cannot impersonate yourself
  if (user.id === authStore.user?.id) {
    return false
  }

  // Cannot impersonate other admins
  if (user.can_access_admin_panel) {
    return false
  }

  // Only show for users with 'user' role (non-admin types)
  if (!user.is_user) {
    return false
  }

  return true
}

// Methods
async function fetchMeta() {
  try {
    const response = await resourceService.getMeta(props.resource, 'index')
    meta.value = response
  } catch (error) {
    console.error('Failed to fetch resource meta:', error)
  }
}

async function fetchData() {
  loading.value = true
  try {
    const params = {
      page: currentPage.value,
      perPage: props.defaultPerPage,
      search: search.value,
      sort: sortBy.value,
      direction: sortDirection.value,
      filters: filters.value
    }

    const response = await resourceService.index(props.resource, params)
    data.value = response.data
    pagination.value = response
  } catch (error) {
    console.error('Failed to fetch resource data:', error)
  } finally {
    loading.value = false
  }
}

function debounceSearch() {
  if (debounceSearch.timeout) clearTimeout(debounceSearch.timeout)
  debounceSearch.timeout = setTimeout(() => {
    currentPage.value = 1
    fetchData()
  }, 300)
}

function handleSort(field) {
  if (sortBy.value === field) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortBy.value = field
    sortDirection.value = 'asc'
  }
  fetchData()
}

function handleFilterChange() {
  currentPage.value = 1
  fetchData()
}

function toggleSelection(id) {
  const index = selectedIds.value.indexOf(id)
  if (index > -1) {
    selectedIds.value.splice(index, 1)
  } else {
    selectedIds.value.push(id)
  }
}

function toggleSelectAll() {
  if (isAllSelected.value) {
    selectedIds.value = []
  } else {
    selectedIds.value = data.value.map(item => item.id)
  }
}

function clearSelection() {
  selectedIds.value = []
}

const dialog = useDialog()

async function handleDelete(id) {
  const confirmed = await dialog.confirmDanger(
    'Are you sure you want to delete this item? This action cannot be undone.',
    {
      title: 'Delete Confirmation',
      confirmLabel: 'Delete',
      cancelLabel: 'Cancel',
    }
  )

  if (!confirmed) return

  try {
    await resourceService.destroy(props.resource, id)
    emit('deleted', [id])
    fetchData()
  } catch (error) {
    console.error('Failed to delete item:', error)
  }
}

async function handleBulkDelete() {
  const confirmed = await dialog.confirmDanger(
    `Are you sure you want to delete ${selectedIds.value.length} items? This action cannot be undone.`,
    {
      title: 'Bulk Delete Confirmation',
      confirmLabel: 'Delete All',
      cancelLabel: 'Cancel',
    }
  )

  if (!confirmed) return

  try {
    await resourceService.bulkDelete(props.resource, selectedIds.value)
    emit('deleted', selectedIds.value)
    selectedIds.value = []
    fetchData()
  } catch (error) {
    console.error('Failed to bulk delete:', error)
  }
}

function changePage(page) {
  currentPage.value = page
  fetchData()
}

function isToggleableField(field) {
  // Check if field has toggleable meta flag
  return field.meta?.toggleable === true
}

function getToggleValue(value, field) {
  if (field.type === 'boolean') {
    return Boolean(value)
  }

  // For select fields with toggle (like status)
  if (field.type === 'select' && field.meta?.toggleable) {
    const trueValue = field.meta.toggleTrueValue || 'active'
    return value === trueValue
  }

  return false
}

async function handleToggle(item, field, newValue) {
  try {
    let updateValue

    if (field.type === 'boolean') {
      updateValue = newValue
    } else if (field.type === 'select' && field.meta?.toggleable) {
      const trueValue = field.meta.toggleTrueValue || 'active'
      const falseValue = field.meta.toggleFalseValue || 'inactive'
      updateValue = newValue ? trueValue : falseValue
    }

    // Update via PATCH API (partial update without full validation)
    await resourceService.patch(props.resource, item.id, {
      [field.attribute]: updateValue
    })

    // Refresh the data to reflect the change
    await fetchData()
  } catch (error) {
    console.error('Failed to toggle field:', error)
    // Optionally show error toast
  }
}

function formatValue(value, field) {
  if (value === null || value === undefined) return '-'
  if (field.type === 'boolean') return value ? 'Yes' : 'No'
  if (field.type === 'date') return new Date(value).toLocaleDateString()

  // Handle status enum
  if (field.attribute === 'status') {
    return value === 'active' ? 'Active' : 'Inactive'
  }

  // Handle BelongsToMany relationships
  if ((field.type === 'belongsToMany' || field.type === 'belongs-to-many') && Array.isArray(value)) {
    if (value.length === 0) return '-'
    // Extract names from relationship objects
    return value.map(item => item.name || item.display || item.id).join(', ')
  }

  return value
}

function handleImageError(event, field) {
  // If fallback URL is provided, use it
  if (field.meta?.fallback) {
    event.target.src = field.meta.fallback
  } else {
    // Hide the broken image
    event.target.style.display = 'none'
  }
}

function handleActionSuccess(result) {
  // Clear selection after successful action
  selectedIds.value = []
  // Refresh data to reflect changes
  fetchData()
  // Could show toast notification here
  console.log('Action completed:', result)
}

function handleActionError(error) {
  // Could show error toast notification here
  console.error('Action failed:', error)
}

// Initialize state from URL query params
function initializeFromQuery() {
  const query = route.query

  // Initialize search
  if (query.search) {
    search.value = query.search
  }

  // Initialize sort
  if (query.sort) {
    sortBy.value = query.sort
  }
  if (query.direction) {
    sortDirection.value = query.direction
  }

  // Initialize page
  if (query.page) {
    currentPage.value = parseInt(query.page) || 1
  }

  // Initialize filters
  if (query.filters) {
    try {
      filters.value = JSON.parse(query.filters)
    } catch (e) {
      console.warn('Failed to parse filters from query params:', e)
    }
  }
}

// Update URL query params when state changes
function updateQueryParams() {
  if (isInitializing.value) return

  const query = { ...route.query }

  // Update search
  if (search.value) {
    query.search = search.value
  } else {
    delete query.search
  }

  // Update sort
  if (sortBy.value) {
    query.sort = sortBy.value
    query.direction = sortDirection.value
  } else {
    delete query.sort
    delete query.direction
  }

  // Update page
  if (currentPage.value > 1) {
    query.page = currentPage.value.toString()
  } else {
    delete query.page
  }

  // Update filters
  if (Object.keys(filters.value).length > 0) {
    query.filters = JSON.stringify(filters.value)
  } else {
    delete query.filters
  }

  // Only update if query actually changed
  if (JSON.stringify(query) !== JSON.stringify(route.query)) {
    router.replace({ query })
  }
}

// Watch state changes and update URL
watch([search, sortBy, sortDirection, currentPage, filters], () => {
  updateQueryParams()
}, { deep: true })

// Watch route query changes (e.g., browser back/forward)
watch(() => route.query, (newQuery) => {
  if (isInitializing.value) return

  const oldQuery = {
    search: search.value || undefined,
    sort: sortBy.value || undefined,
    direction: sortDirection.value !== 'asc' ? sortDirection.value : undefined,
    page: currentPage.value > 1 ? currentPage.value.toString() : undefined,
    filters: Object.keys(filters.value).length > 0 ? JSON.stringify(filters.value) : undefined
  }

  // Only update state if query actually changed
  if (JSON.stringify(newQuery) !== JSON.stringify(oldQuery)) {
    initializeFromQuery()
    fetchData()
  }
}, { deep: true })

// Lifecycle
onMounted(async () => {
  initializeFromQuery()
  await fetchMeta()
  await fetchData()
  isInitializing.value = false // Enable URL updates after initial load
})

// Watch for resource changes
watch(() => props.resource, async () => {
  // Reset state when resource changes
  search.value = ''
  sortBy.value = ''
  sortDirection.value = 'asc'
  filters.value = {}
  currentPage.value = 1

  await fetchMeta()
  await fetchData()
})

// Expose methods for parent component
defineExpose({
  fetchData,
  fetchMeta
})
</script>
