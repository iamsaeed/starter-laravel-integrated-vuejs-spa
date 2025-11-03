<template>
  <div
    @click="$emit('select')"
    class="relative cursor-pointer rounded-lg border-2 p-4 transition-all hover:shadow-md"
    :class="isActive
      ? 'border-primary-600 bg-primary-50 dark:bg-primary-900/20'
      : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'
    "
  >
    <!-- Active Indicator -->
    <div
      v-if="isActive"
      class="absolute top-2 right-2 w-6 h-6 bg-primary-600 rounded-full flex items-center justify-center"
    >
      <Icon name="check" class="w-4 h-4 text-white" />
    </div>

    <!-- Layout Icon -->
    <div class="mb-3 flex justify-center">
      <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-lg flex items-center justify-center">
        <Icon :name="layout.metadata.icon" :size="32" class="text-gray-400" />
      </div>
    </div>

    <!-- Layout Info -->
    <div class="text-center">
      <h3 class="font-semibold text-gray-900 dark:text-white mb-1">
        {{ layout.label }}
      </h3>
      <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
        {{ layout.metadata.description }}
      </p>

      <!-- Features -->
      <div class="flex flex-wrap gap-1 justify-center">
        <span
          v-for="(feature, index) in layout.metadata.features?.slice(0, 2)"
          :key="index"
          class="text-xs px-2 py-1 bg-gray-100 dark:bg-gray-800 rounded text-gray-600 dark:text-gray-400"
        >
          {{ feature }}
        </span>
      </div>
    </div>
  </div>
</template>

<script>
import Icon from '@/components/common/Icon.vue'

export default {
  name: 'LayoutOption',
  components: {
    Icon
  },
  props: {
    layout: {
      type: Object,
      required: true
    },
    isActive: {
      type: Boolean,
      default: false
    }
  },
  emits: ['select']
}
</script>
