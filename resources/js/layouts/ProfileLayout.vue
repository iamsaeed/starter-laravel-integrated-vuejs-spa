<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="max-w-5xl mx-auto py-6 px-4 sm:px-5 lg:px-6">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Profile Settings</h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
          Manage your account information and security settings
        </p>
      </div>

      <!-- Tabbed Navigation -->
      <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
        <!-- Tab Headers -->
        <div class="border-b border-gray-200 dark:border-gray-700">
          <nav class="flex -mb-px overflow-x-auto" aria-label="Tabs">
            <router-link
              v-for="tab in tabs"
              :key="tab.name"
              :to="{ name: tab.route }"
              class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors"
              :class="isActiveTab(tab.route)
                ? 'border-primary-500 text-primary-600 dark:text-primary-400'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:border-gray-600'"
            >
              <Icon :name="tab.icon" :size="20" class="mr-2" />
              {{ tab.label }}
            </router-link>
          </nav>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
          <router-view />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useContextRoutes } from '@/composables/useContextRoutes'
import Icon from '@/components/common/Icon.vue'

const route = useRoute()
const { profileRoutes } = useContextRoutes()

const tabs = computed(() => [
  {
    name: 'personal',
    label: 'Personal Information',
    route: profileRoutes.value.personal,
    icon: 'profile'
  },
  {
    name: 'security',
    label: 'Security',
    route: profileRoutes.value.security,
    icon: 'lock'
  }
])

const isActiveTab = (routeName) => {
  return route.name === routeName
}
</script>

