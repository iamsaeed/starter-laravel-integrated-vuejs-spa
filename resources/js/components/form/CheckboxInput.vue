<template>
  <Field
    :id="id"
    :name="name"
    v-slot="{ field, errorMessage }"
    :type="'checkbox'"
    :value="value"
    :unchecked-value="uncheckedValue"
    :rules="rules"
  >
    <div class="flex items-start">
      <input
        v-bind="field"
        type="checkbox"
        :id="id"
        :value="value"
        :disabled="disabled"
        :class="['form-checkbox mt-0.5', { 'form-input-disabled': disabled }]"
      />
      <label v-if="label || $slots.label" :for="id" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
        <slot name="label">{{ label }}</slot>
      </label>
    </div>
    <FormError :error="errorMessage" />
  </Field>
</template>

<script setup>
import { Field } from 'vee-validate'
import FormError from './FormError.vue'

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
  label: {
    type: String,
    default: ''
  },
  value: {
    type: [String, Number, Boolean],
    default: true
  },
  uncheckedValue: {
    type: [String, Number, Boolean],
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  rules: {
    type: [String, Object, Function],
    default: null
  }
})
</script>