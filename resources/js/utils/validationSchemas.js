/**
 * Validation schemas using Yup for VeeValidate
 * These schemas define validation rules for common forms
 */

import * as yup from 'yup'

export const loginSchema = yup.object({
  email: yup
    .string()
    .required('Please enter your email address.')
    .email('Please enter a valid email address.'),
  password: yup
    .string()
    .required('Please enter your password.'),
  remember: yup
    .boolean()
    .optional()
})

export const registerSchema = yup.object({
  name: yup
    .string()
    .required('Please enter your name.')
    .max(255, 'Name must not exceed 255 characters.'),
  email: yup
    .string()
    .required('Please enter your email address.')
    .email('Please enter a valid email address.')
    .max(255, 'Email must not exceed 255 characters.'),
  password: yup
    .string()
    .required('Please enter a password.')
    .min(8, 'Password must be at least 8 characters.'),
  password_confirmation: yup
    .string()
    .required('Please confirm your password.')
    .oneOf([yup.ref('password')], 'Password confirmation does not match.'),
  terms: yup
    .boolean()
    .required('You must accept the terms and conditions.')
    .oneOf([true], 'You must accept the terms and conditions.')
})

export const forgotPasswordSchema = yup.object({
  email: yup
    .string()
    .required('Please enter your email address.')
    .email('Please enter a valid email address.')
})

export const resetPasswordSchema = yup.object({
  token: yup
    .string()
    .required('Reset token is required.'),
  email: yup
    .string()
    .required('Please enter your email address.')
    .email('Please enter a valid email address.'),
  password: yup
    .string()
    .required('Please enter a new password.')
    .min(8, 'Password must be at least 8 characters.'),
  password_confirmation: yup
    .string()
    .required('Please confirm your password.')
    .oneOf([yup.ref('password')], 'Password confirmation does not match.')
})

export const changePasswordSchema = yup.object({
  current_password: yup
    .string()
    .required('Please enter your current password.'),
  password: yup
    .string()
    .required('Please enter a new password.')
    .min(8, 'Password must be at least 8 characters.'),
  password_confirmation: yup
    .string()
    .required('Please confirm your password.')
    .oneOf([yup.ref('password')], 'Password confirmation does not match.')
})

export const profileUpdateSchema = yup.object({
  name: yup
    .string()
    .required('Please enter your name.')
    .max(255, 'Name must not exceed 255 characters.'),
  email: yup
    .string()
    .required('Please enter your email address.')
    .email('Please enter a valid email address.')
    .max(255, 'Email must not exceed 255 characters.')
})

export const emailVerificationSchema = yup.object({
  code: yup
    .string()
    .required('Please enter the verification code.')
    .length(6, 'Verification code must be 6 digits.')
})