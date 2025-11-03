# Confirmation Dialog System Implementation Plan

## Overview
Create a reusable, asynchronous confirmation dialog system similar to the existing toast notification system, with customizable buttons, icons, messages, and types (success, danger, error, info, warning). Uses theme-based Tailwind classes for consistent styling.

## Components to Create

### 1. **ConfirmDialog.vue** Component
`resources/js/components/common/ConfirmDialog.vue`
- Modal overlay with backdrop
- Close button (X) in top-right corner that triggers `onClose` callback
- Icon based on type or custom icon
- Title and message text (supports HTML)
- Customizable confirm/cancel buttons with labels
- Confirm button triggers `onConfirm` callback and resolves Promise with `true`
- Cancel button triggers `onCancel` callback and resolves Promise with `false`
- Theme-based color schemes using Tailwind classes
- Size variants (sm, md, lg, xl)
- Smooth animations (fade, scale, slide)
- Focus trap for accessibility
- Auto-focus on specified button
- ESC key to cancel (triggers `onClose` callback)
- Backdrop click behavior (configurable)
- Loading state with spinner on buttons
- Optional confirmation input for critical actions
- Optional timer/countdown for auto-dismiss

### 2. **ConfirmDialogContainer.vue** Component
`resources/js/components/common/ConfirmDialogContainer.vue`
- Manages dialog queue (prevents duplicates, priority handling)
- Renders active dialogs from Pinia store
- Positioned fixed on screen with proper z-index stacking
- Handles multiple dialogs if stackable enabled

### 3. **Pinia Store for Dialogs**
`resources/js/stores/dialog.js`
- State: active dialogs array
- Actions:
  - `showDialog(options)` - returns a Promise, handles queue/singleton logic
  - `resolveDialog(id, confirmed)` - resolves the Promise with `true`, calls `onConfirm()` if provided
  - `dismissDialog(id)` - resolves the Promise with `false`, calls `onCancel()` if provided
  - `closeDialog(id)` - resolves the Promise with `false`, calls `onClose()` if provided
  - `setLoading(id, loading)` - updates loading state for a dialog
- Store dialog configurations with Promise resolve/reject handlers and event callbacks
- Queue management with priority support
- Singleton enforcement for specific dialog types

### 4. **useDialog Composable**
`resources/js/composables/useDialog.js`
- Wrapper around the dialog store
- Helper methods:
  - `confirm(message, options)` - general confirmation
  - `confirmSuccess(message, options)` - success confirmation (form saves, completions)
  - `confirmDanger(message, options)` - danger confirmation (delete, destructive actions)
  - `confirmError(message, options)` - error confirmation
  - `confirmInfo(message, options)` - info confirmation
  - `confirmWarning(message, options)` - warning confirmation
  - `prompt(message, options)` - prompt with input fields
- All methods return Promises that resolve to `true` (confirmed), `false` (cancelled), or input values (for prompt)

## Dialog Options Structure
```javascript
{
  // === BASIC OPTIONS ===
  type: 'success' | 'danger' | 'error' | 'warning' | 'info',  // Default: 'info'
  title: string,                    // Dialog title
  message: string,                  // Dialog message
  html: string | null,              // HTML content (overrides message if provided)
  sanitize: boolean,                // Auto-sanitize HTML, default: true
  icon: string | null,              // Custom icon name (overrides type default)

  // === BUTTON OPTIONS ===
  confirmLabel: string,             // Default: 'Confirm'
  cancelLabel: string,              // Default: 'Cancel'
  confirmClass: string,             // Custom button classes
  cancelClass: string,              // Custom button classes
  showCancel: boolean,              // Show cancel button, default: true
  buttons: Array<{                  // Custom multi-button layout (overrides confirm/cancel)
    label: string,
    action: string | Function,
    variant: 'primary' | 'secondary' | 'danger',
    class: string,
  }> | null,

  // === BEHAVIOR OPTIONS ===
  closable: boolean,                // Show X button, default: true
  closeOnBackdrop: boolean,         // Close dialog when clicking backdrop, default: false
  closeOnEscape: boolean,           // Close dialog on ESC key, default: true
  persistent: boolean,              // Cannot be dismissed (force user to choose), default: false

  // === LOADING STATE ===
  loading: boolean,                 // Show loading spinner on confirm button
  loadingText: string,              // Text to show while loading (e.g., "Deleting...")
  disableOnLoading: boolean,        // Disable buttons during loading, default: true

  // === FOCUS MANAGEMENT ===
  autoFocusButton: 'confirm' | 'cancel' | null,  // Which button to focus on open
  returnFocus: boolean,             // Return focus to trigger element on close, default: true

  // === QUEUE & PRIORITY ===
  priority: number,                 // Higher priority dialogs show first, default: 0
  queue: boolean,                   // Queue if another dialog is open vs. replace, default: true
  singleton: boolean,               // Only allow one dialog of this type at a time, default: false

  // === SIZE & LAYOUT ===
  size: 'sm' | 'md' | 'lg' | 'xl',  // Dialog width, default: 'md'
  fullScreen: boolean,              // Mobile full-screen mode, default: false
  mobilePosition: 'bottom' | 'center',  // Sheet style vs. modal on mobile, default: 'center'

  // === ANIMATION ===
  animation: 'fade' | 'scale' | 'slide-up' | 'slide-down',  // Default: 'scale'
  animationDuration: number,        // Duration in ms, default: 200

  // === TIMER/COUNTDOWN ===
  timer: number,                    // Auto-dismiss after X milliseconds, default: null
  timerProgressBar: boolean,        // Show progress bar like Toast, default: false
  timerOnButton: boolean,           // Show countdown on confirm button, default: false

  // === CONFIRMATION INPUT (Dangerous Actions) ===
  requireConfirmation: boolean,     // Require user to type text to confirm, default: false
  confirmationText: string,         // Text user must type (e.g., "DELETE")
  confirmationPlaceholder: string,  // Placeholder for input
  confirmationHint: string,         // Helper text (e.g., "Type DELETE to confirm")

  // === INPUT FIELDS (Prompt Dialogs) ===
  inputs: Array<{
    type: 'text' | 'email' | 'password' | 'number' | 'textarea',
    name: string,
    label: string,
    placeholder: string,
    required: boolean,
    value: any,
    validation: Function,
  }> | null,

  // === CUSTOM CONTENT ===
  component: Component | null,      // Custom Vue component to render
  componentProps: object,           // Props to pass to component

  // === THEME ===
  theme: 'light' | 'dark' | 'auto', // Override system theme, default: 'auto'
  customClass: string,              // Additional CSS classes
  customStyle: object,              // Inline styles

  // === MOBILE OPTIMIZATIONS ===
  mobileFullScreen: boolean,        // Full screen on mobile devices, default: false
  swipeToDismiss: boolean,          // Swipe down to close on mobile, default: false

  // === ADVANCED ===
  zIndex: number,                   // Manual z-index control
  stackable: boolean,               // Allow multiple dialogs stacked, default: false

  // === EVENT CALLBACKS ===
  onConfirm: Function | null,       // Callback when confirm button is clicked
  onCancel: Function | null,        // Callback when cancel button is clicked
  onClose: Function | null,         // Callback when dialog is closed (X button or ESC)
  onError: Function | null,         // Called if onConfirm throws error
  onSubmit: Function | null,        // Callback for prompt dialogs with input values

  // === ERROR HANDLING ===
  showErrorInDialog: boolean,       // Display error in dialog vs. dismiss, default: false
}
```

## Theme Classes (Tailwind)

### Dialog Background & Border
```javascript
const dialogClasses = {
  success: 'bg-white dark:bg-gray-800 border-primary-200 dark:border-primary-700',
  danger: 'bg-white dark:bg-gray-800 border-red-200 dark:border-red-700',
  warning: 'bg-white dark:bg-gray-800 border-yellow-200 dark:border-yellow-700',
  info: 'bg-white dark:bg-gray-800 border-blue-200 dark:border-blue-700',
  error: 'bg-white dark:bg-gray-800 border-red-200 dark:border-red-700',
}
```

### Icon Colors
```javascript
const iconClasses = {
  success: 'text-primary-600 dark:text-primary-400',
  danger: 'text-red-600 dark:text-red-400',
  warning: 'text-yellow-600 dark:text-yellow-400',
  info: 'text-blue-600 dark:text-blue-400',
  error: 'text-red-600 dark:text-red-400',
}
```

### Title Colors
```javascript
const titleClasses = {
  success: 'text-gray-900 dark:text-gray-100',
  danger: 'text-gray-900 dark:text-gray-100',
  warning: 'text-gray-900 dark:text-gray-100',
  info: 'text-gray-900 dark:text-gray-100',
  error: 'text-gray-900 dark:text-gray-100',
}
```

### Confirm Button
```javascript
const confirmButtonClasses = {
  success: 'bg-primary-600 hover:bg-primary-700 text-white dark:bg-primary-500 dark:hover:bg-primary-600',
  danger: 'bg-red-600 hover:bg-red-700 text-white dark:bg-red-500 dark:hover:bg-red-600',
  warning: 'bg-yellow-600 hover:bg-yellow-700 text-white dark:bg-yellow-500 dark:hover:bg-yellow-600',
  info: 'bg-blue-600 hover:bg-blue-700 text-white dark:bg-blue-500 dark:hover:bg-blue-600',
  error: 'bg-red-600 hover:bg-red-700 text-white dark:bg-red-500 dark:hover:bg-red-600',
}
```

### Cancel Button
```javascript
const cancelButtonClasses = 'bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200'
```

## Icon Mappings
```javascript
const iconMap = {
  success: 'check-circle-filled',
  danger: 'delete',
  error: 'alert-triangle',
  warning: 'alert-circle',
  info: 'info-circle',
}
```

## Integration Steps

### 5. Add ConfirmDialogContainer to AdminLayout
Update `resources/js/layouts/AdminLayout.vue`:
- Import and add `<ConfirmDialogContainer />` component at the root level (after router-view)

### 6. Update ResourceTable Delete Handler
Update `resources/js/components/resource/ResourceTable.vue`:
- Import `useDialog` composable
- Replace native `confirm()` with `dialog.confirmDanger()`

## Usage Examples

### Example 1: Basic Delete Confirmation (Promise-based)
```javascript
import { useDialog } from '@/composables/useDialog'

const dialog = useDialog()

async function handleDelete(id) {
  const confirmed = await dialog.confirmDanger(
    'Are you sure you want to delete this item? This action cannot be undone.',
    {
      title: 'Delete Item',
      confirmLabel: 'Delete',
      cancelLabel: 'Cancel',
    }
  )

  if (!confirmed) return

  try {
    await resourceService.destroy(props.resource, id)
    emit('deleted', [id])
    await fetchData()
  } catch (error) {
    console.error('Delete failed:', error)
  }
}
```

### Example 2: Delete with Loading State
```javascript
async function handleDelete(id) {
  const confirmed = await dialog.confirmDanger(
    'Are you sure you want to delete this item? This action cannot be undone.',
    {
      title: 'Delete Item',
      confirmLabel: 'Delete',
      cancelLabel: 'Cancel',
      onConfirm: async () => {
        // The dialog will automatically show loading state
        try {
          await resourceService.destroy(props.resource, id)
          emit('deleted', [id])
          await fetchData()
        } catch (error) {
          console.error('Delete failed:', error)
        }
      }
    }
  )
}
```

### Example 3: Critical Action with Confirmation Input
```javascript
async function handleDeleteAccount(userId) {
  const confirmed = await dialog.confirmDanger(
    'This will permanently delete the account and all associated data.',
    {
      title: 'Delete Account',
      confirmLabel: 'Delete Account',
      cancelLabel: 'Cancel',
      requireConfirmation: true,
      confirmationText: 'DELETE',
      confirmationPlaceholder: 'Type DELETE to confirm',
      confirmationHint: 'Type "DELETE" in capital letters to confirm this action',
      persistent: true,  // Force user to make a choice
    }
  )

  if (!confirmed) return

  // Proceed with deletion
}
```

### Example 4: Success Confirmation After Save
```javascript
async function handleSave() {
  try {
    await saveForm()

    const viewItem = await dialog.confirmSuccess(
      'Your changes have been saved successfully!',
      {
        title: 'Saved',
        confirmLabel: 'View Item',
        cancelLabel: 'Stay Here',
        icon: 'check-circle-filled',
      }
    )

    if (viewItem) {
      router.push({ name: 'item.show', params: { id: item.id } })
    }
  } catch (error) {
    console.error('Save failed:', error)
  }
}
```

### Example 5: Prompt Dialog with Input
```javascript
async function handleRename(item) {
  const result = await dialog.prompt(
    'Enter a new name for this item',
    {
      title: 'Rename Item',
      confirmLabel: 'Rename',
      cancelLabel: 'Cancel',
      inputs: [
        {
          type: 'text',
          name: 'name',
          label: 'Name',
          placeholder: 'Enter new name',
          required: true,
          value: item.name,
          validation: (value) => {
            if (value.length < 3) return 'Name must be at least 3 characters'
            return null
          }
        }
      ],
      onSubmit: async (values) => {
        await resourceService.update(item.id, { name: values.name })
        await fetchData()
      }
    }
  )

  // result is false if cancelled, or object with input values if submitted
  if (result) {
    console.log('New name:', result.name)
  }
}
```

### Example 6: Custom Multi-Button Dialog
```javascript
async function handleDocument() {
  const result = await dialog.confirm(
    'You have unsaved changes. What would you like to do?',
    {
      title: 'Unsaved Changes',
      showCancel: false,
      buttons: [
        {
          label: 'Save',
          action: 'save',
          variant: 'primary',
        },
        {
          label: 'Don\'t Save',
          action: 'discard',
          variant: 'secondary',
        },
        {
          label: 'Cancel',
          action: 'cancel',
          variant: 'secondary',
        }
      ]
    }
  )

  if (result === 'save') {
    await saveDocument()
  } else if (result === 'discard') {
    discardChanges()
  }
  // result === 'cancel' - do nothing
}
```

### Example 7: Warning with Timer
```javascript
async function handleLogout() {
  const confirmed = await dialog.confirmWarning(
    'You will be logged out due to inactivity.',
    {
      title: 'Session Timeout',
      confirmLabel: 'Stay Logged In',
      cancelLabel: 'Logout Now',
      timer: 10000,  // 10 seconds
      timerProgressBar: true,
      timerOnButton: true,  // Shows "Stay Logged In (9s)" countdown
    }
  )

  if (!confirmed) {
    // Timer expired or user clicked logout
    await logout()
  } else {
    // User clicked to stay logged in
    refreshSession()
  }
}
```

### Example 8: Using Event Callbacks
```javascript
async function handleBulkDelete(selectedIds) {
  await dialog.confirmDanger(
    `Delete ${selectedIds.length} items?`,
    {
      title: 'Bulk Delete',
      confirmLabel: 'Delete All',
      cancelLabel: 'Cancel',
      onConfirm: async () => {
        try {
          await resourceService.bulkDelete(selectedIds)
          emit('deleted', selectedIds)
          await fetchData()
        } catch (error) {
          console.error('Bulk delete failed:', error)
        }
      },
      onCancel: () => {
        console.log('Bulk delete cancelled')
      },
      onClose: () => {
        console.log('Dialog closed without action')
      }
    }
  )
}
```

### Example 9: HTML Content with Formatting
```javascript
async function handleTerms() {
  const agreed = await dialog.confirmInfo(
    '', // Empty message since we're using HTML
    {
      title: 'Terms and Conditions',
      html: `
        <div class="space-y-4">
          <p>Please review and accept our terms:</p>
          <ul class="list-disc list-inside space-y-2">
            <li>You must be 18 years or older</li>
            <li>You agree to our privacy policy</li>
            <li>You will not share your account</li>
          </ul>
          <p class="text-sm text-gray-500">Last updated: January 2025</p>
        </div>
      `,
      confirmLabel: 'I Agree',
      cancelLabel: 'Decline',
      size: 'lg',
      persistent: true,
    }
  )

  if (agreed) {
    await acceptTerms()
  }
}
```

## Size Variants
- **sm**: `max-w-sm` (384px) - Simple confirmations
- **md**: `max-w-md` (448px) - Default size
- **lg**: `max-w-lg` (512px) - More content
- **xl**: `max-w-xl` (576px) - Complex dialogs

## Animation Types
- **fade**: Opacity transition
- **scale**: Zoom in/out effect (default)
- **slide-up**: Slide from bottom
- **slide-down**: Slide from top

## Benefits
1. **Promise-based**: Clean async/await syntax
2. **Reusable**: Use from any component via composable
3. **Type-safe**: Consistent API across the app
4. **Customizable**: Full control over appearance and behavior
5. **Accessible**: Keyboard navigation, focus management, ARIA attributes
6. **Consistent UX**: Matches existing Toast system design with theme classes
7. **Flexible**: Supports simple confirmations to complex multi-step dialogs
8. **Queue Management**: Prevents dialog spam, priority support
9. **Loading States**: Built-in async operation handling
10. **Mobile Optimized**: Responsive design with mobile-specific features

## Files to Create/Modify
- ✅ Create: `resources/js/components/common/ConfirmDialog.vue`
- ✅ Create: `resources/js/components/common/ConfirmDialogContainer.vue`
- ✅ Create: `resources/js/stores/dialog.js`
- ✅ Create: `resources/js/composables/useDialog.js`
- ✅ Modify: `resources/js/layouts/AdminLayout.vue`
- ✅ Modify: `resources/js/components/resource/ResourceTable.vue`

## Implementation Priority

### Phase 1: Core Functionality (P0)
- Basic dialog component with theme classes
- Store and composable setup
- Promise-based API
- Size variants
- Auto-focus management
- Queue management (prevent duplicates)
- Backdrop/ESC behavior

### Phase 2: Enhanced Features (P1)
- Loading states
- HTML content support
- Confirmation input for dangerous actions
- Animation customization
- Error handling in callbacks
- Multiple button layouts

### Phase 3: Advanced Features (P2)
- Timer/countdown
- Custom component slots
- Input fields (prompt dialogs)
- Mobile optimizations
- Theme integration

### Phase 4: Polish (P3)
- Dialog stacking
- Advanced queue priorities
- Additional animations
- Performance optimizations
