import { useDialogStore } from '@/stores/dialog'

export function useDialog() {
  const store = useDialogStore()

  function confirm(message, options = {}) {
    return store.showDialog({
      message,
      type: 'info',
      ...options,
    })
  }

  function confirmSuccess(message, options = {}) {
    return store.showDialog({
      message,
      type: 'success',
      confirmLabel: 'OK',
      ...options,
    })
  }

  function confirmDanger(message, options = {}) {
    return store.showDialog({
      message,
      type: 'danger',
      confirmLabel: 'Delete',
      ...options,
    })
  }

  function confirmError(message, options = {}) {
    return store.showDialog({
      message,
      type: 'error',
      confirmLabel: 'OK',
      ...options,
    })
  }

  function confirmInfo(message, options = {}) {
    return store.showDialog({
      message,
      type: 'info',
      confirmLabel: 'OK',
      ...options,
    })
  }

  function confirmWarning(message, options = {}) {
    return store.showDialog({
      message,
      type: 'warning',
      confirmLabel: 'OK',
      ...options,
    })
  }

  function prompt(message, options = {}) {
    return store.showDialog({
      message,
      type: 'info',
      confirmLabel: 'Submit',
      ...options,
    })
  }

  function close(id) {
    store.closeDialog(id)
  }

  function clearAll() {
    store.clearAll()
  }

  return {
    confirm,
    confirmSuccess,
    confirmDanger,
    confirmError,
    confirmInfo,
    confirmWarning,
    prompt,
    close,
    clearAll,
  }
}
