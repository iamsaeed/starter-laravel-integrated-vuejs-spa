import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useDialogStore = defineStore('dialog', () => {
  const dialogs = ref([])
  let nextId = 0

  function showDialog(options) {
    return new Promise((resolve) => {
      const id = `dialog-${nextId++}-${Date.now()}`

      // Check singleton - only allow one dialog of this type
      if (options.singleton) {
        const existingIndex = dialogs.value.findIndex(d => d.type === options.type)
        if (existingIndex > -1) {
          // Return existing dialog's promise
          return dialogs.value[existingIndex].promise
        }
      }

      // Check queue setting
      if (!options.queue && dialogs.value.length > 0) {
        // Replace existing dialogs
        dialogs.value = []
      }

      const dialog = {
        id,
        type: options.type || 'info',
        title: options.title || '',
        message: options.message || '',
        html: options.html || null,
        sanitize: options.sanitize ?? true,
        icon: options.icon || null,

        confirmLabel: options.confirmLabel || 'Confirm',
        cancelLabel: options.cancelLabel || 'Cancel',
        confirmClass: options.confirmClass || '',
        cancelClass: options.cancelClass || '',
        showCancel: options.showCancel ?? true,
        buttons: options.buttons || null,

        closable: options.closable ?? true,
        closeOnBackdrop: options.closeOnBackdrop ?? false,
        closeOnEscape: options.closeOnEscape ?? true,
        persistent: options.persistent ?? false,

        loading: options.loading ?? false,
        loadingText: options.loadingText || 'Processing...',
        disableOnLoading: options.disableOnLoading ?? true,

        autoFocusButton: options.autoFocusButton || 'confirm',
        returnFocus: options.returnFocus ?? true,

        priority: options.priority || 0,
        queue: options.queue ?? true,
        singleton: options.singleton ?? false,

        size: options.size || 'md',
        fullScreen: options.fullScreen ?? false,
        mobilePosition: options.mobilePosition || 'center',

        animation: options.animation || 'scale',
        animationDuration: options.animationDuration || 200,

        timer: options.timer || null,
        timerProgressBar: options.timerProgressBar ?? false,
        timerOnButton: options.timerOnButton ?? false,

        requireConfirmation: options.requireConfirmation ?? false,
        confirmationText: options.confirmationText || 'CONFIRM',
        confirmationPlaceholder: options.confirmationPlaceholder || '',
        confirmationHint: options.confirmationHint || '',

        inputs: options.inputs || null,

        component: options.component || null,
        componentProps: options.componentProps || {},

        theme: options.theme || 'auto',
        customClass: options.customClass || '',
        customStyle: options.customStyle || {},

        mobileFullScreen: options.mobileFullScreen ?? false,
        swipeToDismiss: options.swipeToDismiss ?? false,

        zIndex: options.zIndex || null,
        stackable: options.stackable ?? false,

        showErrorInDialog: options.showErrorInDialog ?? false,

        // Callbacks
        onConfirm: options.onConfirm || null,
        onCancel: options.onCancel || null,
        onClose: options.onClose || null,
        onError: options.onError || null,
        onSubmit: options.onSubmit || null,

        // Internal
        resolve,
        timestamp: Date.now(),
        triggerElement: document.activeElement,
      }

      // Sort by priority (higher first)
      dialogs.value.push(dialog)
      dialogs.value.sort((a, b) => b.priority - a.priority)

      // Handle timer auto-dismiss
      if (dialog.timer) {
        setTimeout(() => {
          dismissDialog(id)
        }, dialog.timer)
      }
    })
  }

  function resolveDialog(id, confirmed = true, data = null) {
    const dialog = dialogs.value.find(d => d.id === id)
    if (!dialog) return

    // Call onConfirm callback if confirmed
    if (confirmed && dialog.onConfirm) {
      try {
        dialog.onConfirm(data)
      } catch (error) {
        if (dialog.onError) {
          dialog.onError(error)
        }
        console.error('Dialog onConfirm error:', error)
      }
    }

    // Resolve promise
    dialog.resolve(data !== null ? data : confirmed)

    // Return focus if needed
    if (dialog.returnFocus && dialog.triggerElement) {
      setTimeout(() => {
        dialog.triggerElement.focus()
      }, 100)
    }

    // Remove dialog
    removeDialog(id)
  }

  function dismissDialog(id) {
    const dialog = dialogs.value.find(d => d.id === id)
    if (!dialog) return

    // Call onCancel callback
    if (dialog.onCancel) {
      try {
        dialog.onCancel()
      } catch (error) {
        console.error('Dialog onCancel error:', error)
      }
    }

    // Resolve promise with false
    dialog.resolve(false)

    // Return focus if needed
    if (dialog.returnFocus && dialog.triggerElement) {
      setTimeout(() => {
        dialog.triggerElement.focus()
      }, 100)
    }

    // Remove dialog
    removeDialog(id)
  }

  function closeDialog(id) {
    const dialog = dialogs.value.find(d => d.id === id)
    if (!dialog) return

    // Don't allow closing persistent dialogs
    if (dialog.persistent) return

    // Call onClose callback
    if (dialog.onClose) {
      try {
        dialog.onClose()
      } catch (error) {
        console.error('Dialog onClose error:', error)
      }
    }

    // Resolve promise with false
    dialog.resolve(false)

    // Return focus if needed
    if (dialog.returnFocus && dialog.triggerElement) {
      setTimeout(() => {
        dialog.triggerElement.focus()
      }, 100)
    }

    // Remove dialog
    removeDialog(id)
  }

  function setLoading(id, loading = true, loadingText = null) {
    const dialog = dialogs.value.find(d => d.id === id)
    if (!dialog) return

    dialog.loading = loading
    if (loadingText) {
      dialog.loadingText = loadingText
    }
  }

  function removeDialog(id) {
    const index = dialogs.value.findIndex(d => d.id === id)
    if (index > -1) {
      dialogs.value.splice(index, 1)
    }
  }

  function clearAll() {
    // Resolve all pending dialogs with false
    dialogs.value.forEach(dialog => {
      dialog.resolve(false)
    })
    dialogs.value = []
  }

  return {
    dialogs,
    showDialog,
    resolveDialog,
    dismissDialog,
    closeDialog,
    setLoading,
    removeDialog,
    clearAll,
  }
})
