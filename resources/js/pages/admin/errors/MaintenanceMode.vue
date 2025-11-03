<template>
  <ErrorPage
    error-code=""
    title="Under Maintenance"
    description="The admin panel is currently undergoing maintenance. We'll be back shortly."
    icon="cog"
    variant="info"
    :help-text="helpText"
  />
</template>

<script>
import ErrorPage from '@/components/common/ErrorPage.vue'

export default {
  name: 'AdminMaintenanceMode',
  components: {
    ErrorPage,
  },
  setup() {
    const helpText = 'Please check back in a few minutes. Urgent issues should be reported to the system administrator.'

    return {
      helpText,
    }
  },
}
</script>
