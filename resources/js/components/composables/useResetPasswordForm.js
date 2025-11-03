/**
 * Composable for Reset Password Form with VeeValidate
 * Provides form state, validation, and submission logic
 */

import { ref } from 'vue'
import { useForm } from 'vee-validate'
import { useRouter, useRoute } from 'vue-router'
import { resetPasswordSchema } from '../../utils/validationSchemas'
import { authService } from '../../services/authService'
import { handleLaravelValidationErrors, getLaravelErrorMessage } from '../../utils/laravelErrorMapper'

export function useResetPasswordForm() {
  const router = useRouter()
  const route = useRoute()
  const isSubmitting = ref(false)
  const successMessage = ref('')
  const errorMessage = ref('')

  // Get token and email from URL query params
  const token = ref(route.query.token || '')
  const email = ref(route.query.email || '')

  const { handleSubmit, errors, values, setErrors, setFieldValue, resetForm } = useForm({
    validationSchema: resetPasswordSchema,
    initialValues: {
      email: email.value,
      password: '',
      password_confirmation: '',
      token: token.value
    },
    validateOnMount: false
  })

  const onSubmit = handleSubmit(async (values) => {
    isSubmitting.value = true
    successMessage.value = ''
    errorMessage.value = ''

    try {
      const response = await authService.resetPassword(values)
      successMessage.value = response.message || 'Password reset successful! Redirecting to login...'

      // Redirect to login page
      setTimeout(() => {
        router.push({ name: 'auth.login' })
      }, 2000)
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
    resetForm,
    token,
    email
  }
}