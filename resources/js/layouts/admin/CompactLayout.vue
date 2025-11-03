<template>
  <div class="page-container font-sans">
    <!-- Compact Sidebar (always icon-only, 64px width) -->
    <CompactSidebar
      :menu-items="allMenuItems"
      @nav-click="handleNavClick"
      @logout="logout"
    />

    <!-- Main Content -->
    <div class="content-area pl-16">
      <!-- Impersonation Banner -->
      <ImpersonationBanner ref="impersonationBanner" />

      <!-- Top Navbar -->
      <Navbar
        :user="user"
        :notification-count="notificationCount"
        :hide-toggle="true"
        @logout="logout"
        @search="handleSearch"
      />

      <!-- Main Content Area -->
      <main class="p-4 sm:p-5 lg:p-6">
        <router-view />
      </main>
    </div>

    <!-- Confirmation Dialog Container -->
    <ConfirmDialogContainer />
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import CompactSidebar from '@/components/layout/CompactSidebar.vue'
import Navbar from '@/components/layout/Navbar.vue'
import ImpersonationBanner from '@/components/common/ImpersonationBanner.vue'
import ConfirmDialogContainer from '@/components/common/ConfirmDialogContainer.vue'
import { useAuthStore } from '@/stores/auth'
import { adminMainMenuItems, getAdminMoreMenuItems, userMainMenuItems, getUserMoreMenuItems } from '@/config/menuItems'

export default {
  name: 'CompactLayout',
  components: {
    CompactSidebar,
    Navbar,
    ImpersonationBanner,
    ConfirmDialogContainer,
  },
  setup() {
    const router = useRouter()
    const authStore = useAuthStore()

    // Determine menu items based on current route
    const currentPath = computed(() => router.currentRoute.value.path)
    const isAdminPanel = computed(() => currentPath.value.startsWith('/admin'))

    // Combine main and more menu items for compact sidebar
    const allMenuItems = computed(() => {
      const main = isAdminPanel.value ? adminMainMenuItems : userMainMenuItems
      const more = isAdminPanel.value ? getAdminMoreMenuItems() : getUserMoreMenuItems()
      return [...main, ...more]
    })

    const notificationCount = ref(3)

    // Get user from auth store
    const user = computed(() => authStore.user ? {
      name: authStore.user.name,
      role: authStore.user.role_name || 'User',
      initials: authStore.user.name.charAt(0).toUpperCase()
    } : {
      name: 'User',
      role: 'User',
      initials: 'U'
    })

    // Methods
    const logout = async () => {
      try {
        await authStore.logout()
        router.push({ name: 'auth.login' })
      } catch (error) {
        console.error('Logout error:', error)
        router.push({ name: 'auth.login' })
      }
    }

    const handleSearch = (query) => {
      console.log('Search query:', query)
    }

    const handleNavClick = () => {
      // Handle navigation click if needed
    }

    // Fetch user data on mount if not already loaded
    onMounted(async () => {
      if (!authStore.user && authStore.token) {
        try {
          await authStore.fetchUser()
        } catch (error) {
          console.error('Failed to fetch user:', error)
        }
      }
    })

    return {
      allMenuItems,
      notificationCount,
      user,
      logout,
      handleSearch,
      handleNavClick,
    }
  }
}
</script>
