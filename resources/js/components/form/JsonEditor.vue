<template>
  <div class="json-editor">
    <div class="flex items-center justify-between mb-2">
      <label v-if="label" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
        {{ label }}
        <span v-if="required" class="text-red-500">*</span>
      </label>
      <div class="flex items-center gap-2">
        <button
          v-if="showFormatButton"
          type="button"
          @click="formatJson"
          class="text-xs px-2 py-1 bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 rounded hover:bg-primary-200 dark:hover:bg-primary-900/50 transition-colors"
          :disabled="disabled"
        >
          Format
        </button>
        <button
          v-if="showValidateButton"
          type="button"
          @click="validateJson"
          class="text-xs px-2 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded hover:bg-green-200 dark:hover:bg-green-900/50 transition-colors"
          :disabled="disabled"
        >
          Validate
        </button>
      </div>
    </div>

    <div class="relative">
      <textarea
        ref="textareaRef"
        v-model="internalValue"
        :placeholder="placeholder"
        :rows="rows"
        :disabled="disabled"
        :class="[
          'w-full px-3 py-2 font-mono text-sm border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors',
          'bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100',
          'border-gray-300 dark:border-gray-600',
          'disabled:opacity-50 disabled:cursor-not-allowed',
          {
            'border-red-500 dark:border-red-500': hasError || validationError,
            'border-green-500 dark:border-green-500': isValid && !hasError
          }
        ]"
        @blur="handleBlur"
        @input="handleInput"
      />

      <!-- Validation Status Icon -->
      <div v-if="showValidationIcon && !disabled" class="absolute top-2 right-2">
        <Icon
          v-if="isValid && !hasError"
          name="check-circle"
          :size="20"
          class="text-green-500"
        />
        <Icon
          v-else-if="validationError || hasError"
          name="x-circle"
          :size="20"
          class="text-red-500"
        />
      </div>
    </div>

    <!-- Validation Error -->
    <p v-if="validationError" class="mt-1 text-sm text-red-600 dark:text-red-400">
      {{ validationError }}
    </p>

    <!-- Field Error -->
    <p v-else-if="error" class="mt-1 text-sm text-red-600 dark:text-red-400">
      {{ error }}
    </p>

    <!-- Help Text -->
    <p v-if="helpText && !validationError && !error" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
      {{ helpText }}
    </p>

    <!-- JSON Preview (when valid) -->
    <div v-if="showPreview && isValid && parsedValue && !disabled" class="mt-3 p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg border border-gray-200 dark:border-gray-700">
      <div class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">Preview:</div>
      <pre class="text-xs text-gray-700 dark:text-gray-300 overflow-x-auto">{{ formattedPreview }}</pre>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import Icon from '@/components/common/Icon.vue'

const props = defineProps({
  modelValue: {
    type: [String, Array, Object],
    default: null
  },
  label: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: 'Enter valid JSON'
  },
  helpText: {
    type: String,
    default: ''
  },
  error: {
    type: String,
    default: ''
  },
  rows: {
    type: Number,
    default: 6
  },
  required: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  showFormatButton: {
    type: Boolean,
    default: true
  },
  showValidateButton: {
    type: Boolean,
    default: true
  },
  showValidationIcon: {
    type: Boolean,
    default: true
  },
  showPreview: {
    type: Boolean,
    default: false
  },
  // Expected type: 'array', 'object', or null for any
  expectedType: {
    type: String,
    default: null,
    validator: (value) => [null, 'array', 'object'].includes(value)
  },
  // Auto-format on blur
  autoFormat: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['update:modelValue', 'valid', 'invalid'])

const textareaRef = ref(null)
const internalValue = ref('')
const validationError = ref('')
const isValid = ref(false)
const parsedValue = ref(null)

const hasError = computed(() => !!props.error || !!validationError.value)

const formattedPreview = computed(() => {
  if (!parsedValue.value) return ''
  try {
    return JSON.stringify(parsedValue.value, null, 2)
  } catch (e) {
    return ''
  }
})

// Initialize internal value from prop
onMounted(() => {
  initializeValue()
})

watch(() => props.modelValue, (newValue) => {
  if (newValue !== parsedValue.value) {
    initializeValue()
  }
}, { deep: true })

function initializeValue() {
  if (props.modelValue === null || props.modelValue === undefined || props.modelValue === '') {
    internalValue.value = ''
    parsedValue.value = null
    isValid.value = false
    validationError.value = ''
    return
  }

  if (typeof props.modelValue === 'string') {
    internalValue.value = props.modelValue
    validateJsonString(props.modelValue)
  } else {
    try {
      internalValue.value = JSON.stringify(props.modelValue, null, 2)
      parsedValue.value = props.modelValue
      isValid.value = true
      validationError.value = ''
    } catch (e) {
      internalValue.value = ''
      parsedValue.value = null
      isValid.value = false
      validationError.value = 'Invalid JSON data'
    }
  }
}

function validateJsonString(value) {
  if (!value || value.trim() === '') {
    parsedValue.value = null
    isValid.value = !props.required
    validationError.value = props.required ? 'This field is required' : ''
    return false
  }

  try {
    const parsed = JSON.parse(value)
    parsedValue.value = parsed

    // Check expected type
    if (props.expectedType === 'array' && !Array.isArray(parsed)) {
      validationError.value = 'Expected a JSON array'
      isValid.value = false
      emit('invalid', validationError.value)
      return false
    }

    if (props.expectedType === 'object' && (Array.isArray(parsed) || typeof parsed !== 'object')) {
      validationError.value = 'Expected a JSON object'
      isValid.value = false
      emit('invalid', validationError.value)
      return false
    }

    validationError.value = ''
    isValid.value = true
    emit('valid', parsed)
    return true
  } catch (e) {
    parsedValue.value = null
    isValid.value = false
    validationError.value = `Invalid JSON: ${e.message}`
    emit('invalid', validationError.value)
    return false
  }
}

function handleInput() {
  validateJsonString(internalValue.value)
}

function handleBlur() {
  if (props.autoFormat && isValid.value) {
    formatJson()
  }

  // Emit the parsed value or null
  if (isValid.value) {
    emit('update:modelValue', parsedValue.value)
  } else if (!internalValue.value || internalValue.value.trim() === '') {
    emit('update:modelValue', null)
  }
}

function formatJson() {
  if (!internalValue.value || internalValue.value.trim() === '') {
    return
  }

  try {
    const parsed = JSON.parse(internalValue.value)
    internalValue.value = JSON.stringify(parsed, null, 2)
    parsedValue.value = parsed
    isValid.value = true
    validationError.value = ''
    emit('update:modelValue', parsed)
    emit('valid', parsed)
  } catch (e) {
    validationError.value = `Cannot format: ${e.message}`
    isValid.value = false
    emit('invalid', validationError.value)
  }
}

function validateJson() {
  validateJsonString(internalValue.value)
}

// Expose methods for parent component
defineExpose({
  validate: validateJson,
  format: formatJson,
  getValue: () => parsedValue.value,
  isValid: () => isValid.value
})
</script>

<style scoped>
.json-editor textarea {
  resize: vertical;
  min-height: 100px;
}

.json-editor pre {
  white-space: pre-wrap;
  word-wrap: break-word;
}
</style>
