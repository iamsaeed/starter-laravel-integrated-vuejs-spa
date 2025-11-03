<template>
  <div class="page-container font-sans">

    <!-- Mobile Overlay -->
    <div
      v-if="isMobileSidebarOpen"
      @click="closeMobileSidebar"
      class="mobile-overlay"
      :class="{ 'opacity-100': isMobileSidebarOpen, 'opacity-0': !isMobileSidebarOpen }"
    ></div>

    <!-- Sidebar -->
    <Sidebar
      :collapsed="isDesktopSidebarCollapsed"
      :is-open="isMobileSidebarOpen"
      :is-mobile="isMobile"
      :message-count="messageCount"
      :logo-title="logoTitle"
      :main-menu-items="mainMenuItems"
      :more-menu-items="moreMenuItems"
      @close="closeMobileSidebar"
      @nav-click="closeMobileSidebarOnNavigation"
      @logout="logout"
    />

    <!-- Main Content -->
    <div class="content-area" :class="mainContentClasses">

      <!-- Impersonation Banner -->
      <ImpersonationBanner ref="impersonationBanner" />

      <!-- Top Navbar -->
      <Navbar
        :user="user"
        :notification-count="notificationCount"
        @toggle-sidebar="toggleDesktopSidebar"
        @toggle-mobile-sidebar="toggleMobileSidebar"
        @logout="logout"
        @search="handleSearch"
      />

      <!-- Main Content Area -->
      <main class="p-4 sm:p-5 lg:p-6 content-area">
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
import Sidebar from '@/components/layout/Sidebar.vue'
import Navbar from '@/components/layout/Navbar.vue'
import ImpersonationBanner from '@/components/common/ImpersonationBanner.vue'
import ConfirmDialogContainer from '@/components/common/ConfirmDialogContainer.vue'
import { useSidebar } from '@/components/composables/useSidebar.js'
import { useEscapeKey } from '@/components/composables/useClickOutside.js'
import { useAuthStore } from '@/stores/auth'
import { adminMainMenuItems, getAdminMoreMenuItems, userMainMenuItems, getUserMoreMenuItems } from '@/config/menuItems'

export default {
  name: 'ClassicLayout',
  components: {
    Sidebar,
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

    const mainMenuItems = computed(() =>
      isAdminPanel.value ? adminMainMenuItems : userMainMenuItems
    )

    const moreMenuItems = computed(() =>
      isAdminPanel.value ? getAdminMoreMenuItems() : getUserMoreMenuItems()
    )

    const logoTitle = computed(() =>
      isAdminPanel.value ? 'AdminPanel' : 'UserPanel'
    )

    // Sidebar state management
    const {
      isMobileSidebarOpen,
      isDesktopSidebarCollapsed,
      isMobile,
      mainContentClasses,
      toggleDesktopSidebar,
      toggleMobileSidebar,
      closeMobileSidebar,
      closeMobileSidebarOnNavigation,
    } = useSidebar()

    // Application state
    const messageCount = ref(5)
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
      // Add search logic here
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

    // Handle escape key to close mobile sidebar
    useEscapeKey(() => {
      if (isMobileSidebarOpen.value) {
        closeMobileSidebar()
      }
    })

    return {
      // Sidebar state
      isMobileSidebarOpen,
      isDesktopSidebarCollapsed,
      isMobile,
      mainContentClasses,

      // Menu items
      mainMenuItems,
      moreMenuItems,
      logoTitle,

      // Application state
      messageCount,
      notificationCount,
      user,

      // Methods
      toggleDesktopSidebar,
      toggleMobileSidebar,
      closeMobileSidebar,
      closeMobileSidebarOnNavigation,
      logout,
      handleSearch,
    }
  }
}
</script>

<style scoped>
/* Smooth backdrop blur for mobile overlay */
@supports (backdrop-filter: blur(8px)) {
  .mobile-overlay {
    backdrop-filter: blur(8px);
  }
}
</style>
