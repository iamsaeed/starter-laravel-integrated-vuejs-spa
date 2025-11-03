/**
 * Composable for Register Form with VeeValidate
 * Provides form state, validation, and submission logic
 */

import { ref } from 'vue'
import { useForm } from 'vee-validate'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import { registerSchema } from '../../utils/validationSchemas'
import { handleLaravelValidationErrors, getLaravelErrorMessage } from '../../utils/laravelErrorMapper'

export function useRegisterForm() {
  const router = useRouter()
  const authStore = useAuthStore()
  const isSubmitting = ref(false)
  const successMessage = ref('')
  const errorMessage = ref('')

  const { handleSubmit, errors, values, setErrors, setFieldValue, resetForm } = useForm({
    validationSchema: registerSchema,
    initialValues: {
      name: '',
      email: '',
      password: '',
      password_confirmation: '',
      terms: false
    },
    validateOnMount: false
  })

  const onSubmit = handleSubmit(async (values) => {
    isSubmitting.value = true
    successMessage.value = ''
    errorMessage.value = ''

    try {
      await authStore.register(values)
      successMessage.value = 'Registration successful! Redirecting...'

      // Redirect to appropriate dashboard based on user permissions
      setTimeout(() => {
        if (authStore.user?.can_access_admin_panel) {
          router.push({ name: 'admin.dashboard' })
        } else {
          router.push({ name: 'user.dashboard' })
        }
      }, 1000)
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