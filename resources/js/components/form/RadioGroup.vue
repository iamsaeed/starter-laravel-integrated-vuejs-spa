<template>
  <Field
    :id="id"
    :name="name"
    v-slot="{ field, errorMessage, meta }"
    :rules="rules"
  >
    <div class="space-y-2">
      <div 
        :class="[
          'space-y-3',
          {
            'flex flex-wrap gap-6': inline,
            'space-y-0': inline
          }
        ]"
      >
        <label
          v-for="option in options"
          :key="option.value"
          :class="[
            'flex items-start cursor-pointer group',
            {
              'opacity-50 cursor-not-allowed': disabled
            }
          ]"
        >
          <input
            :id="`${name}-${option.value}`"
            v-bind="field"
            type="radio"
            :value="option.value"
            :disabled="disabled"
            :class="[
              'form-radio mt-0.5',
              {
                'border-red-500 focus:ring-red-500': errorMessage,
                'border-green-500 focus:ring-green-500': meta.valid && meta.touched && !errorMessage
              }
            ]"
          />
          <div class="ml-3 flex-1">
            <span 
              :class="[
                'text-sm font-medium',
                {
                  'text-gray-900 dark:text-white': !disabled,
                  'text-gray-500 dark:text-gray-400': disabled
                }
              ]"
            >
              {{ option.label }}
            </span>
            <p 
              v-if="option.description"
              :class="[
                'text-sm mt-1',
                {
                  'text-gray-500 dark:text-gray-400': !disabled,
                  'text-gray-400 dark:text-gray-500': disabled
                }
              ]"
            >
              {{ option.description }}
            </p>
          </div>
        </label>
      </div>
    </div>
    
    <FormHelpText v-if="helpText" :text="helpText" />
    <FormError :error="errorMessage" />
  </Field>
</template>

<script setup>
import { Field } from 'vee-validate'
import FormError from './FormError.vue'
import FormHelpText from './FormHelpText.vue'

defineProps({
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
  inline: {
    type: Boolean,
    default: false
  }
})
</script>