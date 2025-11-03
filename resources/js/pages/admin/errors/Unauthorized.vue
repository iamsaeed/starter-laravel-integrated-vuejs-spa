<template>
  <ErrorPage
    error-code="401"
    title="Unauthorized"
    description="You need to be logged in to access this admin resource."
    icon="lock"
    variant="warning"
    :help-text="helpText"
  />
</template>

<script>
import ErrorPage from '@/components/common/ErrorPage.vue'

export default {
  name: 'AdminUnauthorized',
  components: {
    ErrorPage,
  },
  setup() {
    const helpText = 'Please log in with your admin credentials to continue.'

    return {
      helpText,
    }
  },
}
</script>
