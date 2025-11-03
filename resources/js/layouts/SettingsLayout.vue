<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-5 lg:px-6">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Settings</h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
          Manage your preferences and application configuration
        </p>
      </div>

      <!-- Tabbed Navigation -->
      <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
        <!-- Tab Headers -->
        <div class="border-b border-gray-200 dark:border-gray-700">
          <nav class="flex -mb-px overflow-x-auto scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-600" aria-label="Tabs">
            <router-link
              v-for="tab in visibleTabs"
              :key="tab.name"
              :to="{ name: tab.route }"
              class="flex items-center whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors"
              :class="isActiveTab(tab.route)
                ? 'border-primary-500 text-primary-600 dark:text-primary-400'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:border-gray-600'"
            >
              <Icon :name="tab.icon" :size="20" class="mr-2" />
              {{ tab.label }}
              <span v-if="tab.badge" class="ml-2 px-2 py-0.5 text-xs font-medium rounded-full bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-300">
                {{ tab.badge }}
              </span>
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
import { useAuthStore } from '@/stores/auth'
import { useContextRoutes } from '@/composables/useContextRoutes'
import Icon from '@/components/common/Icon.vue'

const route = useRoute()
const authStore = useAuthStore()
const { settingsRoutes } = useContextRoutes()

// All available tabs with role requirements
const allTabs = computed(() => [
  {
    name: 'appearance',
    label: 'Appearance',
    route: settingsRoutes.value.appearance,
    icon: 'palette',
    roles: ['user', 'admin']
  },
  {
    name: 'notifications',
    label: 'Notifications',
    route: settingsRoutes.value.notifications,
    icon: 'bell',
    roles: ['user', 'admin']
  },
  {
    name: 'preferences',
    label: 'Preferences',
    route: settingsRoutes.value.preferences,
    icon: 'settings',
    roles: ['user', 'admin']
  },
  {
    name: 'global',
    label: 'Global Settings',
    route: settingsRoutes.value.global,
    icon: 'globe',
    roles: ['admin'],
    badge: 'Admin'
  },
  {
    name: 'system',
    label: 'System',
    route: settingsRoutes.value.system,
    icon: 'server',
    roles: ['admin'],
    badge: 'Admin'
  }
])

// Filter tabs based on user role
const visibleTabs = computed(() => {
  const userRole = authStore.user?.is_admin ? 'admin' : 'user'
  return allTabs.value.filter(tab => tab.roles.includes(userRole))
})

const isActiveTab = (routeName) => {
  return route.name === routeName
}
</script>
