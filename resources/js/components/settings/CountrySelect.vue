<template>
  <FormGroup>
    <FormLabel :for-id="id" :required="required">
      {{ label }}
    </FormLabel>

    <VirtualSelectInput
      :id="id"
      :name="name"
      :options="countryOptions"
      :placeholder="placeholder"
      :disabled="disabled || isLoading"
      :rules="rules"
      :help-text="helpText"
      @select="handleCountrySelect"
    />
  </FormGroup>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useSettingsStore } from '@/stores/settings'
import FormGroup from '@/components/form/FormGroup.vue'
import FormLabel from '@/components/form/FormLabel.vue'
import VirtualSelectInput from '@/components/form/VirtualSelectInput.vue'

const props = defineProps({
  id: {
    type: String,
    default: 'country'
  },
  name: {
    type: String,
    default: 'user_country'
  },
  label: {
    type: String,
    default: 'Country'
  },
  placeholder: {
    type: String,
    default: 'Select your country'
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
  region: {
    type: String,
    default: null
  },
  autoLoad: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['select', 'change'])

const settingsStore = useSettingsStore()
const isLoading = ref(false)

// Transform countries from store into options format
const countryOptions = computed(() => {
  return settingsStore.countries.map(country => ({
    value: country.code,
    label: country.name,
    description: country.region || undefined,
    icon: country.flag || undefined
  }))
})

// Load countries on mount
onMounted(async () => {
  if (props.autoLoad && settingsStore.countries.length === 0) {
    await loadCountries()
  }
})

// Watch for region changes
watch(() => props.region, async (newRegion) => {
  if (newRegion) {
    await loadCountries(newRegion)
  }
})

// Methods
const loadCountries = async (region = null) => {
  isLoading.value = true
  try {
    await settingsStore.loadCountries()
  } catch (error) {
    console.error('Failed to load countries:', error)
  } finally {
    isLoading.value = false
  }
}

const handleCountrySelect = (country) => {
  emit('select', country)
  emit('change', country.value)
}

// Expose methods for parent components
defineExpose({
  loadCountries,
  isLoading
})
</script>
