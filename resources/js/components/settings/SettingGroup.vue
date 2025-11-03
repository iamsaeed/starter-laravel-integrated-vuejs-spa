<template>
  <FormSection
    :title="title"
    :description="description"
    :collapsible="collapsible"
    :collapsed="collapsed"
    :show-divider="showDivider"
    @toggle="handleToggle"
  >
    <div class="space-y-6">
      <slot />
    </div>
  </FormSection>
</template>

<script setup>
import FormSection from '@/components/form/FormSection.vue'

defineProps({
  title: {
    type: String,
    required: true
  },
  description: {
    type: String,
    default: ''
  },
  collapsible: {
    type: Boolean,
    default: false
  },
  collapsed: {
    type: Boolean,
    default: false
  },
  showDivider: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['toggle'])

const handleToggle = (isCollapsed) => {
  emit('toggle', isCollapsed)
}
</script>
