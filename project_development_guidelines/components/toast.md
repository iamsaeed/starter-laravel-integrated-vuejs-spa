# Toast Notification System

A subtle toast notification system with automatic API error handling and manual notifications.

## Location

- **Component**: `resources/js/components/common/Toast.vue`
- **Container**: `resources/js/components/common/ToastContainer.vue`
- **Store**: `resources/js/stores/toast.js`
- **Composable**: `resources/js/composables/useToast.js`

## Setup

The `<ToastContainer />` is already mounted in layouts - no additional setup needed.

## Basic Usage

```javascript
import { useToast } from '@/composables/useToast'

const toast = useToast()

// Success notification
toast.success('Settings saved successfully!')

// Error notification
toast.error('Failed to save settings')

// Warning notification
toast.warning('Please verify your email')

// Info notification
toast.info('New features available!')
```

## Methods

- `toast.success(message, options)` - Green toast for success
- `toast.error(message, options)` - Red toast for errors
- `toast.warning(message, options)` - Yellow toast for warnings
- `toast.info(message, options)` - Blue toast for information
- `toast.show(message, options)` - Custom toast with full control

## Options

```javascript
{
  duration: number,    // Auto-dismiss duration in ms (default: 4000, 0 = no auto-dismiss)
  closable: boolean,   // Show close button (default: true)
  icon: string,        // Custom icon name (overrides type default)
}
```

## Examples

### Success Toast
```javascript
async function saveSettings() {
  await settingsService.update(data)
  toast.success('Settings saved successfully!')
}
```

### Error Toast with Custom Duration
```javascript
toast.error('Failed to save settings', { duration: 6000 })
```

### Warning with Custom Icon
```javascript
toast.warning('Please verify your email', {
  icon: 'mail',
  closable: false
})
```

### Custom Toast
```javascript
toast.show('Custom message', {
  type: 'info',
  icon: 'bell',
  duration: 3000
})
```

### Manual Dismissal
```javascript
const toastId = toast.success('Processing...')

// Later, dismiss manually
toast.dismiss(toastId)

// Or clear all toasts
toast.clear()
```

## Automatic API Error Handling

The axios interceptor automatically shows toast notifications for API errors:

- **4xx errors**: Warning toast (validation errors, not found, etc.)
- **5xx errors**: Error toast (server errors)
- **Network errors**: Error toast with connection message

No manual toast calls needed for API errors - they're handled automatically.

## Queue System

- Maximum 3 toasts visible at once
- Oldest toast is removed when new toast arrives at max capacity
- Smooth transitions for enter/leave animations

## Toast Types and Icons

Each type has default icon and color scheme:

- **Success**: Green with check-circle icon
- **Error**: Red with x-circle icon
- **Warning**: Yellow with alert-circle icon
- **Info**: Blue with info-circle icon

## Dark Mode

Toasts automatically adapt to dark mode based on user preference.

## Position

Toasts appear in the top-right corner by default. Position is configured globally in the `ToastContainer` component.

## Accessibility

- ARIA live regions for screen reader announcements
- Keyboard accessible close buttons
- Role="alert" for important messages
