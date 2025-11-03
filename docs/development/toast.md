# Toast Notification System - Implementation Plan

## Overview
Create a comprehensive, subtle, and smooth toast notification system with:
- Status-based color coding (success, error, warning, info)
- Smooth transitions and animations
- Automatic axios interceptor integration for API errors
- Global composable for manual toast notifications
- Pinia store for state management
- Queue system for multiple toasts
- Auto-dismiss with configurable duration
- Accessibility support (ARIA live regions)
- Dark mode support

## Architecture

### Core Components
1. **Toast Store** - Pinia store for state management
2. **Toast Component** - Individual toast display
3. **Toast Container** - Global container for all toasts
4. **Toast Composable** - Developer API for showing toasts
5. **Axios Integration** - Automatic error handling

## Files to Create/Modify

### 1. Toast Store (`resources/js/stores/toast.js`)
**Purpose:** Centralized state management for toasts

**Features:**
- Queue management (max 3 visible toasts)
- Auto-dismiss with timers
- Toast ID generation
- Add/remove/clear methods

**State:**
```javascript
{
  toasts: [],        // Array of active toasts
  maxToasts: 3,      // Maximum visible toasts
  defaultDuration: 4000  // Default auto-dismiss duration
}
```

**Actions:**
- `addToast(toast)` - Add new toast to queue
- `removeToast(id)` - Remove specific toast
- `clearAll()` - Clear all toasts

**Toast Object Structure:**
```javascript
{
  id: 'unique-id',
  type: 'success|error|warning|info',
  message: 'Toast message',
  duration: 4000,
  closable: true,
  icon: 'optional-icon-name',
  timestamp: Date.now()
}
```

### 2. Toast Component (`resources/js/components/common/Toast.vue`)
**Purpose:** Individual toast display with animations

**Props:**
- `type` - success, error, warning, info (required)
- `message` - Toast message (required)
- `closable` - Show close button (default: true)
- `duration` - Auto-dismiss duration in ms (default: 4000)
- `icon` - Custom icon name (optional)

**Features:**
- Status-based styling
- Smooth enter/leave transitions (fade + slide)
- Progress bar for auto-dismiss countdown
- Close button
- Icon per status type
- Subtle shadow and backdrop-blur
- Accessible (role="alert")

**Styling Approach:**
All styling uses Tailwind CSS utility classes directly in components. No custom CSS classes are created unless absolutely necessary for animations that Tailwind doesn't support out of the box.

**Example Tailwind Classes:**
```html
<!-- Base toast -->
class="rounded-lg shadow-lg border backdrop-blur-md min-w-[320px] max-w-md p-4 relative overflow-hidden"

<!-- Success variant -->
class="bg-green-50/95 dark:bg-green-900/90 border-green-200 dark:border-green-800 text-green-800 dark:text-green-200"

<!-- Transitions -->
class="transition-all duration-300 ease-out"
```

### 3. Toast Container (`resources/js/components/common/ToastContainer.vue`)
**Purpose:** Global container that renders all active toasts

**Features:**
- Fixed positioning (top-right by default)
- Stacked layout with spacing
- TransitionGroup for smooth enter/leave
- Reads toasts from store
- Responsive positioning

**Position Options:**
- `top-right` (default)
- `top-left`
- `bottom-right`
- `bottom-left`
- `top-center`
- `bottom-center`

**Layout (Tailwind-first):**
```html
<div class="fixed top-4 right-4 z-50 space-y-3 pointer-events-none">
  <TransitionGroup name="toast">
    <Toast
      v-for="toast in toasts"
      :key="toast.id"
      v-bind="toast"
      class="pointer-events-auto"
    />
  </TransitionGroup>
</div>
```
Note: All positioning and spacing uses Tailwind utilities (`fixed`, `top-4`, `right-4`, `z-50`, `space-y-3`)

### 4. Toast Composable (`resources/js/composables/useToast.js`)
**Purpose:** Simple API for showing toasts anywhere in the app

**Methods:**
```javascript
// Success toast
toast.success(message, options?)

// Error toast
toast.error(message, options?)

// Warning toast
toast.warning(message, options?)

// Info toast
toast.info(message, options?)

// Custom toast
toast.show(message, { type, icon, duration, closable })
```

**Usage Examples:**
```javascript
import { useToast } from '@/composables/useToast'

const toast = useToast()

// Simple success
toast.success('Settings saved successfully!')

// Error with custom duration
toast.error('Failed to save settings', { duration: 6000 })

// Warning with custom icon
toast.warning('Please verify your email', {
  icon: 'mail',
  closable: false
})

// Info message
toast.info('New features available!')

// Custom
toast.show('Custom message', {
  type: 'info',
  icon: 'bell',
  duration: 3000
})
```

**Returns:** Toast ID for manual dismissal
```javascript
const toastId = toast.success('Processing...')
// Later...
toast.dismiss(toastId)
```

### 5. Axios Interceptor Enhancement (`resources/js/bootstrap.js`)
**Purpose:** Automatic toast notifications for API errors

**Implementation:**
```javascript
window.axios.interceptors.response.use(
  (response) => response,
  (error) => {
    // Handle 401 (existing logic)
    if (error.response?.status === 401) {
      // ... existing redirect logic
      return Promise.reject(error);
    }

    // Extract error message
    const message = error.response?.data?.message
      || error.response?.data?.error
      || error.message
      || 'An error occurred';

    // Map status codes to toast types
    const status = error.response?.status;

    if (status >= 500) {
      // Server errors
      useToast().error(message);
    } else if (status >= 400 && status < 500) {
      // Client errors (validation, not found, etc.)
      useToast().warning(message);
    } else if (!error.response) {
      // Network errors
      useToast().error('Network error. Please check your connection.');
    }

    return Promise.reject(error);
  }
);
```

**Status Code Mapping:**
- `400-499`: Warning toast (validation errors, not found, etc.)
- `401`: Error toast + redirect (keep existing behavior)
- `500-599`: Error toast (server errors)
- `Network errors`: Error toast

### 6. Toast Transitions (Vue Transition Classes)
**Purpose:** Smooth animations for toast enter/leave

**Note:** All styling uses Tailwind utility classes directly in components. Only transition animations need custom CSS as they're not fully supported by Tailwind utilities.

**Add ONLY transition classes to `resources/css/app.css`:**
```css
/* Toast transition animations */
.toast-enter-active {
  transition: all 300ms cubic-bezier(0.4, 0, 0.2, 1);
}

.toast-enter-from {
  opacity: 0;
  transform: translateX(100%);
}

.toast-enter-to {
  opacity: 1;
  transform: translateX(0);
}

.toast-leave-active {
  transition: all 200ms ease-out;
}

.toast-leave-from {
  opacity: 1;
  transform: translateX(0);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(100%);
}

.toast-move {
  transition: transform 300ms cubic-bezier(0.4, 0, 0.2, 1);
}
```

**All other styling is done with Tailwind classes directly in components:**
- Container positioning: `fixed top-4 right-4 z-50`
- Toast base: `rounded-lg shadow-lg border backdrop-blur-md min-w-[320px] max-w-md p-4 relative overflow-hidden`
- Success: `bg-green-50/95 dark:bg-green-900/90 border-green-200 dark:border-green-800 text-green-800 dark:text-green-200`
- Error: `bg-red-50/95 dark:bg-red-900/90 border-red-200 dark:border-red-800 text-red-800 dark:text-red-200`
- Warning: `bg-yellow-50/95 dark:bg-yellow-900/90 border-yellow-200 dark:border-yellow-800 text-yellow-800 dark:text-yellow-200`
- Info: `bg-blue-50/95 dark:bg-blue-900/90 border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-200`

### 7. Icon Updates (`resources/js/components/common/Icon.vue`)
**Purpose:** Add required icons for toast notifications

**Icons to Add:**
```javascript
// Success icon (filled check circle)
'check-circle-filled': 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z'

// Error icon (X circle)
'x-circle': 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 11H7v-2h10v2z'

// Warning icon (alert circle)
'alert-circle': 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z'

// Info icon (info circle)
'info-circle': 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z'
```

### 8. App Integration
**Purpose:** Mount ToastContainer globally

**Option A: In main Vue app (`resources/js/app.js`)**
```javascript
import ToastContainer from '@/components/common/ToastContainer.vue'

// In your root component template
<ToastContainer />
```

**Option B: In layout component**
Add to your main layout that wraps all pages.

## Detailed Implementation

### Toast Store Implementation
```javascript
// resources/js/stores/toast.js
import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useToastStore = defineStore('toast', () => {
  const toasts = ref([])
  const maxToasts = ref(3)
  const defaultDuration = ref(4000)

  let nextId = 0

  function addToast(toast) {
    const id = `toast-${nextId++}-${Date.now()}`

    const newToast = {
      id,
      type: toast.type || 'info',
      message: toast.message,
      duration: toast.duration ?? defaultDuration.value,
      closable: toast.closable ?? true,
      icon: toast.icon,
      timestamp: Date.now()
    }

    // Remove oldest if at max capacity
    if (toasts.value.length >= maxToasts.value) {
      toasts.value.shift()
    }

    toasts.value.push(newToast)

    // Auto-dismiss if duration is set
    if (newToast.duration > 0) {
      setTimeout(() => {
        removeToast(id)
      }, newToast.duration)
    }

    return id
  }

  function removeToast(id) {
    const index = toasts.value.findIndex(t => t.id === id)
    if (index > -1) {
      toasts.value.splice(index, 1)
    }
  }

  function clearAll() {
    toasts.value = []
  }

  return {
    toasts,
    maxToasts,
    defaultDuration,
    addToast,
    removeToast,
    clearAll
  }
})
```

### Toast Composable Implementation
```javascript
// resources/js/composables/useToast.js
import { useToastStore } from '@/stores/toast'

export function useToast() {
  const store = useToastStore()

  function show(message, options = {}) {
    return store.addToast({
      message,
      ...options
    })
  }

  function success(message, options = {}) {
    return show(message, { type: 'success', ...options })
  }

  function error(message, options = {}) {
    return show(message, { type: 'error', ...options })
  }

  function warning(message, options = {}) {
    return show(message, { type: 'warning', ...options })
  }

  function info(message, options = {}) {
    return show(message, { type: 'info', ...options })
  }

  function dismiss(id) {
    store.removeToast(id)
  }

  function clear() {
    store.clearAll()
  }

  return {
    show,
    success,
    error,
    warning,
    info,
    dismiss,
    clear
  }
}
```

### Toast Component Implementation
```vue
<!-- resources/js/components/common/Toast.vue -->
<template>
  <div
    :class="toastClasses"
    class="rounded-lg shadow-lg border backdrop-blur-md min-w-[320px] max-w-md p-4 relative overflow-hidden pointer-events-auto"
    role="alert"
    :aria-live="type === 'error' ? 'assertive' : 'polite'"
  >
    <div class="flex items-start space-x-3">
      <!-- Icon -->
      <Icon
        :name="iconName"
        :size="20"
        class="flex-shrink-0 w-5 h-5"
      />

      <!-- Message -->
      <p class="flex-1 text-sm font-medium leading-relaxed">{{ message }}</p>

      <!-- Close Button -->
      <button
        v-if="closable"
        @click="handleClose"
        class="flex-shrink-0 ml-3 text-current/60 hover:text-current transition-colors duration-200 cursor-pointer"
        aria-label="Close notification"
      >
        <Icon name="close" :size="16" />
      </button>
    </div>

    <!-- Progress Bar -->
    <div
      v-if="duration > 0"
      :class="progressBarClasses"
      class="absolute bottom-0 left-0 h-1 transition-all duration-100 ease-linear"
      :style="{ width: `${progress}%` }"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import Icon from './Icon.vue'

const props = defineProps({
  id: {
    type: String,
    required: true
  },
  type: {
    type: String,
    required: true,
    validator: (val) => ['success', 'error', 'warning', 'info'].includes(val)
  },
  message: {
    type: String,
    required: true
  },
  duration: {
    type: Number,
    default: 4000
  },
  closable: {
    type: Boolean,
    default: true
  },
  icon: {
    type: String,
    default: null
  }
})

const emit = defineEmits(['close'])

const progress = ref(100)
let progressInterval = null

const toastClasses = computed(() => `toast-${props.type}`)
const progressClasses = computed(() => `toast-progress-${props.type}`)

const iconName = computed(() => {
  if (props.icon) return props.icon

  const icons = {
    success: 'check-circle-filled',
    error: 'x-circle',
    warning: 'alert-circle',
    info: 'info-circle'
  }

  return icons[props.type]
})

function handleClose() {
  emit('close', props.id)
}

onMounted(() => {
  if (props.duration > 0) {
    const interval = 50 // Update every 50ms
    const decrement = (interval / props.duration) * 100

    progressInterval = setInterval(() => {
      progress.value -= decrement
      if (progress.value <= 0) {
        progress.value = 0
        clearInterval(progressInterval)
      }
    }, interval)
  }
})

onUnmounted(() => {
  if (progressInterval) {
    clearInterval(progressInterval)
  }
})
</script>
```

### Toast Container Implementation
```vue
<!-- resources/js/components/common/ToastContainer.vue -->
<template>
  <div
    :class="containerClasses"
    class="toast-container"
    aria-live="polite"
    aria-atomic="false"
  >
    <TransitionGroup
      name="toast"
      tag="div"
      class="space-y-3"
    >
      <Toast
        v-for="toast in toasts"
        :key="toast.id"
        v-bind="toast"
        @close="handleClose"
      />
    </TransitionGroup>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { storeToRefs } from 'pinia'
import { useToastStore } from '@/stores/toast'
import Toast from './Toast.vue'

const props = defineProps({
  position: {
    type: String,
    default: 'top-right',
    validator: (val) => [
      'top-right',
      'top-left',
      'bottom-right',
      'bottom-left',
      'top-center',
      'bottom-center'
    ].includes(val)
  }
})

const toastStore = useToastStore()
const { toasts } = storeToRefs(toastStore)

const containerClasses = computed(() => `toast-container-${props.position}`)

function handleClose(id) {
  toastStore.removeToast(id)
}
</script>
```

## Usage Examples

### In Components
```vue
<script setup>
import { useToast } from '@/composables/useToast'

const toast = useToast()

async function saveSettings() {
  try {
    await settingsService.update(data)
    toast.success('Settings saved successfully!')
  } catch (error) {
    // Error toast shown automatically by axios interceptor
    // But you can also show custom message:
    toast.error('Failed to save settings. Please try again.')
  }
}

function showInfo() {
  toast.info('New feature: Dark mode is now available!')
}
</script>
```

### In Stores
```javascript
import { useToast } from '@/composables/useToast'

export const useSettingsStore = defineStore('settings', () => {
  const toast = useToast()

  async function updateSetting(key, value) {
    try {
      const response = await settingsService.updateSetting(key, value)
      toast.success('Setting updated!')
      return response
    } catch (error) {
      // Error handled by axios interceptor
      throw error
    }
  }

  return { updateSetting }
})
```

### In Services
```javascript
// Generally avoid showing toasts in services
// Let the interceptor or component handle it
// But for special cases:
import { useToast } from '@/composables/useToast'

export const authService = {
  async logout() {
    const toast = useToast()
    await axios.post('/api/logout')
    toast.info('You have been logged out')
  }
}
```

## Accessibility Features

1. **ARIA Live Regions**: Each toast has `role="alert"` and appropriate `aria-live`
2. **Keyboard Support**: Close button is keyboard accessible
3. **Screen Reader Announcements**: Messages are announced when toasts appear
4. **Focus Management**: Toasts don't trap focus
5. **Color Independence**: Icons provide visual distinction beyond color

## Testing Strategy

### Unit Tests
- Toast store (add/remove/queue management)
- Toast composable (all methods)
- Progress bar countdown

### Component Tests
- Toast.vue (all variants, close button, progress)
- ToastContainer.vue (multiple toasts, positioning)
- Icon updates

### Integration Tests
- Axios interceptor toast display
- Store + composable + component integration
- Auto-dismiss timing
- Queue overflow behavior

### E2E Tests
- Toast appears on API error
- Manual toast display
- Multiple toasts stacking
- Close button functionality
- Auto-dismiss

### Accessibility Tests
- ARIA live region announcements
- Keyboard navigation
- Screen reader compatibility
- Color contrast

## Configuration Options

### Global Config (Optional Enhancement)
```javascript
// In app setup
app.provide('toastConfig', {
  position: 'top-right',
  maxToasts: 3,
  defaultDuration: 4000,
  showProgressBar: true
})
```

## File Checklist

- [ ] Create `resources/js/stores/toast.js`
- [ ] Create `resources/js/composables/useToast.js`
- [ ] Create `resources/js/components/common/Toast.vue`
- [ ] Create `resources/js/components/common/ToastContainer.vue`
- [ ] Update `resources/js/components/common/Icon.vue` (add 4 icons)
- [ ] Update `resources/js/bootstrap.js` (axios interceptor)
- [ ] Update `resources/css/components.css` (toast styles)
- [ ] Update main app to include `<ToastContainer />`
- [ ] Write unit tests
- [ ] Write component tests
- [ ] Write integration tests
- [ ] Write E2E tests
- [ ] Write accessibility tests

## Estimated Implementation Time
- Toast Store: 30 minutes
- Toast Component: 1 hour
- Toast Container: 30 minutes
- Toast Composable: 20 minutes
- Icon Updates: 15 minutes
- Axios Integration: 20 minutes
- Styling: 45 minutes
- Testing: 2 hours
- **Total: ~5-6 hours**

## Future Enhancements
1. Toast actions (e.g., "Undo" button)
2. Custom toast templates
3. Sound notifications (optional)
4. Persistent toasts (requires user action)
5. Toast groups/categories
6. Animation customization
7. Position per toast
8. Rich content (HTML, components)
