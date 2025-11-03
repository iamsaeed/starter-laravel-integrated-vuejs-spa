<template>
  <Field
    :id="id"
    :name="name"
    v-slot="{ field, errorMessage, meta }"
    :rules="rules"
  >
    <div class="relative">
      <textarea
        ref="textareaRef"
        v-bind="field"
        :placeholder="placeholder"
        :disabled="disabled"
        :rows="autoResize ? 1 : rows"
        :maxlength="maxLength"
        :class="[
          'form-textarea',
          {
            'form-input-error': errorMessage,
            'form-input-success': meta.valid && meta.touched,
            'form-input-disabled': disabled,
            'resize-none': autoResize
          }
        ]"
        @input="handleInput"
      />
      
      <!-- Character counter -->
      <div 
        v-if="maxLength && showCounter"
        class="absolute bottom-2 right-2 text-xs text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-700 px-1 rounded"
      >
        {{ characterCount }}/{{ maxLength }}
      </div>
    </div>
    
    <FormHelpText v-if="helpText" :text="helpText" />
    <FormError :error="errorMessage" />
  </Field>
</template>

<script setup>
import { ref, computed, nextTick, onMounted } from 'vue'
import { Field } from 'vee-validate'
import FormError from './FormError.vue'
import FormHelpText from './FormHelpText.vue'

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
  rows: {
    type: Number,
    default: 4
  },
  maxLength: {
    type: Number,
    default: null
  },
  showCounter: {
    type: Boolean,
    default: true
  },
  autoResize: {
    type: Boolean,
    default: false
  },
  minHeight: {
    type: String,
    default: 'auto'
  },
  maxHeight: {
    type: String,
    default: '200px'
  }
})

// State
const textareaRef = ref(null)
const characterCount = ref(0)

// Computed
const isNearLimit = computed(() => {
  if (!props.maxLength) return false
  return characterCount.value / props.maxLength > 0.8
})

const isAtLimit = computed(() => {
  if (!props.maxLength) return false
  return characterCount.value >= props.maxLength
})

// Methods
const handleInput = (event) => {
  const value = event.target.value
  characterCount.value = value.length
  
  if (props.autoResize) {
    autoResizeTextarea()
  }
}

const autoResizeTextarea = async () => {
  if (!textareaRef.value || !props.autoResize) return
  
  await nextTick()
  
  const textarea = textareaRef.value
  
  // Reset height to auto to get the correct scrollHeight
  textarea.style.height = 'auto'
  
  // Calculate new height
  let newHeight = textarea.scrollHeight
  
  // Apply min height
  if (props.minHeight !== 'auto') {
    const minHeight = parseInt(props.minHeight)
    newHeight = Math.max(newHeight, minHeight)
  }
  
  // Apply max height
  if (props.maxHeight !== 'auto') {
    const maxHeight = parseInt(props.maxHeight)
    newHeight = Math.min(newHeight, maxHeight)
  }
  
  textarea.style.height = `${newHeight}px`
}

// Initialize auto-resize on mount
onMounted(() => {
  if (props.autoResize) {
    autoResizeTextarea()
  }
})
</script>

