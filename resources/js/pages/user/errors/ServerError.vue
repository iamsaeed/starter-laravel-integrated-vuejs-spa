<template>
  <ErrorPage
    error-code="500"
    title="Server Error"
    description="Something went wrong on our end. We're working to fix it."
    icon="alert-triangle"
    variant="danger"
    :help-text="helpText"
  />
</template>

<script>
import ErrorPage from '@/components/common/ErrorPage.vue'

export default {
  name: 'UserServerError',
  components: {
    ErrorPage,
  },
  setup() {
    const helpText = 'Please try again in a few moments. If the problem persists, contact support.'

    return {
      helpText,
    }
  },
}
</script>
