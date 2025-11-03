<template>
  <div class="form-section">
    <!-- Section Header -->
    <div v-if="title || description" class="section-header mb-6">
      <div class="flex items-center justify-between">
        <div class="flex-1">
          <h3 v-if="title" class="form-section-title">
            {{ title }}
          </h3>
          <p v-if="description" class="form-section-description mt-1">
            {{ description }}
          </p>
        </div>
        
        <!-- Collapsible toggle button -->
        <button
          v-if="collapsible"
          type="button"
          @click="toggleCollapsed"
          class="ml-4 p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors duration-200"
          :aria-expanded="!isCollapsed"
          :aria-label="isCollapsed ? 'Expand section' : 'Collapse section'"
        >
          <svg
            class="w-5 h-5 transform transition-transform duration-200"
            :class="{ 'rotate-180': !isCollapsed }"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>
      </div>
      
      <!-- Divider -->
      <div v-if="showDivider" class="mt-4 border-t border-gray-200 dark:border-gray-700"></div>
    </div>
    
    <!-- Section Content -->
    <Transition
      name="collapse"
      @enter="onEnter"
      @after-enter="onAfterEnter"
      @leave="onLeave"
      @after-leave="onAfterLeave"
    >
      <div v-show="!isCollapsed" class="section-content">
        <slot />
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  title: {
    type: String,
    default: ''
  },
  description: {
    type: String,
    default: ''
  },
  collapsible: {
    type: Boolean,
    default: false
  },
  collapsed: {
    type: Boolean,
    default: false
  },
  showDivider: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['toggle'])

// State
const isCollapsed = ref(props.collapsed)

// Methods
const toggleCollapsed = () => {
  isCollapsed.value = !isCollapsed.value
  emit('toggle', isCollapsed.value)
}

// Transition handlers
const onEnter = (el) => {
  el.style.height = '0'
  el.style.overflow = 'hidden'
}

const onAfterEnter = (el) => {
  el.style.height = 'auto'
  el.style.overflow = 'visible'
}

const onLeave = (el) => {
  el.style.height = `${el.scrollHeight}px`
  el.style.overflow = 'hidden'
  // Force reflow
  el.offsetHeight
  el.style.height = '0'
}

const onAfterLeave = (el) => {
  el.style.height = 'auto'
  el.style.overflow = 'visible'
}
</script>

<style scoped>
.collapse-enter-active,
.collapse-leave-active {
  transition: height 0.3s ease;
}

.collapse-enter-from,
.collapse-leave-to {
  height: 0;
  overflow: hidden;
}
</style>