import { ref, computed } from 'vue'

/**
 * Composable for managing dropdown state and behavior
 * @param {Object} options - Configuration options
 * @param {Boolean} options.closeOnSelect - Whether to close dropdown after selection
 * @param {Boolean} options.multiple - Whether to allow multiple selections
 * @returns {Object} Dropdown state and methods
 */
export function useDropdown(options = {}) {
  const {
    closeOnSelect = true,
    multiple = false
  } = options

  const isOpen = ref(false)
  const searchQuery = ref('')
  const selectedValue = ref(multiple ? [] : null)

  const open = () => {
    isOpen.value = true
  }

  const close = () => {
    isOpen.value = false
    searchQuery.value = ''
  }

  const toggle = () => {
    isOpen.value = !isOpen.value
  }

  const select = (value) => {
    if (multiple) {
      const values = Array.isArray(selectedValue.value) ? [...selectedValue.value] : []
      const index = values.indexOf(value)

      if (index > -1) {
        values.splice(index, 1)
      } else {
        values.push(value)
      }

      selectedValue.value = values
    } else {
      selectedValue.value = value

      if (closeOnSelect) {
        close()
      }
    }
  }

  const isSelected = (value) => {
    if (multiple) {
      const values = Array.isArray(selectedValue.value) ? selectedValue.value : []
      return values.includes(value)
    }
    return selectedValue.value === value
  }

  const clear = () => {
    selectedValue.value = multiple ? [] : null
  }

  const selectedCount = computed(() => {
    if (multiple && Array.isArray(selectedValue.value)) {
      return selectedValue.value.length
    }
    return selectedValue.value ? 1 : 0
  })

  return {
    isOpen,
    searchQuery,
    selectedValue,
    selectedCount,
    open,
    close,
    toggle,
    select,
    isSelected,
    clear
  }
}
