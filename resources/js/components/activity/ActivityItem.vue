<template>
  <div class="flex items-center space-x-4">
    <div class="indicator w-2 h-2 rounded-full flex-shrink-0" :class="indicatorClasses"></div>
    <div class="flex-1 min-w-0">
      <p class="text-sm font-medium text-title truncate">
        {{ activity.message }}
      </p>
      <p class="text-xs text-muted">
        {{ formattedTime }}
      </p>
    </div>
    <div v-if="activity.badge" class="flex-shrink-0">
      <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium" :class="badgeClasses">
        {{ activity.badge }}
      </span>
    </div>
  </div>
</template>

<script>
import { computed } from 'vue'

export default {
  name: 'ActivityItem',
  props: {
    activity: {
      type: Object,
      required: true,
      // Expected structure: { message: String, time: String, type?: String, badge?: String }
    },
  },
  setup(props) {
    const indicatorClasses = computed(() => {
      const typeClasses = {
        success: 'bg-green-500',
        warning: 'bg-orange-500',
        info: 'bg-secondary-500',
        error: 'bg-red-500',
        primary: 'bg-primary-500',
      }
      return typeClasses[props.activity.type] || 'bg-secondary-500'
    })

    const badgeClasses = computed(() => {
      const typeClasses = {
        success: 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100',
        warning: 'bg-orange-100 text-orange-800 dark:bg-orange-800 dark:text-orange-100',
        info: 'bg-secondary-100 text-secondary-800 dark:bg-secondary-800 dark:text-secondary-100',
        error: 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100',
        primary: 'bg-primary-100 text-primary-800 dark:bg-primary-800 dark:text-primary-100',
      }
      return typeClasses[props.activity.type] || 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100'
    })

    const formattedTime = computed(() => {
      if (typeof props.activity.time === 'string') {
        return props.activity.time
      }

      // If it's a Date object, format it
      if (props.activity.time instanceof Date) {
        const now = new Date()
        const diff = now - props.activity.time
        const minutes = Math.floor(diff / 60000)
        const hours = Math.floor(minutes / 60)
        const days = Math.floor(hours / 24)

        if (minutes < 1) return 'Just now'
        if (minutes < 60) return `${minutes} minute${minutes > 1 ? 's' : ''} ago`
        if (hours < 24) return `${hours} hour${hours > 1 ? 's' : ''} ago`
        return `${days} day${days > 1 ? 's' : ''} ago`
      }

      return 'Unknown time'
    })

    return {
      indicatorClasses,
      badgeClasses,
      formattedTime,
    }
  },
}
</script>

<style scoped>
.indicator {
  transition: transform 0.2s ease-in-out;
}

.activity-item:hover .indicator {
  transform: scale(1.2);
}
</style>