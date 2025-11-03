<template>
  <div class="max-w-4xl mx-auto p-6">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-8">
      Advanced Form Components Demo
    </h1>

    <!-- VeeValidate Form -->
    <Form @submit="handleSubmit" :validation-schema="demoSchema" class="space-y-8">
      
      <!-- Personal Information Section -->
      <FormSection 
        title="Personal Information" 
        description="Basic information about yourself"
        collapsible
      >
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <FormGroup>
            <FormLabel for="firstName" :required="true">First Name</FormLabel>
            <FormInput
              id="firstName"
              name="firstName"
              placeholder="Enter your first name"
              help-text="This will be displayed on your profile"
            />
          </FormGroup>

          <FormGroup>
            <FormLabel for="lastName" :required="true">Last Name</FormLabel>
            <FormInput
              id="lastName"
              name="lastName"
              placeholder="Enter your last name"
            />
          </FormGroup>

          <FormGroup>
            <FormLabel for="email" :required="true">Email Address</FormLabel>
            <FormInput
              id="email"
              name="email"
              type="email"
              placeholder="Enter your email address"
              help-text="We'll use this to send you important notifications"
            />
            <!-- Async validation indicator -->
            <div v-if="emailValidation.isValidating" class="text-sm text-blue-600 mt-1">
              Checking email availability...
            </div>
          </FormGroup>

          <FormGroup>
            <FormLabel for="birthDate">Date of Birth</FormLabel>
            <DateInput
              id="birthDate"
              name="birthDate"
              :max-date="maxBirthDate"
              help-text="Must be 18 years or older"
            />
          </FormGroup>
        </div>
      </FormSection>

      <!-- Preferences Section -->
      <FormSection 
        title="Preferences" 
        description="Customize your experience"
      >
        <div class="space-y-6">
          <FormGroup>
            <FormLabel for="country" :required="true">Country</FormLabel>
            <SelectInput
              id="country"
              name="country"
              :options="countryOptions"
              placeholder="Select your country"
              searchable
            />
          </FormGroup>

          <FormGroup>
            <FormLabel for="interests">Interests</FormLabel>
            <SelectInput
              id="interests"
              name="interests"
              :options="interestOptions"
              multiple
              searchable
              help-text="Select all that apply"
            />
          </FormGroup>

          <FormGroup>
            <FormLabel for="contactMethod" :required="true">Preferred Contact Method</FormLabel>
            <RadioGroup
              id="contactMethod"
              name="contactMethod"
              :options="contactOptions"
              inline
            />
          </FormGroup>

          <FormGroup>
            <FormLabel for="bio">Biography</FormLabel>
            <TextareaInput
              id="bio"
              name="bio"
              placeholder="Tell us about yourself..."
              :max-length="500"
              auto-resize
              help-text="Share a brief description about yourself"
            />
          </FormGroup>

          <FormGroup>
            <FormLabel for="profilePicture">Profile Picture</FormLabel>
            <FileInput
              id="profilePicture"
              name="profilePicture"
              accept="image/*"
              :max-size="5242880"
              help-text="Upload a profile picture (max 5MB)"
            />
          </FormGroup>
        </div>
      </FormSection>

      <!-- Terms and Conditions -->
      <FormSection title="Terms and Conditions">
        <FormGroup>
          <CheckboxInput
            id="terms"
            name="terms"
            label="I agree to the Terms of Service and Privacy Policy"
          />
        </FormGroup>

        <FormGroup>
          <CheckboxInput
            id="newsletter"
            name="newsletter"
            label="Subscribe to our newsletter for updates and news"
          />
        </FormGroup>
      </FormSection>

      <!-- Form Actions -->
      <FormActions align="between">
        <button
          type="button"
          @click="resetForm"
          class="form-button-secondary"
        >
          Reset Form
        </button>
        
        <div class="flex space-x-3">
          <button
            type="button"
            @click="saveDraft"
            class="form-button-secondary"
            :disabled="!formState.isDirty"
          >
            Save Draft
          </button>
          <button
            type="submit"
            class="form-button-primary"
            :disabled="isSubmitting"
          >
            <span v-if="isSubmitting">Submitting...</span>
            <span v-else>Submit Form</span>
          </button>
        </div>
      </FormActions>

      <!-- Form State Debug Info (Development only) -->
      <div v-if="showDebugInfo" class="mt-8 p-4 bg-gray-100 dark:bg-gray-800 rounded-lg">
        <h3 class="font-semibold mb-2">Form State Debug</h3>
        <div class="text-sm space-y-1">
          <p>Is Dirty: {{ formState.isDirty }}</p>
          <p>Has Unsaved Changes: {{ formState.hasUnsavedChanges }}</p>
          <p>Touched Fields: {{ formState.touchedFields.join(', ') }}</p>
          <p>Email Validation: {{ emailValidation.isValid ? 'Valid' : 'Invalid' }}</p>
        </div>
      </div>
    </Form>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Form } from 'vee-validate'
import * as yup from 'yup'

// Components
import FormSection from '../components/form/FormSection.vue'
import FormGroup from '../components/form/FormGroup.vue'
import FormLabel from '../components/form/FormLabel.vue'
import FormInput from '../components/form/FormInput.vue'
import SelectInput from '../components/form/SelectInput.vue'
import RadioGroup from '../components/form/RadioGroup.vue'
import TextareaInput from '../components/form/TextareaInput.vue'
import FileInput from '../components/form/FileInput.vue'
import CheckboxInput from '../components/form/CheckboxInput.vue'
import DateInput from '../components/form/DateInput.vue'
import FormActions from '../components/form/FormActions.vue'

// Composables
import { useFormState } from '../components/composables/useFormState'
import { useAsyncValidation, createAsyncValidators } from '../components/composables/useAsyncValidation'

// State
const isSubmitting = ref(false)
const showDebugInfo = ref(true) // Set to false in production

// Form validation schema
const demoSchema = yup.object({
  firstName: yup.string().required('First name is required').max(50),
  lastName: yup.string().required('Last name is required').max(50),
  email: yup.string().required('Email is required').email('Must be a valid email'),
  birthDate: yup.date().max(new Date(Date.now() - 567648000000), 'Must be 18 years or older'), // 18 years ago
  country: yup.string().required('Please select your country'),
  interests: yup.array().min(1, 'Select at least one interest'),
  contactMethod: yup.string().required('Please select a contact method'),
  bio: yup.string().max(500, 'Biography must be 500 characters or less'),
  terms: yup.boolean().isTrue('You must agree to the terms and conditions')
})

// Form state management
const formState = useFormState({
  firstName: '',
  lastName: '',
  email: '',
  birthDate: '',
  country: '',
  interests: [],
  contactMethod: '',
  bio: '',
  profilePicture: null,
  terms: false,
  newsletter: false
}, {
  enableUnsavedWarning: true,
  autoSave: true,
  autoSaveDelay: 3000
})

// Async email validation
const emailValidation = useAsyncValidation(
  createAsyncValidators.emailUniqueness('/api/validate-email'),
  {
    debounceDelay: 500,
    validateOnBlur: true,
    cacheResults: true
  }
)

// Options for select components
const countryOptions = [
  { value: 'us', label: 'United States' },
  { value: 'ca', label: 'Canada' },
  { value: 'uk', label: 'United Kingdom' },
  { value: 'au', label: 'Australia' },
  { value: 'de', label: 'Germany' },
  { value: 'fr', label: 'France' },
  { value: 'jp', label: 'Japan' }
]

const interestOptions = [
  { value: 'tech', label: 'Technology' },
  { value: 'sports', label: 'Sports' },
  { value: 'music', label: 'Music' },
  { value: 'art', label: 'Art & Design' },
  { value: 'travel', label: 'Travel' },
  { value: 'food', label: 'Food & Cooking' },
  { value: 'books', label: 'Books & Literature' },
  { value: 'movies', label: 'Movies & TV' }
]

const contactOptions = [
  { value: 'email', label: 'Email', description: 'Receive notifications via email' },
  { value: 'sms', label: 'SMS', description: 'Receive text message notifications' },
  { value: 'phone', label: 'Phone', description: 'Receive phone call notifications' },
  { value: 'none', label: 'No Contact', description: 'Don\'t send me notifications' }
]

// Computed
const maxBirthDate = computed(() => {
  const eighteenYearsAgo = new Date()
  eighteenYearsAgo.setFullYear(eighteenYearsAgo.getFullYear() - 18)
  return eighteenYearsAgo.toISOString().split('T')[0]
})

// Methods
const handleSubmit = async (values) => {
  isSubmitting.value = true
  
  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 2000))
    
    console.log('Form submitted:', values)
    formState.markAsSubmitted()
    
    // Show success message or redirect
    alert('Form submitted successfully!')
  } catch (error) {
    console.error('Form submission error:', error)
    alert('Error submitting form. Please try again.')
  } finally {
    isSubmitting.value = false
  }
}

const resetForm = () => {
  if (formState.hasUnsavedChanges && !confirm('Are you sure you want to reset the form? All changes will be lost.')) {
    return
  }
  
  formState.resetForm()
  emailValidation.clearValidation()
}

const saveDraft = () => {
  // Simulate saving draft
  console.log('Saving draft:', formState.formValues)
  alert('Draft saved!')
}
</script>