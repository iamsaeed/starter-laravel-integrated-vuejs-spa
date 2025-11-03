<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Impersonation Banner -->
    <ImpersonationBanner ref="impersonationBanner" />

    <!-- Top Navigation -->
    <HorizontalNav
      :user="user"
      :notification-count="notificationCount"
      :menu-items="mainMenuItems"
      :logo-title="logoTitle"
      @logout="logout"
      @search="handleSearch"
    />

    <!-- Main Content -->
    <main class="pt-16 p-4 sm:p-5 lg:p-6">
      <router-view />
    </main>

    <ConfirmDialogContainer />
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import HorizontalNav from '@/components/layout/HorizontalNav.vue'
import ImpersonationBanner from '@/components/common/ImpersonationBanner.vue'
import ConfirmDialogContainer from '@/components/common/ConfirmDialogContainer.vue'
import { useAuthStore } from '@/stores/auth'
import { adminMainMenuItems, userMainMenuItems } from '@/config/menuItems'

export default {
  name: 'HorizontalLayout',
  components: {
    HorizontalNav,
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

    const logoTitle = computed(() =>
      isAdminPanel.value ? 'AdminPanel' : 'UserPanel'
    )

    const notificationCount = ref(3)

    const user = computed(() => authStore.user ? {
      name: authStore.user.name,
      role: authStore.user.role_name || 'User',
      initials: authStore.user.name.charAt(0).toUpperCase()
    } : {
      name: 'User',
      role: 'User',
      initials: 'U'
    })

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
      mainMenuItems,
      logoTitle,
      notificationCount,
      user,
      logout,
      handleSearch,
    }
  }
}
</script>
