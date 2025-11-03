# Tooltip Directive

A lightweight, global Vue directive for displaying tooltips on hover.

## Location

- **Directive**: `resources/js/directives/tooltip.js`
- **Registered in**: `resources/js/spa.js`

## Setup

The directive is globally registered as `v-tooltip` - no imports needed in components.

## Basic Usage

```vue
<!-- Simple string tooltip -->
<button v-tooltip="'Save changes'">Save</button>

<!-- Icon with tooltip -->
<Icon name="help-circle" v-tooltip="'Click for help'" />

<!-- Dynamic content -->
<span v-tooltip="dynamicMessage">Hover me</span>
```

## Advanced Usage

```vue
<!-- With position -->
<button v-tooltip="{ text: 'Delete item', position: 'bottom' }">
  Delete
</button>

<!-- With delay -->
<button v-tooltip="{ text: 'Processing...', delay: 500 }">
  Process
</button>
```

## Options

When using object syntax:

```javascript
{
  text: string,       // Tooltip text (required)
  position: string,   // 'top' | 'bottom' | 'left' | 'right' (default: 'top')
  delay: number,      // Show delay in ms (default: 300)
}
```

## Features

- **Viewport-aware**: Automatically adjusts position if tooltip doesn't fit
- **Dark mode compatible**: Adapts to user's theme preference
- **Arrow pointer**: Visual indicator pointing to target element
- **Non-intrusive**: Appended to body with `position: fixed`
- **Smooth animations**: Fade in/out transitions

## Position Fallback

If tooltip doesn't fit in viewport with specified position, it automatically tries:
1. Specified position
2. Opposite position
3. Left position
4. Right position

## Examples

### Icon Tooltip
```vue
<Icon name="info" v-tooltip="'Additional information'" />
```

### Button Tooltip
```vue
<button
  v-tooltip="'This action cannot be undone'"
  @click="handleDelete"
>
  Delete
</button>
```

### Multi-line Tooltip
```vue
<span v-tooltip="'Line 1\nLine 2\nLine 3'">
  Hover for details
</span>
```

### Dynamic Tooltip
```vue
<template>
  <div v-tooltip="errorMessage">
    {{ status }}
  </div>
</template>

<script setup>
import { computed } from 'vue'

const status = ref('Processing')
const errorMessage = computed(() => {
  return status.value === 'Error' ? 'An error occurred' : 'All good'
})
</script>
```

## Styling

Tooltips use Tailwind classes and automatically support:
- Dark mode (`dark:` classes)
- Responsive text sizing
- High z-index (`z-[9999]`)
- Backdrop blur effect

## Accessibility

The tooltip directive is primarily visual. For critical information, consider using:
- `aria-label` attributes
- `aria-describedby` with hidden text
- Visible text instead of tooltips

## Cleanup

Tooltips are automatically removed when:
- Mouse leaves the element
- Component is unmounted
- Element is destroyed
