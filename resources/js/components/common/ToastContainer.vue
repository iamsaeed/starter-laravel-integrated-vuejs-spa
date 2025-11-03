<template>
  <div
    :class="containerClasses"
    aria-live="polite"
    aria-atomic="false"
  >
    <TransitionGroup
      name="toast"
      tag="div"
      class="space-y-3"
    >
      <Toast
        v-for="toast in toasts"
        :key="toast.id"
        v-bind="toast"
        @close="handleClose"
      />
    </TransitionGroup>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { storeToRefs } from 'pinia'
import { useToastStore } from '@/stores/toast'
import Toast from './Toast.vue'

const props = defineProps({
  position: {
    type: String,
    default: 'top-right',
    validator: (val) => [
      'top-right',
      'top-left',
      'bottom-right',
      'bottom-left',
      'top-center',
      'bottom-center'
    ].includes(val)
  }
})

const toastStore = useToastStore()
const { toasts } = storeToRefs(toastStore)

const containerClasses = computed(() => {
  const positions = {
    'top-right': 'fixed top-4 right-4 z-[9999] pointer-events-none',
    'top-left': 'fixed top-4 left-4 z-[9999] pointer-events-none',
    'bottom-right': 'fixed bottom-4 right-4 z-[9999] pointer-events-none',
    'bottom-left': 'fixed bottom-4 left-4 z-[9999] pointer-events-none',
    'top-center': 'fixed top-4 left-1/2 -translate-x-1/2 z-[9999] pointer-events-none',
    'bottom-center': 'fixed bottom-4 left-1/2 -translate-x-1/2 z-[9999] pointer-events-none'
  }
  return positions[props.position] || positions['top-right']
})

function handleClose(id) {
  toastStore.removeToast(id)
}
</script>
