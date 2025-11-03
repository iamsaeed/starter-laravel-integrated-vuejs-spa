<template>
  <div class="card-content">
    <h3 class="text-lg font-semibold text-title mb-4">
      {{ title }}
    </h3>
    <div class="space-y-4">
      <ActivityItem
        v-for="(item, index) in activities"
        :key="index"
        :activity="item"
      />
      <div v-if="activities.length === 0" class="text-center py-8">
        <Icon
          name="clipboard"
          :size="48"
          variant="muted"
          class="mx-auto mb-3"
        />
        <p class="text-muted">{{ emptyMessage }}</p>
      </div>
    </div>
    <div v-if="showViewAll && activities.length > 0" class="mt-6 text-center">
      <button
        @click="$emit('view-all')"
        class="text-accent hover:text-accent-hover font-medium text-sm transition-colors duration-200"
      >
        View all activities
      </button>
    </div>
  </div>
</template>

<script>
import Icon from '@/components/common/Icon.vue'
import ActivityItem from './ActivityItem.vue'

export default {
  name: 'ActivityFeed',
  components: {
    Icon,
    ActivityItem,
  },
  emits: ['view-all'],
  props: {
    title: {
      type: String,
      default: 'Recent Activity',
    },
    activities: {
      type: Array,
      default: () => [],
    },
    emptyMessage: {
      type: String,
      default: 'No recent activity',
    },
    showViewAll: {
      type: Boolean,
      default: false,
    },
  },
}
</script>