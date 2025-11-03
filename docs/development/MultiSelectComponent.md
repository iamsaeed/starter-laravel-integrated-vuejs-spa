# Multi-Select Component Planning

## Core Shared Features

### Selection & Validation
- **Minimum selection** - Configurable minimum number of items required (1+)
- **Maximum selection** - Optional maximum number of items allowed
- **Required validation** - Integration with VeeValidate for required field validation
- **Custom validation rules** - Support for custom validation logic

### UI/UX Features
- **Tag display** - Selected items shown as dismissible tags/chips
- **Tag removal** - Click X icon to remove individual tags
- **Clear all** - Optional "Clear all" button to remove all selections
- **Disabled state** - Support for disabled/readonly mode
- **Empty state** - Custom message when no options available
- **Loading state** - Loading indicator during data fetch/search
- **Error state** - Display validation errors below component

### Visual & Styling
- **Tag styling** - Customizable tag colors, sizes, and styles
- **Tag positioning** - Display tags inside input or below it
- **Dropdown positioning** - Auto-position dropdown (top/bottom based on space)
- **Max height** - Configurable max height for dropdown with scroll
- **Dark mode** - Support for dark mode theming
- **Custom icons** - Customizable icons for dropdown, remove, clear actions

### Accessibility
- **Keyboard navigation** - Arrow keys to navigate, Enter to select, Escape to close
- **Screen reader support** - Proper ARIA labels and announcements
- **Focus management** - Proper focus states and tab order
- **Label association** - Proper label-input association

### Data Handling
- **Value/Label separation** - Support for different value and display label
- **Custom display template** - Slot for custom option rendering
- **Selected item filtering** - Remove selected items from available options
- **Preserve order** - Maintain selection order
- **Duplicate prevention** - Prevent selecting same item twice

---

## Component 1: Static Multi-Select (API Data)

### Data Loading
- **Initial data prop** - Accept pre-loaded array of options from API
- **Reactive updates** - Watch for prop changes and update options
- **Data transformation** - Support for transforming API response format
- **Grouping support** - Optional grouping of options with headers

### Performance
- **Virtual scrolling** - For large lists (500+ items)
- **Memoization** - Cache filtered/sorted results
- **Debounced filtering** - Debounce local search/filter

### Search/Filter (Local)
- **Client-side search** - Filter options locally by typing
- **Case insensitive** - Case-insensitive search
- **Multi-field search** - Search across multiple fields (name, description, etc.)
- **Fuzzy matching** - Optional fuzzy search
- **Highlight matches** - Highlight search term in results

### Additional Features
- **Select all** - Option to select all visible items
- **Bulk selection** - Checkbox mode for bulk selection
- **Sorting** - Sort options alphabetically or custom order
- **Default selections** - Pre-select items on load

---

## Component 2: Live Search Multi-Select (Server-Side)

### Server Search
- **Debounced search** - Configurable debounce delay (default 300ms)
- **Minimum characters** - Require minimum chars before search (default 2-3)
- **API endpoint** - Configurable search endpoint URL
- **Query parameters** - Customizable query param names
- **Request cancellation** - Cancel previous requests on new search
- **Search on focus** - Optional initial search when focused

### Loading & Performance
- **Loading indicator** - Show spinner during search
- **Request throttling** - Limit requests per second
- **Cache strategy** - Cache search results locally
- **Pagination support** - Load more results on scroll
- **Initial load** - Optional initial items without search

### Error Handling
- **Error messages** - Display friendly error messages
- **Retry mechanism** - Retry failed requests
- **Fallback options** - Show cached/default options on error
- **Network status** - Indicate offline/network issues

### Advanced Features
- **Multi-field API search** - Support searching multiple fields server-side
- **Filters** - Additional filter parameters sent to API
- **Sorting options** - Server-side sorting parameters
- **Related data** - Load related/nested data with selections
- **Infinite scroll** - Load more results on scroll
- **Result preview** - Show item details on hover before selection

### Selected Items Handling
- **Preserve selections** - Keep selected items even if not in current search results
- **Re-fetch selected** - Option to re-fetch selected item details from server
- **Sync with server** - Validate selections against server on submit

---

## Configuration Options (Both Components)

### Props/Config
- `modelValue` / `v-model` - Selected values array
- `options` - Available options (Component 1 only)
- `searchEndpoint` - API endpoint (Component 2 only)
- `label` - Field label
- `placeholder` - Input placeholder text
- `required` - Is field required
- `minSelection` - Minimum items required
- `maxSelection` - Maximum items allowed
- `disabled` - Disable component
- `searchable` - Enable/disable search
- `clearable` - Show clear all button
- `closeOnSelect` - Close dropdown after selection
- `valueKey` - Key for item value (default: 'id')
- `labelKey` - Key for item label (default: 'name')
- `tagColor` - Tag/chip color theme
- `maxVisibleTags` - Max tags to show before "+N more"

### Events
- `@update:modelValue` - Emit selected values
- `@search` - Emit search query
- `@select` - Emit when item selected
- `@remove` - Emit when item removed
- `@clear` - Emit when all cleared
- `@open` - Emit when dropdown opens
- `@close` - Emit when dropdown closes

### Slots
- `#tag` - Custom tag rendering
- `#option` - Custom option rendering
- `#empty` - Empty state content
- `#loading` - Loading state content
- `#prepend` - Content before input
- `#append` - Content after input

---

## Integration Requirements

### VeeValidate Integration
- Support `rules` prop for validation
- Display validation errors
- Trigger validation on blur/change
- Support field-level and form-level validation

### API Service Integration
- Use existing `resourceService.js` or dedicated service
- Follow project's API error handling patterns
- Use centralized axios instance

### State Management
- Optional Pinia store integration for caching
- Sync selections with form state
- Persist selections in localStorage (optional)

### Reusability
- Extend base `SelectInput.vue` if exists
- Follow project's form component patterns
- Match existing form field styling
