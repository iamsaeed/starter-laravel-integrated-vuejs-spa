<template>
  <ErrorPage
    error-code="401"
    title="Unauthorized"
    description="You need to be logged in to access this resource."
    icon="lock"
    variant="warning"
    :help-text="helpText"
  />
</template>

<script>
import ErrorPage from '@/components/common/ErrorPage.vue'

export default {
  name: 'UserUnauthorized',
  components: {
    ErrorPage,
  },
  setup() {
    const helpText = 'Please log in to continue accessing your workspace.'

    return {
      helpText,
    }
  },
}
</script>
