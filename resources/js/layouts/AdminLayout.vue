<template>
  <component :is="currentLayoutComponent" />
</template>

<script>
import { computed, onMounted, defineAsyncComponent } from 'vue'
import { useSettingsStore } from '@/stores/settings'
import { storeToRefs } from 'pinia'

export default {
  name: 'AdminLayout',
  setup() {
    const settingsStore = useSettingsStore()
    const { currentAdminLayout } = storeToRefs(settingsStore)

    // Layout component mapping
    const layoutComponents = {
      classic: defineAsyncComponent(() => import('./admin/ClassicLayout.vue')),
      horizontal: defineAsyncComponent(() => import('./admin/HorizontalLayout.vue')),
      compact: defineAsyncComponent(() => import('./admin/CompactLayout.vue')),
      mini: defineAsyncComponent(() => import('./admin/MiniLayout.vue')),
    }

    // Dynamically load the current layout component
    const currentLayoutComponent = computed(() => {
      return layoutComponents[currentAdminLayout.value] || layoutComponents.classic
    })

    // Layout is already initialized in spa.js from database settings
    // This component just reacts to changes in currentAdminLayout
    onMounted(() => {
      // Settings are already loaded in spa.js before app mount
      // No need to reload them here
    })

    return {
      currentLayoutComponent
    }
  }
}
</script>