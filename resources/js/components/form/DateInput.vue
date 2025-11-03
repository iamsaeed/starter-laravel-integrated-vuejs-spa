<template>
  <Field
    :id="id"
    :name="name"
    v-slot="{ field, errorMessage, meta }"
    :rules="rules"
  >
    <div class="relative">
      <input
        ref="dateInput"
        v-bind="field"
        type="date"
        :min="minDate"
        :max="maxDate"
        :disabled="disabled"
        :class="[
          'form-input',
          {
            'form-input-error': errorMessage,
            'form-input-success': meta.valid && meta.touched,
            'form-input-disabled': disabled
          }
        ]"
        @change="handleDateChange"
      />
      
      <!-- Custom calendar icon for better UX -->
      <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
      </div>
    </div>
    
    <!-- Date format hint -->
    <div v-if="showFormat && format" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
      Format: {{ format }}
    </div>
    
    <FormHelpText v-if="helpText" :text="helpText" />
    <FormError :error="errorMessage" />
  </Field>
</template>

<script setup>
import { ref, computed } from 'vue'
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
  minDate: {
    type: String,
    default: null
  },
  maxDate: {
    type: String,
    default: null
  },
  format: {
    type: String,
    default: 'YYYY-MM-DD'
  },
  showFormat: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['dateSelected'])

// State
const dateInput = ref(null)

// Methods
const handleDateChange = (event) => {
  const selectedDate = event.target.value
  emit('dateSelected', selectedDate)
}

// Computed
const todayDate = computed(() => {
  return new Date().toISOString().split('T')[0]
})
</script>