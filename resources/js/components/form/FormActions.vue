<template>
  <div 
    :class="[
      'form-actions',
      {
        'justify-start': align === 'left',
        'justify-center': align === 'center',
        'justify-end': align === 'right',
        'justify-between': align === 'between',
        'flex-col space-y-3 space-x-0': stacked,
        'flex-row space-x-3 space-y-0': !stacked
      }
    ]"
  >
    <slot />
  </div>
</template>

<script setup>
defineProps({
  align: {
    type: String,
    default: 'right',
    validator: (value) => ['left', 'center', 'right', 'between'].includes(value)
  },
  stacked: {
    type: Boolean,
    default: false
  }
})
</script>

<style scoped>
.form-actions {
  display: flex;
  align-items: center;
}

/* Responsive stacking on small screens */
@media (max-width: 640px) {
  .form-actions:not(.flex-col) {
    flex-direction: column;
    gap: 0.75rem;
  }
  
  .form-actions:not(.flex-col) > * {
    width: 100%;
  }
}
</style>