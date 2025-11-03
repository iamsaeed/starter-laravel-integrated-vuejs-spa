<template>
  <Field
    :id="id"
    :name="name"
    v-slot="{ field, errorMessage, meta }"
    :rules="rules"
  >
    <div class="relative">
      <input
        ref="searchInput"
        v-model="searchQuery"
        type="text"
        :placeholder="placeholder || 'Search options...'"
        :disabled="disabled"
        :class="[
          'form-input',
          {
            'form-input-error': errorMessage,
            'form-input-success': meta.valid && meta.touched,
            'form-input-disabled': disabled
          }
        ]"
        @focus="showDropdown = true"
        @blur="handleBlur"
        @keydown="handleKeydown"
        autocomplete="off"
      />
      
      <!-- Loading indicator -->
      <div v-if="isLoading" class="absolute inset-y-0 right-0 flex items-center pr-3">
        <svg class="animate-spin h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
        </svg>
      </div>

      <!-- Dropdown with virtual scrolling -->
      <div
        v-show="showDropdown && (filteredOptions.length > 0 || isLoading)"
        class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg"
      >
        <!-- Search results info -->
        <div v-if="searchQuery && filteredOptions.length > 0" class="px-3 py-2 text-xs text-gray-500 border-b border-gray-200 dark:border-gray-600">
          {{ filteredOptions.length }} result{{ filteredOptions.length !== 1 ? 's' : '' }} found
        </div>
        
        <!-- No results message -->
        <div v-if="searchQuery && filteredOptions.length === 0 && !isLoading" class="px-3 py-2 text-sm text-gray-500 text-center">
          No options found for "{{ searchQuery }}"
        </div>
        
        <!-- Loading message -->
        <div v-if="isLoading" class="px-3 py-2 text-sm text-gray-500 text-center">
          Loading options...
        </div>
        
        <!-- Virtual scrolled options -->
        <VirtualScroll
          v-if="filteredOptions.length > 0"
          :items="filteredOptions"
          :item-height="40"
          :container-height="Math.min(240, filteredOptions.length * 40)"
          :highlighted-index="selectedIndex"
          @item-click="handleItemClick"
        >
          <template #default="{ item, index, isHighlighted }">
            <div
              :class="[
                'px-4 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 flex items-center',
                {
                  'bg-primary-100 dark:bg-primary-800': isHighlighted,
                  'bg-green-50 dark:bg-green-900/20': isItemSelected(item)
                }
              ]"
              @mousedown.prevent="selectOption(item)"
              @mouseenter="selectedIndex = index"
            >
              <!-- Option icon (if provided) -->
              <div v-if="item.icon" class="flex-shrink-0 mr-3">
                <component :is="item.icon" class="w-4 h-4" />
              </div>
              
              <!-- Option content -->
              <div class="flex-1 min-w-0">
                <div class="font-medium text-gray-900 dark:text-white truncate">
                  {{ item.label }}
                </div>
                <div v-if="item.description" class="text-xs text-gray-500 dark:text-gray-400 truncate">
                  {{ item.description }}
                </div>
              </div>
              
              <!-- Selected indicator -->
              <div v-if="isItemSelected(item)" class="flex-shrink-0 ml-2">
                <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
              </div>
            </div>
          </template>
        </VirtualScroll>
      </div>
    </div>
    
    <!-- Selected items for multiple select -->
    <div v-if="multiple && selectedItems.length > 0" class="mt-2 flex flex-wrap gap-2">
      <span
        v-for="(item, index) in selectedItems"
        :key="`selected-${item.value}-${index}`"
        class="inline-flex items-center px-2 py-1 rounded-md text-sm font-medium bg-primary-100 text-primary-800 dark:bg-primary-800 dark:text-primary-200"
      >
        {{ item.label }}
        <button
          type="button"
          @click="removeItem(index)"
          class="ml-1 text-primary-600 hover:text-primary-800 dark:text-primary-300 dark:hover:text-primary-100 focus:outline-none"
          :aria-label="`Remove ${item.label}`"
        >
          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </span>
    </div>
    
    <FormHelpText v-if="helpText" :text="helpText" />
    <FormError :error="errorMessage" />
  </Field>
</template>

<script setup>
import { ref, computed, watch, nextTick, shallowRef } from 'vue'
import { Field } from 'vee-validate'
import FormError from './FormError.vue'
import FormHelpText from './FormHelpText.vue'
import VirtualScroll from '../common/VirtualScroll.vue'
import { useDebouncedSearch } from '../../utils/debouncedValidation'
import { useMemoizedOptionFilter } from '../../utils/memoization'

const props = defineProps({
  id: {
    type: String,
    required: false,
    default: null
  },
  name: {
    type: String,
    required: true
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
    default: ''
  },
  disabled: {
    type: Boolean,
    default: false
  },
  rules: {
    type: [String, Function, Object],
    default: null
  },
  helpText: {
    type: String,
    default: ''
  },
  multiple: {
    type: Boolean,
    default: false
  },
  async: {
    type: Boolean,
    default: false
  },
  searchFn: {
    type: Function,
    default: null
  },
  minSearchLength: {
    type: Number,
    default: 0
  }
})

const emit = defineEmits(['search', 'select', 'remove'])

// State
const searchQuery = ref('')
const showDropdown = ref(false)
const selectedIndex = ref(-1)
const selectedItems = shallowRef([])
const searchInput = ref(null)
const isLoading = ref(false)

// Memoized option filtering
const { filterOptions } = useMemoizedOptionFilter()

// Async search if enabled
const asyncSearch = props.async && props.searchFn 
  ? useDebouncedSearch(props.searchFn, { 
      delay: 300, 
      minLength: props.minSearchLength 
    })
  : null

// Computed
const filteredOptions = computed(() => {
  if (asyncSearch) {
    return asyncSearch.searchResults.value
  }
  
  if (!searchQuery.value || searchQuery.value.length < props.minSearchLength) {
    return props.options
  }
  
  return filterOptions(props.options, searchQuery.value, ['label', 'description'])
})

const availableOptions = computed(() => {
  if (!props.multiple) return filteredOptions.value
  
  const selectedValues = new Set(selectedItems.value.map(item => item.value))
  return filteredOptions.value.filter(option => !selectedValues.has(option.value))
})

// Methods
const selectOption = (option) => {
  if (props.multiple) {
    addItem(option)
  } else {
    searchQuery.value = option.label
    showDropdown.value = false
    emit('select', option)
  }
  selectedIndex.value = -1
}

const addItem = (option) => {
  if (props.multiple && !selectedItems.value.find(item => item.value === option.value)) {
    selectedItems.value = [...selectedItems.value, option]
    searchQuery.value = ''
    emit('select', option)
  }
}

const removeItem = (index) => {
  const removedItem = selectedItems.value[index]
  selectedItems.value = selectedItems.value.filter((_, i) => i !== index)
  emit('remove', removedItem)
}

const isItemSelected = (item) => {
  if (props.multiple) {
    return selectedItems.value.some(selected => selected.value === item.value)
  }
  return false
}

const handleItemClick = ({ item, index }) => {
  selectedIndex.value = index
  selectOption(item)
}

const handleBlur = () => {
  setTimeout(() => {
    showDropdown.value = false
    selectedIndex.value = -1
  }, 150)
}

const handleKeydown = (event) => {
  const options = availableOptions.value
  
  switch (event.key) {
    case 'ArrowDown':
      event.preventDefault()
      selectedIndex.value = Math.min(selectedIndex.value + 1, options.length - 1)
      break
    case 'ArrowUp':
      event.preventDefault()
      selectedIndex.value = Math.max(selectedIndex.value - 1, -1)
      break
    case 'Enter':
      event.preventDefault()
      if (selectedIndex.value >= 0 && options[selectedIndex.value]) {
        selectOption(options[selectedIndex.value])
      }
      break
    case 'Escape':
      showDropdown.value = false
      selectedIndex.value = -1
      searchInput.value?.blur()
      break
  }
}

// Watch for async search
if (asyncSearch) {
  watch(() => asyncSearch.searchQuery, (query) => {
    searchQuery.value = query
  })
  
  watch(() => asyncSearch.isSearching, (loading) => {
    isLoading.value = loading
  })
  
  watch(searchQuery, (query) => {
    if (asyncSearch) {
      asyncSearch.searchQuery.value = query
    }
  })
}

// Watch for dropdown visibility
watch(showDropdown, (visible) => {
  if (visible) {
    nextTick(() => {
      selectedIndex.value = -1
    })
  }
})
</script>