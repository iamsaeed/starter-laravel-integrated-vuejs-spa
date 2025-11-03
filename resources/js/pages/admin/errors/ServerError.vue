<template>
  <ErrorPage
    error-code="500"
    title="Server Error"
    description="Something went wrong on the admin server. Our team has been notified."
    icon="alert-triangle"
    variant="danger"
    :help-text="helpText"
  />
</template>

<script>
import ErrorPage from '@/components/common/ErrorPage.vue'

export default {
  name: 'AdminServerError',
  components: {
    ErrorPage,
  },
  setup() {
    const helpText = 'Please try again in a few moments. If the problem persists, contact the system administrator.'

    return {
      helpText,
    }
  },
}
</script>
