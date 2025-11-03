<template>
  <ErrorPage
    error-code=""
    title="Under Maintenance"
    description="We're currently performing maintenance. We'll be back shortly."
    icon="cog"
    variant="info"
    :help-text="helpText"
  />
</template>

<script>
import ErrorPage from '@/components/common/ErrorPage.vue'

export default {
  name: 'UserMaintenanceMode',
  components: {
    ErrorPage,
  },
  setup() {
    const helpText = 'Please check back in a few minutes. Thank you for your patience!'

    return {
      helpText,
    }
  },
}
</script>
