<template>
  <aside
    class="sidebar-base"
    :class="{
      // Desktop sidebar toggle
      'w-64': !collapsed || isMobile,
      'w-16': collapsed && !isMobile,
      // Mobile sidebar toggle
      'translate-x-0': (isMobile && isOpen) || (!isMobile && !collapsed),
      '-translate-x-full': (isMobile && !isOpen) || (!isMobile && collapsed)
    }"
  >
    <!-- Logo Section -->
    <div
      class="flex items-center justify-between border-b border-white/10"
      :class="collapsed && !isMobile ? 'px-2 py-4' : 'px-4 py-4 sm:px-6'"
    >
      <div
        class="flex items-center"
        :class="collapsed && !isMobile ? 'justify-center w-full' : 'space-x-3'"
      >
        <div class="sidebar-logo">
          <Icon
            name="shield"
            class="sidebar-logo-icon"
          />
        </div>
        <div
          v-show="!collapsed || isMobile"
          class="text-white transition-opacity duration-300"
        >
          <h1 class="text-lg font-bold leading-tight">{{ logoTitle }}</h1>
          <p class="text-xs text-primary-200 dark:text-primary-300">{{ logoSubtitle }}</p>
        </div>
      </div>

      <!-- Close button for mobile -->
      <button
        v-if="isMobile"
        @click="$emit('close')"
        class="p-2 rounded-lg text-white/80 hover:text-white hover:bg-white/10 transition-all duration-200"
        aria-label="Close sidebar"
      >
        <Icon name="close" :size="20" />
      </button>
    </div>

    <!-- Navigation -->
    <nav
      class="flex-1 py-4 space-y-6 overflow-y-auto"
      :class="collapsed && !isMobile ? 'px-1' : 'px-3'"
    >
      <!-- Main Menu Section -->
      <div>
        <div
          v-show="!collapsed || isMobile"
          class="px-3 mb-3 transition-opacity duration-300"
        >
          <p class="text-xs font-semibold text-primary-200 dark:text-primary-300 uppercase tracking-wider">
            Main Menu
          </p>
        </div>

        <div class="space-y-1">
          <NavItem
            v-for="item in mainMenuItems"
            :key="item.to"
            :to="item.to"
            :icon="item.icon"
            :label="item.label"
            :badge="item.badge"
            :collapsed="collapsed && !isMobile"
            :exact-match="item.exactMatch"
            @click="handleNavItemClick"
          />
        </div>
      </div>

      <!-- More Section -->
      <div>
        <div
          v-show="!collapsed || isMobile"
          class="px-3 mb-3 transition-opacity duration-300"
        >
          <p class="text-xs font-semibold text-primary-200 dark:text-primary-300 uppercase tracking-wider">
            More
          </p>
        </div>

        <div class="space-y-1">
          <NavItem
            v-for="item in moreMenuItems"
            :key="item.to || item.label"
            :to="item.to"
            :icon="item.icon"
            :label="item.label"
            :collapsed="collapsed && !isMobile"
            @click="handleActionItemClick(item)"
          />
        </div>
      </div>
    </nav>

    <!-- Logout Button -->
    <div class="logout-section" :class="collapsed && !isMobile ? 'p-2' : 'p-4'">
      <button
        @click="$emit('logout')"
        v-tooltip="collapsed && !isMobile ? 'Logout' : ''"
        class="w-full nav-item nav-item-inactive"
        :class="collapsed && !isMobile ? 'px-2 py-2.5 justify-center' : 'px-3 py-2.5'"
      >
        <Icon
          name="logout"
          :size="20"
          class="flex-shrink-0"
          :class="collapsed && !isMobile ? '' : 'mr-3'"
        />
        <span v-show="!collapsed || isMobile" class="transition-opacity duration-300">
          Logout
        </span>
      </button>
    </div>
  </aside>
</template>

<script>
import Icon from '@/components/common/Icon.vue'
import NavItem from './NavItem.vue'

export default {
  name: 'Sidebar',
  components: {
    Icon,
    NavItem,
  },
  emits: ['close', 'logout', 'nav-click'],
  props: {
    collapsed: {
      type: Boolean,
      default: false,
    },
    isOpen: {
      type: Boolean,
      default: false,
    },
    isMobile: {
      type: Boolean,
      default: false,
    },
    logoTitle: {
      type: String,
      default: 'AdminPanel',
    },
    logoSubtitle: {
      type: String,
      default: 'Learn & Play',
    },
    mainMenuItems: {
      type: Array,
      default: () => [],
    },
    moreMenuItems: {
      type: Array,
      default: () => [],
    },
  },
  setup(props, { emit }) {
    const handleNavItemClick = () => {
      emit('nav-click')
    }

    const handleActionItemClick = (item) => {
      if (item.action) {
        emit(item.action)
      } else {
        handleNavItemClick()
      }
    }

    return {
      handleNavItemClick,
      handleActionItemClick,
    }
  },
}
</script>