<template>
  <ErrorPage
    error-code="403"
    title="Access Forbidden"
    description="You don't have permission to access this admin resource."
    icon="shield-exclamation"
    variant="danger"
    :help-text="helpText"
  />
</template>

<script>
import ErrorPage from '@/components/common/ErrorPage.vue'

export default {
  name: 'AdminForbidden',
  components: {
    ErrorPage,
  },
  setup() {
    const helpText = 'If you believe you should have access to this resource, please contact your system administrator.'

    return {
      helpText,
    }
  },
}
</script>
