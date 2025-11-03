<template>
  <label
    :for="forId"
    :class="['form-label', { 'form-label-required': required }]"
  >
    <slot />
  </label>
</template>

<script setup>
defineProps({
  forId: {
    type: String,
    required: false,
    default: null
  },
  required: {
    type: Boolean,
    default: false
  }
})
</script>