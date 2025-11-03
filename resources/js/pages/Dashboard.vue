<template>
  <div class="card min-h-[80vh]">
    <div class="p-8">
      <div class="text-center mb-8">
        <div class="w-24 h-24 bg-gradient-to-br from-primary-500 to-secondary-500 rounded-full flex items-center justify-center mx-auto mb-6">
          <Icon name="dashboard" :size="48" class="text-white" />
        </div>
        <h1 class="text-3xl font-bold text-title mb-4">Welcome to Admin Dashboard</h1>
        <p class="text-lg text-subtitle max-w-2xl mx-auto">
          This is your Vue.js SPA dashboard with refactored components and theme system.
        </p>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <StatCard
          v-for="stat in stats"
          :key="stat.title"
          :title="stat.title"
          :value="stat.value"
          :icon="stat.icon"
          :variant="stat.variant"
          :trend="stat.trend"
          :prefix="stat.prefix"
          :suffix="stat.suffix"
        />
      </div>

      <!-- Recent Activity -->
      <ActivityFeed
        :activities="activities"
        :show-view-all="true"
        @view-all="handleViewAllActivities"
      />
    </div>
  </div>
</template>

<script>
import { ref } from 'vue'
import Icon from '@/components/common/Icon.vue'
import StatCard from '@/components/stats/StatCard.vue'
import ActivityFeed from '@/components/activity/ActivityFeed.vue'

export default {
  name: 'Dashboard',
  components: {
    Icon,
    StatCard,
    ActivityFeed,
  },
  setup() {
    const stats = ref([
      {
        title: 'Total Users',
        value: 1234,
        icon: 'users',
        variant: 'blue',
        trend: {
          value: 12.5,
          isPositive: true,
          period: 'month',
        },
      },
      {
        title: 'Revenue',
        value: 12345,
        icon: 'check-circle',
        variant: 'green',
        prefix: '$',
        trend: {
          value: 8.2,
          isPositive: true,
          period: 'month',
        },
      },

      {
        title: 'Tasks',
        value: 89,
        icon: 'clipboard',
        variant: 'orange',
        trend: {
          value: 15.3,
          isPositive: true,
          period: 'week',
        },
      },
    ])

    const activities = ref([
      {
        message: 'New user registered',
        time: '2 minutes ago',
        type: 'info',
      },

      {
        message: 'New task assigned',
        time: '3 hours ago',
        type: 'primary',
        badge: 'New',
      },
      {
        message: 'Server backup completed',
        time: '5 hours ago',
        type: 'success',
      },
      {
        message: 'Payment received',
        time: '1 day ago',
        type: 'success',
        badge: '$1,200',
      },
    ])

    const handleViewAllActivities = () => {
      console.log('View all activities clicked')
      // Navigate to activities page or open modal
    }

    return {
      stats,
      activities,
      handleViewAllActivities,
    }
  },
}
</script>