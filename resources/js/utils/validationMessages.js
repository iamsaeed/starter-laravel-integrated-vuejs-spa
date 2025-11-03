/**
 * Custom validation messages for VeeValidate
 * These messages can be used globally or per-field
 */

export const validationMessages = {
  required: 'This field is required.',
  email: 'Please enter a valid email address.',
  min: 'Must be at least {length} characters.',
  max: 'Must not exceed {length} characters.',
  confirmed: 'The passwords do not match.',
  url: 'Please enter a valid URL.',
  numeric: 'This field must be numeric.',
  alpha: 'This field may only contain letters.',
  alphanumeric: 'This field may only contain letters and numbers.',
  minValue: 'Value must be at least {min}.',
  maxValue: 'Value must not exceed {max}.',
  between: 'Value must be between {min} and {max}.',
  oneOf: 'This field must be one of the following: {values}.',
  regex: 'The format is invalid.',
  length: 'Must be exactly {length} characters.',
  integer: 'This field must be an integer.'
}

/**
 * Generate validation message with interpolation
 * @param {string} rule - The validation rule name
 * @param {Object} params - Parameters to interpolate
 * @returns {string} - The validation message
 */
export function generateMessage(rule, params = {}) {
  let message = validationMessages[rule] || 'This field is invalid.'

  // Replace placeholders with actual values
  Object.keys(params).forEach((key) => {
    message = message.replace(`{${key}}`, params[key])
  })

  return message
}