<template>
  <aside class="mini-sidebar group">
    <!-- Logo -->
    <div class="flex items-center h-16 border-b border-white/10 px-4 group-hover:px-6 transition-all duration-300">
      <div class="w-8 h-8 bg-white dark:bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0">
        <Icon name="shield" class="w-5 h-5 text-primary-600" />
      </div>
      <div class="ml-3 overflow-hidden transition-all duration-300 whitespace-nowrap opacity-0 w-0 group-hover:opacity-100 group-hover:w-auto">
        <h1 class="text-base font-bold text-white leading-tight">{{ logoTitle }}</h1>
        <p class="text-xs text-primary-200">{{ logoSubtitle }}</p>
      </div>
    </div>

    <!-- Navigation Items -->
    <nav class="flex-1 flex flex-col py-4 px-2 group-hover:px-3 space-y-1 overflow-y-auto transition-all duration-300">
      <router-link
        v-for="item in menuItems"
        :key="item.to.name"
        :to="item.to"
        class="flex items-center px-3 py-2.5 rounded-lg text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200"
        active-class="bg-white/20"
      >
        <Icon :name="item.icon" :size="20" class="flex-shrink-0" />
        <span class="ml-3 overflow-hidden whitespace-nowrap opacity-0 w-0 transition-all duration-300 group-hover:opacity-100 group-hover:w-auto">{{ item.label }}</span>
      </router-link>
    </nav>

    <!-- Logout Button -->
    <div class="p-2 border-t border-white/10 group-hover:px-3 transition-all duration-300">
      <button
        @click="$emit('logout')"
        class="flex items-center px-3 py-2.5 rounded-lg text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 w-full"
      >
        <Icon name="logout" :size="20" class="flex-shrink-0" />
        <span class="ml-3 overflow-hidden whitespace-nowrap opacity-0 w-0 transition-all duration-300 group-hover:opacity-100 group-hover:w-auto">Logout</span>
      </button>
    </div>
  </aside>
</template>

<script>
import Icon from '@/components/common/Icon.vue'

export default {
  name: 'MiniSidebar',
  components: {
    Icon
  },
  emits: ['nav-click', 'logout'],
  props: {
    menuItems: {
      type: Array,
      default: () => [],
    },
    logoTitle: {
      type: String,
      default: 'AdminPanel',
    },
    logoSubtitle: {
      type: String,
      default: 'Learn & Play',
    },
  },
}
</script>

<style scoped>
.mini-sidebar {
  position: fixed;
  inset: 0 auto 0 0;
  z-index: 50;
  width: 4rem;
  background: linear-gradient(to bottom, var(--color-primary-600), var(--color-secondary-600));
  display: flex;
  flex-direction: column;
  transition: all 300ms ease-in-out;
}

.dark .mini-sidebar {
  background: linear-gradient(to bottom, var(--color-primary-800), var(--color-secondary-800));
}

.mini-sidebar:hover {
  width: 16rem;
  box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
}
</style>
