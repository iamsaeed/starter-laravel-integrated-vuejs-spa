<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <div>
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">System Settings</h2>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Configure system-level settings and advanced options
          </p>
        </div>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
          <Icon name="shield" :size="14" class="mr-1" />
          Admin Only
        </span>
      </div>
    </div>

    <!-- Info Message -->
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
      <div class="flex">
        <Icon name="info-circle" :size="20" class="text-blue-400 mr-3 flex-shrink-0 mt-0.5" />
        <div class="text-sm text-blue-700 dark:text-blue-300">
          <p class="font-medium">System-level settings</p>
          <p class="mt-1">These settings affect the core functionality of the application. Changes should be made carefully.</p>
        </div>
      </div>
    </div>

    <!-- System Configuration -->
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
      <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
          <Icon name="server" :size="20" class="mr-2" />
          System Information
        </h3>
      </div>

      <div class="p-6">
        <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
          <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Application Version</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-white font-mono">v1.0.0</dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Environment</dt>
            <dd class="mt-1">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                Production
              </span>
            </dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">PHP Version</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-white font-mono">8.2.28</dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Laravel Version</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-white font-mono">12.x</dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Database</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-white font-mono">MySQL</dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Cache Driver</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-white font-mono">Redis</dd>
          </div>
        </dl>
      </div>
    </div>

    <!-- Cache Management -->
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
      <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
          <Icon name="database" :size="20" class="mr-2" />
          Cache Management
        </h3>
      </div>

      <div class="p-6 space-y-4">
        <p class="text-sm text-gray-600 dark:text-gray-400">
          Clear application caches to ensure fresh data or resolve performance issues.
        </p>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <button
            type="button"
            @click="clearCache('application')"
            class="form-button-secondary inline-flex items-center"
            :disabled="isClearing"
          >
            <Icon name="refresh" :size="18" class="mr-2" />
            Clear Application Cache
          </button>

          <button
            type="button"
            @click="clearCache('route')"
            class="form-button-secondary inline-flex items-center"
            :disabled="isClearing"
          >
            <Icon name="refresh" :size="18" class="mr-2" />
            Clear Route Cache
          </button>

          <button
            type="button"
            @click="clearCache('config')"
            class="form-button-secondary inline-flex items-center"
            :disabled="isClearing"
          >
            <Icon name="refresh" :size="18" class="mr-2" />
            Clear Config Cache
          </button>

          <button
            type="button"
            @click="clearCache('view')"
            class="form-button-secondary inline-flex items-center"
            :disabled="isClearing"
          >
            <Icon name="refresh" :size="18" class="mr-2" />
            Clear View Cache
          </button>
        </div>
      </div>
    </div>

    <!-- Database Management -->
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
      <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
          <Icon name="database" :size="20" class="mr-2" />
          Database Management
        </h3>
      </div>

      <div class="p-6">
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 mb-4">
          <div class="flex">
            <Icon name="alert-triangle" :size="20" class="text-yellow-400 mr-3 flex-shrink-0 mt-0.5" />
            <div class="text-sm text-yellow-700 dark:text-yellow-300">
              <p class="font-medium">Warning</p>
              <p class="mt-1">Database operations can affect your application data. Proceed with caution.</p>
            </div>
          </div>
        </div>

        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-900 dark:text-white">Optimize Database</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Run database optimization queries</p>
          </div>
          <button
            type="button"
            @click="optimizeDatabase"
            class="form-button-secondary inline-flex items-center"
            :disabled="isOptimizing"
          >
            <Icon v-if="isOptimizing" name="spinner" :size="18" class="mr-2 animate-spin" />
            <Icon v-else name="zap" :size="18" class="mr-2" />
            {{ isOptimizing ? 'Optimizing...' : 'Optimize' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Success Message -->
    <div
      v-if="showSuccess"
      class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg flex items-center animate-fade-in"
    >
      <Icon name="check-circle" :size="20" class="mr-2" />
      {{ successMessage }}
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import Icon from '@/components/common/Icon.vue'

const isClearing = ref(false)
const isOptimizing = ref(false)
const showSuccess = ref(false)
const successMessage = ref('')

// Clear cache
const clearCache = async (type) => {
  isClearing.value = true
  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1000))

    successMessage.value = `${type.charAt(0).toUpperCase() + type.slice(1)} cache cleared successfully`
    showSuccess.value = true
    setTimeout(() => {
      showSuccess.value = false
    }, 3000)
  } catch (error) {
    console.error('Failed to clear cache:', error)
  } finally {
    isClearing.value = false
  }
}

// Optimize database
const optimizeDatabase = async () => {
  isOptimizing.value = true
  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 2000))

    successMessage.value = 'Database optimized successfully'
    showSuccess.value = true
    setTimeout(() => {
      showSuccess.value = false
    }, 3000)
  } catch (error) {
    console.error('Failed to optimize database:', error)
  } finally {
    isOptimizing.value = false
  }
}
</script>

<style scoped>
@keyframes fade-in {
  from {
    opacity: 0;
    transform: translateY(1rem);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in {
  animation: fade-in 0.3s ease-out;
}
</style>
