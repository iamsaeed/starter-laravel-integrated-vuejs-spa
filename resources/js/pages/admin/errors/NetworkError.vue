<template>
  <ErrorPage
    error-code=""
    title="Network Error"
    description="Unable to connect to the admin server. Please check your internet connection."
    icon="alert-circle"
    variant="warning"
    :help-text="helpText"
  />
</template>

<script>
import ErrorPage from '@/components/common/ErrorPage.vue'

export default {
  name: 'AdminNetworkError',
  components: {
    ErrorPage,
  },
  setup() {
    const helpText = 'Check your network connection and try refreshing the page.'

    return {
      helpText,
    }
  },
}
</script>
