/**
 * Composable for Login Form with VeeValidate
 * Provides form state, validation, and submission logic
 */

import { ref } from 'vue'
import { useForm } from 'vee-validate'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import { loginSchema } from '../../utils/validationSchemas'
import { handleLaravelValidationErrors, getLaravelErrorMessage } from '../../utils/laravelErrorMapper'

export function useLoginForm() {
  const router = useRouter()
  const route = useRoute()
  const authStore = useAuthStore()
  const isSubmitting = ref(false)
  const successMessage = ref('')
  const errorMessage = ref('')

  // Prefill credentials in local environment for development convenience
  const isLocalEnv = import.meta.env.VITE_APP_ENV === 'local'

  const { handleSubmit, errors, values, setErrors, setFieldValue, resetForm } = useForm({
    validationSchema: loginSchema,
    initialValues: {
      email: isLocalEnv ? 'user@app.com' : '',
      password: isLocalEnv ? 'password' : '',
      remember: false
    },
    validateOnMount: false
  })

  const onSubmit = handleSubmit(async (values) => {
    isSubmitting.value = true
    successMessage.value = ''
    errorMessage.value = ''

    try {
      await authStore.login(values)
      successMessage.value = 'Login successful! Redirecting...'

      // Determine redirect destination based on user permissions
      let redirectTo
      if (route.query.redirect) {
        redirectTo = route.query.redirect
      } else if (authStore.user?.can_access_admin_panel) {
        redirectTo = { name: 'admin.dashboard' }
      } else {
        redirectTo = { name: 'user.dashboard' }
      }

      // Redirect to intended route or dashboard
      setTimeout(() => {
        router.push(redirectTo)
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