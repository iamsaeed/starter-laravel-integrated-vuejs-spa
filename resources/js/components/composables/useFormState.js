/**
 * Advanced Form State Management Composable
 * Provides dirty/pristine tracking, unsaved changes warning, and form reset functionality
 */

import { ref, computed, watch, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'

export function useFormState(initialValues = {}, options = {}) {
  const {
    enableUnsavedWarning = true,
    confirmationMessage = 'You have unsaved changes. Are you sure you want to leave?',
    excludeFields = [],
    autoSave = false,
    autoSaveDelay = 2000
  } = options

  const router = useRouter()

  // State
  const formValues = ref({ ...initialValues })
  const initialFormValues = ref({ ...initialValues })
  const touchedFields = ref(new Set())
  const hasSubmitted = ref(false)
  const autoSaveTimer = ref(null)

  // Computed properties
  const isDirty = computed(() => {
    return Object.keys(formValues.value).some(key => {
      if (excludeFields.includes(key)) return false
      return JSON.stringify(formValues.value[key]) !== JSON.stringify(initialFormValues.value[key])
    })
  })

  const isPristine = computed(() => !isDirty.value)

  const touchedFieldsList = computed(() => Array.from(touchedFields.value))

  const isFieldTouched = (fieldName) => touchedFields.value.has(fieldName)

  const isFieldDirty = (fieldName) => {
    if (excludeFields.includes(fieldName)) return false
    return JSON.stringify(formValues.value[fieldName]) !== JSON.stringify(initialFormValues.value[fieldName])
  }

  const hasUnsavedChanges = computed(() => isDirty.value && !hasSubmitted.value)

  // Methods
  const setFieldValue = (fieldName, value) => {
    formValues.value[fieldName] = value
    touchField(fieldName)
    
    if (autoSave && hasUnsavedChanges.value) {
      scheduleAutoSave()
    }
  }

  const touchField = (fieldName) => {
    touchedFields.value.add(fieldName)
  }

  const resetForm = (newValues = null) => {
    const resetValues = newValues || initialFormValues.value
    formValues.value = { ...resetValues }
    initialFormValues.value = { ...resetValues }
    touchedFields.value.clear()
    hasSubmitted.value = false
    clearAutoSave()
  }

  const markAsSubmitted = () => {
    hasSubmitted.value = true
    clearAutoSave()
  }

  const updateInitialValues = (newValues) => {
    initialFormValues.value = { ...newValues }
    formValues.value = { ...newValues }
    touchedFields.value.clear()
    hasSubmitted.value = false
  }

  const scheduleAutoSave = () => {
    clearAutoSave()
    autoSaveTimer.value = setTimeout(() => {
      // Emit auto-save event or call auto-save function
      // This would need to be implemented based on specific needs
      console.log('Auto-saving form data...', formValues.value)
    }, autoSaveDelay)
  }

  const clearAutoSave = () => {
    if (autoSaveTimer.value) {
      clearTimeout(autoSaveTimer.value)
      autoSaveTimer.value = null
    }
  }

  // Browser beforeunload warning
  const handleBeforeUnload = (event) => {
    if (hasUnsavedChanges.value && enableUnsavedWarning) {
      event.preventDefault()
      event.returnValue = confirmationMessage
      return confirmationMessage
    }
  }

  // Vue Router navigation guard
  const routerGuard = (to, from, next) => {
    if (hasUnsavedChanges.value && enableUnsavedWarning) {
      if (window.confirm(confirmationMessage)) {
        next()
      } else {
        next(false)
      }
    } else {
      next()
    }
  }

  // Setup unsaved changes warning
  const setupUnsavedWarning = () => {
    if (enableUnsavedWarning) {
      // Browser beforeunload event
      window.addEventListener('beforeunload', handleBeforeUnload)
      
      // Vue Router beforeRouteLeave guard
      const removeGuard = router.beforeEach(routerGuard)
      
      return () => {
        window.removeEventListener('beforeunload', handleBeforeUnload)
        removeGuard()
      }
    }
    return () => {}
  }

  // Watch for changes to enable/disable unsaved warning
  let removeWarningSetup = null
  
  watch(() => enableUnsavedWarning, (enabled) => {
    if (removeWarningSetup) {
      removeWarningSetup()
    }
    
    if (enabled) {
      removeWarningSetup = setupUnsavedWarning()
    }
  }, { immediate: true })

  // Cleanup on unmount
  onBeforeUnmount(() => {
    if (removeWarningSetup) {
      removeWarningSetup()
    }
    clearAutoSave()
  })

  return {
    // State
    formValues,
    touchedFields: touchedFieldsList,
    hasSubmitted,
    
    // Computed
    isDirty,
    isPristine,
    hasUnsavedChanges,
    
    // Methods
    setFieldValue,
    touchField,
    resetForm,
    markAsSubmitted,
    updateInitialValues,
    isFieldTouched,
    isFieldDirty,
    
    // Auto-save
    scheduleAutoSave,
    clearAutoSave
  }
}