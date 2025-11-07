<template>
  <div
    v-if="isImpersonating"
    class="bg-warning-600 text-white px-4 py-3 flex items-center justify-between shadow-lg sticky top-0 z-50"
  >
    <div class="flex items-center gap-3">
      <Icon name="warning" :size="20" />
      <div class="text-sm">
        <span class="font-semibold">Impersonating:</span>
        <span class="ml-2">{{ currentUser?.name }} ({{ currentUser?.email }})</span>
        <span class="ml-3 opacity-80">|</span>
        <span class="ml-3">Original Admin: {{ impersonationData?.admin?.name }}</span>
      </div>
    </div>

    <button
      @click="handleStopImpersonating"
      :disabled="loading"
      class="px-4 py-1.5 bg-white text-warning-700 hover:bg-warning-50 rounded font-medium text-sm transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
    >
      <Icon v-if="loading" name="loading" :size="16" class="animate-spin" />
      <span>{{ loading ? 'Stopping...' : 'Stop Impersonating' }}</span>
    </button>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { impersonationService } from '@/services/impersonationService'
import { useToast } from '@/composables/useToast'
import Icon from '@/components/common/Icon.vue'

const router = useRouter()
const authStore = useAuthStore()
const { showToast } = useToast()

const loading = ref(false)

// Use cached impersonation status from auth store
const isImpersonating = computed(() => {
  return authStore.impersonationStatus?.is_impersonating || false
})

const impersonationData = computed(() => authStore.impersonationStatus)

const currentUser = computed(() => authStore.user)

const handleStopImpersonating = async () => {
  loading.value = true
  try {
    await impersonationService.stopImpersonating()

    // Refresh user data (this will also update impersonation status)
    await authStore.fetchUser()

    showToast({
      message: 'Successfully stopped impersonating.',
      type: 'success',
    })

    // Redirect to admin users page
    router.push({ name: 'admin.users' })
  } catch (error) {
    console.error('Failed to stop impersonating:', error)
    showToast({
      message: error.response?.data?.message || 'Failed to stop impersonating.',
      type: 'error',
    })
  } finally {
    loading.value = false
  }
}
</script>
