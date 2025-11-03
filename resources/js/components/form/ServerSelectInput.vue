<template>
  <div ref="componentRoot" class="relative">
    <!-- Multiple Select -->
    <div v-if="multiple">
      <!-- Input container with tags inside -->
      <div
        class="relative flex flex-wrap items-center gap-1.5 min-h-[42px] px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 transition-all duration-200"
        :class="{
          'opacity-50 cursor-not-allowed': disabled || hasReachedMaxSelections,
          'ring-2 ring-primary-500 border-primary-500 dark:border-primary-500': showDropdown
        }"
        @click="focusInput"
      >
        <!-- Search icon or loading spinner -->
        <div class="flex items-center pointer-events-none">
          <svg v-if="!loading" class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <div v-else class="animate-spin rounded-full h-5 w-5 border-b-2 border-primary-600" />
        </div>

        <!-- Selected tags -->
        <span
          v-for="(item, index) in selectedItems.filter(i => modelValue.includes(i.value))"
          :key="`selected-${item.value}-${index}`"
          class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-primary-500 text-white transition-all duration-200 hover:bg-primary-600"
        >
          <span>{{ item.label }}</span>
          <button
            type="button"
            @click.stop="removeItem(index)"
            class="flex items-center justify-center w-3.5 h-3.5 rounded-full hover:bg-primary-700 transition-colors"
            :aria-label="`Remove ${item.label}`"
          >
            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </span>

        <!-- Flexible input -->
        <input
          ref="multiSearchInput"
          v-model="searchQuery"
          type="text"
          :placeholder="selectedItems.filter(i => modelValue.includes(i.value)).length === 0 ? (hasReachedMaxSelections ? `Maximum ${maxSelections} items selected` : placeholder) : ''"
          :disabled="disabled || hasReachedMaxSelections"
          class="flex-1 min-w-[120px] border-0 outline-none bg-transparent text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 p-0"
          @focus="handleFocus"
          @blur="handleBlur"
          @keydown="handleKeydown"
        />

        <!-- Dropdown toggle -->
        <button
          type="button"
          @click.stop="toggleDropdown"
          :disabled="disabled || hasReachedMaxSelections"
          class="flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors ml-auto"
          :class="{ 'opacity-50 cursor-not-allowed': disabled || hasReachedMaxSelections }"
        >
          <svg class="w-5 h-5 transition-transform duration-200" :class="{ 'rotate-180': showDropdown }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>
      </div>

      <!-- Dropdown for multiple select -->
      <transition
        enter-active-class="transition ease-out duration-100"
        enter-from-class="transform opacity-0 scale-95"
        enter-to-class="transform opacity-100 scale-100"
        leave-active-class="transition ease-in duration-75"
        leave-from-class="transform opacity-100 scale-100"
        leave-to-class="transform opacity-0 scale-95"
      >
        <div
          v-show="showDropdown"
          ref="multiDropdown"
          class="absolute z-50 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-xl overflow-auto custom-scrollbar"
          :class="dropdownPosition === 'top' ? 'bottom-full mb-1' : 'top-full mt-1'"
          :style="{ maxHeight: dropdownMaxHeight + 'px' }"
        >
          <!-- Loading state -->
          <div v-if="loading" class="px-4 py-8 text-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600 mx-auto mb-2" />
            <p class="text-sm text-gray-500 dark:text-gray-400">Searching...</p>
          </div>

          <!-- Empty state -->
          <div
            v-else-if="availableOptions.length === 0"
            class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400"
          >
            <svg class="w-12 h-12 mx-auto mb-2 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
            <p v-if="hasReachedMaxSelections">Maximum selections reached</p>
            <p v-else-if="searchQuery">No results found for "{{ searchQuery }}"</p>
            <p v-else>Type to search...</p>
          </div>

          <!-- Options list -->
          <div
            v-else
            v-for="(option, index) in availableOptions"
            :key="`multi-option-${option.value}-${index}`"
            :class="[
              'px-4 py-2.5 cursor-pointer flex items-center justify-between transition-colors duration-150',
              {
                'bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300': index === selectedIndex,
                'hover:bg-gray-50 dark:hover:bg-gray-700/50 text-gray-900 dark:text-gray-100': index !== selectedIndex
              }
            ]"
            @mousedown="addItem(option)"
            @mouseenter="selectedIndex = index"
          >
            <span class="text-sm">{{ option.label }}</span>
            <svg
              v-if="index === selectedIndex"
              class="w-5 h-5 text-primary-600 dark:text-primary-400"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
          </div>
        </div>
      </transition>
    </div>

    <!-- Single Select -->
    <div v-else class="relative">
      <!-- Input display -->
      <div class="relative">
        <input
          ref="singleSearchInput"
          v-model="displayValue"
          type="text"
          :placeholder="placeholder"
          :disabled="disabled"
          class="w-full pl-10 pr-10 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:focus:border-primary-500 transition-all duration-200"
          :class="{
            'opacity-50 cursor-not-allowed': disabled,
            'border-primary-500 dark:border-primary-500': showDropdown
          }"
          @focus="handleFocus"
          @blur="handleBlur"
          @keydown="handleKeydown"
        />
        <!-- Icon -->
        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
          <svg v-if="!loading" class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <div v-else class="animate-spin rounded-full h-5 w-5 border-b-2 border-primary-600" />
        </div>
        <!-- Clear button -->
        <button
          v-if="modelValue && !disabled"
          type="button"
          @click.stop="clearSelection"
          class="absolute inset-y-0 right-10 flex items-center pr-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
        <!-- Dropdown toggle -->
        <button
          type="button"
          @click="toggleDropdown"
          :disabled="disabled"
          class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
          :class="{ 'opacity-50 cursor-not-allowed': disabled }"
        >
          <svg
            class="w-5 h-5 transition-transform duration-200"
            :class="{ 'rotate-180': showDropdown }"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>
      </div>

      <!-- Dropdown for single select -->
      <transition
        enter-active-class="transition ease-out duration-100"
        enter-from-class="transform opacity-0 scale-95"
        enter-to-class="transform opacity-100 scale-100"
        leave-active-class="transition ease-in duration-75"
        leave-from-class="transform opacity-100 scale-100"
        leave-to-class="transform opacity-0 scale-95"
      >
        <div
          v-show="showDropdown"
          ref="singleDropdown"
          class="absolute z-50 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-xl overflow-auto custom-scrollbar"
          :class="dropdownPosition === 'top' ? 'bottom-full mb-1' : 'top-full mt-1'"
          :style="{ maxHeight: dropdownMaxHeight + 'px' }"
        >
          <!-- Loading state -->
          <div v-if="loading" class="px-4 py-8 text-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600 mx-auto mb-2" />
            <p class="text-sm text-gray-500 dark:text-gray-400">Searching...</p>
          </div>

          <!-- Empty state -->
          <div
            v-else-if="serverOptions.length === 0"
            class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400"
          >
            <svg class="w-12 h-12 mx-auto mb-2 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p v-if="searchQuery">No results found for "{{ searchQuery }}"</p>
            <p v-else>Type to search...</p>
          </div>

          <!-- Options list -->
          <div
            v-else
            v-for="(option, index) in serverOptions"
            :key="`option-${option.value}-${index}`"
            :class="[
              'px-4 py-2.5 cursor-pointer flex items-center justify-between transition-colors duration-150',
              {
                'bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300': index === selectedIndex,
                'hover:bg-gray-50 dark:hover:bg-gray-700/50 text-gray-900 dark:text-gray-100': index !== selectedIndex,
                'bg-primary-100 dark:bg-primary-900/50': option.value === modelValue && index !== selectedIndex
              }
            ]"
            @mousedown="selectOption(option)"
            @mouseenter="selectedIndex = index"
          >
            <span class="text-sm">{{ option.label }}</span>
            <svg
              v-if="option.value === modelValue"
              class="w-5 h-5 text-primary-600 dark:text-primary-400"
              fill="currentColor"
              viewBox="0 0 24 24"
            >
              <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
            </svg>
          </div>
        </div>
      </transition>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue'
import { resourceService } from '@/services/resourceService'

const props = defineProps({
  modelValue: {
    type: [String, Number, Array],
    default: null
  },
  resourceKey: {
    type: String,
    required: true
  },
  titleAttribute: {
    type: String,
    default: 'name'
  },
  placeholder: {
    type: String,
    default: 'Search...'
  },
  disabled: {
    type: Boolean,
    default: false
  },
  multiple: {
    type: Boolean,
    default: false
  },
  maxSelections: {
    type: Number,
    default: null
  },
  minSearchLength: {
    type: Number,
    default: 0
  }
})

const emit = defineEmits(['update:modelValue'])

// Refs
const singleSearchInput = ref(null)
const multiSearchInput = ref(null)
const singleDropdown = ref(null)
const multiDropdown = ref(null)
const componentRoot = ref(null)

// State
const searchQuery = ref('')
const showDropdown = ref(false)
const selectedIndex = ref(-1)
const dropdownPosition = ref('bottom')
const dropdownMaxHeight = ref(240)
const loading = ref(false)
const serverOptions = ref([])
const selectedItems = ref([])
const searchTimeout = ref(null)

// Computed
const hasReachedMaxSelections = computed(() => {
  if (!props.multiple || !props.maxSelections) return false
  return Array.isArray(props.modelValue) && props.modelValue.length >= props.maxSelections
})

const availableOptions = computed(() => {
  if (!props.multiple) return serverOptions.value
  const selectedValues = new Set(props.modelValue || [])
  return serverOptions.value.filter(option => !selectedValues.has(option.value))
})

const displayValue = computed({
  get() {
    if (props.multiple) return ''
    if (showDropdown.value) return searchQuery.value

    if (props.modelValue) {
      const selectedOption = selectedItems.value.find(item => item.value === props.modelValue)
      return selectedOption ? selectedOption.label : ''
    }
    return ''
  },
  set(value) {
    searchQuery.value = value
  }
})

// Methods
const focusInput = () => {
  if (props.multiple && multiSearchInput.value && !props.disabled && !hasReachedMaxSelections.value) {
    multiSearchInput.value.focus()
  }
}

const fetchOptions = async (query = '') => {
  if (props.minSearchLength > 0 && query.length < props.minSearchLength) {
    serverOptions.value = []
    return
  }

  loading.value = true
  try {
    const response = await resourceService.index(props.resourceKey, {
      search: query,
      perPage: 50
    })

    serverOptions.value = response.data.map(item => ({
      value: item.id,
      label: item[props.titleAttribute] || item.id
    }))
  } catch (error) {
    console.error('Failed to fetch options:', error)
    serverOptions.value = []
  } finally {
    loading.value = false
  }
}

const debouncedSearch = (query) => {
  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value)
  }

  searchTimeout.value = setTimeout(() => {
    fetchOptions(query)
  }, 300)
}

const loadSelectedItems = async () => {
  if (!props.modelValue) return

  const ids = Array.isArray(props.modelValue) ? props.modelValue : [props.modelValue]
  if (ids.length === 0) return

  try {
    const promises = ids.map(id => resourceService.show(props.resourceKey, id))
    const responses = await Promise.all(promises)

    selectedItems.value = responses.map(response => ({
      value: response.data.id,
      label: response.data[props.titleAttribute] || response.data.id
    }))
  } catch (error) {
    console.error('Failed to load selected items:', error)
  }
}

const calculateDropdownPosition = () => {
  const input = props.multiple ? multiSearchInput.value : singleSearchInput.value
  if (!input) return

  const inputRect = input.getBoundingClientRect()
  const viewportHeight = window.innerHeight
  const spaceBelow = viewportHeight - inputRect.bottom
  const spaceAbove = inputRect.top
  const minRequiredSpace = 200
  const maxDropdownHeight = 320

  if (spaceBelow >= minRequiredSpace) {
    dropdownPosition.value = 'bottom'
    dropdownMaxHeight.value = Math.min(maxDropdownHeight, spaceBelow - 20)
  } else if (spaceAbove >= minRequiredSpace) {
    dropdownPosition.value = 'top'
    dropdownMaxHeight.value = Math.min(maxDropdownHeight, spaceAbove - 20)
  } else {
    if (spaceBelow > spaceAbove) {
      dropdownPosition.value = 'bottom'
      dropdownMaxHeight.value = Math.max(150, spaceBelow - 20)
    } else {
      dropdownPosition.value = 'top'
      dropdownMaxHeight.value = Math.max(150, spaceAbove - 20)
    }
  }
}

const handleScroll = () => {
  if (showDropdown.value) {
    calculateDropdownPosition()
  }
}

const handleResize = () => {
  if (showDropdown.value) {
    calculateDropdownPosition()
  }
}

const handleClickOutside = (event) => {
  if (showDropdown.value && componentRoot.value && !componentRoot.value.contains(event.target)) {
    showDropdown.value = false
    selectedIndex.value = -1
    searchQuery.value = ''
  }
}

const selectOption = (option) => {
  emit('update:modelValue', option.value)
  searchQuery.value = ''
  showDropdown.value = false
  selectedIndex.value = -1

  // Add to selected items cache
  if (!selectedItems.value.find(item => item.value === option.value)) {
    selectedItems.value.push(option)
  }
}

const addItem = (option) => {
  if (props.multiple) {
    if (hasReachedMaxSelections.value) return

    const newValue = [...(props.modelValue || []), option.value]
    emit('update:modelValue', newValue)

    // Add to selected items cache
    if (!selectedItems.value.find(item => item.value === option.value)) {
      selectedItems.value.push(option)
    }

    searchQuery.value = ''
    selectedIndex.value = -1

    const willReachMax = props.maxSelections && newValue.length >= props.maxSelections
    if (willReachMax) {
      showDropdown.value = false
    }

    nextTick(() => {
      if (multiSearchInput.value && !willReachMax) {
        multiSearchInput.value.focus()
      }
    })
  }
}

const removeItem = (index) => {
  if (props.multiple && Array.isArray(props.modelValue)) {
    const item = selectedItems.value.filter(i => props.modelValue.includes(i.value))[index]
    const newValue = props.modelValue.filter(v => v !== item.value)
    emit('update:modelValue', newValue)

    if (hasReachedMaxSelections.value) {
      nextTick(() => {
        if (multiSearchInput.value) {
          multiSearchInput.value.focus()
        }
      })
    }
  }
}

const clearSelection = () => {
  emit('update:modelValue', null)
  searchQuery.value = ''
  showDropdown.value = false
  selectedIndex.value = -1
}

const toggleDropdown = () => {
  if (props.disabled || (props.multiple && hasReachedMaxSelections.value)) return
  showDropdown.value = !showDropdown.value
  selectedIndex.value = -1

  if (showDropdown.value) {
    nextTick(() => {
      calculateDropdownPosition()
      if (!searchQuery.value) {
        fetchOptions()
      }

      if (props.multiple && multiSearchInput.value) {
        multiSearchInput.value.focus()
      } else if (!props.multiple && singleSearchInput.value) {
        singleSearchInput.value.focus()
      }
    })
  }
}

const handleFocus = () => {
  if (props.disabled || (props.multiple && hasReachedMaxSelections.value)) return
  showDropdown.value = true
  selectedIndex.value = -1

  nextTick(() => {
    calculateDropdownPosition()
    if (!searchQuery.value) {
      fetchOptions()
    }
  })
}

const handleBlur = () => {
  setTimeout(() => {
    if (!props.multiple) {
      showDropdown.value = false
      selectedIndex.value = -1
      searchQuery.value = ''
    }
  }, 200)
}

const handleKeydown = (event) => {
  const options = props.multiple ? availableOptions.value : serverOptions.value

  if (event.key === 'ArrowDown') {
    event.preventDefault()
    if (!showDropdown.value) {
      showDropdown.value = true
      selectedIndex.value = 0
    } else {
      selectedIndex.value = Math.min(selectedIndex.value + 1, options.length - 1)
    }
  } else if (event.key === 'ArrowUp') {
    event.preventDefault()
    selectedIndex.value = Math.max(selectedIndex.value - 1, 0)
  } else if (event.key === 'Enter') {
    event.preventDefault()
    if (showDropdown.value && selectedIndex.value >= 0 && options[selectedIndex.value]) {
      if (props.multiple) {
        addItem(options[selectedIndex.value])
      } else {
        selectOption(options[selectedIndex.value])
      }
    } else if (!showDropdown.value) {
      showDropdown.value = true
    }
  } else if (event.key === 'Escape') {
    event.preventDefault()
    showDropdown.value = false
    selectedIndex.value = -1
    searchQuery.value = ''
  } else if (event.key === 'Backspace' && props.multiple && !searchQuery.value) {
    const currentSelected = selectedItems.value.filter(i => props.modelValue.includes(i.value))
    if (currentSelected.length > 0) {
      event.preventDefault()
      removeItem(currentSelected.length - 1)
    }
  }
}

// Watch for search query changes
watch(searchQuery, (newValue) => {
  if (newValue || props.minSearchLength === 0) {
    debouncedSearch(newValue)
    showDropdown.value = true
    selectedIndex.value = -1

    nextTick(() => {
      calculateDropdownPosition()
    })
  }
})

// Watch for modelValue changes to load selected items
watch(() => props.modelValue, () => {
  loadSelectedItems()
}, { immediate: true })

// Lifecycle hooks
onMounted(() => {
  window.addEventListener('scroll', handleScroll, true)
  window.addEventListener('resize', handleResize)
  document.addEventListener('click', handleClickOutside, true)

  // Load initial selected items
  loadSelectedItems()
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll, true)
  window.removeEventListener('resize', handleResize)
  document.removeEventListener('click', handleClickOutside, true)

  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value)
  }
})
</script>

<style scoped>
/* Custom scrollbar styling for dropdown */
.custom-scrollbar {
  scrollbar-width: thin;
  scrollbar-color: rgb(203 213 225) transparent;
}

.dark .custom-scrollbar {
  scrollbar-color: rgb(71 85 105) transparent;
}

.custom-scrollbar::-webkit-scrollbar {
  width: 8px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
  border-radius: 8px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: rgb(203 213 225);
  border-radius: 8px;
  border: 2px solid transparent;
  background-clip: padding-box;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background-color: rgb(148 163 184);
}

.dark .custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: rgb(71 85 105);
}

.dark .custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background-color: rgb(100 116 139);
}
</style>
