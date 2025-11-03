/**
 * Virtual Scrolling Component for Large Lists
 * Renders only visible items to improve performance with large datasets
 */

<template>
  <div 
    ref="containerRef"
    :style="{ height: containerHeight + 'px', overflow: 'auto' }"
    @scroll="handleScroll"
    class="virtual-scroll-container"
  >
    <!-- Spacer for items before visible range -->
    <div :style="{ height: offsetY + 'px' }" />
    
    <!-- Visible items -->
    <div
      v-for="(item, index) in visibleItems"
      :key="getItemKey(item, startIndex + index)"
      :style="{ height: itemHeight + 'px' }"
      class="virtual-scroll-item"
    >
      <slot 
        :item="item" 
        :index="startIndex + index"
        :is-selected="isSelected(item)"
        :is-highlighted="startIndex + index === highlightedIndex"
      >
        {{ getItemLabel(item) }}
      </slot>
    </div>
    
    <!-- Spacer for items after visible range -->
    <div :style="{ height: totalHeight - offsetY - visibleHeight + 'px' }" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from 'vue'

const props = defineProps({
  items: {
    type: Array,
    required: true
  },
  itemHeight: {
    type: Number,
    default: 40
  },
  containerHeight: {
    type: Number,
    default: 200
  },
  overscan: {
    type: Number,
    default: 5
  },
  keyField: {
    type: String,
    default: 'value'
  },
  labelField: {
    type: String,
    default: 'label'
  },
  selectedValue: {
    type: [String, Number, Array],
    default: null
  },
  highlightedIndex: {
    type: Number,
    default: -1
  }
})

const emit = defineEmits(['scroll', 'item-click'])

// Refs
const containerRef = ref(null)
const scrollTop = ref(0)

// Computed values
const totalHeight = computed(() => props.items.length * props.itemHeight)
const visibleCount = computed(() => Math.ceil(props.containerHeight / props.itemHeight))
const startIndex = computed(() => Math.max(0, Math.floor(scrollTop.value / props.itemHeight) - props.overscan))
const endIndex = computed(() => Math.min(props.items.length - 1, startIndex.value + visibleCount.value + props.overscan * 2))
const visibleItems = computed(() => props.items.slice(startIndex.value, endIndex.value + 1))
const offsetY = computed(() => startIndex.value * props.itemHeight)
const visibleHeight = computed(() => (endIndex.value - startIndex.value + 1) * props.itemHeight)

// Methods
const handleScroll = (event) => {
  scrollTop.value = event.target.scrollTop
  emit('scroll', {
    scrollTop: scrollTop.value,
    startIndex: startIndex.value,
    endIndex: endIndex.value
  })
}

const scrollToIndex = (index) => {
  if (!containerRef.value) return
  
  const targetScrollTop = Math.max(0, index * props.itemHeight - props.containerHeight / 2)
  containerRef.value.scrollTop = targetScrollTop
}

const scrollToItem = (item) => {
  const index = props.items.findIndex(i => i[props.keyField] === item[props.keyField])
  if (index >= 0) {
    scrollToIndex(index)
  }
}

const getItemKey = (item, index) => {
  return item[props.keyField] || index
}

const getItemLabel = (item) => {
  return item[props.labelField] || item.toString()
}

const isSelected = (item) => {
  if (Array.isArray(props.selectedValue)) {
    return props.selectedValue.includes(item[props.keyField])
  }
  return props.selectedValue === item[props.keyField]
}

const handleItemClick = (item, index) => {
  emit('item-click', { item, index: startIndex.value + index })
}

// Watch for highlighted index changes to auto-scroll
watch(() => props.highlightedIndex, (newIndex) => {
  if (newIndex >= 0) {
    nextTick(() => {
      const containerElement = containerRef.value
      if (!containerElement) return
      
      const itemTop = newIndex * props.itemHeight
      const itemBottom = itemTop + props.itemHeight
      const containerTop = containerElement.scrollTop
      const containerBottom = containerTop + props.containerHeight
      
      if (itemTop < containerTop) {
        containerElement.scrollTop = itemTop
      } else if (itemBottom > containerBottom) {
        containerElement.scrollTop = itemBottom - props.containerHeight
      }
    })
  }
})

// Expose methods
defineExpose({
  scrollToIndex,
  scrollToItem,
  containerRef
})
</script>

<style scoped>
.virtual-scroll-container {
  position: relative;
}

.virtual-scroll-item {
  display: flex;
  align-items: center;
  box-sizing: border-box;
}
</style>