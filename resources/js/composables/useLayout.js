/**
 * useLayout Composable
 * Provides layout management utilities
 */

import { computed } from 'vue'
import { storeToRefs } from 'pinia'
import { useSettingsStore } from '@/stores/settings'

export function useLayout() {
  const settingsStore = useSettingsStore()
  const { currentAdminLayout, adminLayouts } = storeToRefs(settingsStore)

  /**
   * Get current layout configuration
   */
  const currentLayoutConfig = computed(() => {
    return adminLayouts.value.find(l => l.value === currentAdminLayout.value)
  })

  /**
   * Check if current layout has sidebar
   */
  const hasSidebar = computed(() => {
    const navType = currentLayoutConfig.value?.metadata?.navigation_type
    return ['vertical', 'vertical-icons', 'vertical-mini'].includes(navType)
  })

  /**
   * Check if current layout has horizontal navigation
   */
  const hasHorizontalNav = computed(() => {
    const navType = currentLayoutConfig.value?.metadata?.navigation_type
    return navType === 'horizontal'
  })

  /**
   * Check if content is constrained width
   */
  const isContentConstrained = computed(() => {
    const contentWidth = currentLayoutConfig.value?.metadata?.content_width
    return contentWidth === 'constrained'
  })

  /**
   * Switch to a different layout
   */
  async function switchLayout(layoutName) {
    return await settingsStore.updateUserLayout(layoutName)
  }

  return {
    currentAdminLayout,
    currentLayoutConfig,
    hasSidebar,
    hasHorizontalNav,
    isContentConstrained,
    switchLayout,
  }
}
