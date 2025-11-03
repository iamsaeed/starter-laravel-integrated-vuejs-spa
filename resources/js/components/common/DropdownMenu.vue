<template>
  <div ref="dropdownRef" class="relative inline-block">
    <!-- Trigger Slot -->
    <div @click="toggleDropdown" class="cursor-pointer">
      <slot name="trigger" :is-open="isOpen">
        <!-- Default trigger button if no slot provided -->
        <button
          type="button"
          class="btn-ghost"
        >
          <Icon :name="triggerIcon" class="w-5 h-5" />
        </button>
      </slot>
    </div>

    <!-- Dropdown Menu with Teleport -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition ease-out duration-100"
        enter-from-class="transform opacity-0 scale-95"
        enter-to-class="transform opacity-100 scale-100"
        leave-active-class="transition ease-in duration-75"
        leave-from-class="transform opacity-100 scale-100"
        leave-to-class="transform opacity-0 scale-95"
      >
        <div
          v-show="isOpen"
          ref="dropdownMenuRef"
          :class="[
            'fixed bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg',
            widthClass
          ]"
          :style="dropdownStyle"
        >
        <!-- Search bar (if searchable) -->
        <div
          v-if="searchable"
          class="p-2 border-b border-gray-200 dark:border-gray-600"
        >
          <input
            ref="searchInputRef"
            v-model="searchQuery"
            type="text"
            :placeholder="searchPlaceholder"
            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            @keydown.stop
          />
        </div>

        <!-- Options List -->
        <div :class="['overflow-auto', maxHeightClass]">
          <div
            v-if="filteredOptions.length === 0"
            class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 text-center"
          >
            {{ emptyMessage }}
          </div>

          <div
            v-for="(option, index) in filteredOptions"
            :key="getOptionKey(option, index)"
            :class="[
              'cursor-pointer transition-colors duration-150 flex items-center justify-between',
              sizeClasses.padding,
              sizeClasses.text,
              {
                'hover:bg-gray-100 dark:hover:bg-gray-600': !isOptionSelected(option),
                'bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300': isOptionSelected(option),
                'border-b border-gray-100 dark:border-gray-600': index < filteredOptions.length - 1
              }
            ]"
            @click="handleOptionClick(option)"
          >
            <div :class="['flex items-center flex-1', sizeClasses.spacing]">
              <!-- Custom option content slot -->
              <slot name="option" :option="option">
                <!-- Icon (if provided) -->
                <Icon
                  v-if="option.icon"
                  :name="option.icon"
                  :class="[sizeClasses.icon, 'flex-shrink-0', option.iconClass]"
                />

                <!-- Color indicator (if provided) -->
                <span
                  v-if="option.color"
                  :class="['w-3 h-3 rounded-full flex-shrink-0', option.color]"
                ></span>

                <!-- Label -->
                <span class="truncate">{{ option.label }}</span>
              </slot>
            </div>

            <!-- Checkmark for selected items (multi-select) -->
            <Icon
              v-if="multiple && isOptionSelected(option)"
              name="check"
              :class="[sizeClasses.icon, 'text-primary-600 dark:text-primary-400 flex-shrink-0']"
            />
          </div>
        </div>

        <!-- Footer slot (optional) -->
        <div v-if="$slots.footer" class="border-t border-gray-200 dark:border-gray-600 p-2">
          <slot name="footer" :selected="selectedValues" :close="closeDropdown"></slot>
        </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import Icon from './Icon.vue'

const props = defineProps({
  modelValue: {
    type: [String, Number, Array, Object, null],
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
  multiple: {
    type: Boolean,
    default: false
  },
  searchable: {
    type: Boolean,
    default: false
  },
  searchPlaceholder: {
    type: String,
    default: 'Search...'
  },
  emptyMessage: {
    type: String,
    default: 'No options available'
  },
  closeOnSelect: {
    type: Boolean,
    default: true
  },
  width: {
    type: String,
    default: 'w-56',
    validator: (value) => {
      return ['w-48', 'w-56', 'w-64', 'w-72', 'w-80', 'w-96', 'auto'].includes(value)
    }
  },
  maxHeight: {
    type: String,
    default: 'max-h-60',
    validator: (value) => {
      return ['max-h-40', 'max-h-48', 'max-h-60', 'max-h-72', 'max-h-80', 'max-h-96'].includes(value)
    }
  },
  size: {
    type: String,
    default: 'medium',
    validator: (value) => {
      return ['small', 'medium', 'large'].includes(value)
    }
  },
  position: {
    type: String,
    default: 'left',
    validator: (value) => {
      return ['left', 'right'].includes(value)
    }
  },
  triggerIcon: {
    type: String,
    default: 'chevron-down'
  }
})

const emit = defineEmits(['update:modelValue', 'change'])

// Refs
const dropdownRef = ref(null)
const dropdownMenuRef = ref(null)
const searchInputRef = ref(null)
const isOpen = ref(false)
const searchQuery = ref('')
const dropdownPosition = ref({ top: 0, left: 0 })

// Computed
const widthClass = computed(() => props.width === 'auto' ? 'min-w-[12rem]' : props.width)
const maxHeightClass = computed(() => props.maxHeight)

const sizeClasses = computed(() => {
  const sizes = {
    small: {
      padding: 'px-3 py-1.5',
      text: 'text-xs',
      spacing: 'gap-1.5',
      icon: 'w-3.5 h-3.5'
    },
    medium: {
      padding: 'px-4 py-2.5',
      text: 'text-sm',
      spacing: 'gap-2',
      icon: 'w-4 h-4'
    },
    large: {
      padding: 'px-5 py-3',
      text: 'text-base',
      spacing: 'gap-2.5',
      icon: 'w-5 h-5'
    }
  }
  return sizes[props.size]
})

const dropdownStyle = computed(() => ({
  top: `${dropdownPosition.value.top}px`,
  left: `${dropdownPosition.value.left}px`,
  zIndex: 9999
}))

const selectedValues = computed(() => {
  if (props.multiple) {
    return Array.isArray(props.modelValue) ? props.modelValue : []
  }
  return props.modelValue
})

const filteredOptions = computed(() => {
  if (!props.searchable || !searchQuery.value) {
    return props.options
  }

  const query = searchQuery.value.toLowerCase()
  return props.options.filter(option =>
    option.label.toLowerCase().includes(query)
  )
})

// Methods
const updatePosition = () => {
  if (!dropdownRef.value) return

  const rect = dropdownRef.value.getBoundingClientRect()
  const scrollTop = window.pageYOffset || document.documentElement.scrollTop
  const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft

  dropdownPosition.value = {
    top: rect.bottom + scrollTop + 8,
    left: props.position === 'right'
      ? rect.right + scrollLeft - 192 // 192px = w-48
      : rect.left + scrollLeft
  }
}

const toggleDropdown = () => {
  isOpen.value = !isOpen.value
  if (isOpen.value) {
    setTimeout(() => {
      updatePosition()
      if (props.searchable) {
        searchInputRef.value?.focus()
      }
    }, 10)
  }
}

const closeDropdown = () => {
  isOpen.value = false
  searchQuery.value = ''
}

const handleOptionClick = (option) => {
  if (props.multiple) {
    handleMultiSelect(option)
  } else {
    handleSingleSelect(option)
  }

  emit('change', props.modelValue)
}

const handleSingleSelect = (option) => {
  emit('update:modelValue', option.value)

  if (props.closeOnSelect) {
    closeDropdown()
  }
}

const handleMultiSelect = (option) => {
  const currentValues = Array.isArray(props.modelValue) ? [...props.modelValue] : []
  const index = currentValues.indexOf(option.value)

  if (index > -1) {
    currentValues.splice(index, 1)
  } else {
    currentValues.push(option.value)
  }

  emit('update:modelValue', currentValues)

  if (!props.closeOnSelect) {
    // Keep dropdown open for multi-select
    return
  }

  // Only close if closeOnSelect is true
  if (props.closeOnSelect) {
    closeDropdown()
  }
}

const isOptionSelected = (option) => {
  if (props.multiple) {
    const values = Array.isArray(props.modelValue) ? props.modelValue : []
    return values.includes(option.value)
  }
  return props.modelValue === option.value
}

const getOptionKey = (option, index) => {
  return `option-${option.value}-${index}`
}

// Click outside handler
const handleClickOutside = (event) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    // Also check if click is inside the teleported dropdown menu
    if (dropdownMenuRef.value && !dropdownMenuRef.value.contains(event.target)) {
      closeDropdown()
    }
  }
}

// Lifecycle
onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  window.addEventListener('scroll', updatePosition, true)
  window.addEventListener('resize', updatePosition)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
  window.removeEventListener('scroll', updatePosition, true)
  window.removeEventListener('resize', updatePosition)
})

// Watch for searchQuery changes
watch(searchQuery, () => {
  // Reset search when dropdown closes
  if (!isOpen.value) {
    searchQuery.value = ''
  }
})

// Expose methods for parent components
defineExpose({
  closeDropdown,
  toggleDropdown,
  isOpen
})
</script>
