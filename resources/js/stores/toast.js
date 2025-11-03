import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useToastStore = defineStore('toast', () => {
  const toasts = ref([])
  const maxToasts = ref(3)
  const defaultDuration = ref(4000)

  let nextId = 0

  function addToast(toast) {
    const id = `toast-${nextId++}-${Date.now()}`

    const newToast = {
      id,
      type: toast.type || 'info',
      message: toast.message,
      duration: toast.duration ?? defaultDuration.value,
      closable: toast.closable ?? true,
      icon: toast.icon,
      timestamp: Date.now()
    }

    // Remove oldest if at max capacity
    if (toasts.value.length >= maxToasts.value) {
      toasts.value.shift()
    }

    toasts.value.push(newToast)

    // Auto-dismiss if duration is set
    if (newToast.duration > 0) {
      setTimeout(() => {
        removeToast(id)
      }, newToast.duration)
    }

    return id
  }

  function removeToast(id) {
    const index = toasts.value.findIndex(t => t.id === id)
    if (index > -1) {
      toasts.value.splice(index, 1)
    }
  }

  function clearAll() {
    toasts.value = []
  }

  return {
    toasts,
    maxToasts,
    defaultDuration,
    addToast,
    removeToast,
    clearAll
  }
})
