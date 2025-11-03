<template>
  <router-link
    v-if="to"
    :to="to"
    @click="$emit('click')"
    v-tooltip="collapsed ? label : ''"
    class="nav-item"
    :class="[
      isActive ? 'nav-item-active' : 'nav-item-inactive',
      collapsed ? 'px-2 py-2.5 justify-center' : 'px-3 py-2.5'
    ]"
  >
    <Icon
      :name="icon"
      :size="20"
      class="flex-shrink-0"
      :class="collapsed ? '' : 'mr-3'"
    />
    <span v-show="!collapsed" class="transition-opacity duration-300">
      {{ label }}
    </span>
    <span
      v-if="badge && !collapsed"
      class="ml-auto bg-red-500 dark:bg-red-600 text-white text-xs font-bold px-2 py-1 rounded-full min-w-[1.5rem] text-center"
    >
      {{ badge }}
    </span>
    <span
      v-if="badge && collapsed && badge > 0"
      class="absolute -top-1 -right-1 h-3 w-3 bg-red-500 dark:bg-red-600 rounded-full"
    ></span>
  </router-link>
  <button
    v-else
    @click="$emit('click')"
    v-tooltip="collapsed ? label : ''"
    class="w-full nav-item nav-item-inactive"
    :class="collapsed ? 'px-2 py-2.5 justify-center' : 'px-3 py-2.5'"
  >
    <Icon
      :name="icon"
      :size="20"
      class="flex-shrink-0"
      :class="collapsed ? '' : 'mr-3'"
    />
    <span v-show="!collapsed" class="transition-opacity duration-300">
      {{ label }}
    </span>
  </button>
</template>

<script>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import Icon from '@/components/common/Icon.vue'

export default {
  name: 'NavItem',
  components: {
    Icon,
  },
  emits: ['click'],
  props: {
    to: {
      type: [String, Object],
      default: null,
    },
    icon: {
      type: String,
      required: true,
    },
    label: {
      type: String,
      required: true,
    },
    badge: {
      type: [Number, String],
      default: null,
    },
    collapsed: {
      type: Boolean,
      default: false,
    },
    exactMatch: {
      type: Boolean,
      default: false,
    },
  },
  setup(props) {
    const route = useRoute()

    const isActive = computed(() => {
      if (!props.to) return false

      // Handle route object with name property
      if (typeof props.to === 'object' && props.to.name) {
        return route.name === props.to.name
      }

      // Handle string route paths
      if (props.exactMatch) {
        return route.path === props.to
      }

      // For non-exact matches, check if the current path includes the route
      return route.path === props.to || route.path.includes(props.to)
    })

    return {
      isActive,
    }
  },
}
</script>