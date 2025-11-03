<template>
  <transition
    enter-active-class="transition-all duration-200 ease-out"
    enter-from-class="opacity-0 -translate-y-1"
    enter-to-class="opacity-100 translate-y-0"
    leave-active-class="transition-all duration-150 ease-in"
    leave-from-class="opacity-100 translate-y-0"
    leave-to-class="opacity-0 -translate-y-1"
  >
    <p v-if="error" class="form-error">
      {{ error }}
    </p>
  </transition>
</template>

<script setup>
defineProps({
  error: {
    type: String,
    default: null
  }
})
</script>