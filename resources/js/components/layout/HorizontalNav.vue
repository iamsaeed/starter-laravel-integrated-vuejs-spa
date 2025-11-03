<template>
  <nav class="fixed top-0 left-0 right-0 z-40 bg-gradient-to-r from-primary-600 to-secondary-600 dark:from-primary-800 dark:to-secondary-800 border-b border-white/10">
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">
        <!-- Logo -->
        <div class="flex items-center space-x-3">
          <div class="w-8 h-8 bg-white dark:bg-gray-100 rounded-lg flex items-center justify-center">
            <Icon name="shield" class="w-5 h-5 text-primary-600" />
          </div>
          <span class="text-xl font-bold text-white">{{ logoTitle }}</span>
        </div>

        <!-- Horizontal Menu - Desktop -->
        <div class="hidden md:flex items-center space-x-1">
          <router-link
            v-for="item in menuItems"
            :key="item.to.name"
            :to="item.to"
            class="flex items-center px-4 py-2 rounded-lg text-white/80 hover:text-white hover:bg-white/10 transition-all duration-200 text-sm font-medium"
            active-class="bg-white/20"
          >
            <Icon :name="item.icon" :size="18" />
            <span class="ml-2">{{ item.label }}</span>
          </router-link>
        </div>

        <!-- Right Actions -->
        <div class="flex items-center space-x-4">
          <!-- Dark Mode Toggle -->
          <DarkModeToggle />

          <!-- User Dropdown -->
          <div class="relative">
            <button
              @click="toggleUserMenu"
              class="flex items-center space-x-2 px-3 py-2 rounded-lg text-white hover:bg-white/10 transition-all duration-200"
            >
              <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center">
                <span class="text-sm font-semibold text-white">{{ user.initials }}</span>
              </div>
              <Icon name="chevron-down" :size="16" />
            </button>

            <!-- Dropdown Menu -->
            <div
              v-show="isUserMenuOpen"
              @click="isUserMenuOpen = false"
              class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg py-1 border border-gray-200 dark:border-gray-700"
            >
              <router-link
                :to="{ name: profileRoutes.personal }"
                class="flex items-center space-x-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-150"
              >
                <Icon name="user" :size="16" />
                <span>Profile</span>
              </router-link>
              <router-link
                :to="{ name: settingsRoutes.appearance }"
                class="flex items-center space-x-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-150"
              >
                <Icon name="settings" :size="16" />
                <span>Settings</span>
              </router-link>
              <button
                @click="$emit('logout')"
                class="flex items-center space-x-2 px-4 py-2 text-sm w-full text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-150"
              >
                <Icon name="logout" :size="16" />
                <span>Logout</span>
              </button>
            </div>
          </div>

          <!-- Mobile Menu Button -->
          <button
            @click="toggleMobileMenu"
            class="md:hidden p-2 rounded-lg text-white hover:bg-white/10"
          >
            <Icon :name="isMobileMenuOpen ? 'close' : 'menu'" :size="24" />
          </button>
        </div>
      </div>

      <!-- Mobile Menu -->
      <div
        v-show="isMobileMenuOpen"
        class="md:hidden py-4 space-y-1 border-t border-white/10"
      >
        <router-link
          v-for="item in menuItems"
          :key="item.to.name"
          :to="item.to"
          @click="isMobileMenuOpen = false"
          class="flex items-center px-4 py-2.5 rounded-lg text-white/80 hover:text-white hover:bg-white/10 transition-all duration-200"
          active-class="bg-white/20"
        >
          <Icon :name="item.icon" :size="18" />
          <span class="ml-3">{{ item.label }}</span>
        </router-link>
      </div>
    </div>
  </nav>
</template>

<script>
import { ref } from 'vue'
import Icon from '@/components/common/Icon.vue'
import DarkModeToggle from '@/components/common/DarkModeToggle.vue'
import { useContextRoutes } from '@/composables/useContextRoutes'

export default {
  name: 'HorizontalNav',
  components: {
    Icon,
    DarkModeToggle
  },
  props: {
    user: {
      type: Object,
      required: true
    },
    notificationCount: {
      type: Number,
      default: 0
    },
    menuItems: {
      type: Array,
      default: () => [],
    },
    logoTitle: {
      type: String,
      default: 'Admin Panel',
    },
  },
  emits: ['logout', 'search'],
  setup() {
    const isUserMenuOpen = ref(false)
    const isMobileMenuOpen = ref(false)
    const { profileRoutes, settingsRoutes } = useContextRoutes()

    const toggleUserMenu = () => {
      isUserMenuOpen.value = !isUserMenuOpen.value
    }

    const toggleMobileMenu = () => {
      isMobileMenuOpen.value = !isMobileMenuOpen.value
    }

    return {
      isUserMenuOpen,
      isMobileMenuOpen,
      profileRoutes,
      settingsRoutes,
      toggleUserMenu,
      toggleMobileMenu
    }
  }
}
</script>
