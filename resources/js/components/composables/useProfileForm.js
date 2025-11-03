/**
 * Composable for Profile Update Form with VeeValidate
 * Provides form state, validation, and submission logic
 */

import { ref } from 'vue'
import { useForm } from 'vee-validate'
import { useAuthStore } from '../../stores/auth'
import { profileUpdateSchema } from '../../utils/validationSchemas'
import { handleLaravelValidationErrors, getLaravelErrorMessage } from '../../utils/laravelErrorMapper'

export function useProfileForm() {
  const authStore = useAuthStore()
  const isSubmitting = ref(false)
  const successMessage = ref('')
  const errorMessage = ref('')

  const { handleSubmit, errors, values, setErrors, setFieldValue, resetForm } = useForm({
    validationSchema: profileUpdateSchema,
    initialValues: {
      name: authStore.user?.name || '',
      email: authStore.user?.email || ''
    },
    validateOnMount: false
  })

  const onSubmit = handleSubmit(async (values) => {
    isSubmitting.value = true
    successMessage.value = ''
    errorMessage.value = ''

    try {
      await authStore.updateProfile(values)
      successMessage.value = 'Profile updated successfully!'
    } catch (error) {
      // Handle validation errors
      if (!handleLaravelValidationErrors(error, setErrors)) {
        // Handle other errors
        errorMessage.value = getLaravelErrorMessage(error)
      }
    } finally {
      isSubmitting.value = false
    }
  })

  return {
    onSubmit,
    errors,
    values,
    isSubmitting,
    successMessage,
    errorMessage,
    setFieldValue,
    resetForm
  }
}
