import { ref, computed, watch, onMounted, onUnmounted } from 'vue'

export function useSidebar() {
  // Local storage key
  const SIDEBAR_STORAGE_KEY = 'admin-sidebar-collapsed'

  // Reactive state
  const isMobileSidebarOpen = ref(false)
  const isDesktopSidebarCollapsed = ref(false)
  const isMobile = ref(false)

  // Load saved sidebar state from localStorage
  const loadSidebarState = () => {
    try {
      const saved = localStorage.getItem(SIDEBAR_STORAGE_KEY)
      if (saved !== null) {
        isDesktopSidebarCollapsed.value = JSON.parse(saved)
      }
    } catch (error) {
      console.warn('Failed to load sidebar state from localStorage:', error)
    }
  }

  // Save sidebar state to localStorage
  const saveSidebarState = () => {
    try {
      localStorage.setItem(SIDEBAR_STORAGE_KEY, JSON.stringify(isDesktopSidebarCollapsed.value))
    } catch (error) {
      console.warn('Failed to save sidebar state to localStorage:', error)
    }
  }

  // Watch for desktop sidebar state changes and save to localStorage
  watch(isDesktopSidebarCollapsed, saveSidebarState)

  // Check if device is mobile
  const checkMobile = () => {
    isMobile.value = window.innerWidth < 1024
    // Auto-hide sidebar on mobile
    if (isMobile.value) {
      isMobileSidebarOpen.value = false
    }
  }

  // Methods
  const toggleDesktopSidebar = () => {
    isDesktopSidebarCollapsed.value = !isDesktopSidebarCollapsed.value
  }

  const toggleMobileSidebar = () => {
    isMobileSidebarOpen.value = !isMobileSidebarOpen.value
    if (isMobileSidebarOpen.value) {
      document.body.style.overflow = 'hidden'
    } else {
      document.body.style.overflow = ''
    }
  }

  const closeMobileSidebar = () => {
    isMobileSidebarOpen.value = false
    document.body.style.overflow = ''
  }

  const closeMobileSidebarOnNavigation = () => {
    // Close sidebar when navigating on mobile
    if (isMobile.value) {
      closeMobileSidebar()
    }
  }

  // Handle resize
  const handleResize = () => {
    checkMobile()
    if (isMobile.value && isMobileSidebarOpen.value) {
      // Keep mobile sidebar behavior as is
    } else if (!isMobile.value && isMobileSidebarOpen.value) {
      // Close mobile sidebar when switching to desktop
      closeMobileSidebar()
    }
  }

  // Computed properties for main content margin
  const mainContentClasses = computed(() => {
    return !isMobile.value && !isDesktopSidebarCollapsed.value ? 'lg:ml-64' : ''
  })

  // Lifecycle hooks
  onMounted(() => {
    loadSidebarState()
    checkMobile()
    window.addEventListener('resize', handleResize)
  })

  onUnmounted(() => {
    window.removeEventListener('resize', handleResize)
    document.body.style.overflow = ''
  })

  return {
    // State
    isMobileSidebarOpen,
    isDesktopSidebarCollapsed,
    isMobile,

    // Computed
    mainContentClasses,

    // Methods
    toggleDesktopSidebar,
    toggleMobileSidebar,
    closeMobileSidebar,
    closeMobileSidebarOnNavigation,
  }
}