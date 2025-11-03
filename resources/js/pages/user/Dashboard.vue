<template>
  <div class="p-6">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Welcome back, {{ user?.name }}!</h1>
      <p class="text-gray-600 dark:text-gray-400 mt-1">Here's what's happening with your account</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <!-- Account Status Card -->
      <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Account Status</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">Active</p>
          </div>
          <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
            <Icon name="check-circle" class="w-6 h-6 text-green-600 dark:text-green-400" />
          </div>
        </div>
      </div>

      <!-- Profile Completion Card -->
      <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Profile</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">100%</p>
          </div>
          <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
            <Icon name="profile" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
          </div>
        </div>
      </div>

      <!-- Email Verified Card -->
      <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Email</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ user?.email_verified_at ? 'Verified' : 'Pending' }}</p>
          </div>
          <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
            <Icon name="mail" :class="user?.email_verified_at ? 'w-6 h-6 text-purple-600 dark:text-purple-400' : 'w-6 h-6 text-gray-400'" />
          </div>
        </div>
      </div>

      <!-- Member Since Card -->
      <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Member Since</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ formatDate(user?.created_at) }}</p>
          </div>
          <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
            <Icon name="calendar" class="w-6 h-6 text-orange-600 dark:text-orange-400" />
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700 mb-8">
      <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Actions</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <router-link
          :to="{ name: profileRoutes.personal }"
          class="flex items-center p-4 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-200"
        >
          <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center mr-3">
            <Icon name="profile" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
          </div>
          <div>
            <p class="font-medium text-gray-900 dark:text-white">Edit Profile</p>
            <p class="text-sm text-gray-600 dark:text-gray-400">Update your information</p>
          </div>
        </router-link>

        <router-link
          :to="{ name: profileRoutes.security }"
          class="flex items-center p-4 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-200"
        >
          <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center mr-3">
            <Icon name="lock" class="w-5 h-5 text-green-600 dark:text-green-400" />
          </div>
          <div>
            <p class="font-medium text-gray-900 dark:text-white">Security</p>
            <p class="text-sm text-gray-600 dark:text-gray-400">Change password</p>
          </div>
        </router-link>

        <router-link
          :to="{ name: settingsRoutes.appearance }"
          class="flex items-center p-4 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-200"
        >
          <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center mr-3">
            <Icon name="settings" class="w-5 h-5 text-purple-600 dark:text-purple-400" />
          </div>
          <div>
            <p class="font-medium text-gray-900 dark:text-white">Settings</p>
            <p class="text-sm text-gray-600 dark:text-gray-400">Customize your experience</p>
          </div>
        </router-link>
      </div>
    </div>

    <!-- Account Info -->
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
      <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Account Information</h2>
      <div class="space-y-3">
        <div class="flex justify-between py-2 border-b border-gray-200 dark:border-gray-700">
          <span class="text-gray-600 dark:text-gray-400">Name</span>
          <span class="font-medium text-gray-900 dark:text-white">{{ user?.name }}</span>
        </div>
        <div class="flex justify-between py-2 border-b border-gray-200 dark:border-gray-700">
          <span class="text-gray-600 dark:text-gray-400">Email</span>
          <span class="font-medium text-gray-900 dark:text-white">{{ user?.email }}</span>
        </div>
        <div class="flex justify-between py-2 border-b border-gray-200 dark:border-gray-700">
          <span class="text-gray-600 dark:text-gray-400">Role</span>
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
            {{ user?.role || 'User' }}
          </span>
        </div>
        <div class="flex justify-between py-2">
          <span class="text-gray-600 dark:text-gray-400">Member Since</span>
          <span class="font-medium text-gray-900 dark:text-white">{{ formatFullDate(user?.created_at) }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useContextRoutes } from '@/composables/useContextRoutes'
import Icon from '@/components/common/Icon.vue'

export default {
  name: 'UserDashboard',
  components: {
    Icon
  },
  setup() {
    const authStore = useAuthStore()
    const user = computed(() => authStore.user)
    const { profileRoutes, settingsRoutes } = useContextRoutes()

    const formatDate = (date) => {
      if (!date) return 'N/A'
      return new Date(date).getFullYear()
    }

    const formatFullDate = (date) => {
      if (!date) return 'N/A'
      return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      })
    }

    return {
      user,
      profileRoutes,
      settingsRoutes,
      formatDate,
      formatFullDate
    }
  }
}
</script>
