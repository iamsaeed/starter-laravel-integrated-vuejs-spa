# Confirmation Dialog System

A reusable, Promise-based confirmation dialog system with customizable themes and options.

## Location

- **Component**: `resources/js/components/common/ConfirmDialog.vue`
- **Container**: `resources/js/components/common/ConfirmDialogContainer.vue`
- **Store**: `resources/js/stores/dialog.js`
- **Composable**: `resources/js/composables/useDialog.js`

## Setup

The `<ConfirmDialogContainer />` is already mounted in `AdminLayout.vue` - no additional setup needed.

## Basic Usage

```javascript
import { useDialog } from '@/composables/useDialog'

const dialog = useDialog()

// Simple confirmation
const confirmed = await dialog.confirm('Are you sure?')
if (confirmed) {
  // User clicked confirm
}

// Danger confirmation (for destructive actions)
const confirmed = await dialog.confirmDanger(
  'This action cannot be undone',
  { title: 'Delete Item' }
)

// Success confirmation
const viewItem = await dialog.confirmSuccess(
  'Item saved successfully!',
  { confirmLabel: 'View Item', cancelLabel: 'Stay Here' }
)
```

## Available Methods

- `confirm(message, options)` - General confirmation
- `confirmDanger(message, options)` - Red theme for destructive actions
- `confirmSuccess(message, options)` - Green theme for success
- `confirmWarning(message, options)` - Yellow theme for warnings
- `confirmInfo(message, options)` - Blue theme for information
- `confirmError(message, options)` - Red theme for errors

## Common Options

```javascript
{
  title: string,                // Dialog title
  confirmLabel: string,         // Default: 'Confirm'
  cancelLabel: string,          // Default: 'Cancel'
  html: string,                 // HTML content (use instead of message)
  icon: string,                 // Custom icon name
  persistent: boolean,          // Force user to choose (no X button, ESC)
  onConfirm: Function,          // Callback when confirmed
  onCancel: Function,           // Callback when cancelled
}
```

## Examples

### Delete Confirmation
```javascript
async function handleDelete(id) {
  const confirmed = await dialog.confirmDanger(
    'This will permanently delete the item',
    {
      title: 'Delete Item',
      confirmLabel: 'Delete',
    }
  )

  if (!confirmed) return

  await resourceService.destroy(resource, id)
}
```

### Save Confirmation
```javascript
async function handleSave() {
  await saveForm()

  const viewItem = await dialog.confirmSuccess(
    'Changes saved successfully!',
    {
      title: 'Saved',
      confirmLabel: 'View Item',
      cancelLabel: 'Stay Here',
    }
  )

  if (viewItem) {
    router.push({ name: 'item.show', params: { id } })
  }
}
```

### Using HTML Content
```javascript
const agreed = await dialog.confirmInfo('', {
  title: 'Terms and Conditions',
  html: `
    <div class="space-y-4">
      <p>Please review and accept our terms:</p>
      <ul class="list-disc list-inside">
        <li>You must be 18 years or older</li>
        <li>You agree to our privacy policy</li>
      </ul>
    </div>
  `,
  confirmLabel: 'I Agree',
  cancelLabel: 'Decline',
})
```

## Themes

The dialog automatically styles buttons and icons based on the method used:

- **Danger**: Red buttons and icons (delete, destructive actions)
- **Success**: Green buttons and icons (confirmations, completions)
- **Warning**: Yellow buttons and icons (warnings, alerts)
- **Info**: Blue buttons and icons (information)
- **Error**: Red buttons and icons (errors)

## Dark Mode

The dialog system automatically supports dark mode based on the user's theme preference.

## Accessibility

- Focus management (auto-focus confirm button)
- Keyboard navigation (ESC to cancel, Enter to confirm)
- ARIA labels for screen readers
