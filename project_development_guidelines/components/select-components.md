# Select Components

The application includes several select components for different use cases.

## SelectInput

Basic single/multiple select dropdown for static options.

**Location**: `resources/js/components/form/SelectInput.vue`

**Usage**:
```vue
<SelectInput
  v-model="selectedValue"
  :options="options"
  label="Choose an option"
  placeholder="Select..."
/>

<!-- Multiple selection -->
<SelectInput
  v-model="selectedValues"
  :options="options"
  multiple
  label="Choose options"
/>
```

**Props**:
- `options` - Array of `{ value, label }` objects
- `multiple` - Enable multiple selection
- `placeholder` - Placeholder text
- `disabled` - Disable the select

## VirtualSelectInput

High-performance select for large datasets (500+ items) using virtual scrolling.

**Location**: `resources/js/components/form/VirtualSelectInput.vue`

**Usage**:
```vue
<VirtualSelectInput
  v-model="selectedId"
  :options="largeOptionsList"
  label="Select from large list"
/>
```

**Use when**:
- Options array has 500+ items
- Need to maintain performance with large datasets
- Country/timezone selectors with full lists

## ServerSelectInput

Server-side search select with debounced API requests.

**Location**: `resources/js/components/form/ServerSelectInput.vue`

**Usage**:
```vue
<ServerSelectInput
  v-model="selectedId"
  :search-endpoint="`/api/search/users`"
  label="Search users"
  :min-chars="2"
  :debounce="300"
/>
```

**Props**:
- `searchEndpoint` - API endpoint for search
- `minChars` - Minimum characters before search (default: 2)
- `debounce` - Debounce delay in ms (default: 300)
- `multiple` - Enable multiple selection

**Use when**:
- Dataset is too large to load at once
- Need real-time search functionality
- Working with dynamic, filtered data

## ResourceSelectInput

Specialized select for choosing Resource items (uses Resource API endpoints).

**Location**: `resources/js/components/form/ResourceSelectInput.vue`

**Usage**:
```vue
<ResourceSelectInput
  v-model="userId"
  resource="users"
  label="Select user"
  value-key="id"
  label-key="name"
/>
```

**Props**:
- `resource` - Resource name (e.g., 'users', 'roles')
- `valueKey` - Field to use as value (default: 'id')
- `labelKey` - Field to display (default: 'name')
- `filters` - Additional filters for Resource query

**Use when**:
- Selecting items from a Resource (users, roles, etc.)
- Need to integrate with Resource CRUD system
- Want automatic loading from Resource endpoints

## Specialized Selects

### CountrySelect
Pre-configured select for countries.

**Location**: `resources/js/components/settings/CountrySelect.vue`

```vue
<CountrySelect v-model="country" />
```

### TimezoneSelect
Pre-configured select for timezones.

**Location**: `resources/js/components/settings/TimezoneSelect.vue`

```vue
<TimezoneSelect v-model="timezone" />
```

### CategorySelect
Pre-configured select for website categories.

**Location**: `resources/js/components/website/CategorySelect.vue`

```vue
<CategorySelect v-model="categoryId" />
```

## Selection Guide

| Scenario | Component | Reason |
|----------|-----------|--------|
| Static list (< 100 items) | SelectInput | Simple, lightweight |
| Large static list (500+) | VirtualSelectInput | Performance |
| Dynamic search | ServerSelectInput | Server-side filtering |
| Resource items | ResourceSelectInput | Integrates with Resource system |
| Countries | CountrySelect | Pre-configured |
| Timezones | TimezoneSelect | Pre-configured |

## Common Patterns

### With VeeValidate
```vue
<Field name="status" v-slot="{ field, errors }">
  <SelectInput
    v-bind="field"
    :options="statusOptions"
    label="Status"
    :class="{ 'border-red-500': errors[0] }"
  />
  <FormError :error="errors[0]" />
</Field>
```

### Loading State
```vue
<ServerSelectInput
  v-model="selectedId"
  :search-endpoint="endpoint"
  :loading="isLoading"
  loading-text="Loading..."
/>
```

### Multiple Selection with Tags
```vue
<SelectInput
  v-model="selectedTags"
  :options="tagOptions"
  multiple
  label="Tags"
  placeholder="Select tags..."
/>
```

## Styling

All select components support:
- Dark mode
- Error states (red border)
- Disabled states
- Custom placeholder text
- Consistent styling with other form inputs

## Accessibility

All select components include:
- Proper label association
- Keyboard navigation (arrow keys, Enter, Escape)
- Screen reader support
- Focus indicators
