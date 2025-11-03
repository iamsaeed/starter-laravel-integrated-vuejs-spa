<template>
  <ErrorPage
    error-code="404"
    title="Page Not Found"
    description="The admin page you are looking for might have been removed, had its name changed, or is temporarily unavailable."
    icon="search"
    variant="warning"
    :help-text="helpText"
  />
</template>

<script>
import ErrorPage from '@/components/common/ErrorPage.vue'

export default {
  name: 'AdminNotFound',
  components: {
    ErrorPage,
  },
  setup() {
    const helpText = 'Double-check the URL for any typos, or use the admin navigation menu to find what you\'re looking for.'

    return {
      helpText,
    }
  },
}
</script>
