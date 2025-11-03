import { useToastStore } from '@/stores/toast'

export function useToast() {
  const store = useToastStore()

  function show(message, options = {}) {
    return store.addToast({
      message,
      ...options
    })
  }

  function success(message, options = {}) {
    return show(message, { type: 'success', ...options })
  }

  function error(message, options = {}) {
    return show(message, { type: 'error', ...options })
  }

  function warning(message, options = {}) {
    return show(message, { type: 'warning', ...options })
  }

  function info(message, options = {}) {
    return show(message, { type: 'info', ...options })
  }

  function dismiss(id) {
    store.removeToast(id)
  }

  function clear() {
    store.clearAll()
  }

  return {
    show,
    success,
    error,
    warning,
    info,
    dismiss,
    clear
  }
}
