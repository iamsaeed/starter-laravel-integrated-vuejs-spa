/**
 * Composable for Change Password Form with VeeValidate
 * Provides form state, validation, and submission logic
 */

import { ref } from 'vue'
import { useForm } from 'vee-validate'
import { useAuthStore } from '../../stores/auth'
import { changePasswordSchema } from '../../utils/validationSchemas'
import { handleLaravelValidationErrors, getLaravelErrorMessage } from '../../utils/laravelErrorMapper'

export function useChangePasswordForm() {
  const authStore = useAuthStore()
  const isSubmitting = ref(false)
  const successMessage = ref('')
  const errorMessage = ref('')

  const { handleSubmit, errors, values, setErrors, setFieldValue, resetForm, meta } = useForm({
    validationSchema: changePasswordSchema,
    initialValues: {
      current_password: '',
      password: '',
      password_confirmation: ''
    },
    validateOnMount: false,
    validateOnBlur: true,
    validateOnChange: false,
    validateOnInput: false,
    initialErrors: {},
    initialTouched: {}
  })

  const onSubmit = handleSubmit(async (values) => {
    isSubmitting.value = true
    successMessage.value = ''
    errorMessage.value = ''

    try {
      await authStore.changePassword(values)
      successMessage.value = 'Password changed successfully!'
      resetForm()
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
