<template>
  <div class="card-stat bg-gradient-to-br" :class="gradientClasses">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-sm font-medium" :class="labelClasses">
          {{ title }}
        </p>
        <p class="text-2xl font-bold">
          {{ displayValue }}
        </p>
        <div v-if="trend" class="flex items-center mt-2">
          <Icon
            :name="trend.isPositive ? 'chevron-up' : 'chevron-down'"
            :size="16"
            class="mr-1"
          />
          <span class="text-sm font-medium">
            {{ trend.value }}%
          </span>
          <span class="text-xs ml-1 opacity-80">
            vs last {{ trend.period || 'month' }}
          </span>
        </div>
      </div>
      <div class="icon-container w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
        <Icon
          :name="icon"
          :size="24"
          class="text-white"
        />
      </div>
    </div>
  </div>
</template>

<script>
import { computed } from 'vue'
import Icon from '@/components/common/Icon.vue'

export default {
  name: 'StatCard',
  components: {
    Icon,
  },
  props: {
    title: {
      type: String,
      required: true,
    },
    value: {
      type: [String, Number],
      required: true,
    },
    icon: {
      type: String,
      required: true,
    },
    variant: {
      type: String,
      default: 'blue',
      validator: (value) => ['blue', 'green', 'purple', 'orange', 'red'].includes(value),
    },
    trend: {
      type: Object,
      default: null,
      // Expected structure: { value: 12.5, isPositive: true, period: 'month' }
    },
    prefix: {
      type: String,
      default: '',
    },
    suffix: {
      type: String,
      default: '',
    },
    loading: {
      type: Boolean,
      default: false,
    },
  },
  setup(props) {
    const gradientClasses = computed(() => {
      const variants = {
        blue: 'from-secondary-500 to-secondary-600',
        green: 'from-green-500 to-green-600',
        purple: 'from-primary-500 to-primary-600',
        orange: 'from-orange-500 to-orange-600',
        red: 'from-red-500 to-red-600',
      }
      return variants[props.variant] || variants.blue
    })

    const labelClasses = computed(() => {
      const variants = {
        blue: 'text-blue-100',
        green: 'text-green-100',
        purple: 'text-purple-100',
        orange: 'text-orange-100',
        red: 'text-red-100',
      }
      return variants[props.variant] || variants.blue
    })

    const displayValue = computed(() => {
      if (props.loading) return '...'

      let formattedValue = props.value

      // Format numbers with commas
      if (typeof props.value === 'number') {
        formattedValue = props.value.toLocaleString()
      }

      return `${props.prefix}${formattedValue}${props.suffix}`
    })

    return {
      gradientClasses,
      labelClasses,
      displayValue,
    }
  },
}
</script>

<style scoped>
.icon-container {
  transition: transform 0.2s ease-in-out;
}

.card-stat:hover .icon-container {
  transform: scale(1.05);
}
</style>