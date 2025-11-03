<template>
  <Teleport to="body">
    <div
      v-if="show"
      class="fixed inset-0 z-[60] overflow-y-auto"
      @click="handleOverlayClick"
    >
      <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 transition-opacity bg-gray-500 dark:bg-gray-900 bg-opacity-75 dark:bg-opacity-75" />

        <!-- Modal panel -->
        <div
          class="relative inline-block w-full max-w-2xl p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-gray-800 shadow-xl rounded-lg"
          @click.stop
        >
          <!-- Modal Header -->
          <div class="flex items-center justify-between mb-4 pb-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
              Quick Create {{ title }}
            </h3>
            <button
              @click="$emit('cancel')"
              class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors duration-200"
              v-tooltip="'Close'"
            >
              <Icon name="close" :size="24" />
            </button>
          </div>

          <!-- Resource Form -->
          <ResourceForm
            :resource="resource"
            @success="handleSuccess"
            @cancel="$emit('cancel')"
          />
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import ResourceForm from './ResourceForm.vue'
import Icon from '@/components/common/Icon.vue'

const props = defineProps({
  show: {
    type: Boolean,
    required: true
  },
  resource: {
    type: String,
    required: true
  },
  title: {
    type: String,
    default: 'Record'
  }
})

const emit = defineEmits(['created', 'cancel'])

function handleSuccess(data) {
  emit('created', data)
}

function handleOverlayClick() {
  emit('cancel')
}
</script>
