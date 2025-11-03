<template>
  <div class="upload-progress-bar">
    <!-- File Info Header -->
    <div class="progress-header">
      <div class="file-info">
        <Icon name="image" class="w-4 h-4 text-primary-600 dark:text-primary-400" />
        <span class="file-name">{{ truncatedFileName }}</span>
        <span class="file-size">{{ formattedFileSize }}</span>
      </div>
      <button
        v-if="cancellable && !isCompleted"
        @click="handleCancel"
        class="cancel-button"
        type="button"
        v-tooltip="'Cancel Upload'"
      >
        <Icon name="x" class="w-4 h-4" />
      </button>
    </div>

    <!-- Progress Bar -->
    <div class="progress-bar-container">
      <div
        class="progress-bar-fill"
        :class="progressBarClass"
        :style="{ width: `${progress}%` }"
      >
        <div class="progress-bar-shine"></div>
      </div>
    </div>

    <!-- Progress Stats -->
    <div class="progress-stats">
      <div class="stat-item">
        <span class="stat-value">{{ progress }}%</span>
      </div>
      <div v-if="showSpeed && speed > 0" class="stat-item">
        <Icon name="zap" class="w-3 h-3" />
        <span class="stat-value">{{ formattedSpeed }}/s</span>
      </div>
      <div v-if="showEta && eta > 0 && !isCompleted" class="stat-item">
        <Icon name="clock" class="w-3 h-3" />
        <span class="stat-value">{{ formattedEta }}</span>
      </div>
      <div v-if="isCompleted" class="stat-item text-success-600 dark:text-success-400">
        <Icon name="check-circle" class="w-4 h-4" />
        <span class="stat-value">Complete</span>
      </div>
      <div v-if="hasError" class="stat-item text-danger-600 dark:text-danger-400">
        <Icon name="alert-circle" class="w-4 h-4" />
        <span class="stat-value">{{ errorMessage }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import Icon from '@/components/common/Icon.vue'

const props = defineProps({
  // File information
  fileName: {
    type: String,
    required: true
  },
  fileSize: {
    type: Number,
    default: 0
  },

  // Progress data
  progress: {
    type: Number,
    default: 0,
    validator: (value) => value >= 0 && value <= 100
  },
  loaded: {
    type: Number,
    default: 0
  },
  total: {
    type: Number,
    default: 0
  },

  // Speed & ETA
  speed: {
    type: Number,
    default: 0
  },
  eta: {
    type: Number,
    default: 0
  },

  // State
  isCompleted: {
    type: Boolean,
    default: false
  },
  hasError: {
    type: Boolean,
    default: false
  },
  errorMessage: {
    type: String,
    default: 'Upload failed'
  },

  // Options
  cancellable: {
    type: Boolean,
    default: true
  },
  showSpeed: {
    type: Boolean,
    default: true
  },
  showEta: {
    type: Boolean,
    default: true
  },
  maxFileNameLength: {
    type: Number,
    default: 30
  }
})

const emit = defineEmits(['cancel'])

// Computed properties
const truncatedFileName = computed(() => {
  if (props.fileName.length <= props.maxFileNameLength) {
    return props.fileName
  }

  const ext = props.fileName.split('.').pop()
  const nameWithoutExt = props.fileName.substring(0, props.fileName.lastIndexOf('.'))
  const maxNameLength = props.maxFileNameLength - ext.length - 4 // -4 for "..." and "."

  return `${nameWithoutExt.substring(0, maxNameLength)}...${ext}`
})

const formattedFileSize = computed(() => {
  return formatBytes(props.fileSize)
})

const formattedSpeed = computed(() => {
  return formatBytes(props.speed)
})

const formattedEta = computed(() => {
  return formatTime(props.eta)
})

const progressBarClass = computed(() => {
  if (props.hasError) {
    return 'progress-error'
  }
  if (props.isCompleted) {
    return 'progress-complete'
  }
  return 'progress-active'
})

// Helper functions
function formatBytes(bytes) {
  if (bytes === 0) return '0 B'

  const k = 1024
  const sizes = ['B', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))

  return `${parseFloat((bytes / Math.pow(k, i)).toFixed(1))} ${sizes[i]}`
}

function formatTime(seconds) {
  if (seconds === 0) return '0s'
  if (seconds < 60) return `${Math.round(seconds)}s`

  const minutes = Math.floor(seconds / 60)
  const remainingSeconds = Math.round(seconds % 60)

  if (minutes < 60) {
    return remainingSeconds > 0 ? `${minutes}m ${remainingSeconds}s` : `${minutes}m`
  }

  const hours = Math.floor(minutes / 60)
  const remainingMinutes = minutes % 60

  return remainingMinutes > 0 ? `${hours}h ${remainingMinutes}m` : `${hours}h`
}

function handleCancel() {
  emit('cancel')
}
</script>

<style scoped>
.upload-progress-bar {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  padding: 0.75rem;
  background-color: rgb(255 255 255);
  border: 1px solid rgb(229 231 235);
  border-radius: 0.5rem;
  box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
}

.dark .upload-progress-bar {
  background-color: rgb(31 41 55);
  border-color: rgb(75 85 99);
}

.progress-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
}

.file-info {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex: 1;
  min-width: 0;
}

.file-name {
  font-size: 0.875rem;
  font-weight: 500;
  color: rgb(17 24 39);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.dark .file-name {
  color: rgb(243 244 246);
}

.file-size {
  font-size: 0.75rem;
  color: rgb(107 114 128);
  white-space: nowrap;
}

.dark .file-size {
  color: rgb(156 163 175);
}

.cancel-button {
  padding: 0.25rem;
  color: rgb(107 114 128);
  border-radius: 0.25rem;
  transition: colors 0.2s;
}

.cancel-button:hover {
  color: rgb(239 68 68);
  background-color: rgb(254 242 242);
}

.dark .cancel-button {
  color: rgb(156 163 175);
}

.dark .cancel-button:hover {
  color: rgb(248 113 113);
  background-color: rgb(127 29 29);
}

.progress-bar-container {
  width: 100%;
  height: 0.5rem;
  background-color: rgb(229 231 235);
  border-radius: 9999px;
  overflow: hidden;
  position: relative;
}

.dark .progress-bar-container {
  background-color: rgb(55 65 81);
}

.progress-bar-fill {
  height: 100%;
  border-radius: 9999px;
  transition: width 0.3s ease;
  position: relative;
  overflow: hidden;
}

.progress-active {
  background: linear-gradient(90deg, rgb(59 130 246), rgb(37 99 235));
}

.progress-complete {
  background: linear-gradient(90deg, rgb(34 197 94), rgb(22 163 74));
}

.progress-error {
  background: linear-gradient(90deg, rgb(239 68 68), rgb(220 38 38));
}

.progress-bar-shine {
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(
    90deg,
    transparent,
    rgba(255, 255, 255, 0.3),
    transparent
  );
  animation: shine 2s infinite;
}

@keyframes shine {
  0% {
    left: -100%;
  }
  100% {
    left: 200%;
  }
}

.progress-stats {
  display: flex;
  align-items: center;
  gap: 1rem;
  font-size: 0.75rem;
}

.stat-item {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  color: rgb(107 114 128);
}

.dark .stat-item {
  color: rgb(156 163 175);
}

.stat-value {
  font-weight: 500;
}
</style>
