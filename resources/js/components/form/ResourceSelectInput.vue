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
        <!-- Search icon -->
        <div class="flex items-center pointer-events-none">
          <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>

        <!-- Selected tags -->
        <span
          v-for="(item, index) in selectedItems"
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
          :placeholder="selectedItems.length === 0 ? (hasReachedMaxSelections ? `Maximum ${maxSelections} items selected` : placeholder) : ''"
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
          <!-- Empty state -->
          <div
            v-if="availableOptions.length === 0"
            class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400"
          >
            <svg class="w-12 h-12 mx-auto mb-2 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
            <p v-if="hasReachedMaxSelections">Maximum selections reached</p>
            <p v-else>{{ searchQuery ? 'No results found' : 'All items selected' }}</p>
          </div>

          <!-- Options list -->
          <div
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
          :readonly="!searchable"
          class="w-full pl-10 pr-10 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:focus:border-primary-500 transition-all duration-200"
          :class="{
            'opacity-50 cursor-not-allowed': disabled,
            'cursor-pointer': !searchable,
            'border-primary-500 dark:border-primary-500': showDropdown
          }"
          @focus="handleFocus"
          @blur="handleBlur"
          @click="!searchable && toggleDropdown()"
          @keydown="handleKeydown"
        />
        <!-- Icon -->
        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
          <svg
            v-if="searchable"
            class="w-5 h-5 text-gray-400"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <svg
            v-else
            class="w-5 h-5 text-gray-400"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </div>
        <!-- Clear button (only show when has value) -->
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
          <!-- Empty state -->
          <div
            v-if="filteredOptions.length === 0"
            class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400"
          >
            <svg class="w-12 h-12 mx-auto mb-2 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p>No results found</p>
          </div>

          <!-- Options list -->
          <div
            v-for="(option, index) in filteredOptions"
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

const props = defineProps({
  modelValue: {
    type: [String, Number, Array],
    default: null
  },
  options: {
    type: Array,
    required: true,
    validator: (options) => {
      return options.every(option =>
        typeof option === 'object' &&
        'value' in option &&
        'label' in option
      )
    }
  },
  placeholder: {
    type: String,
    default: 'Select an option'
  },
  disabled: {
    type: Boolean,
    default: false
  },
  searchable: {
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
const internalDisplayValue = ref('')
const dropdownPosition = ref('bottom') // 'top' or 'bottom'
const dropdownMaxHeight = ref(240) // Default max height in pixels

// Computed
const selectedItems = computed(() => {
  if (!props.multiple || !Array.isArray(props.modelValue)) return []
  return props.options.filter(opt => props.modelValue.includes(opt.value))
})

const displayValue = computed({
  get() {
    if (props.multiple) return ''

    // When searchable and dropdown is open, show search query
    if (props.searchable && showDropdown.value) {
      return searchQuery.value
    }

    // Otherwise show selected option label
    if (props.modelValue) {
      const selectedOption = props.options.find(opt => opt.value === props.modelValue)
      return selectedOption ? selectedOption.label : ''
    }

    return ''
  },
  set(value) {
    if (props.searchable) {
      searchQuery.value = value
    }
  }
})

const filteredOptions = computed(() => {
  if (!searchQuery.value) return props.options
  const query = searchQuery.value.toLowerCase()
  return props.options.filter(option =>
    option.label.toLowerCase().includes(query)
  )
})

const availableOptions = computed(() => {
  if (!props.multiple) return filteredOptions.value
  const selectedValues = new Set(props.modelValue || [])
  return filteredOptions.value.filter(option => !selectedValues.has(option.value))
})

const hasReachedMaxSelections = computed(() => {
  if (!props.multiple || !props.maxSelections) return false
  return Array.isArray(props.modelValue) && props.modelValue.length >= props.maxSelections
})

// Methods
const calculateDropdownPosition = () => {
  const input = props.multiple ? multiSearchInput.value : singleSearchInput.value
  if (!input) return

  const inputRect = input.getBoundingClientRect()
  const viewportHeight = window.innerHeight

  const spaceBelow = viewportHeight - inputRect.bottom
  const spaceAbove = inputRect.top

  // Minimum required space for dropdown (consider padding and a few items)
  const minRequiredSpace = 200
  const maxDropdownHeight = 320 // Maximum desired height

  // Determine position based on available space
  if (spaceBelow >= minRequiredSpace) {
    // Enough space below
    dropdownPosition.value = 'bottom'
    dropdownMaxHeight.value = Math.min(maxDropdownHeight, spaceBelow - 20)
  } else if (spaceAbove >= minRequiredSpace) {
    // Not enough space below, but enough above
    dropdownPosition.value = 'top'
    dropdownMaxHeight.value = Math.min(maxDropdownHeight, spaceAbove - 20)
  } else {
    // Limited space on both sides, use the larger space
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

const focusInput = () => {
  if (props.multiple && multiSearchInput.value && !props.disabled && !hasReachedMaxSelections.value) {
    multiSearchInput.value.focus()
  }
}

const selectOption = (option) => {
  emit('update:modelValue', option.value)
  searchQuery.value = ''
  showDropdown.value = false
  selectedIndex.value = -1
}

const addItem = (option) => {
  if (props.multiple) {
    // Check if max selections reached
    if (hasReachedMaxSelections.value) {
      return
    }

    const newValue = [...(props.modelValue || []), option.value]
    emit('update:modelValue', newValue)
    searchQuery.value = ''
    selectedIndex.value = -1

    // Close dropdown if max selections reached after adding this item
    const willReachMax = props.maxSelections && newValue.length >= props.maxSelections
    if (willReachMax) {
      showDropdown.value = false
    }

    // Keep focus on input
    nextTick(() => {
      if (multiSearchInput.value && !willReachMax) {
        multiSearchInput.value.focus()
      }
    })
  }
}

const removeItem = (index) => {
  if (props.multiple && Array.isArray(props.modelValue)) {
    const item = selectedItems.value[index]
    const newValue = props.modelValue.filter(v => v !== item.value)
    emit('update:modelValue', newValue)

    // If we were at max and removed an item, focus the input to allow more selections
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
  })
}

const handleBlur = () => {
  setTimeout(() => {
    // For multiple select, don't close dropdown on blur
    // This allows users to keep selecting items
    if (!props.multiple) {
      showDropdown.value = false
      selectedIndex.value = -1

      // Reset search query for single select when losing focus
      if (props.searchable) {
        searchQuery.value = ''
      }
    }
  }, 200)
}

const handleKeydown = (event) => {
  const options = props.multiple ? availableOptions.value : filteredOptions.value

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
  } else if (event.key === 'Backspace' && props.multiple && !searchQuery.value && selectedItems.value.length > 0) {
    // Remove last item when backspace is pressed with empty search
    event.preventDefault()
    removeItem(selectedItems.value.length - 1)
  }
}

// Watch for options changes to reset selected index
watch(() => props.options, () => {
  selectedIndex.value = -1
})

// Watch for searchQuery changes to show dropdown
watch(searchQuery, (newValue) => {
  if (newValue && props.searchable) {
    showDropdown.value = true
    selectedIndex.value = -1

    nextTick(() => {
      calculateDropdownPosition()
    })
  }
})

// Lifecycle hooks
onMounted(() => {
  // Add scroll and resize listeners to recalculate position
  window.addEventListener('scroll', handleScroll, true)
  window.addEventListener('resize', handleResize)
  // Add click outside listener
  document.addEventListener('click', handleClickOutside, true)
})

onUnmounted(() => {
  // Clean up listeners
  window.removeEventListener('scroll', handleScroll, true)
  window.removeEventListener('resize', handleResize)
  document.removeEventListener('click', handleClickOutside, true)
})
</script>

<style scoped>
/* Custom scrollbar styling for dropdown */
.custom-scrollbar {
  scrollbar-width: thin;
  scrollbar-color: rgb(203 213 225) transparent; /* Light mode: slate-300 */
}

.dark .custom-scrollbar {
  scrollbar-color: rgb(71 85 105) transparent; /* Dark mode: slate-600 */
}

/* Webkit browsers (Chrome, Safari, Edge) */
.custom-scrollbar::-webkit-scrollbar {
  width: 8px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
  border-radius: 8px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: rgb(203 213 225); /* Light mode: slate-300 */
  border-radius: 8px;
  border: 2px solid transparent;
  background-clip: padding-box;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background-color: rgb(148 163 184); /* Light mode hover: slate-400 */
}

/* Dark mode scrollbar for webkit */
.dark .custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: rgb(71 85 105); /* Dark mode: slate-600 */
}

.dark .custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background-color: rgb(100 116 139); /* Dark mode hover: slate-500 */
}
</style>
