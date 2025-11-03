<template>
  <div v-if="filters && filters.length > 0" class="filter-bar bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
    <div class="flex flex-col gap-3">
      <div class="flex items-center gap-2">
        <Icon name="filter" :size="18" class="text-gray-500 dark:text-gray-400" />
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Filters</h3>
        <span v-if="hasActiveFilters" class="px-2 py-0.5 bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 rounded-full text-xs font-medium">
          {{ activeFilterCount }}
        </span>
      </div>

      <div class="flex flex-wrap gap-3">
        <!-- Dynamic Filters -->
        <div
          v-for="filter in filters"
          :key="filter.key"
          class="filter-group"
        >
          <!-- Select Filter -->
          <select
            v-if="filter.type === 'select'"
            :id="`filter-${filter.key}`"
            v-model="activeFilters[filter.key]"
            class="filter-select px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent min-w-[180px] hover:border-gray-400 dark:hover:border-gray-500 transition-colors"
            @change="handleFilterChange"
          >
            <option value="">All {{ filter.label }}</option>
            <option
              v-for="option in filter.options"
              :key="option.value"
              :value="option.value"
            >
              {{ option.label }}
            </option>
          </select>

        <!-- Boolean Filter -->
        <div v-else-if="filter.type === 'boolean'" class="flex items-center gap-2 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-900 hover:border-gray-400 dark:hover:border-gray-500 transition-colors">
          <input
            :id="`filter-${filter.key}`"
            v-model="activeFilters[filter.key]"
            type="checkbox"
            class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
            @change="handleFilterChange"
          />
          <label
            :for="`filter-${filter.key}`"
            class="text-sm text-gray-700 dark:text-gray-300 cursor-pointer font-medium"
          >
            {{ filter.label }}
          </label>
        </div>

        <!-- Date Range Filter -->
        <div v-else-if="filter.type === 'dateRange'" class="flex items-center gap-2">
          <input
            :id="`filter-${filter.key}-from`"
            v-model="activeFilters[`${filter.key}_from`]"
            type="date"
            class="filter-date px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent hover:border-gray-400 dark:hover:border-gray-500 transition-colors"
            @change="handleFilterChange"
          />
          <span class="text-gray-500 dark:text-gray-400 text-sm font-medium">to</span>
          <input
            :id="`filter-${filter.key}-to`"
            v-model="activeFilters[`${filter.key}_to`]"
            type="date"
            class="filter-date px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent hover:border-gray-400 dark:hover:border-gray-500 transition-colors"
            @change="handleFilterChange"
          />
        </div>

        <!-- Number Range Filter -->
        <div v-else-if="filter.type === 'numberRange'" class="flex items-center gap-2">
          <input
            :id="`filter-${filter.key}-min`"
            v-model.number="activeFilters[`${filter.key}_min`]"
            type="number"
            :placeholder="`Min ${filter.label}`"
            class="filter-number px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent w-28 hover:border-gray-400 dark:hover:border-gray-500 transition-colors"
            @change="handleFilterChange"
          />
          <span class="text-gray-500 dark:text-gray-400 text-sm font-medium">to</span>
          <input
            :id="`filter-${filter.key}-max`"
            v-model.number="activeFilters[`${filter.key}_max`]"
            type="number"
            :placeholder="`Max ${filter.label}`"
            class="filter-number px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent w-28 hover:border-gray-400 dark:hover:border-gray-500 transition-colors"
            @change="handleFilterChange"
          />
        </div>
      </div>

      <!-- Clear Filters Button -->
      <button
        v-if="hasActiveFilters"
        @click="clearFilters"
        class="filter-clear self-start px-4 py-2 text-sm font-medium text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg transition-all duration-200 flex items-center gap-2"
      >
        <Icon name="close" :size="16" />
        <span>Clear Filters</span>
      </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import Icon from '@/components/common/Icon.vue'

const props = defineProps({
  filters: {
    type: Array,
    default: () => []
  },
  modelValue: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['update:modelValue', 'change'])

// Active filters state
const activeFilters = ref({ ...props.modelValue })

// Computed
const hasActiveFilters = computed(() => {
  return Object.values(activeFilters.value).some(value => {
    if (typeof value === 'boolean') return value === true
    return value !== '' && value !== null && value !== undefined
  })
})

const activeFilterCount = computed(() => {
  return Object.values(activeFilters.value).filter(value => {
    if (typeof value === 'boolean') return value === true
    return value !== '' && value !== null && value !== undefined
  }).length
})

// Methods
function handleFilterChange() {
  emit('update:modelValue', activeFilters.value)
  emit('change', activeFilters.value)
}

function clearFilters() {
  activeFilters.value = {}
  handleFilterChange()
}

// Watch for external changes to modelValue
watch(() => props.modelValue, (newValue) => {
  activeFilters.value = { ...newValue }
}, { deep: true })
</script>
