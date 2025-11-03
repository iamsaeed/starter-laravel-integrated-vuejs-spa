/**
 * Composable for password visibility toggle
 * Provides reactive state and methods for showing/hiding password fields
 */

import { ref } from 'vue'

export function usePasswordToggle() {
  const showPassword = ref(false)

  const togglePassword = () => {
    showPassword.value = !showPassword.value
  }

  const inputType = () => {
    return showPassword.value ? 'text' : 'password'
  }

  const toggleIcon = () => {
    return showPassword.value ? 'eye-slash' : 'eye'
  }

  return {
    showPassword,
    togglePassword,
    inputType,
    toggleIcon
  }
}