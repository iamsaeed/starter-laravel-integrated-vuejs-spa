import { ref } from 'vue'

/**
 * Composable for managing modal state
 *
 * @example
 * const { isOpen, open, close, toggle } = useModal()
 *
 * // Open modal
 * open()
 *
 * // Close modal
 * close()
 *
 * // Toggle modal
 * toggle()
 *
 * // Use in template with v-model
 * <BaseModal v-model="isOpen" title="My Modal">
 *   Content here
 * </BaseModal>
 */
export function useModal(initialState = false) {
  const isOpen = ref(initialState)

  const open = () => {
    isOpen.value = true
  }

  const close = () => {
    isOpen.value = false
  }

  const toggle = () => {
    isOpen.value = !isOpen.value
  }

  return {
    isOpen,
    open,
    close,
    toggle
  }
}

/**
 * Composable for managing multiple modals with data
 * Useful when you need to pass data to the modal or handle modal-specific state
 *
 * @example
 * const userModal = useModalWithData()
 *
 * // Open modal with data
 * userModal.open({ id: 1, name: 'John' })
 *
 * // Access data in component
 * <BaseModal v-model="userModal.isOpen" title="Edit User">
 *   <div>Editing: {{ userModal.data.name }}</div>
 * </BaseModal>
 *
 * // Close and clear data
 * userModal.close()
 */
export function useModalWithData(initialData = null) {
  const isOpen = ref(false)
  const data = ref(initialData)

  const open = (modalData = null) => {
    if (modalData !== null) {
      data.value = modalData
    }
    isOpen.value = true
  }

  const close = () => {
    isOpen.value = false
  }

  const closeAndClear = () => {
    isOpen.value = false
    data.value = null
  }

  const toggle = (modalData = null) => {
    if (!isOpen.value && modalData !== null) {
      data.value = modalData
    }
    isOpen.value = !isOpen.value
  }

  const updateData = (newData) => {
    data.value = newData
  }

  return {
    isOpen,
    data,
    open,
    close,
    closeAndClear,
    toggle,
    updateData
  }
}

/**
 * Composable for managing modal loading and submission states
 * Useful for forms in modals
 *
 * @example
 * const { isOpen, isLoading, errors, open, close, startLoading, stopLoading, setErrors, clearErrors } = useModalForm()
 *
 * async function handleSubmit() {
 *   clearErrors()
 *   startLoading()
 *   try {
 *     await api.submitForm(formData)
 *     close()
 *   } catch (error) {
 *     setErrors(error.response.data.errors)
 *   } finally {
 *     stopLoading()
 *   }
 * }
 */
export function useModalForm(initialData = null) {
  const isOpen = ref(false)
  const isLoading = ref(false)
  const data = ref(initialData)
  const errors = ref({})

  const open = (modalData = null) => {
    if (modalData !== null) {
      data.value = modalData
    }
    isOpen.value = true
    errors.value = {}
  }

  const close = () => {
    isOpen.value = false
    isLoading.value = false
    errors.value = {}
  }

  const startLoading = () => {
    isLoading.value = true
  }

  const stopLoading = () => {
    isLoading.value = false
  }

  const setErrors = (newErrors) => {
    errors.value = newErrors || {}
  }

  const clearErrors = () => {
    errors.value = {}
  }

  const updateData = (newData) => {
    data.value = newData
  }

  return {
    isOpen,
    isLoading,
    data,
    errors,
    open,
    close,
    startLoading,
    stopLoading,
    setErrors,
    clearErrors,
    updateData
  }
}

/**
 * Composable for managing a confirmation modal
 * Returns a promise-based confirmation dialog
 *
 * @example
 * const { isOpen, confirm, cancel, message, title } = useModalConfirm()
 *
 * // In your component
 * async function deleteItem() {
 *   const confirmed = await confirm('Are you sure?', 'Delete Item')
 *   if (confirmed) {
 *     // Delete the item
 *   }
 * }
 *
 * // In template
 * <BaseModal v-model="isOpen" :title="title">
 *   <p>{{ message }}</p>
 *   <template #footer>
 *     <button @click="cancel">Cancel</button>
 *     <button @click="confirm(true)">Confirm</button>
 *   </template>
 * </BaseModal>
 */
export function useModalConfirm() {
  const isOpen = ref(false)
  const message = ref('')
  const title = ref('')
  let resolvePromise = null

  const confirm = (msg = 'Are you sure?', modalTitle = 'Confirm') => {
    message.value = msg
    title.value = modalTitle
    isOpen.value = true

    return new Promise((resolve) => {
      resolvePromise = resolve
    })
  }

  const handleConfirm = () => {
    isOpen.value = false
    if (resolvePromise) {
      resolvePromise(true)
      resolvePromise = null
    }
  }

  const handleCancel = () => {
    isOpen.value = false
    if (resolvePromise) {
      resolvePromise(false)
      resolvePromise = null
    }
  }

  return {
    isOpen,
    message,
    title,
    confirm,
    handleConfirm,
    handleCancel
  }
}
