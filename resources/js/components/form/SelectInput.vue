<template>
  <Field
    :id="id"
    :name="name"
    v-slot="{ field, errorMessage, meta }"
    :rules="rules"
  >
    <div class="relative">
      <!-- Single Select -->
      <div v-if="!multiple && !searchable" class="relative">
        <select
          v-bind="field"
          :disabled="disabled"
          :class="[
            'form-select',
            {
              'form-input-error': errorMessage,
              'form-input-success': meta.valid && meta.touched,
              'form-input-disabled': disabled
            }
          ]"
        >
          <option value="" disabled>{{ placeholder || 'Select an option' }}</option>
          <option
            v-for="option in options"
            :key="option.value"
            :value="option.value"
          >
            {{ option.label }}
          </option>
        </select>
        <!-- Custom dropdown arrow -->
        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
          <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </div>
      </div>

      <!-- Searchable Select -->
      <div v-else-if="searchable && !multiple" class="relative">
        <input
          ref="searchInput"
          v-model="searchQuery"
          type="text"
          :placeholder="placeholder || 'Search and select...'"
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
        />
        
        <!-- Dropdown with v-show for better performance -->
        <div
          v-show="showDropdown && filteredOptions.length > 0"
          class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg max-h-60 overflow-auto"
        >
          <div
            v-for="(option, index) in filteredOptions"
            :key="`option-${option.value}-${index}`"
            :class="[
              'px-4 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600',
              {
                'bg-primary-100 dark:bg-primary-800': index === selectedIndex
              }
            ]"
            @mousedown="selectOption(option)"
          >
            {{ option.label }}
          </div>
        </div>
      </div>

      <!-- Multiple Select -->
      <div v-else-if="multiple" class="relative">
        <!-- Input container with tags inside -->
        <div
          :class="[
            'form-input min-h-[42px] flex flex-wrap items-center gap-1 py-1 px-2 cursor-text',
            {
              'form-input-error': errorMessage,
              'form-input-success': meta.valid && meta.touched,
              'form-input-disabled': disabled,
              'ring-2 ring-primary-500 border-primary-500': showDropdown
            }
          ]"
          @click="focusInput"
        >
          <!-- Selected tags inside input -->
          <span
            v-for="(item, index) in selectedItems"
            :key="`selected-${item.value}-${index}`"
            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-sm font-medium bg-primary-100 text-primary-800 dark:bg-primary-800 dark:text-primary-200 flex-shrink-0"
          >
            <span class="truncate max-w-[150px]">{{ item.label }}</span>
            <button
              type="button"
              @click.stop="removeItem(index)"
              class="flex-shrink-0 text-primary-600 hover:text-primary-800 dark:text-primary-300 dark:hover:text-primary-100"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </span>

          <!-- Search input inside the container -->
          <input
            ref="multiSearchInput"
            v-model="searchQuery"
            type="text"
            :placeholder="selectedItems.length === 0 ? (placeholder || 'Search and select...') : ''"
            :disabled="disabled"
            class="flex-1 min-w-[120px] border-none outline-none bg-transparent text-sm p-0 m-0 focus:ring-0 disabled:bg-transparent disabled:cursor-not-allowed"
            @focus="showDropdown = true"
            @blur="handleBlur"
            @keydown="handleKeydown"
          />
        </div>

        <!-- Dropdown for multiple select with v-show -->
        <div
          v-show="showDropdown && availableOptions.length > 0"
          class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg max-h-60 overflow-auto"
        >
          <div
            v-for="(option, index) in availableOptions"
            :key="`multi-option-${option.value}-${index}`"
            :class="[
              'px-4 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600',
              {
                'bg-primary-100 dark:bg-primary-800': index === selectedIndex
              }
            ]"
            @mousedown="addItem(option)"
          >
            {{ option.label }}
          </div>
        </div>
      </div>
    </div>
    
    <FormHelpText v-if="helpText" :text="helpText" />
    <FormError :error="errorMessage" />
  </Field>
</template>

<script setup>
import { ref, computed, watch, shallowRef } from 'vue'
import { Field } from 'vee-validate'
import FormError from './FormError.vue'
import FormHelpText from './FormHelpText.vue'
import { memoize, useMemoizedOptionFilter } from '../../utils/memoization'

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
  searchable: {
    type: Boolean,
    default: false
  },
  multiple: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue'])

// Refs
const searchInput = ref(null)
const multiSearchInput = ref(null)

// State
const searchQuery = ref('')
const showDropdown = ref(false)
const selectedIndex = ref(-1)
const selectedItems = shallowRef([]) // Use shallowRef for better performance

// Initialize selectedItems from modelValue
const initializeSelectedItems = () => {
  if (!props.multiple || !props.modelValue) {
    selectedItems.value = []
    return
  }

  const values = Array.isArray(props.modelValue) ? props.modelValue : [props.modelValue]
  selectedItems.value = values
    .map(val => props.options.find(opt => opt.value === val))
    .filter(Boolean)
}

// Memoized option filtering
const { filterOptions } = useMemoizedOptionFilter()

// Memoized filtered options computation
const memoizedFilterOptions = memoize(
  (options, query, searchable) => {
    if (!searchable || !query) return options
    return filterOptions(options, query, ['label'])
  },
  { maxCacheSize: 50, ttl: 30000 }
)

// Computed with performance optimizations
const filteredOptions = computed(() => {
  return memoizedFilterOptions(props.options, searchQuery.value, props.searchable)
})

const availableOptions = computed(() => {
  if (!props.multiple) return filteredOptions.value

  const selectedValues = new Set(selectedItems.value.map(item => item.value))
  return filteredOptions.value.filter(option => !selectedValues.has(option.value))
})

// Methods
const selectOption = (option) => {
  if (props.searchable && !props.multiple) {
    searchQuery.value = option.label
  }
  showDropdown.value = false
  selectedIndex.value = -1
}

const addItem = (option) => {
  if (props.multiple) {
    // Create new array for better reactivity
    selectedItems.value = [...selectedItems.value, option]
    // Emit the values array to parent
    emit('update:modelValue', selectedItems.value.map(item => item.value))
    searchQuery.value = ''
    showDropdown.value = false
    selectedIndex.value = -1
  }
}

const removeItem = (index) => {
  if (typeof index === 'number') {
    // Direct index removal for better performance
    selectedItems.value = selectedItems.value.filter((_, i) => i !== index)
  } else {
    // Fallback for object-based removal
    const item = index
    selectedItems.value = selectedItems.value.filter(i => i.value !== item.value)
  }
  // Emit the updated values array to parent
  emit('update:modelValue', selectedItems.value.map(item => item.value))
}

const handleBlur = () => {
  setTimeout(() => {
    showDropdown.value = false
    selectedIndex.value = -1
  }, 150)
}

const focusInput = () => {
  if (props.multiple && multiSearchInput.value) {
    multiSearchInput.value.focus()
  } else if (searchInput.value) {
    searchInput.value.focus()
  }
}

const handleKeydown = (event) => {
  const options = props.multiple ? availableOptions.value : filteredOptions.value

  if (event.key === 'ArrowDown') {
    event.preventDefault()
    selectedIndex.value = Math.min(selectedIndex.value + 1, options.length - 1)
  } else if (event.key === 'ArrowUp') {
    event.preventDefault()
    selectedIndex.value = Math.max(selectedIndex.value - 1, -1)
  } else if (event.key === 'Enter') {
    event.preventDefault()
    if (selectedIndex.value >= 0 && options[selectedIndex.value]) {
      if (props.multiple) {
        addItem(options[selectedIndex.value])
      } else {
        selectOption(options[selectedIndex.value])
      }
    }
  } else if (event.key === 'Escape') {
    showDropdown.value = false
    selectedIndex.value = -1
  } else if (event.key === 'Backspace' && props.multiple && searchQuery.value === '' && selectedItems.value.length > 0) {
    // Remove last tag when backspace is pressed with empty search
    event.preventDefault()
    removeItem(selectedItems.value.length - 1)
  }
}

// Watch for changes to update dropdown visibility
watch(searchQuery, () => {
  if (props.searchable) {
    showDropdown.value = true
    selectedIndex.value = -1
  }
})

// Watch for modelValue changes to update selectedItems
watch(() => props.modelValue, () => {
  initializeSelectedItems()
}, { immediate: true })

// Watch for options changes to re-initialize selectedItems
watch(() => props.options, () => {
  initializeSelectedItems()
})
</script>