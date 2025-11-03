# Tooltip Directive Implementation Plan

## Overview
Create a lightweight, global Vue 3 directive (`v-tooltip`) that works like native HTML attributes with:
- ✅ Simple usage: `<button v-tooltip="'Save changes'">Save</button>`
- ✅ Dark mode compatible (reads from `document.documentElement.classList`)
- ✅ Viewport-aware positioning (auto-adjusts when near edges)
- ✅ Non-intrusive (uses CSS `position: fixed` with high z-index)
- ✅ No DOM disruption (tooltip element appended to body)
- ✅ Clean animations with Tailwind transitions

## File Structure

### 1. Create Directive File
**Location**: `resources/js/directives/tooltip.js`
- Export a custom Vue directive
- Handle mounted/unmounted lifecycle hooks
- Manage tooltip element creation/destruction
- Calculate smart positioning (top, bottom, left, right with auto-fallback)
- Add event listeners for mouseenter/mouseleave
- Support string and object syntax:
  - String: `v-tooltip="'Text'"`
  - Object: `v-tooltip="{ text: 'Text', position: 'top', delay: 300 }"`

### 2. Register Directive Globally
**Location**: `resources/js/spa.js`
- Import the tooltip directive
- Register globally: `app.directive('tooltip', tooltip)`
- Makes it available everywhere without imports

## Technical Implementation Details

### Positioning Logic (Viewport Aware)
1. **Default position**: Top
2. **Collision detection**: Check if tooltip fits in viewport
3. **Auto-fallback order**: top → bottom → left → right
4. **Smart placement**:
   - Calculate element bounding rect
   - Calculate tooltip dimensions
   - Check viewport boundaries
   - Adjust position + arrow accordingly

### Styling Approach
- Use Tailwind utility classes
- Dark mode: `bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900`
- Arrow: CSS triangle using borders
- Z-index: `z-[9999]` to stay above all content
- Positioning: `fixed` to avoid parent overflow issues
- Transitions: Fade in/out with scale

### Features
1. **Delay**: Configurable show delay (default: 300ms)
2. **Arrow**: Visual pointer to target element
3. **Multi-line**: Support `\n` or wrap long text
4. **Hover behavior**: Show on mouseenter, hide on mouseleave
5. **Cleanup**: Remove tooltip on unmount/destroy

## Example Usage

```vue
<!-- Simple string -->
<Icon name="help-circle" v-tooltip="'Click for help'" />

<!-- With options -->
<button v-tooltip="{ text: 'This action cannot be undone', position: 'bottom' }">
  Delete
</button>

<!-- Dynamic content -->
<span v-tooltip="dynamicTooltipText">Hover me</span>
```

## Files to Create/Modify

1. **CREATE** `resources/js/directives/tooltip.js` (~150 lines)
   - Directive logic
   - Positioning algorithm
   - Event handlers

2. **MODIFY** `resources/js/spa.js` (+2 lines)
   - Import directive
   - Register globally

3. **BUILD** Frontend assets
   - Run `npm run build`

## Benefits
- ✅ Zero dependencies (pure Vue + Tailwind)
- ✅ ~3KB uncompressed
- ✅ Works with existing dark mode system
- ✅ No layout shift or DOM pollution
- ✅ Accessible (can add aria-describedby if needed)
- ✅ Reusable across entire application
