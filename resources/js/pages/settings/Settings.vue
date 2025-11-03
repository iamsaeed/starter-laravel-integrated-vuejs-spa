<template>
  <div class="card min-h-[80vh]">
    <div class="p-8">
      <!-- Page Header -->
      <div class="mb-6">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center">
            <div class="w-12 h-12 bg-gradient-to-br from-primary-500 to-secondary-500 rounded-full flex items-center justify-center mr-4">
              <Icon name="settings" :size="24" class="text-white" />
            </div>
            <div>
              <h1 class="text-3xl font-bold text-title">Global Settings</h1>
              <p class="text-subtitle">Configure application-wide settings and defaults</p>
            </div>
          </div>

          <!-- Admin Badge -->
          <div class="bg-primary-100 dark:bg-primary-900 text-primary-800 dark:text-primary-200 px-3 py-1 rounded-full text-sm font-medium">
            Admin Only
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
        <!-- Application Settings Group -->
        <SettingGroup
          title="Application"
          description="General application settings and configuration"
          :collapsible="true"
        >
          <FormGroup>
            <FormLabel for-id="app_name" :required="true">
              Application Name
            </FormLabel>
            <FormInput
              id="app_name"
              name="app_name"
              type="text"
              placeholder="Enter application name"
              rules="required|min:3"
            />
            <FormHelpText text="The name of your application" />
          </FormGroup>

          <FormGroup>
            <FormLabel for-id="app_url">
              Application URL
            </FormLabel>
            <FormInput
              id="app_url"
              name="app_url"
              type="url"
              placeholder="https://example.com"
            />
            <FormHelpText text="The base URL of your application" />
          </FormGroup>

          <FormGroup>
            <FormLabel for-id="default_items_per_page">
              Default Items Per Page
            </FormLabel>
            <SelectInput
              id="default_items_per_page"
              name="default_items_per_page"
              :options="itemsPerPageOptions"
              placeholder="Select default items per page"
            />
            <FormHelpText text="Default number of items per page for new users" />
          </FormGroup>
        </SettingGroup>

        <!-- Security Settings Group -->
        <SettingGroup
          title="Security"
          description="Security and authentication settings"
          :collapsible="true"
        >
          <FormGroup>
            <CheckboxInput
              id="require_email_verification"
              name="require_email_verification"
              label="Require email verification"
            />
            <FormHelpText text="Require users to verify their email address before accessing the application" />
          </FormGroup>

          <FormGroup>
            <CheckboxInput
              id="enable_two_factor"
              name="enable_two_factor"
              label="Enable two-factor authentication"
            />
            <FormHelpText text="Allow users to enable two-factor authentication for their accounts" />
          </FormGroup>

          <FormGroup>
            <FormLabel for-id="session_lifetime">
              Session Lifetime (minutes)
            </FormLabel>
            <FormInput
              id="session_lifetime"
              name="session_lifetime"
              type="number"
              placeholder="120"
              rules="numeric|min_value:5"
            />
            <FormHelpText text="How long user sessions should remain active (in minutes)" />
          </FormGroup>
        </SettingGroup>

        <!-- Email Settings Group -->
        <SettingGroup
          title="Email"
          description="Email configuration and notification settings"
          :collapsible="true"
        >
          <FormGroup>
            <FormLabel for-id="mail_from_address">
              From Email Address
            </FormLabel>
            <FormInput
              id="mail_from_address"
              name="mail_from_address"
              type="email"
              placeholder="noreply@example.com"
              rules="email"
            />
            <FormHelpText text="The email address that system emails will be sent from" />
          </FormGroup>

          <FormGroup>
            <FormLabel for-id="mail_from_name">
              From Name
            </FormLabel>
            <FormInput
              id="mail_from_name"
              name="mail_from_name"
              type="text"
              placeholder="Application Name"
            />
            <FormHelpText text="The name that will appear in system emails" />
          </FormGroup>

          <FormGroup>
            <CheckboxInput
              id="enable_notifications"
              name="enable_notifications"
              label="Enable email notifications globally"
            />
            <FormHelpText text="Enable or disable email notifications for all users" />
          </FormGroup>
        </SettingGroup>

        <!-- Localization Settings Group -->
        <SettingGroup
          title="Localization"
          description="Default localization settings for new users"
          :collapsible="true"
        >
          <FormGroup>
            <FormLabel for-id="default_timezone">
              Default Timezone
            </FormLabel>
            <TimezoneSelect
              id="default_timezone"
              name="default_timezone"
              label=""
              placeholder="Select default timezone"
              :require-country="false"
              :show-preview="true"
              help-text="The default timezone for new users"
            />
          </FormGroup>

          <FormGroup>
            <FormLabel for-id="default_date_format">
              Default Date Format
            </FormLabel>
            <SelectInput
              id="default_date_format"
              name="default_date_format"
              :options="dateFormatOptions"
              placeholder="Select default date format"
            />
            <FormHelpText text="The default date format for new users" />
          </FormGroup>

          <FormGroup>
            <FormLabel for-id="default_language">
              Default Language
            </FormLabel>
            <SelectInput
              id="default_language"
              name="default_language"
              :options="languageOptions"
              placeholder="Select default language"
            />
            <FormHelpText text="The default language for new users" />
          </FormGroup>
        </SettingGroup>

        <!-- Appearance Settings Group -->
        <SettingGroup
          title="Appearance"
          description="Default appearance settings for new users"
          :collapsible="true"
        >
          <FormGroup>
            <FormLabel for-id="default_theme">
              Default Theme
            </FormLabel>
            <SelectInput
              id="default_theme"
              name="default_theme"
              :options="themeOptions"
              placeholder="Select default theme"
            />
            <FormHelpText text="The default theme for new users" />
          </FormGroup>

          <FormGroup>
            <CheckboxInput
              id="allow_theme_change"
              name="allow_theme_change"
              label="Allow users to change theme"
            />
            <FormHelpText text="Allow users to customize their theme preference" />
          </FormGroup>
        </SettingGroup>
      </SettingsForm>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useForm } from 'vee-validate'
import { useSettingsStore } from '@/stores/settings'
import Icon from '@/components/common/Icon.vue'
import SettingsForm from '@/components/settings/SettingsForm.vue'
import SettingGroup from '@/components/settings/SettingGroup.vue'
import TimezoneSelect from '@/components/settings/TimezoneSelect.vue'
import FormGroup from '@/components/form/FormGroup.vue'
import FormLabel from '@/components/form/FormLabel.vue'
import FormInput from '@/components/form/FormInput.vue'
import FormHelpText from '@/components/form/FormHelpText.vue'
import SelectInput from '@/components/form/SelectInput.vue'
import CheckboxInput from '@/components/form/CheckboxInput.vue'

const settingsStore = useSettingsStore()

// Form setup
const { values, setValues, meta } = useForm({
  initialValues: {
    app_name: '',
    app_url: '',
    default_items_per_page: 25,
    require_email_verification: false,
    enable_two_factor: false,
    session_lifetime: 120,
    mail_from_address: '',
    mail_from_name: '',
    enable_notifications: true,
    default_timezone: '',
    default_date_format: 'MM/DD/YYYY',
    default_language: 'en',
    default_theme: 'default',
    allow_theme_change: true
  }
})

// State
const isSaving = ref(false)
const showSuccess = ref(false)
const successMessage = ref('Settings saved successfully!')
const errorMessage = ref('')

// Computed
const isDirty = computed(() => meta.value.dirty)

// Options
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

const languageOptions = ref([
  { value: 'en', label: 'English' },
  { value: 'es', label: 'Spanish' },
  { value: 'fr', label: 'French' },
  { value: 'de', label: 'German' }
])

// Load settings on mount
onMounted(async () => {
  await loadGlobalSettings()
  await loadThemes()
})

// Methods
const loadGlobalSettings = async () => {
  try {
    await settingsStore.loadGlobalSettings()

    // Set form values from store
    setValues({
      app_name: settingsStore.globalSettings.app_name || '',
      app_url: settingsStore.globalSettings.app_url || '',
      default_items_per_page: settingsStore.globalSettings.default_items_per_page || 25,
      require_email_verification: settingsStore.globalSettings.require_email_verification ?? false,
      enable_two_factor: settingsStore.globalSettings.enable_two_factor ?? false,
      session_lifetime: settingsStore.globalSettings.session_lifetime || 120,
      mail_from_address: settingsStore.globalSettings.mail_from_address || '',
      mail_from_name: settingsStore.globalSettings.mail_from_name || '',
      enable_notifications: settingsStore.globalSettings.enable_notifications ?? true,
      default_timezone: settingsStore.globalSettings.default_timezone || '',
      default_date_format: settingsStore.globalSettings.default_date_format || 'MM/DD/YYYY',
      default_language: settingsStore.globalSettings.default_language || 'en',
      default_theme: settingsStore.globalSettings.default_theme || 'default',
      allow_theme_change: settingsStore.globalSettings.allow_theme_change ?? true
    })
  } catch (error) {
    console.error('Failed to load global settings:', error)
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
    // Save each setting individually (or bulk update if API supports it)
    for (const [key, value] of Object.entries(values)) {
      await settingsStore.updateGlobalSetting(key, value)
    }

    showSuccess.value = true
    successMessage.value = 'Global settings saved successfully!'

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
  await loadGlobalSettings()
  errorMessage.value = ''
  showSuccess.value = false
}
</script>
