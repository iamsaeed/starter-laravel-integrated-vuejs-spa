<template>
  <div class="relative">
    <button
      @click="toggleDropdown"
      class="flex items-center space-x-2 p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200"
    >
      <div class="w-8 h-8 bg-gradient-to-r from-primary-500 to-secondary-500 dark:from-primary-600 dark:to-secondary-600 rounded-full flex items-center justify-center shadow-md">
        <span class="text-white font-semibold text-sm">{{ user.initials }}</span>
      </div>
      <div class="hidden sm:block text-left">
        <p class="text-sm font-medium text-title leading-tight">{{ user.name }}</p>
        <p class="text-xs text-muted">{{ user.role }}</p>
      </div>
      <Icon
        name="chevron-down"
        :size="16"
        class="text-muted transition-transform duration-200"
        :class="{ 'rotate-180': isOpen }"
      />
    </button>

    <!-- Profile dropdown -->
    <div
      v-if="isOpen"
      class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-50"
      @click.stop
    >
      <div class="py-2">
        <router-link
          :to="{ name: profileRoutes.personal }"
          @click="closeDropdown"
          class="block px-4 py-2 text-sm text-title hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200"
        >
          View Profile
        </router-link>
        <router-link
          :to="{ name: profileRoutes.security }"
          @click="closeDropdown"
          class="block px-4 py-2 text-sm text-title hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200"
        >
          Security
        </router-link>
        <router-link
          :to="{ name: settingsRoutes.appearance }"
          @click="closeDropdown"
          class="block px-4 py-2 text-sm text-title hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200"
        >
          Settings
        </router-link>
        <div class="border-t border-gray-200 dark:border-gray-700 my-1"></div>
        <button
          @click="handleLogout"
          class="block w-full text-left px-4 py-2 text-sm text-title hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200"
        >
          Sign out
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue'
import Icon from '@/components/common/Icon.vue'
import { useContextRoutes } from '@/composables/useContextRoutes'

export default {
  name: 'UserDropdown',
  components: {
    Icon,
  },
  emits: ['logout'],
  props: {
    user: {
      type: Object,
      required: true,
      // Expected: { name: String, role: String, initials: String }
    },
  },
  setup(props, { emit }) {
    const isOpen = ref(false)
    const { profileRoutes, settingsRoutes } = useContextRoutes()

    const toggleDropdown = () => {
      isOpen.value = !isOpen.value
    }

    const closeDropdown = () => {
      isOpen.value = false
    }

    const handleLogout = () => {
      closeDropdown()
      emit('logout')
    }

    return {
      isOpen,
      profileRoutes,
      settingsRoutes,
      toggleDropdown,
      closeDropdown,
      handleLogout,
    }
  },
}
</script>