<template>
  <FormGroup>
    <FormLabel :for-id="id" :required="required">
      {{ label }}
    </FormLabel>

    <VirtualSelectInput
      :id="id"
      :name="name"
      :options="timezoneOptions"
      :placeholder="placeholder"
      :disabled="disabled || isLoading || (requireCountry && !countryCode)"
      :rules="rules"
      :help-text="computedHelpText"
      @select="handleTimezoneSelect"
    />

    <!-- Current time preview -->
    <div v-if="showPreview && selectedTimezone" class="mt-2 text-sm text-gray-600 dark:text-gray-400">
      <span class="font-medium">Current time:</span> {{ currentTimeInTimezone }}
    </div>
  </FormGroup>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useSettingsStore } from '@/stores/settings'
import FormGroup from '@/components/form/FormGroup.vue'
import FormLabel from '@/components/form/FormLabel.vue'
import VirtualSelectInput from '@/components/form/VirtualSelectInput.vue'

const props = defineProps({
  id: {
    type: String,
    default: 'timezone'
  },
  name: {
    type: String,
    default: 'user_timezone'
  },
  label: {
    type: String,
    default: 'Timezone'
  },
  placeholder: {
    type: String,
    default: 'Select your timezone'
  },
  required: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  rules: {
    type: [String, Object, Function],
    default: null
  },
  helpText: {
    type: String,
    default: ''
  },
  countryCode: {
    type: String,
    default: null
  },
  requireCountry: {
    type: Boolean,
    default: false
  },
  showPreview: {
    type: Boolean,
    default: true
  },
  autoLoad: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['select', 'change'])

const settingsStore = useSettingsStore()
const isLoading = ref(false)
const selectedTimezone = ref(null)
const currentTime = ref(new Date())

// Update current time every minute
let timeInterval = null
onMounted(() => {
  timeInterval = setInterval(() => {
    currentTime.value = new Date()
  }, 60000) // Update every minute
})

onUnmounted(() => {
  if (timeInterval) {
    clearInterval(timeInterval)
  }
})

// Transform timezones from store into options format
const timezoneOptions = computed(() => {
  let timezones = settingsStore.timezones

  // Filter by country if provided
  if (props.countryCode && timezones.length > 0) {
    const countryId = settingsStore.countries.find(
      c => c.code === props.countryCode
    )?.id

    if (countryId) {
      timezones = timezones.filter(tz => tz.country_id === countryId)
    }
  }

  // Sort primary timezone first, then alphabetically
  const sorted = [...timezones].sort((a, b) => {
    if (a.is_primary && !b.is_primary) return -1
    if (!a.is_primary && b.is_primary) return 1
    return a.display_name.localeCompare(b.display_name)
  })

  return sorted.map(tz => ({
    value: tz.id,
    label: tz.display_name,
    description: `${tz.timezone} (UTC ${tz.offset})`,
    data: tz
  }))
})

// Computed help text
const computedHelpText = computed(() => {
  if (props.requireCountry && !props.countryCode) {
    return 'Please select a country first'
  }
  return props.helpText
})

// Format current time in selected timezone
const currentTimeInTimezone = computed(() => {
  if (!selectedTimezone.value?.data) return ''

  try {
    const formatter = new Intl.DateTimeFormat('en-US', {
      timeZone: selectedTimezone.value.data.timezone,
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit',
      hour12: true
    })
    return formatter.format(currentTime.value)
  } catch (error) {
    console.error('Error formatting timezone:', error)
    return ''
  }
})

// Load timezones on mount
onMounted(async () => {
  if (props.autoLoad && settingsStore.timezones.length === 0) {
    await loadTimezones()
  }
})

// Watch for country changes
watch(() => props.countryCode, async (newCountryCode) => {
  if (newCountryCode && props.requireCountry) {
    await loadTimezones()
  }
})

// Methods
const loadTimezones = async () => {
  isLoading.value = true
  try {
    await settingsStore.loadTimezones()
  } catch (error) {
    console.error('Failed to load timezones:', error)
  } finally {
    isLoading.value = false
  }
}

const handleTimezoneSelect = (timezone) => {
  selectedTimezone.value = timezone
  emit('select', timezone)
  emit('change', timezone.value)
}

// Expose methods for parent components
defineExpose({
  loadTimezones,
  isLoading,
  selectedTimezone
})
</script>
