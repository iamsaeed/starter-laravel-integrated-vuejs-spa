<template>
  <Field
    :id="id"
    :name="name"
    v-slot="{ field, errorMessage, meta }"
    :rules="rules"
  >
    <input
      v-bind="field"
      :type="type"
      :placeholder="placeholder"
      :disabled="disabled"
      :class="[
        'form-input',
        {
          'form-input-error': errorMessage,
          'form-input-success': meta.valid && meta.touched,
          'form-input-disabled': disabled
        }
      ]"
    />
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
  type: {
    type: String,
    default: 'text'
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
    type: [String, Object, Function],
    default: null
  }
})
</script>