/**
 * Common validation rules for VeeValidate
 * These rules are reusable across all forms in the application
 */

export const validationRules = {
  required: (value) => {
    if (value === undefined || value === null || value === '') {
      return 'This field is required.'
    }
    return true
  },

  email: (value) => {
    if (!value) {
      return true
    }
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    if (!emailRegex.test(value)) {
      return 'Please enter a valid email address.'
    }
    return true
  },

  min: (minLength) => (value) => {
    if (!value) {
      return true
    }
    if (value.length < minLength) {
      return `Must be at least ${minLength} characters.`
    }
    return true
  },

  max: (maxLength) => (value) => {
    if (!value) {
      return true
    }
    if (value.length > maxLength) {
      return `Must not exceed ${maxLength} characters.`
    }
    return true
  },

  confirmed: (targetValue) => (value) => {
    if (value !== targetValue) {
      return 'The passwords do not match.'
    }
    return true
  },

  url: (value) => {
    if (!value) {
      return true
    }
    try {
      new URL(value)
      return true
    } catch {
      return 'Please enter a valid URL.'
    }
  },

  numeric: (value) => {
    if (!value) {
      return true
    }
    if (!/^\d+$/.test(value)) {
      return 'This field must be numeric.'
    }
    return true
  },

  alpha: (value) => {
    if (!value) {
      return true
    }
    if (!/^[a-zA-Z]+$/.test(value)) {
      return 'This field may only contain letters.'
    }
    return true
  },

  alphanumeric: (value) => {
    if (!value) {
      return true
    }
    if (!/^[a-zA-Z0-9]+$/.test(value)) {
      return 'This field may only contain letters and numbers.'
    }
    return true
  },

  password: (value) => {
    if (!value) {
      return true
    }
    if (value.length < 8) {
      return 'Password must be at least 8 characters.'
    }
    return true
  },

  strongPassword: (value) => {
    if (!value) {
      return true
    }
    const hasUpperCase = /[A-Z]/.test(value)
    const hasLowerCase = /[a-z]/.test(value)
    const hasNumber = /\d/.test(value)
    const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(value)
    const isLongEnough = value.length >= 8

    if (!isLongEnough) {
      return 'Password must be at least 8 characters.'
    }
    if (!hasUpperCase || !hasLowerCase) {
      return 'Password must contain both uppercase and lowercase letters.'
    }
    if (!hasNumber) {
      return 'Password must contain at least one number.'
    }
    if (!hasSpecialChar) {
      return 'Password must contain at least one special character.'
    }
    return true
  },

  phone: (value) => {
    if (!value) {
      return true
    }
    const phoneRegex = /^\+?[\d\s\-()]+$/
    if (!phoneRegex.test(value)) {
      return 'Please enter a valid phone number.'
    }
    return true
  },

  date: (value) => {
    if (!value) {
      return true
    }
    const date = new Date(value)
    if (isNaN(date.getTime())) {
      return 'Please enter a valid date.'
    }
    return true
  },

  minValue: (minVal) => (value) => {
    if (!value) {
      return true
    }
    if (Number(value) < minVal) {
      return `Value must be at least ${minVal}.`
    }
    return true
  },

  maxValue: (maxVal) => (value) => {
    if (!value) {
      return true
    }
    if (Number(value) > maxVal) {
      return `Value must not exceed ${maxVal}.`
    }
    return true
  },

  fileSize: (maxSizeInMB) => (file) => {
    if (!file) {
      return true
    }
    const fileSizeInMB = file.size / (1024 * 1024)
    if (fileSizeInMB > maxSizeInMB) {
      return `File size must not exceed ${maxSizeInMB}MB.`
    }
    return true
  },

  fileType: (allowedTypes) => (file) => {
    if (!file) {
      return true
    }
    const fileType = file.type
    if (!allowedTypes.includes(fileType)) {
      return `File type must be one of: ${allowedTypes.join(', ')}.`
    }
    return true
  }
}