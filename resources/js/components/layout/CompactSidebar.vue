<template>
  <aside class="fixed inset-y-0 left-0 z-50 w-16 bg-gradient-to-b from-primary-600 to-secondary-600 dark:from-primary-800 dark:to-secondary-800 flex flex-col">
    <!-- Logo -->
    <div class="flex items-center justify-center h-16 border-b border-white/10">
      <div class="w-8 h-8 bg-white dark:bg-gray-100 rounded-lg flex items-center justify-center">
        <Icon name="shield" class="w-5 h-5 text-primary-600" />
      </div>
    </div>

    <!-- Navigation Items -->
    <nav class="flex-1 flex flex-col items-center py-4 space-y-2 overflow-y-auto">
      <router-link
        v-for="item in menuItems"
        :key="item.to.name"
        :to="item.to"
        v-tooltip="item.label"
        class="w-12 h-12 flex items-center justify-center rounded-lg text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200"
        active-class="bg-white/20"
      >
        <Icon :name="item.icon" :size="20" />
      </router-link>
    </nav>

    <!-- Logout Button -->
    <div class="p-2 border-t border-white/10">
      <button
        @click="$emit('logout')"
        v-tooltip="'Logout'"
        class="w-12 h-12 flex items-center justify-center rounded-lg text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200"
      >
        <Icon name="logout" :size="20" />
      </button>
    </div>
  </aside>
</template>

<script>
import Icon from '@/components/common/Icon.vue'

export default {
  name: 'CompactSidebar',
  components: {
    Icon
  },
  emits: ['nav-click', 'logout'],
  props: {
    menuItems: {
      type: Array,
      default: () => [],
    },
  },
}
</script>
