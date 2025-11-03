# DataTable Component

A flexible, reusable table component inspired by ResourceTable.vue, designed for use across the application.

## Features

- ✅ **Flexible Column Configuration** - Define columns with custom formatting, sorting, and visibility
- ✅ **Client & Server-Side Operations** - Supports both client-side and server-side search, sorting, and pagination
- ✅ **Row Selection** - Single or multi-row selection with bulk actions
- ✅ **Custom Cell Rendering** - Use slots to customize cell content
- ✅ **Search & Filtering** - Built-in search with debouncing
- ✅ **Sorting** - Sortable columns with visual indicators
- ✅ **Pagination** - Built-in pagination with customizable display
- ✅ **Loading States** - Elegant loading overlays
- ✅ **Dark Mode** - Full dark mode support
- ✅ **Empty States** - Customizable empty state messages
- ✅ **Responsive** - Mobile-friendly with horizontal scrolling

## Basic Usage

### Client-Side Table (Simple Array)

```vue
<template>
  <DataTable
    :data="users"
    :columns="columns"
    searchable
    selectable
    :search-keys="['name', 'email']"
  >
    <template #actions>
      <button @click="createUser">Create User</button>
    </template>

    <template #row-actions="{ row }">
      <button @click="editUser(row)">Edit</button>
      <button @click="deleteUser(row)">Delete</button>
    </template>
  </DataTable>
</template>

<script setup>
import { ref } from 'vue'
import DataTable from '@/components/tables/DataTable.vue'

const users = ref([
  { id: 1, name: 'John Doe', email: 'john@example.com', status: 'active' },
  { id: 2, name: 'Jane Smith', email: 'jane@example.com', status: 'inactive' }
])

const columns = [
  { key: 'id', label: 'ID', sortable: true, width: '80px' },
  { key: 'name', label: 'Name', sortable: true },
  { key: 'email', label: 'Email', sortable: true },
  { key: 'status', label: 'Status', sortable: true }
]
</script>
```

### Server-Side Table (Laravel Pagination)

```vue
<template>
  <DataTable
    :data="paginatedUsers"
    :columns="columns"
    :loading="loading"
    searchable
    server-side
    @search="handleSearch"
    @sort="handleSort"
    @page-change="handlePageChange"
  >
    <template #actions>
      <button @click="createUser">Create User</button>
    </template>
  </DataTable>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import DataTable from '@/components/tables/DataTable.vue'
import { userService } from '@/services/userService'

const paginatedUsers = ref(null)
const loading = ref(false)
const currentParams = ref({
  page: 1,
  search: '',
  sort: '',
  direction: 'asc'
})

const columns = [
  { key: 'id', label: 'ID', sortable: true },
  { key: 'name', label: 'Name', sortable: true },
  { key: 'email', label: 'Email', sortable: true }
]

async function fetchUsers() {
  loading.value = true
  try {
    // Laravel returns: { data: [...], current_page, last_page, from, to, total, per_page }
    paginatedUsers.value = await userService.getUsers(currentParams.value)
  } finally {
    loading.value = false
  }
}

function handleSearch(query) {
  currentParams.value.search = query
  currentParams.value.page = 1
  fetchUsers()
}

function handleSort({ key, direction }) {
  currentParams.value.sort = key
  currentParams.value.direction = direction
  fetchUsers()
}

function handlePageChange(page) {
  currentParams.value.page = page
  fetchUsers()
}

onMounted(() => {
  fetchUsers()
})
</script>
```

## Props

### Data Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `data` | `Array\|Object` | `[]` | Array of data objects OR Laravel pagination response object |
| `columns` | `Array` | **required** | Column configuration (see Column Configuration) |
| `serverSide` | `Boolean` | `false` | Enable server-side mode (disables client-side search/sort) |

### Selection Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `selectable` | `Boolean` | `false` | Enable row selection checkboxes |
| `rowKey` | `String\|Function` | `'id'` | Unique key for rows |

### Search Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `searchable` | `Boolean` | `false` | Enable search bar |
| `searchPlaceholder` | `String` | `'Search...'` | Search input placeholder |
| `searchKeys` | `Array` | `[]` | Keys to search (for client-side search) |
| `searchDebounce` | `Number` | `300` | Search debounce delay (ms) |

### Sorting Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `sortable` | `Boolean` | `false` | Enable global sorting |
| `defaultSort` | `Object` | `{ key: '', direction: 'asc' }` | Default sort configuration |

### Pagination Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `pagination` | `Object` | `null` | Pagination data (see Pagination) |

### UI Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `loading` | `Boolean` | `false` | Show loading overlay |
| `showHeader` | `Boolean` | `true` | Show header with search and actions |
| `emptyMessage` | `String` | `'No records found'` | Empty state message |

## Column Configuration

Each column object can have the following properties:

```javascript
{
  key: 'name',              // Required: Property key from data object
  label: 'Name',            // Required: Column header label
  sortable: true,           // Optional: Enable sorting for this column
  visible: true,            // Optional: Show/hide column (default: true)
  width: '200px',           // Optional: Fixed column width
  type: 'text',             // Optional: Data type (text, boolean, date, datetime, currency)
  formatter: (value, row) => { // Optional: Custom formatter function
    return value.toUpperCase()
  }
}
```

### Supported Column Types

- `text` (default) - Plain text display
- `boolean` - Displays "Yes"/"No"
- `date` - Formats as localized date
- `datetime` - Formats as localized date and time
- `currency` - Formats as USD currency

## Events

| Event | Payload | Description |
|-------|---------|-------------|
| `search` | `String` | Emitted when search query changes (debounced) |
| `sort` | `{ key: String, direction: String }` | Emitted when sort changes |
| `page-change` | `Number` | Emitted when page changes |
| `selection-change` | `Array` | Emitted when row selection changes |

## Slots

### Header Slots

#### `actions`
Header actions area (e.g., Create button)

```vue
<template #actions>
  <button @click="createNew">Create New</button>
  <button @click="exportData">Export</button>
</template>
```

### Row Slots

#### `row-actions`
Actions for each row (scoped slot)

**Scope:**
- `row` - Current row data
- `index` - Row index

```vue
<template #row-actions="{ row, index }">
  <button @click="edit(row)">Edit</button>
  <button @click="delete(row)">Delete</button>
</template>
```

#### `cell-{columnKey}`
Custom cell rendering for specific column (scoped slot)

**Scope:**
- `row` - Current row data
- `value` - Cell value
- `column` - Column configuration

```vue
<template #cell-status="{ value, row }">
  <span :class="value === 'active' ? 'text-green-600' : 'text-red-600'">
    {{ value }}
  </span>
</template>

<template #cell-avatar="{ row }">
  <img :src="row.avatar" class="w-8 h-8 rounded-full" />
</template>
```

### Bulk Actions Slot

#### `bulk-actions`
Bulk actions when rows are selected (scoped slot)

**Scope:**
- `selected` - Array of selected rows
- `clear` - Function to clear selection

```vue
<template #bulk-actions="{ selected, clear }">
  <button @click="bulkDelete(selected)">Delete {{ selected.length }} items</button>
  <button @click="bulkExport(selected)">Export Selected</button>
</template>
```

### Empty State Slot

#### `empty-state`
Custom empty state content

```vue
<template #empty-state>
  <div class="text-center py-8">
    <Icon name="inbox" :size="48" class="mx-auto text-gray-400" />
    <p class="mt-2 text-gray-500">No users found</p>
    <button @click="createFirst" class="mt-4">Create your first user</button>
  </div>
</template>
```

## Usage Examples

### Example 1: Simple Table

```vue
<DataTable
  :data="products"
  :columns="[
    { key: 'id', label: 'ID', sortable: true },
    { key: 'name', label: 'Product Name', sortable: true },
    { key: 'price', label: 'Price', type: 'currency' }
  ]"
/>
```

### Example 2: Table with Search and Selection

```vue
<DataTable
  :data="users"
  :columns="userColumns"
  searchable
  selectable
  :search-keys="['name', 'email']"
>
  <template #bulk-actions="{ selected }">
    <button @click="deleteUsers(selected)">Delete Selected</button>
  </template>
</DataTable>
```

### Example 3: Laravel Server-Side Pagination (Recommended)

```vue
<template>
  <DataTable
    :data="paginatedData"
    :columns="columns"
    :loading="loading"
    searchable
    server-side
    @search="handleSearch"
    @sort="handleSort"
    @page-change="handlePageChange"
  />
</template>

<script setup>
import { ref, onMounted } from 'vue'
import DataTable from '@/components/tables/DataTable.vue'
import { userService } from '@/services/userService'

// Store the entire Laravel pagination response
const paginatedData = ref(null)
const loading = ref(false)

const params = ref({
  page: 1,
  search: '',
  sort: '',
  direction: 'asc'
})

const columns = [
  { key: 'name', label: 'Name', sortable: true },
  { key: 'email', label: 'Email', sortable: true }
]

async function fetchData() {
  loading.value = true
  try {
    // Laravel returns: { data: [...], current_page, last_page, from, to, total, per_page }
    paginatedData.value = await userService.getUsers(params.value)
  } catch (error) {
    console.error('Failed to fetch users:', error)
  } finally {
    loading.value = false
  }
}

function handleSearch(query) {
  params.value.search = query
  params.value.page = 1 // Reset to first page
  fetchData()
}

function handleSort({ key, direction }) {
  params.value.sort = key
  params.value.direction = direction
  fetchData()
}

function handlePageChange(page) {
  params.value.page = page
  fetchData()
}

onMounted(() => {
  fetchData()
})
</script>
```

**Backend Service Example:**

```javascript
// services/userService.js
import api from '@/utils/api'

export const userService = {
  async getUsers(params) {
    const response = await api.get('/api/users', { params })
    return response.data // Laravel pagination response
  }
}
```

**Laravel Controller Example:**

```php
// app/Http/Controllers/UserController.php
public function index(Request $request)
{
    $query = User::query();

    // Search
    if ($request->has('search')) {
        $query->where(function ($q) use ($request) {
            $q->where('name', 'like', '%' . $request->search . '%')
              ->orWhere('email', 'like', '%' . $request->search . '%');
        });
    }

    // Sort
    if ($request->has('sort')) {
        $query->orderBy(
            $request->sort,
            $request->get('direction', 'asc')
        );
    }

    // Paginate (15 per page by default)
    return $query->paginate($request->get('perPage', 15));
}
```

### Example 4: Custom Cell Rendering

```vue
<DataTable :data="users" :columns="columns">
  <!-- Custom status badge -->
  <template #cell-status="{ value }">
    <span
      :class="[
        'px-2 py-1 rounded-full text-xs font-medium',
        value === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
      ]"
    >
      {{ value }}
    </span>
  </template>

  <!-- Custom avatar with name -->
  <template #cell-name="{ row }">
    <div class="flex items-center gap-3">
      <img :src="row.avatar" class="w-8 h-8 rounded-full" />
      <span>{{ row.name }}</span>
    </div>
  </template>

  <!-- Custom date formatting -->
  <template #cell-created_at="{ value }">
    {{ formatDate(value) }}
  </template>

  <!-- Row actions -->
  <template #row-actions="{ row }">
    <div class="flex items-center justify-end gap-2">
      <button @click="edit(row)" class="text-blue-600 hover:text-blue-800">
        <Icon name="edit" :size="18" />
      </button>
      <button @click="deleteUser(row)" class="text-red-600 hover:text-red-800">
        <Icon name="delete" :size="18" />
      </button>
    </div>
  </template>
</DataTable>
```

### Example 5: Using Custom Formatters

```vue
<script setup>
const columns = [
  {
    key: 'price',
    label: 'Price',
    formatter: (value) => `$${value.toFixed(2)}`
  },
  {
    key: 'status',
    label: 'Status',
    formatter: (value) => value.toUpperCase()
  },
  {
    key: 'tags',
    label: 'Tags',
    formatter: (value) => Array.isArray(value) ? value.join(', ') : '-'
  },
  {
    key: 'user',
    label: 'User',
    formatter: (value, row) => `${row.user.name} (${row.user.email})`
  }
]
</script>
```

### Example 6: Nested Object Access

```vue
<script setup>
const columns = [
  { key: 'user.name', label: 'User Name' },         // Access nested property
  { key: 'user.profile.city', label: 'City' },      // Deep nesting
  { key: 'metadata.tags', label: 'Tags' }
]

const data = [
  {
    id: 1,
    user: {
      name: 'John Doe',
      profile: { city: 'New York' }
    },
    metadata: {
      tags: ['admin', 'verified']
    }
  }
]
</script>
```

## Exposed Methods

Access these methods using template refs:

```vue
<template>
  <DataTable ref="tableRef" ... />
</template>

<script setup>
import { ref, onMounted } from 'vue'

const tableRef = ref(null)

onMounted(() => {
  // Clear selection
  tableRef.value.clearSelection()

  // Get selected rows
  const selected = tableRef.value.getSelectedRows()

  // Set selected rows
  tableRef.value.setSelectedRows([row1, row2])
})
</script>
```

## Styling

The component uses Tailwind CSS classes and follows the application's theme. You can customize styling by:

1. Overriding CSS classes in your component
2. Using the `class` attribute on the DataTable component
3. Customizing Tailwind theme colors in `tailwind.config.js`

## Accessibility

- Proper ARIA labels for checkboxes
- Keyboard navigation support
- Screen reader friendly
- Focus management

## Performance Tips

1. **Use `row-key` prop** for efficient row tracking
2. **Implement server-side operations** for large datasets (search, sort, pagination)
3. **Use `v-memo`** in custom cell slots for expensive computations
4. **Debounce search** (already built-in with `searchDebounce` prop)
5. **Lazy load data** when using pagination

## Migration from ResourceTable

If you're migrating from `ResourceTable.vue`, here are the key differences:

| ResourceTable | DataTable | Notes |
|---------------|-----------|-------|
| `resource` prop | `data` prop | Pass data directly instead of resource name |
| Auto-fetches data | Manual data management | You control when data is fetched |
| Field definitions | Column definitions | Similar structure, simpler API |
| Built-in filters | External filtering | Emit `search` event to handle filtering |

## Laravel Integration

### Understanding Laravel Pagination Response

Laravel's `paginate()` method returns a JSON response with this structure:

```json
{
  "data": [
    { "id": 1, "name": "John Doe", "email": "john@example.com" },
    { "id": 2, "name": "Jane Smith", "email": "jane@example.com" }
  ],
  "current_page": 1,
  "last_page": 10,
  "from": 1,
  "to": 15,
  "total": 150,
  "per_page": 15,
  "path": "http://example.com/api/users",
  "first_page_url": "http://example.com/api/users?page=1",
  "last_page_url": "http://example.com/api/users?page=10",
  "next_page_url": "http://example.com/api/users?page=2",
  "prev_page_url": null
}
```

**DataTable automatically detects this format** and extracts:
- `data` array for table rows
- `current_page`, `last_page`, `from`, `to`, `total` for pagination

### Two Ways to Use Laravel Pagination

#### Method 1: Direct Laravel Response (Recommended)

Pass the entire Laravel response to `data` prop:

```vue
<DataTable
  :data="laravelResponse"
  :columns="columns"
  server-side
  @page-change="handlePageChange"
/>
```

```javascript
const laravelResponse = ref(null)

async function fetchData() {
  const response = await api.get('/api/users')
  laravelResponse.value = response.data // Entire Laravel response
}
```

#### Method 2: Separate Data and Pagination

If you need more control:

```vue
<DataTable
  :data="users"
  :pagination="paginationMeta"
  :columns="columns"
  server-side
  @page-change="handlePageChange"
/>
```

```javascript
const users = ref([])
const paginationMeta = ref(null)

async function fetchData() {
  const response = await api.get('/api/users')
  users.value = response.data.data
  paginationMeta.value = {
    current_page: response.data.current_page,
    last_page: response.data.last_page,
    from: response.data.from,
    to: response.data.to,
    total: response.data.total
  }
}
```

### Complete Laravel Integration Example

**Frontend Component:**

```vue
<template>
  <div class="p-6">
    <DataTable
      :data="users"
      :columns="columns"
      :loading="loading"
      searchable
      selectable
      server-side
      @search="handleSearch"
      @sort="handleSort"
      @page-change="handlePageChange"
      @selection-change="handleSelectionChange"
    >
      <template #actions>
        <button @click="createUser" class="btn-primary">
          <Icon name="add" :size="20" />
          Create User
        </button>
      </template>

      <template #bulk-actions="{ selected }">
        <button @click="bulkDelete(selected)" class="btn-danger">
          Delete {{ selected.length }} users
        </button>
      </template>

      <template #cell-status="{ value }">
        <span
          :class="[
            'px-2 py-1 rounded-full text-xs',
            value === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
          ]"
        >
          {{ value }}
        </span>
      </template>

      <template #row-actions="{ row }">
        <button @click="editUser(row)" class="text-blue-600">
          <Icon name="edit" :size="18" />
        </button>
        <button @click="deleteUser(row)" class="text-red-600">
          <Icon name="delete" :size="18" />
        </button>
      </template>
    </DataTable>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import DataTable from '@/components/tables/DataTable.vue'
import { userService } from '@/services/userService'
import { useToast } from '@/composables/useToast'
import { useDialog } from '@/composables/useDialog'

const users = ref(null)
const loading = ref(false)
const toast = useToast()
const dialog = useDialog()

const params = ref({
  page: 1,
  perPage: 15,
  search: '',
  sort: '',
  direction: 'asc'
})

const columns = [
  { key: 'id', label: 'ID', sortable: true, width: '80px' },
  { key: 'name', label: 'Name', sortable: true },
  { key: 'email', label: 'Email', sortable: true },
  { key: 'status', label: 'Status', sortable: true },
  { key: 'created_at', label: 'Created', type: 'date', sortable: true }
]

async function fetchUsers() {
  loading.value = true
  try {
    users.value = await userService.getUsers(params.value)
  } catch (error) {
    toast.showToast({ message: 'Failed to load users', type: 'error' })
  } finally {
    loading.value = false
  }
}

function handleSearch(query) {
  params.value.search = query
  params.value.page = 1
  fetchUsers()
}

function handleSort({ key, direction }) {
  params.value.sort = key
  params.value.direction = direction
  fetchUsers()
}

function handlePageChange(page) {
  params.value.page = page
  fetchUsers()
}

async function deleteUser(user) {
  const confirmed = await dialog.confirmDanger(
    `Are you sure you want to delete ${user.name}?`
  )
  if (!confirmed) return

  try {
    await userService.deleteUser(user.id)
    toast.showToast({ message: 'User deleted successfully', type: 'success' })
    fetchUsers()
  } catch (error) {
    toast.showToast({ message: 'Failed to delete user', type: 'error' })
  }
}

async function bulkDelete(selected) {
  const confirmed = await dialog.confirmDanger(
    `Are you sure you want to delete ${selected.length} users?`
  )
  if (!confirmed) return

  try {
    await userService.bulkDelete(selected.map(u => u.id))
    toast.showToast({ message: 'Users deleted successfully', type: 'success' })
    fetchUsers()
  } catch (error) {
    toast.showToast({ message: 'Failed to delete users', type: 'error' })
  }
}

onMounted(() => {
  fetchUsers()
})
</script>
```

**Frontend Service:**

```javascript
// services/userService.js
import api from '@/utils/api'

export const userService = {
  async getUsers(params) {
    const response = await api.get('/api/users', { params })
    return response.data
  },

  async deleteUser(id) {
    const response = await api.delete(`/api/users/${id}`)
    return response.data
  },

  async bulkDelete(ids) {
    const response = await api.post('/api/users/bulk-delete', { ids })
    return response.data
  }
}
```

**Backend Controller:**

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Sort
        if ($request->filled('sort')) {
            $query->orderBy(
                $request->sort,
                $request->get('direction', 'asc')
            );
        } else {
            $query->latest();
        }

        // Paginate
        return $query->paginate($request->get('perPage', 15));
    }

    public function destroy(User $user)
    {
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:users,id'
        ]);

        User::whereIn('id', $request->ids)->delete();

        return response()->json([
            'message' => 'Users deleted successfully'
        ]);
    }
}
```

### Performance Tips for Large Datasets

1. **Use `select()` to limit columns:**
```php
$query->select(['id', 'name', 'email', 'status', 'created_at'])
```

2. **Eager load relationships:**
```php
$query->with(['role', 'profile'])
```

3. **Add database indexes:**
```php
Schema::table('users', function (Blueprint $table) {
    $table->index(['name', 'email']);
});
```

4. **Use pagination with reasonable per-page limits:**
```php
return $query->paginate(min($request->get('perPage', 15), 100));
```

## Best Practices

1. **Always define a unique `rowKey`** when using selection
2. **Use server-side operations** for large datasets (>1000 rows)
3. **Provide loading states** for async operations
4. **Use custom formatters** for complex data transformations
5. **Leverage slots** for rich cell content
6. **Keep column definitions in a separate constant** for reusability
7. **Implement proper error handling** in event handlers
8. **Pass entire Laravel response** to `data` prop for automatic pagination extraction
9. **Set `server-side` prop** when using Laravel pagination to disable client-side operations
10. **Reset page to 1** when search or filters change
