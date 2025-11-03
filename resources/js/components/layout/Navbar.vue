<template>
  <header class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-30 transition-colors duration-300">
    <div class="px-4 py-3 sm:px-6">
      <div class="flex items-center justify-between">
        <!-- Left Section -->
        <div class="flex items-center space-x-4">
          <!-- Desktop sidebar toggle -->
          <button
            @click="$emit('toggle-sidebar')"
            class="hidden lg:flex btn-ghost"
            aria-label="Toggle sidebar"
          >
            <Icon name="menu" :size="20" />
          </button>

          <!-- Mobile menu button -->
          <button
            @click="$emit('toggle-mobile-sidebar')"
            class="lg:hidden btn-ghost"
            aria-label="Toggle sidebar"
          >
            <Icon name="menu" :size="24" />
          </button>

          <!-- Page Name -->
          <div class="hidden sm:flex items-center">
            <span class="text-lg font-semibold capitalize text-title">
              {{ pageName }}
            </span>
          </div>
        </div>

        <!-- Right Section -->
        <div class="flex items-center space-x-2 sm:space-x-4">
          <!-- Search -->
          <div class="relative hidden md:block">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <Icon name="search" :size="16" class="text-muted" />
            </div>
            <input
              v-model="searchQuery"
              type="text"
              :placeholder="searchPlaceholder"
              class="block w-48 xl:w-64 pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-title placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent text-sm transition-all duration-200"
            >
          </div>

          <!-- Dark Mode Toggle -->
          <DarkModeToggle />

          <!-- Notifications -->
          <div class="relative">
            <button
              @click="toggleNotifications"
              class="btn-ghost"
              aria-label="View notifications"
            >
              <Icon name="bell" :size="24" />
              <span
                v-if="notificationCount > 0"
                class="absolute -top-1 -right-1 h-5 w-5 bg-red-500 dark:bg-red-600 text-white text-xs font-bold rounded-full flex items-center justify-center"
              >
                {{ notificationCount > 9 ? '9+' : notificationCount }}
              </span>
            </button>

            <!-- Notification dropdown (placeholder) -->
            <div
              v-if="showNotifications"
              class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-50"
              @click.stop
            >
              <div class="p-3 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-sm font-semibold text-title">Notifications</h3>
              </div>
              <div class="p-3 text-sm text-muted">
                {{ notificationCount > 0 ? `${notificationCount} new notifications` : 'No new notifications' }}
              </div>
            </div>
          </div>

          <!-- User Profile Dropdown -->
          <UserDropdown
            :user="user"
            @logout="$emit('logout')"
          />
        </div>
      </div>
    </div>
  </header>
</template>

<script>
import { ref, computed } from 'vue'
import { useRoute } from 'vue-router'
import Icon from '@/components/common/Icon.vue'
import UserDropdown from './UserDropdown.vue'
import DarkModeToggle from '@/components/common/DarkModeToggle.vue'

export default {
  name: 'Navbar',
  components: {
    Icon,
    UserDropdown,
    DarkModeToggle,
  },
  emits: ['toggle-sidebar', 'toggle-mobile-sidebar', 'logout', 'search'],
  props: {
    user: {
      type: Object,
      required: true,
    },
    notificationCount: {
      type: Number,
      default: 0,
    },
    searchPlaceholder: {
      type: String,
      default: 'Search...',
    },
  },
  setup(props, { emit }) {
    const route = useRoute()
    const searchQuery = ref('')
    const showNotifications = ref(false)

    const pageName = computed(() => {
      return route.meta?.title || 'Page Name'
    })

    const toggleNotifications = () => {
      showNotifications.value = !showNotifications.value
    }

    // Watch search query and emit search event
    const handleSearch = () => {
      emit('search', searchQuery.value)
    }

    return {
      searchQuery,
      showNotifications,
      pageName,
      toggleNotifications,
      handleSearch,
    }
  },
}
</script>