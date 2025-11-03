<template>
  <div class="data-table">
    <!-- Initial Loading State -->
    <div v-if="loading && !hasData" class="flex justify-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
    </div>

    <!-- Main Content -->
    <div v-if="hasData || !loading" class="space-y-5">
      <!-- Header with Search and Actions -->
      <div
        v-if="showHeader"
        class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4"
      >
        <!-- Search -->
        <div v-if="searchable" class="relative flex-1 max-w-md">
          <Icon name="search" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 dark:text-gray-500" :size="20" />
          <input
            v-model="searchQuery"
            type="text"
            :placeholder="searchPlaceholder"
            class="w-full pl-10 pr-10 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent focus:bg-white dark:focus:bg-gray-800 transition-colors duration-200"
            @input="handleSearchInput"
          />
          <button
            v-if="searchQuery"
            @click="clearSearch"
            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
          >
            <Icon name="close" :size="18" />
          </button>
        </div>

        <!-- Actions Slot -->
        <div v-if="$slots.actions" class="flex items-center gap-3">
          <slot name="actions" />
        </div>
      </div>

      <!-- Bulk Selection Bar -->
      <div v-if="selectable && selectedRows.length > 0" class="flex items-center justify-between gap-4 p-3 bg-primary-50 dark:bg-primary-900/20 rounded-lg">
        <div class="flex items-center gap-3">
          <span class="text-sm font-medium text-primary-900 dark:text-primary-100">
            {{ selectedRows.length }} selected
          </span>
          <slot name="bulk-actions" :selected="selectedRows" :clear="clearSelection" />
        </div>
        <button
          @click="clearSelection"
          class="px-3 py-1 bg-gray-600 hover:bg-gray-700 text-white rounded text-sm font-medium transition-colors duration-200"
        >
          Clear
        </button>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-lg shadow relative">
        <!-- Loading Overlay -->
        <div
          v-if="loading"
          class="absolute inset-0 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm z-50 flex items-center justify-center rounded-lg transition-opacity duration-200"
        >
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
        </div>

        <table class="w-full">
          <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
            <tr>
              <!-- Selection Checkbox -->
              <th v-if="selectable" class="px-4 py-3 text-left w-12">
                <input
                  type="checkbox"
                  :checked="isAllSelected"
                  :indeterminate="isIndeterminate"
                  @change="toggleSelectAll"
                  class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                />
              </th>

              <!-- Column Headers -->
              <th
                v-for="column in visibleColumns"
                :key="column.key"
                :class="[
                  'px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider',
                  { 'cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600': column.sortable }
                ]"
                :style="column.width ? { width: column.width } : {}"
                @click="column.sortable && handleSort(column.key)"
              >
                <div class="flex items-center gap-2">
                  <span>{{ column.label }}</span>
                  <Icon
                    v-if="column.sortable && sortBy === column.key"
                    :name="sortDirection === 'asc' ? 'arrow-up' : 'arrow-down'"
                    :size="14"
                  />
                </div>
              </th>

              <!-- Actions Column -->
              <th v-if="$slots['row-actions']" class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>

          <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            <!-- Empty State -->
            <tr v-if="!processedData || processedData.length === 0">
              <td :colspan="totalColumns" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                <slot name="empty-state">
                  {{ emptyMessage }}
                </slot>
              </td>
            </tr>

            <!-- Data Rows -->
            <tr
              v-for="(row, index) in processedData"
              :key="getRowKey(row, index)"
              :class="[
                'hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150',
                { 'bg-primary-50/30 dark:bg-primary-900/10': selectable && isRowSelected(row) }
              ]"
            >
              <!-- Selection Checkbox -->
              <td v-if="selectable" class="px-4 py-3">
                <input
                  type="checkbox"
                  :checked="isRowSelected(row)"
                  @change="toggleRowSelection(row)"
                  class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                />
              </td>

              <!-- Data Cells -->
              <td
                v-for="column in visibleColumns"
                :key="column.key"
                class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100"
              >
                <slot :name="`cell-${column.key}`" :row="row" :value="getCellValue(row, column.key)" :column="column">
                  {{ formatCellValue(row, column) }}
                </slot>
              </td>

              <!-- Actions Cell -->
              <td v-if="$slots['row-actions']" class="px-4 py-3 text-right text-sm">
                <slot name="row-actions" :row="row" :index="index" />
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="showPagination && paginationData" class="flex items-center justify-between">
        <div class="text-sm text-gray-700 dark:text-gray-300">
          Showing {{ paginationData.from }} to {{ paginationData.to }} of {{ paginationData.total }} results
        </div>
        <div class="flex gap-2">
          <button
            v-for="page in paginationPages"
            :key="page"
            @click="handlePageChange(page)"
            :disabled="page === paginationData.current_page"
            :class="[
              'px-3 py-1 rounded border transition-colors duration-200',
              page === paginationData.current_page
                ? 'bg-primary-600 text-white border-primary-600'
                : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700'
            ]"
          >
            {{ page }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, useSlots } from 'vue'
import Icon from '@/components/common/Icon.vue'

const slots = useSlots()

const props = defineProps({
  // Data - can be Array or Laravel pagination response object
  data: {
    type: [Array, Object],
    default: () => []
  },
  columns: {
    type: Array,
    required: true,
    validator: (columns) => {
      return columns.every(col => col.key && col.label)
    }
  },

  // Selection
  selectable: {
    type: Boolean,
    default: false
  },
  rowKey: {
    type: [String, Function],
    default: 'id'
  },

  // Search
  searchable: {
    type: Boolean,
    default: false
  },
  searchPlaceholder: {
    type: String,
    default: 'Search...'
  },
  searchKeys: {
    type: Array,
    default: () => []
  },

  // Sorting
  sortable: {
    type: Boolean,
    default: false
  },
  defaultSort: {
    type: Object,
    default: () => ({ key: '', direction: 'asc' })
  },

  // Pagination - backwards compatible (use data prop for Laravel pagination)
  pagination: {
    type: Object,
    default: null
  },

  // Server-side mode
  serverSide: {
    type: Boolean,
    default: false
  },

  // UI
  loading: {
    type: Boolean,
    default: false
  },
  showHeader: {
    type: Boolean,
    default: true
  },
  emptyMessage: {
    type: String,
    default: 'No records found'
  },

  // Debounce
  searchDebounce: {
    type: Number,
    default: 300
  }
})

const emit = defineEmits(['search', 'sort', 'page-change', 'selection-change'])

// Search
const searchQuery = ref('')
let searchTimeout = null

// Sorting
const sortBy = ref(props.defaultSort.key)
const sortDirection = ref(props.defaultSort.direction)

// Selection
const selectedRows = ref([])

// Computed
const isLaravelPagination = computed(() => {
  return props.data && typeof props.data === 'object' && 'data' in props.data
})

const rawData = computed(() => {
  if (isLaravelPagination.value) {
    return props.data.data || []
  }
  return Array.isArray(props.data) ? props.data : []
})

const hasData = computed(() => {
  if (isLaravelPagination.value) {
    return rawData.value.length > 0 || (props.data.total && props.data.total > 0)
  }
  return rawData.value.length > 0
})

const visibleColumns = computed(() => {
  return props.columns.filter(col => col.visible !== false)
})

const totalColumns = computed(() => {
  let count = visibleColumns.value.length
  if (props.selectable) count++
  if (slots['row-actions']) count++
  return count
})

const processedData = computed(() => {
  let result = [...rawData.value]

  // Client-side search (only if not server-side and searchKeys are provided)
  if (searchQuery.value && props.searchKeys.length > 0 && !props.serverSide && !isLaravelPagination.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(row => {
      return props.searchKeys.some(key => {
        const value = getCellValue(row, key)
        return String(value).toLowerCase().includes(query)
      })
    })
  }

  // Client-side sorting (only if not server-side)
  if (sortBy.value && !props.serverSide && !isLaravelPagination.value) {
    result.sort((a, b) => {
      const aVal = getCellValue(a, sortBy.value)
      const bVal = getCellValue(b, sortBy.value)

      const comparison = aVal > bVal ? 1 : aVal < bVal ? -1 : 0
      return sortDirection.value === 'asc' ? comparison : -comparison
    })
  }

  return result
})

const isAllSelected = computed(() => {
  return processedData.value.length > 0 && selectedRows.value.length === processedData.value.length
})

const isIndeterminate = computed(() => {
  return selectedRows.value.length > 0 && selectedRows.value.length < processedData.value.length
})

const paginationData = computed(() => {
  // Support both Laravel pagination object and separate pagination prop
  if (isLaravelPagination.value) {
    return {
      current_page: props.data.current_page,
      last_page: props.data.last_page,
      from: props.data.from,
      to: props.data.to,
      total: props.data.total,
      per_page: props.data.per_page
    }
  }
  return props.pagination
})

const showPagination = computed(() => {
  return paginationData.value && paginationData.value.last_page > 1
})

const paginationPages = computed(() => {
  if (!paginationData.value) return []

  const pages = []
  const total = paginationData.value.last_page
  const current = paginationData.value.current_page

  let start = Math.max(1, current - 2)
  let end = Math.min(total, current + 2)

  for (let i = start; i <= end; i++) {
    pages.push(i)
  }

  return pages
})

// Methods
function getRowKey(row, index) {
  if (typeof props.rowKey === 'function') {
    return props.rowKey(row, index)
  }
  return row[props.rowKey] || index
}

function getCellValue(row, key) {
  if (key.includes('.')) {
    return key.split('.').reduce((obj, k) => obj?.[k], row)
  }
  return row[key]
}

function formatCellValue(row, column) {
  const value = getCellValue(row, column.key)

  if (value === null || value === undefined) return '-'

  // Custom formatter
  if (column.formatter && typeof column.formatter === 'function') {
    return column.formatter(value, row)
  }

  // Type-based formatting
  if (column.type === 'boolean') return value ? 'Yes' : 'No'
  if (column.type === 'date') return new Date(value).toLocaleDateString()
  if (column.type === 'datetime') return new Date(value).toLocaleString()
  if (column.type === 'currency') return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value)

  return value
}

function handleSearchInput() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    emit('search', searchQuery.value)
  }, props.searchDebounce)
}

function clearSearch() {
  searchQuery.value = ''
  emit('search', '')
}

function handleSort(key) {
  if (sortBy.value === key) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortBy.value = key
    sortDirection.value = 'asc'
  }

  emit('sort', { key: sortBy.value, direction: sortDirection.value })
}

function toggleRowSelection(row) {
  const key = getRowKey(row, processedData.value.indexOf(row))
  const index = selectedRows.value.findIndex(r => getRowKey(r, 0) === key)

  if (index > -1) {
    selectedRows.value.splice(index, 1)
  } else {
    selectedRows.value.push(row)
  }

  emit('selection-change', selectedRows.value)
}

function toggleSelectAll() {
  if (isAllSelected.value) {
    selectedRows.value = []
  } else {
    selectedRows.value = [...processedData.value]
  }

  emit('selection-change', selectedRows.value)
}

function clearSelection() {
  selectedRows.value = []
  emit('selection-change', [])
}

function isRowSelected(row) {
  const key = getRowKey(row, processedData.value.indexOf(row))
  return selectedRows.value.some(r => getRowKey(r, 0) === key)
}

function handlePageChange(page) {
  emit('page-change', page)
}

// Watch for external data changes to clear invalid selections
watch(() => props.data, () => {
  if (selectedRows.value.length > 0) {
    const currentData = rawData.value
    selectedRows.value = selectedRows.value.filter(selected => {
      const key = getRowKey(selected, 0)
      return currentData.some((row, idx) => getRowKey(row, idx) === key)
    })
  }
}, { deep: true })

// Expose methods
defineExpose({
  clearSelection,
  getSelectedRows: () => selectedRows.value,
  setSelectedRows: (rows) => { selectedRows.value = rows }
})
</script>
