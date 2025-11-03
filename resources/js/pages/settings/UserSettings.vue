<template>
  <div class="card min-h-[80vh]">
    <div class="p-8">
      <!-- Page Header -->
      <div class="mb-6">
        <div class="flex items-center mb-4">
          <div class="w-12 h-12 bg-gradient-to-br from-primary-500 to-secondary-500 rounded-full flex items-center justify-center mr-4">
            <Icon name="settings" :size="24" class="text-white" />
          </div>
          <div>
            <h1 class="text-3xl font-bold text-title">Settings</h1>
            <p class="text-subtitle">Manage your account preferences and settings</p>
          </div>
        </div>
      </div>

      <!-- Settings Form -->
      <SettingsForm
        :is-saving="isSaving"
        :is-dirty="isDirty"
        :show-success="showSuccess"
        :success-message="successMessage"
        :error-message="errorMessage"
        @submit="handleSaveSettings"
        @cancel="handleResetSettings"
      >
        <!-- General Settings Group -->
        <SettingGroup
          title="General"
          description="Basic account and application settings"
        >
          <FormGroup>
            <FormLabel for-id="items_per_page" :required="true">
              Items Per Page
            </FormLabel>
            <SelectInput
              id="items_per_page"
              name="items_per_page"
              :options="itemsPerPageOptions"
              placeholder="Select items per page"
              rules="required"
            />
            <FormHelpText text="Number of items to display per page in lists and tables" />
          </FormGroup>
        </SettingGroup>

        <!-- Appearance Settings Group -->
        <SettingGroup
          title="Appearance"
          description="Customize the look and feel of your interface"
        >
          <FormGroup>
            <FormLabel for-id="user_theme" :required="true">
              Theme
            </FormLabel>
            <SelectInput
              id="user_theme"
              name="user_theme"
              :options="themeOptions"
              placeholder="Select a theme"
              rules="required"
            />
            <FormHelpText text="Choose your preferred color theme" />
          </FormGroup>
        </SettingGroup>

        <!-- Localization Settings Group -->
        <SettingGroup
          title="Localization"
          description="Set your location, timezone, and language preferences"
        >
          <CountrySelect
            id="user_country"
            name="user_country"
            label="Country"
            placeholder="Select your country"
            help-text="Your country for localization and regional settings"
            @change="handleCountryChange"
          />

          <TimezoneSelect
            id="user_timezone"
            name="user_timezone"
            label="Timezone"
            placeholder="Select your timezone"
            :country-code="selectedCountry"
            :require-country="false"
            help-text="Used for displaying dates and times in your local timezone"
            :show-preview="true"
          />

          <FormGroup>
            <FormLabel for-id="date_format">
              Date Format
            </FormLabel>
            <SelectInput
              id="date_format"
              name="date_format"
              :options="dateFormatOptions"
              placeholder="Select date format"
            />
            <FormHelpText text="How dates should be displayed throughout the application" />
          </FormGroup>
        </SettingGroup>

        <!-- Notification Settings Group -->
        <SettingGroup
          title="Notifications"
          description="Manage your notification preferences"
        >
          <FormGroup>
            <CheckboxInput
              id="notifications_enabled"
              name="notifications_enabled"
              label="Enable notifications"
            />
            <FormHelpText text="Receive notifications about important updates and activities" />
          </FormGroup>

          <FormGroup>
            <CheckboxInput
              id="email_notifications"
              name="email_notifications"
              label="Email notifications"
            />
            <FormHelpText text="Receive notifications via email" />
          </FormGroup>

          <FormGroup>
            <CheckboxInput
              id="push_notifications"
              name="push_notifications"
              label="Push notifications"
            />
            <FormHelpText text="Receive push notifications in your browser" />
          </FormGroup>
        </SettingGroup>
      </SettingsForm>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useForm } from 'vee-validate'
import { useSettingsStore } from '@/stores/settings'
import Icon from '@/components/common/Icon.vue'
import SettingsForm from '@/components/settings/SettingsForm.vue'
import SettingGroup from '@/components/settings/SettingGroup.vue'
import CountrySelect from '@/components/settings/CountrySelect.vue'
import TimezoneSelect from '@/components/settings/TimezoneSelect.vue'
import FormGroup from '@/components/form/FormGroup.vue'
import FormLabel from '@/components/form/FormLabel.vue'
import FormHelpText from '@/components/form/FormHelpText.vue'
import SelectInput from '@/components/form/SelectInput.vue'
import CheckboxInput from '@/components/form/CheckboxInput.vue'

const settingsStore = useSettingsStore()

// Form setup
const { values, setValues, meta } = useForm({
  initialValues: {
    user_theme: 'ocean',
    items_per_page: 25,
    user_country: '',
    user_timezone: '',
    date_format: 'MM/DD/YYYY',
    notifications_enabled: true,
    email_notifications: true,
    push_notifications: false
  }
})

// State
const isSaving = ref(false)
const showSuccess = ref(false)
const successMessage = ref('Settings saved successfully!')
const errorMessage = ref('')
const selectedCountry = ref('')

// Computed
const isDirty = computed(() => meta.value.dirty)

// Options for dropdowns
const themeOptions = computed(() => {
  return settingsStore.themes.map(theme => ({
    value: theme.value,
    label: theme.label
  }))
})

const itemsPerPageOptions = ref([
  { value: 10, label: '10 items' },
  { value: 25, label: '25 items' },
  { value: 50, label: '50 items' },
  { value: 100, label: '100 items' }
])

const dateFormatOptions = ref([
  { value: 'MM/DD/YYYY', label: 'MM/DD/YYYY (12/31/2024)' },
  { value: 'DD/MM/YYYY', label: 'DD/MM/YYYY (31/12/2024)' },
  { value: 'YYYY-MM-DD', label: 'YYYY-MM-DD (2024-12-31)' },
  { value: 'MMM DD, YYYY', label: 'MMM DD, YYYY (Dec 31, 2024)' }
])

// Load settings on mount
onMounted(async () => {
  await loadUserSettings()
  await loadThemes()
})

// Methods
const loadUserSettings = async () => {
  try {
    await settingsStore.loadUserSettings()

    // Set form values from store
    setValues({
      user_theme: settingsStore.userSettings.user_theme || 'ocean',
      items_per_page: settingsStore.userSettings.items_per_page || 25,
      user_country: settingsStore.userSettings.user_country || '',
      user_timezone: settingsStore.userSettings.user_timezone || '',
      date_format: settingsStore.userSettings.date_format || 'MM/DD/YYYY',
      notifications_enabled: settingsStore.userSettings.notifications_enabled ?? true,
      email_notifications: settingsStore.userSettings.email_notifications ?? true,
      push_notifications: settingsStore.userSettings.push_notifications ?? false
    })

    selectedCountry.value = settingsStore.userSettings.user_country || ''
  } catch (error) {
    console.error('Failed to load user settings:', error)
    errorMessage.value = 'Failed to load settings. Please try again.'
  }
}

const loadThemes = async () => {
  try {
    await settingsStore.loadThemes()
  } catch (error) {
    console.error('Failed to load themes:', error)
  }
}

const handleSaveSettings = async () => {
  isSaving.value = true
  errorMessage.value = ''
  showSuccess.value = false

  try {
    await settingsStore.updateUserSettings(values)

    showSuccess.value = true
    successMessage.value = 'Settings saved successfully!'

    // Auto-hide success message after 3 seconds
    setTimeout(() => {
      showSuccess.value = false
    }, 3000)
  } catch (error) {
    console.error('Failed to save settings:', error)
    errorMessage.value = error.response?.data?.message || 'Failed to save settings. Please try again.'
  } finally {
    isSaving.value = false
  }
}

const handleResetSettings = async () => {
  await loadUserSettings()
  errorMessage.value = ''
  showSuccess.value = false
}

const handleCountryChange = (countryCode) => {
  selectedCountry.value = countryCode
}
</script>
