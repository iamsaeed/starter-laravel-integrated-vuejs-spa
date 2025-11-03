<template>
  <div class="card min-h-[60vh] flex items-center justify-center">
    <div class="text-center p-8 max-w-md mx-auto">
      <!-- Error Icon -->
      <div class="w-24 h-24 mx-auto mb-8 rounded-full flex items-center justify-center" :class="iconBgClass">
        <Icon :name="icon" :size="48" class="text-white" />
      </div>

      <!-- Error Code -->
      <h1 class="text-6xl font-bold text-title mb-4" :class="codeColorClass">
        {{ errorCode }}
      </h1>

      <!-- Error Title -->
      <h2 class="text-2xl font-semibold text-title mb-4">
        {{ title }}
      </h2>

      <!-- Error Description -->
      <p class="text-subtitle mb-8 leading-relaxed">
        {{ description }}
      </p>

      <!-- Action Buttons -->
      <div class="space-y-4 sm:space-y-0 sm:space-x-4 sm:flex sm:justify-center">
        <!-- Primary Action (Go to Dashboard) -->
        <router-link :to="{ name: 'admin.dashboard' }" class="btn-primary inline-flex items-center">
          <Icon name="dashboard" :size="20" class="mr-2" />
          {{ primaryActionText }}
        </router-link>

        <!-- Secondary Action (Go Back or Retry) -->
        <button
          v-if="showSecondaryAction"
          @click="handleSecondaryAction"
          class="btn-ghost inline-flex items-center border border-gray-300 dark:border-gray-600"
        >
          <Icon :name="secondaryActionIcon" :size="20" class="mr-2" />
          {{ secondaryActionText }}
        </button>
      </div>

      <!-- Additional Help Text -->
      <div v-if="helpText" class="mt-8 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
        <p class="text-sm text-muted">
          {{ helpText }}
        </p>
      </div>

      <!-- Contact Support -->
      <div v-if="showContactSupport" class="mt-6">
        <p class="text-sm text-muted">
          Still having trouble?
          <button @click="$emit('contact-support')" class="text-accent hover:text-accent-hover font-medium ml-1">
            Contact Support
          </button>
        </p>
      </div>
    </div>
  </div>
</template>

<script>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import Icon from './Icon.vue'

export default {
  name: 'ErrorPage',
  components: {
    Icon,
  },
  emits: ['contact-support', 'retry'],
  props: {
    errorCode: {
      type: [String, Number],
      default: '404',
    },
    title: {
      type: String,
      default: 'Page Not Found',
    },
    description: {
      type: String,
      default: 'The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.',
    },
    icon: {
      type: String,
      default: 'search',
    },
    variant: {
      type: String,
      default: 'warning',
      validator: (value) => ['warning', 'error', 'info', 'success'].includes(value),
    },
    primaryActionText: {
      type: String,
      default: 'Go to Dashboard',
    },
    secondaryActionText: {
      type: String,
      default: 'Go Back',
    },
    secondaryActionIcon: {
      type: String,
      default: 'chevron-left',
    },
    showSecondaryAction: {
      type: Boolean,
      default: true,
    },
    showContactSupport: {
      type: Boolean,
      default: false,
    },
    helpText: {
      type: String,
      default: null,
    },
    customSecondaryAction: {
      type: Function,
      default: null,
    },
  },
  setup(props, { emit }) {
    const router = useRouter()

    const iconBgClass = computed(() => {
      const variants = {
        warning: 'bg-gradient-to-br from-orange-500 to-orange-600',
        error: 'bg-gradient-to-br from-red-500 to-red-600',
        info: 'bg-gradient-to-br from-secondary-500 to-secondary-600',
        success: 'bg-gradient-to-br from-green-500 to-green-600',
      }
      return variants[props.variant] || variants.warning
    })

    const codeColorClass = computed(() => {
      const variants = {
        warning: 'text-orange-600 dark:text-orange-400',
        error: 'text-red-600 dark:text-red-400',
        info: 'text-secondary-600 dark:text-secondary-400',
        success: 'text-green-600 dark:text-green-400',
      }
      return variants[props.variant] || variants.warning
    })

    const handleSecondaryAction = () => {
      if (props.customSecondaryAction) {
        props.customSecondaryAction()
      } else if (props.secondaryActionText === 'Retry') {
        emit('retry')
      } else {
        // Default: go back in history
        if (window.history.length > 1) {
          router.go(-1)
        } else {
          router.push({ name: 'admin.dashboard' })
        }
      }
    }

    return {
      iconBgClass,
      codeColorClass,
      handleSecondaryAction,
    }
  },
}
</script>