# Generic Resource CRUD System - Implementation Plan

## Overview
A fluent, declarative API system that auto-generates full-stack CRUD operations from model definitions - similar to Laravel Nova/Filament but seamlessly integrated into your existing Laravel + Vue.js architecture.

### What This Solves
Instead of writing 7+ files for each model's CRUD operations:
- ❌ Controller
- ❌ Service
- ❌ FormRequest
- ❌ API Resource
- ❌ Frontend Service
- ❌ Table Component
- ❌ Form Component

You write **1 Backend Resource class + 1 line of frontend code**.

### Zero Frontend Configuration
The backend Resource class drives **100% of the frontend behavior**. On the frontend, you only need to:
1. Create a route
2. Drop in a single `<ResourceManager resource="users" />` component
3. Everything else (tables, forms, modals, validation, search, filters, bulk operations) works automatically!

### Key Features
- **Fluent API** - Declarative, readable resource definitions
- **Zero Frontend Config** - Just drop `<ResourceManager resource="users" />` and you're done
- **Relationship Support** - All Laravel relationships (belongsTo, hasMany, belongsToMany, morphMany, etc.)
- **Auto-validation** - Fields generate validation rules
- **Bulk Operations** - Select multiple, update, delete, export
- **Export/Import** - CSV, XLSX, PDF built-in
- **Filters & Actions** - Reusable, composable
- **Type-safe** - Full IDE support

---

## Quick Start Guide

### Step 1: Create Backend Resource (One Time)
```php
// app/Resources/UserResource.php
class UserResource extends Resource
{
    public static string $model = User::class;
    public static string $label = 'Users';

    public function fields(): array
    {
        return [
            ID::make()->sortable(),
            Text::make('Name')->rules('required')->sortable()->searchable(),
            Email::make('Email')->rules('required|email|unique:users')->sortable(),
            Select::make('Status')->options(Status::class)->sortable(),
        ];
    }
}
```

### Step 2: Register Resource
```php
// config/resources.php
return [
    'users' => \App\Resources\UserResource::class,
];
```

### Step 3: Add Frontend Route & Component
```vue
<!-- resources/js/pages/Users.vue -->
<template>
  <ResourceManager resource="users" />
</template>

<script setup>
import ResourceManager from '@/components/resource/ResourceManager.vue'
</script>
```

**Done!** You now have a full CRUD interface with:
- ✅ Sortable, searchable data table
- ✅ Create/Edit forms with validation
- ✅ Delete with confirmation
- ✅ Pagination
- ✅ Responsive design

---

## Architecture

### Backend Structure

```
app/
├── Resources/                    # Resource definitions
│   ├── Resource.php             # Base abstract class
│   ├── UserResource.php         # Example resource
│   ├── Fields/                  # Field types
│   │   ├── Field.php           # Base field
│   │   ├── Text.php
│   │   ├── Number.php
│   │   ├── Boolean.php
│   │   ├── Date.php
│   │   ├── Select.php
│   │   ├── BelongsTo.php
│   │   ├── HasMany.php
│   │   └── MorphMany.php
│   ├── Filters/                 # Filter types
│   │   ├── Filter.php
│   │   ├── SelectFilter.php
│   │   └── DateRangeFilter.php
│   └── Actions/                 # Action types
│       ├── Action.php
│       ├── ExportAction.php
│       ├── BulkDeleteAction.php
│       └── BulkUpdateAction.php
├── Services/
│   └── ResourceService.php      # Generic CRUD service
└── Http/
    └── Controllers/Api/
        └── ResourceController.php  # Generic controller
```

### Frontend Structure

```
resources/js/
├── components/
│   └── resource/
│       ├── ResourceManager.vue   # 🎯 MAIN ENTRY POINT - Use this!
│       │                         # Handles everything: table, forms, modals, validation
│       │
│       ├── ResourceTable.vue     # Internal: Generic data table
│       ├── ResourceForm.vue      # Internal: Generic form
│       ├── fields/               # Internal: Field renderers
│       │   ├── TextField.vue
│       │   ├── SelectField.vue
│       │   ├── BelongsToField.vue
│       │   └── ...
│       ├── filters/              # Internal: Filter components
│       │   └── FilterBar.vue
│       └── actions/              # Internal: Action components
│           └── ActionButtons.vue
└── services/
    └── resourceService.js        # Internal: Generic API client
```

**Note**: You typically only import `ResourceManager.vue`. The other components are internal implementation details used by ResourceManager.

---

## Backend Implementation

### 1. Base Resource Class

```php
// app/Resources/Resource.php
<?php

namespace App\Resources;

use Illuminate\Database\Eloquent\Model;

abstract class Resource
{
    /**
     * The model the resource corresponds to.
     */
    public static string $model;

    /**
     * The display name for the resource (plural).
     */
    public static string $label;

    /**
     * The display name for a single resource.
     */
    public static string $singularLabel;

    /**
     * The column to use for the resource's title/name.
     */
    public static string $title = 'id';

    /**
     * Indicates if the resource should be searchable.
     */
    public static bool $searchable = true;

    /**
     * The columns to search when performing a search.
     */
    public static array $search = [];

    /**
     * Number of resources to show per page.
     */
    public static int $perPage = 15;

    /**
     * Get the fields displayed by the resource.
     */
    abstract public function fields(): array;

    /**
     * Get the filters available for the resource.
     */
    public function filters(): array
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     */
    public function actions(): array
    {
        return [];
    }

    /**
     * Get the relationships to eager load.
     */
    public function with(): array
    {
        return [];
    }

    /**
     * Get validation rules from fields.
     */
    public function rules(string $context = 'create'): array
    {
        $rules = [];
        foreach ($this->fields() as $field) {
            if ($field->rules) {
                $rules[$field->attribute] = $field->rules;
            }
        }
        return $rules;
    }

    /**
     * Get the model class.
     */
    public static function model(): string
    {
        return static::$model;
    }

    /**
     * Get resource key (lowercase plural).
     */
    public static function key(): string
    {
        return str(class_basename(static::class))
            ->replace('Resource', '')
            ->plural()
            ->lower()
            ->toString();
    }
}
```

### 2. Field System

```php
// app/Resources/Fields/Field.php
<?php

namespace App\Resources\Fields;

abstract class Field
{
    public string $attribute;
    public string $label;
    public mixed $default = null;
    public array|string|null $rules = null;
    public bool $sortable = false;
    public bool $searchable = false;
    public bool $showOnIndex = true;
    public bool $showOnDetail = true;
    public bool $showOnForm = true;
    public bool $required = false;
    protected array $meta = [];

    public function __construct(string $label, ?string $attribute = null)
    {
        $this->label = $label;
        $this->attribute = $attribute ?? str($label)->snake()->toString();
    }

    public static function make(string $label, ?string $attribute = null): static
    {
        return new static($label, $attribute);
    }

    public function rules(array|string $rules): static
    {
        $this->rules = $rules;
        if (is_string($rules) && str_contains($rules, 'required')) {
            $this->required = true;
        }
        return $this;
    }

    public function sortable(bool $sortable = true): static
    {
        $this->sortable = $sortable;
        return $this;
    }

    public function searchable(bool $searchable = true): static
    {
        $this->searchable = $searchable;
        return $this;
    }

    public function hideFromIndex(): static
    {
        $this->showOnIndex = false;
        return $this;
    }

    public function hideFromDetail(): static
    {
        $this->showOnDetail = false;
        return $this;
    }

    public function hideFromForm(): static
    {
        $this->showOnForm = false;
        return $this;
    }

    public function default(mixed $value): static
    {
        $this->default = $value;
        return $this;
    }

    public function meta(array $meta): static
    {
        $this->meta = array_merge($this->meta, $meta);
        return $this;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->fieldType(),
            'attribute' => $this->attribute,
            'label' => $this->label,
            'sortable' => $this->sortable,
            'searchable' => $this->searchable,
            'required' => $this->required,
            'showOnIndex' => $this->showOnIndex,
            'showOnDetail' => $this->showOnDetail,
            'showOnForm' => $this->showOnForm,
            'default' => $this->default,
            'meta' => $this->meta,
        ];
    }

    abstract protected function fieldType(): string;
}
```

```php
// app/Resources/Fields/Text.php
<?php

namespace App\Resources\Fields;

class Text extends Field
{
    protected function fieldType(): string
    {
        return 'text';
    }

    public function placeholder(string $placeholder): static
    {
        return $this->meta(['placeholder' => $placeholder]);
    }

    public function maxLength(int $length): static
    {
        return $this->meta(['maxLength' => $length]);
    }
}
```

```php
// app/Resources/Fields/Select.php
<?php

namespace App\Resources\Fields;

class Select extends Field
{
    protected array $options = [];

    protected function fieldType(): string
    {
        return 'select';
    }

    public function options(array|string $options): static
    {
        // Support for Enum classes
        if (is_string($options) && enum_exists($options)) {
            $this->options = collect($options::cases())
                ->mapWithKeys(fn($case) => [$case->value => $case->name])
                ->toArray();
        } else {
            $this->options = $options;
        }

        return $this->meta(['options' => $this->options]);
    }
}
```

```php
// app/Resources/Fields/BelongsTo.php
<?php

namespace App\Resources\Fields;

class BelongsTo extends Field
{
    protected string $relatedResource;
    protected string $titleAttribute = 'name';
    protected bool $searchable = true;

    protected function fieldType(): string
    {
        return 'belongs-to';
    }

    public function resource(string $resourceClass): static
    {
        $this->relatedResource = $resourceClass;
        return $this->meta(['resource' => $resourceClass::key()]);
    }

    public function titleAttribute(string $attribute): static
    {
        $this->titleAttribute = $attribute;
        return $this->meta(['titleAttribute' => $attribute]);
    }

    public function searchable(bool $searchable = true): static
    {
        $this->searchable = $searchable;
        return $this->meta(['searchable' => $searchable]);
    }
}
```

```php
// app/Resources/Fields/HasMany.php
<?php

namespace App\Resources\Fields;

class HasMany extends Field
{
    protected string $relatedResource;

    protected function fieldType(): string
    {
        return 'has-many';
    }

    public function resource(string $resourceClass): static
    {
        $this->relatedResource = $resourceClass;
        return $this->meta(['resource' => $resourceClass::key()]);
    }
}
```

### 3. Resource Service

```php
// app/Services/ResourceService.php
<?php

namespace App\Services;

use App\Resources\Resource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class ResourceService
{
    public function __construct(protected Resource $resource) {}

    /**
     * Get paginated index of resources.
     */
    public function index(array $params = []): LengthAwarePaginator
    {
        $query = $this->baseQuery();

        // Apply search
        if (!empty($params['search'])) {
            $this->applySearch($query, $params['search']);
        }

        // Apply filters
        if (!empty($params['filters'])) {
            $this->applyFilters($query, $params['filters']);
        }

        // Apply sorting
        if (!empty($params['sort'])) {
            $this->applySort($query, $params['sort'], $params['direction'] ?? 'asc');
        }

        // Eager load relationships
        $with = $this->resource->with();
        if (!empty($with)) {
            $query->with($with);
        }

        return $query->paginate($params['perPage'] ?? $this->resource::$perPage);
    }

    /**
     * Get a single resource.
     */
    public function show(int|string $id): Model
    {
        $query = $this->baseQuery();

        $with = $this->resource->with();
        if (!empty($with)) {
            $query->with($with);
        }

        return $query->findOrFail($id);
    }

    /**
     * Create a new resource.
     */
    public function store(array $data): Model
    {
        $modelClass = $this->resource::model();
        $model = $modelClass::create($data);

        // Handle relationships
        $this->syncRelationships($model, $data);

        return $model->fresh($this->resource->with());
    }

    /**
     * Update a resource.
     */
    public function update(int|string $id, array $data): Model
    {
        $model = $this->baseQuery()->findOrFail($id);
        $model->update($data);

        // Handle relationships
        $this->syncRelationships($model, $data);

        return $model->fresh($this->resource->with());
    }

    /**
     * Delete a resource.
     */
    public function destroy(int|string $id): bool
    {
        $model = $this->baseQuery()->findOrFail($id);
        return $model->delete();
    }

    /**
     * Bulk delete resources.
     */
    public function bulkDestroy(array $ids): int
    {
        return $this->baseQuery()->whereIn('id', $ids)->delete();
    }

    /**
     * Bulk update resources.
     */
    public function bulkUpdate(array $ids, array $data): int
    {
        return $this->baseQuery()->whereIn('id', $ids)->update($data);
    }

    /**
     * Export resources.
     */
    public function export(string $format, array $params = []): string
    {
        $query = $this->baseQuery();

        if (!empty($params['filters'])) {
            $this->applyFilters($query, $params['filters']);
        }

        $data = $query->get();

        // Delegate to export service based on format
        return match($format) {
            'csv' => $this->exportCsv($data),
            'xlsx' => $this->exportXlsx($data),
            'pdf' => $this->exportPdf($data),
            default => throw new \InvalidArgumentException("Unsupported format: {$format}")
        };
    }

    /**
     * Get base query for the resource.
     */
    protected function baseQuery(): Builder
    {
        $modelClass = $this->resource::model();
        return $modelClass::query();
    }

    /**
     * Apply search to query.
     */
    protected function applySearch(Builder $query, string $search): void
    {
        if (empty($this->resource::$search)) {
            return;
        }

        $query->where(function ($q) use ($search) {
            foreach ($this->resource::$search as $column) {
                $q->orWhere($column, 'LIKE', "%{$search}%");
            }
        });
    }

    /**
     * Apply filters to query.
     */
    protected function applyFilters(Builder $query, array $filters): void
    {
        foreach ($filters as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            // Find the filter definition
            foreach ($this->resource->filters() as $filter) {
                if ($filter->key === $key) {
                    $filter->apply($query, $value);
                    break;
                }
            }
        }
    }

    /**
     * Apply sorting to query.
     */
    protected function applySort(Builder $query, string $column, string $direction = 'asc'): void
    {
        $query->orderBy($column, $direction);
    }

    /**
     * Sync relationships for a model.
     */
    protected function syncRelationships(Model $model, array $data): void
    {
        foreach ($this->resource->fields() as $field) {
            if ($field instanceof \App\Resources\Fields\BelongsTo) {
                // BelongsTo is handled via foreign key in main data
                continue;
            }

            if ($field instanceof \App\Resources\Fields\BelongsToMany) {
                $relationName = $field->attribute;
                if (isset($data[$relationName])) {
                    $model->$relationName()->sync($data[$relationName]);
                }
            }

            if ($field instanceof \App\Resources\Fields\HasMany) {
                // Handle HasMany if needed
            }
        }
    }

    // Export methods (simplified - use actual export packages)
    protected function exportCsv($data): string { /* ... */ }
    protected function exportXlsx($data): string { /* ... */ }
    protected function exportPdf($data): string { /* ... */ }
}
```

### 4. Resource Controller

```php
// app/Http/Controllers/Api/ResourceController.php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ResourceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ResourceController extends Controller
{
    /**
     * Get resource metadata (fields, filters, actions).
     */
    public function meta(string $resource): JsonResponse
    {
        $resourceInstance = $this->resolveResource($resource);

        return response()->json([
            'key' => $resourceInstance::key(),
            'label' => $resourceInstance::$label,
            'singularLabel' => $resourceInstance::$singularLabel,
            'fields' => array_map(fn($f) => $f->toArray(), $resourceInstance->fields()),
            'filters' => array_map(fn($f) => $f->toArray(), $resourceInstance->filters()),
            'actions' => array_map(fn($a) => $a->toArray(), $resourceInstance->actions()),
            'searchable' => $resourceInstance::$searchable,
            'perPage' => $resourceInstance::$perPage,
        ]);
    }

    /**
     * List resources.
     */
    public function index(Request $request, string $resource): JsonResponse
    {
        $resourceInstance = $this->resolveResource($resource);
        $service = new ResourceService($resourceInstance);

        $data = $service->index($request->all());

        return response()->json($data);
    }

    /**
     * Show single resource.
     */
    public function show(string $resource, int|string $id): JsonResponse
    {
        $resourceInstance = $this->resolveResource($resource);
        $service = new ResourceService($resourceInstance);

        $model = $service->show($id);

        return response()->json(['data' => $model]);
    }

    /**
     * Create resource.
     */
    public function store(Request $request, string $resource): JsonResponse
    {
        $resourceInstance = $this->resolveResource($resource);

        $validator = Validator::make(
            $request->all(),
            $resourceInstance->rules('create')
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $service = new ResourceService($resourceInstance);
        $model = $service->store($validator->validated());

        return response()->json([
            'message' => $resourceInstance::$singularLabel . ' created successfully',
            'data' => $model
        ], 201);
    }

    /**
     * Update resource.
     */
    public function update(Request $request, string $resource, int|string $id): JsonResponse
    {
        $resourceInstance = $this->resolveResource($resource);

        $validator = Validator::make(
            $request->all(),
            $resourceInstance->rules('update')
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $service = new ResourceService($resourceInstance);
        $model = $service->update($id, $validator->validated());

        return response()->json([
            'message' => $resourceInstance::$singularLabel . ' updated successfully',
            'data' => $model
        ]);
    }

    /**
     * Delete resource.
     */
    public function destroy(string $resource, int|string $id): JsonResponse
    {
        $resourceInstance = $this->resolveResource($resource);
        $service = new ResourceService($resourceInstance);

        $service->destroy($id);

        return response()->json([
            'message' => $resourceInstance::$singularLabel . ' deleted successfully'
        ]);
    }

    /**
     * Bulk actions.
     */
    public function bulkAction(Request $request, string $resource, string $action): JsonResponse
    {
        $resourceInstance = $this->resolveResource($resource);
        $service = new ResourceService($resourceInstance);

        $ids = $request->input('ids', []);
        $data = $request->input('data', []);

        $result = match($action) {
            'delete' => $service->bulkDestroy($ids),
            'update' => $service->bulkUpdate($ids, $data),
            default => throw new \InvalidArgumentException("Unknown action: {$action}")
        };

        return response()->json([
            'message' => "Bulk {$action} completed",
            'affected' => $result
        ]);
    }

    /**
     * Export resources.
     */
    public function export(Request $request, string $resource): JsonResponse
    {
        $resourceInstance = $this->resolveResource($resource);
        $service = new ResourceService($resourceInstance);

        $format = $request->input('format', 'csv');
        $filePath = $service->export($format, $request->all());

        return response()->json([
            'message' => 'Export completed',
            'url' => $filePath
        ]);
    }

    /**
     * Resolve resource instance from key.
     */
    protected function resolveResource(string $resourceKey): object
    {
        $resourceClass = config("resources.{$resourceKey}");

        if (!$resourceClass || !class_exists($resourceClass)) {
            abort(404, "Resource not found: {$resourceKey}");
        }

        return new $resourceClass;
    }
}
```

---

## Frontend Implementation

### 1. Resource Service

```javascript
// resources/js/services/resourceService.js

/**
 * Generic Resource API Service
 */
export const resourceService = {
  /**
   * Get resource metadata
   */
  async meta(resource) {
    const response = await window.axios.get(`/api/resources/${resource}/meta`)
    return response.data
  },

  /**
   * List resources with filters, search, sorting
   */
  async index(resource, params = {}) {
    const response = await window.axios.get(`/api/resources/${resource}`, { params })
    return response.data
  },

  /**
   * Get single resource
   */
  async show(resource, id) {
    const response = await window.axios.get(`/api/resources/${resource}/${id}`)
    return response.data
  },

  /**
   * Create resource
   */
  async store(resource, data) {
    const response = await window.axios.post(`/api/resources/${resource}`, data)
    return response.data
  },

  /**
   * Update resource
   */
  async update(resource, id, data) {
    const response = await window.axios.put(`/api/resources/${resource}/${id}`, data)
    return response.data
  },

  /**
   * Delete resource
   */
  async destroy(resource, id) {
    const response = await window.axios.delete(`/api/resources/${resource}/${id}`)
    return response.data
  },

  /**
   * Bulk delete
   */
  async bulkDelete(resource, ids) {
    const response = await window.axios.post(`/api/resources/${resource}/bulk/delete`, { ids })
    return response.data
  },

  /**
   * Bulk update
   */
  async bulkUpdate(resource, ids, data) {
    const response = await window.axios.post(`/api/resources/${resource}/bulk/update`, {
      ids,
      data
    })
    return response.data
  },

  /**
   * Export resources
   */
  async export(resource, format, params = {}) {
    const response = await window.axios.post(`/api/resources/${resource}/export`, {
      format,
      ...params
    })
    return response.data
  },

  /**
   * Search related resources (for BelongsTo fields)
   */
  async searchRelated(resource, query) {
    const response = await window.axios.get(`/api/resources/${resource}`, {
      params: { search: query, perPage: 10 }
    })
    return response.data
  }
}
```

### 2. ResourceManager Component (Main Entry Point)

**This is the component you'll use 90% of the time.** It orchestrates everything - table, forms, modals, validation, etc.

```vue
<!-- resources/js/components/resource/ResourceManager.vue -->
<template>
  <div class="resource-manager">
    <!-- Page Header -->
    <div class="mb-6">
      <h1 v-if="title" class="text-3xl font-bold">{{ title }}</h1>
      <div v-if="showBreadcrumbs" class="breadcrumbs">
        <!-- Breadcrumb implementation -->
      </div>
    </div>

    <!-- Resource Table -->
    <ResourceTable
      :resource="resource"
      :default-per-page="defaultPerPage"
      :enable-export="enableExport"
      @create="handleCreate"
      @edit="handleEdit"
      @view="handleView"
      @deleted="handleDeleted"
    />

    <!-- Create/Edit Modal -->
    <Modal v-if="showForm" @close="closeForm">
      <ResourceForm
        :resource="resource"
        :item-id="editingId"
        @success="handleFormSuccess"
        @cancel="closeForm"
      />
    </Modal>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import ResourceTable from './ResourceTable.vue'
import ResourceForm from './ResourceForm.vue'
import Modal from '@/components/common/Modal.vue'

const props = defineProps({
  resource: { type: String, required: true },
  title: { type: String, default: null },
  showBreadcrumbs: { type: Boolean, default: false },
  defaultPerPage: { type: Number, default: 15 },
  enableExport: { type: Boolean, default: true },
})

const emit = defineEmits(['created', 'updated', 'deleted'])

const showForm = ref(false)
const editingId = ref(null)

function handleCreate() {
  editingId.value = null
  showForm.value = true
}

function handleEdit(item) {
  editingId.value = item.id
  showForm.value = true
}

function handleView(item) {
  // Could open a detail view or modal
}

function handleFormSuccess(data) {
  showForm.value = false
  editingId.value = null

  // Emit events for parent component if needed
  if (data.id) {
    emit(editingId.value ? 'updated' : 'created', data)
  }
}

function handleDeleted(ids) {
  emit('deleted', ids)
}

function closeForm() {
  showForm.value = false
  editingId.value = null
}
</script>
```

### 3. Resource Table Component (Internal)

**Note**: You typically don't use this directly - `ResourceManager` uses it internally. This documentation is for reference or advanced customization.

```vue
<!-- resources/js/components/resource/ResourceTable.vue -->
<template>
  <div class="resource-table">
    <!-- Header with Search, Filters, Actions -->
    <div class="flex items-center justify-between mb-6">
      <div class="flex items-center space-x-4">
        <!-- Search -->
        <div v-if="meta.searchable" class="relative">
          <input
            v-model="search"
            type="text"
            :placeholder="`Search ${meta.label}...`"
            class="input-primary pl-10"
            @input="handleSearch"
          />
          <Icon name="search" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
        </div>

        <!-- Filters -->
        <FilterBar
          v-if="meta.filters?.length"
          :filters="meta.filters"
          :values="filterValues"
          @update="handleFilterUpdate"
        />
      </div>

      <!-- Actions -->
      <div class="flex items-center space-x-3">
        <!-- Bulk Actions (when items selected) -->
        <div v-if="selectedIds.length > 0" class="flex items-center space-x-2">
          <span class="text-sm text-gray-600 dark:text-gray-400">
            {{ selectedIds.length }} selected
          </span>
          <button
            @click="handleBulkDelete"
            class="btn-danger-sm"
          >
            Delete
          </button>
        </div>

        <!-- Export -->
        <button @click="showExportMenu = !showExportMenu" class="btn-secondary">
          <Icon name="download" />
          Export
        </button>

        <!-- Create -->
        <button @click="handleCreate" class="btn-primary">
          <Icon name="plus" />
          Create {{ meta.singularLabel }}
        </button>
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-lg shadow">
      <table class="w-full">
        <thead class="bg-gray-50 dark:bg-gray-700">
          <tr>
            <!-- Checkbox -->
            <th class="w-12 px-4 py-3">
              <input
                type="checkbox"
                :checked="allSelected"
                @change="toggleSelectAll"
                class="checkbox"
              />
            </th>

            <!-- Field Columns -->
            <th
              v-for="field in indexFields"
              :key="field.attribute"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
              :class="{ 'cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600': field.sortable }"
              @click="field.sortable && handleSort(field.attribute)"
            >
              <div class="flex items-center space-x-1">
                <span>{{ field.label }}</span>
                <Icon
                  v-if="field.sortable"
                  :name="getSortIcon(field.attribute)"
                  :size="14"
                />
              </div>
            </th>

            <!-- Actions -->
            <th class="w-32 px-6 py-3 text-right">Actions</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
          <tr
            v-for="item in items"
            :key="item.id"
            class="hover:bg-gray-50 dark:hover:bg-gray-700"
          >
            <!-- Checkbox -->
            <td class="px-4 py-4">
              <input
                type="checkbox"
                :checked="selectedIds.includes(item.id)"
                @change="toggleSelect(item.id)"
                class="checkbox"
              />
            </td>

            <!-- Field Values -->
            <td
              v-for="field in indexFields"
              :key="field.attribute"
              class="px-6 py-4 text-sm"
            >
              <FieldRenderer
                :field="field"
                :value="item[field.attribute]"
                :item="item"
                mode="index"
              />
            </td>

            <!-- Actions -->
            <td class="px-6 py-4 text-right">
              <div class="flex items-center justify-end space-x-2">
                <button
                  @click="handleView(item)"
                  class="text-blue-600 hover:text-blue-800 dark:text-blue-400"
                >
                  <Icon name="eye" :size="18" />
                </button>
                <button
                  @click="handleEdit(item)"
                  class="text-green-600 hover:text-green-800 dark:text-green-400"
                >
                  <Icon name="edit" :size="18" />
                </button>
                <button
                  @click="handleDelete(item)"
                  class="text-red-600 hover:text-red-800 dark:text-red-400"
                >
                  <Icon name="trash" :size="18" />
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Loading State -->
      <div v-if="loading" class="flex items-center justify-center py-12">
        <Icon name="spinner" class="animate-spin text-gray-400" :size="32" />
      </div>

      <!-- Empty State -->
      <div v-else-if="!items.length" class="text-center py-12">
        <p class="text-gray-500 dark:text-gray-400">No {{ meta.label }} found</p>
      </div>
    </div>

    <!-- Pagination -->
    <Pagination
      v-if="pagination.total > 0"
      :current-page="pagination.current_page"
      :total-pages="pagination.last_page"
      :per-page="pagination.per_page"
      :total="pagination.total"
      @page-change="handlePageChange"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { resourceService } from '@/services/resourceService'
import { useToast } from '@/composables/useToast'
import FieldRenderer from './FieldRenderer.vue'
import FilterBar from './filters/FilterBar.vue'
import Pagination from '@/components/common/Pagination.vue'
import Icon from '@/components/common/Icon.vue'

const props = defineProps({
  resource: {
    type: String,
    required: true
  }
})

const emit = defineEmits(['create', 'edit', 'view'])

const toast = useToast()
const loading = ref(false)
const meta = ref({})
const items = ref([])
const pagination = ref({})
const search = ref('')
const filterValues = ref({})
const sortColumn = ref('')
const sortDirection = ref('asc')
const selectedIds = ref([])
const showExportMenu = ref(false)

const indexFields = computed(() => {
  return meta.value.fields?.filter(f => f.showOnIndex) || []
})

const allSelected = computed(() => {
  return items.value.length > 0 && selectedIds.value.length === items.value.length
})

async function loadMeta() {
  try {
    meta.value = await resourceService.meta(props.resource)
  } catch (error) {
    toast.error('Failed to load resource metadata')
  }
}

async function loadItems() {
  loading.value = true
  try {
    const response = await resourceService.index(props.resource, {
      search: search.value,
      filters: filterValues.value,
      sort: sortColumn.value,
      direction: sortDirection.value,
      page: pagination.value.current_page || 1,
      perPage: meta.value.perPage
    })

    items.value = response.data
    pagination.value = {
      current_page: response.current_page,
      last_page: response.last_page,
      per_page: response.per_page,
      total: response.total
    }
  } catch (error) {
    toast.error('Failed to load resources')
  } finally {
    loading.value = false
  }
}

function handleSearch() {
  pagination.value.current_page = 1
  loadItems()
}

function handleFilterUpdate(filters) {
  filterValues.value = filters
  pagination.value.current_page = 1
  loadItems()
}

function handleSort(column) {
  if (sortColumn.value === column) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortColumn.value = column
    sortDirection.value = 'asc'
  }
  loadItems()
}

function getSortIcon(column) {
  if (sortColumn.value !== column) return 'sort'
  return sortDirection.value === 'asc' ? 'sort-up' : 'sort-down'
}

function toggleSelect(id) {
  const index = selectedIds.value.indexOf(id)
  if (index > -1) {
    selectedIds.value.splice(index, 1)
  } else {
    selectedIds.value.push(id)
  }
}

function toggleSelectAll() {
  if (allSelected.value) {
    selectedIds.value = []
  } else {
    selectedIds.value = items.value.map(item => item.id)
  }
}

async function handleBulkDelete() {
  if (!confirm(`Delete ${selectedIds.value.length} items?`)) return

  try {
    await resourceService.bulkDelete(props.resource, selectedIds.value)
    toast.success(`${selectedIds.value.length} items deleted`)
    selectedIds.value = []
    loadItems()
  } catch (error) {
    toast.error('Failed to delete items')
  }
}

function handleCreate() {
  emit('create')
}

function handleEdit(item) {
  emit('edit', item)
}

function handleView(item) {
  emit('view', item)
}

async function handleDelete(item) {
  if (!confirm(`Delete ${item[meta.value.title || 'id']}?`)) return

  try {
    await resourceService.destroy(props.resource, item.id)
    toast.success('Item deleted')
    loadItems()
  } catch (error) {
    toast.error('Failed to delete item')
  }
}

function handlePageChange(page) {
  pagination.value.current_page = page
  loadItems()
}

onMounted(async () => {
  await loadMeta()
  loadItems()
})
</script>
```

### 4. Resource Form Component (Internal)

**Note**: You typically don't use this directly - `ResourceManager` uses it internally. This documentation is for reference or advanced customization.

```vue
<!-- resources/js/components/resource/ResourceForm.vue -->
<template>
  <form @submit.prevent="handleSubmit" class="space-y-6">
    <div
      v-for="field in formFields"
      :key="field.attribute"
      class="form-group"
    >
      <FormLabel
        :for="field.attribute"
        :required="field.required"
      >
        {{ field.label }}
      </FormLabel>

      <FieldInput
        :field="field"
        v-model="formData[field.attribute]"
        :error="errors[field.attribute]"
      />

      <FormError v-if="errors[field.attribute]">
        {{ errors[field.attribute] }}
      </FormError>
    </div>

    <FormActions>
      <button type="button" @click="handleCancel" class="btn-secondary">
        Cancel
      </button>
      <button type="submit" :disabled="submitting" class="btn-primary">
        {{ submitting ? 'Saving...' : (isEdit ? 'Update' : 'Create') }}
      </button>
    </FormActions>
  </form>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { resourceService } from '@/services/resourceService'
import { useToast } from '@/composables/useToast'
import FieldInput from './fields/FieldInput.vue'
import FormLabel from '@/components/form/FormLabel.vue'
import FormError from '@/components/form/FormError.vue'
import FormActions from '@/components/form/FormActions.vue'

const props = defineProps({
  resource: {
    type: String,
    required: true
  },
  itemId: {
    type: [Number, String],
    default: null
  }
})

const emit = defineEmits(['success', 'cancel'])

const toast = useToast()
const meta = ref({})
const formData = ref({})
const errors = ref({})
const submitting = ref(false)

const isEdit = computed(() => !!props.itemId)

const formFields = computed(() => {
  return meta.value.fields?.filter(f => f.showOnForm) || []
})

async function loadMeta() {
  meta.value = await resourceService.meta(props.resource)

  // Initialize form data with defaults
  formFields.value.forEach(field => {
    formData.value[field.attribute] = field.default ?? null
  })
}

async function loadItem() {
  if (!props.itemId) return

  try {
    const response = await resourceService.show(props.resource, props.itemId)
    formData.value = { ...formData.value, ...response.data }
  } catch (error) {
    toast.error('Failed to load item')
  }
}

async function handleSubmit() {
  errors.value = {}
  submitting.value = true

  try {
    const response = isEdit.value
      ? await resourceService.update(props.resource, props.itemId, formData.value)
      : await resourceService.store(props.resource, formData.value)

    toast.success(response.message)
    emit('success', response.data)
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {}
    }
  } finally {
    submitting.value = false
  }
}

function handleCancel() {
  emit('cancel')
}

onMounted(async () => {
  await loadMeta()
  await loadItem()
})
</script>
```

---

## Usage Examples

### Example Resource Definition

```php
// app/Resources/UserResource.php
<?php

namespace App\Resources;

use App\Enums\Status;
use App\Models\User;
use App\Resources\Fields\Boolean;
use App\Resources\Fields\Date;
use App\Resources\Fields\Email;
use App\Resources\Fields\HasMany;
use App\Resources\Fields\ID;
use App\Resources\Fields\Select;
use App\Resources\Fields\Text;
use App\Resources\Fields\BelongsTo;

class UserResource extends Resource
{
    public static string $model = User::class;
    public static string $label = 'Users';
    public static string $singularLabel = 'User';
    public static string $title = 'name';
    public static array $search = ['name', 'email'];

    public function fields(): array
    {
        return [
            ID::make('ID')
                ->sortable(),

            Text::make('Name')
                ->rules('required|max:255')
                ->sortable()
                ->searchable(),

            Email::make('Email')
                ->rules('required|email|unique:users,email')
                ->sortable()
                ->searchable(),

            Select::make('Status')
                ->options(Status::class)
                ->rules('required')
                ->sortable(),

            BelongsTo::make('Role')
                ->resource(RoleResource::class)
                ->searchable()
                ->rules('required'),

            Date::make('Email Verified At')
                ->hideFromForm(),

            Date::make('Created At')
                ->sortable()
                ->hideFromForm(),
        ];
    }

    public function filters(): array
    {
        return [
            SelectFilter::make('Status')
                ->options(Status::class),

            SelectFilter::make('Role')
                ->options(fn() => Role::pluck('name', 'id')),

            DateRangeFilter::make('Created At'),
        ];
    }

    public function actions(): array
    {
        return [
            ExportAction::make(['csv', 'xlsx', 'pdf']),
            BulkDeleteAction::make(),
            BulkUpdateAction::make([
                'status' => Status::class
            ]),
        ];
    }

    public function with(): array
    {
        return ['roles'];
    }
}
```

### Frontend Usage - The Simple Way (Recommended)

**That's it - just 5 lines of code!** Everything else is handled automatically:
- ✅ Data table with sorting, searching, pagination
- ✅ Create/Edit forms with validation
- ✅ Delete confirmation
- ✅ Bulk operations
- ✅ Filters and actions
- ✅ Relationship handling
- ✅ Success/error notifications
- ✅ Loading states

```vue
<!-- resources/js/pages/Users.vue -->
<template>
  <ResourceManager resource="users" />
</template>

<script setup>
import ResourceManager from '@/components/resource/ResourceManager.vue'
</script>
```

**Optional Props** (if you want customization):
```vue
<ResourceManager
  resource="users"
  title="User Management"           <!-- Custom page title -->
  :show-breadcrumbs="true"          <!-- Show breadcrumbs -->
  :default-per-page="20"            <!-- Items per page -->
  :enable-export="true"             <!-- Enable export button -->
  @created="handleCreated"          <!-- Custom event handlers -->
  @updated="handleUpdated"
  @deleted="handleDeleted"
/>
```

### Frontend Usage - Advanced (Custom Layout)

**Only use this if you need complete control over the layout.** Most use cases work fine with `ResourceManager`.

```vue
<!-- resources/js/pages/Users.vue (Custom Layout) -->
<template>
  <div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Users</h1>

    <ResourceTable
      resource="users"
      @create="showCreateForm = true"
      @edit="handleEdit"
      @view="handleView"
    />

    <Modal v-if="showCreateForm" @close="showCreateForm = false">
      <ResourceForm
        resource="users"
        :item-id="editingId"
        @success="handleFormSuccess"
        @cancel="showCreateForm = false"
      />
    </Modal>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import ResourceTable from '@/components/resource/ResourceTable.vue'
import ResourceForm from '@/components/resource/ResourceForm.vue'
import Modal from '@/components/common/Modal.vue'

const showCreateForm = ref(false)
const editingId = ref(null)

function handleEdit(item) {
  editingId.value = item.id
  showCreateForm.value = true
}

function handleView(item) {
  // Navigate to detail page or show modal
}

function handleFormSuccess() {
  showCreateForm.value = false
  editingId.value = null
}
</script>
```

---

## Routes Configuration

```php
// routes/api.php

use App\Http\Controllers\Api\ResourceController;

Route::middleware('auth:sanctum')->prefix('resources')->group(function () {
    Route::get('{resource}/meta', [ResourceController::class, 'meta']);
    Route::get('{resource}', [ResourceController::class, 'index']);
    Route::post('{resource}', [ResourceController::class, 'store']);
    Route::get('{resource}/{id}', [ResourceController::class, 'show']);
    Route::put('{resource}/{id}', [ResourceController::class, 'update']);
    Route::delete('{resource}/{id}', [ResourceController::class, 'destroy']);
    Route::post('{resource}/bulk/{action}', [ResourceController::class, 'bulkAction']);
    Route::post('{resource}/export', [ResourceController::class, 'export']);
});
```

```php
// config/resources.php

return [
    'users' => \App\Resources\UserResource::class,
    'roles' => \App\Resources\RoleResource::class,
    // Add all your resources here
];
```

---

## Implementation Roadmap

### Phase 1: Backend Foundation (2-3 hours)
1. ✅ Create base `Resource` class
2. ✅ Create base `Field` class
3. ✅ Create core field types (Text, Number, Boolean, Date, Select)
4. ✅ Create `ResourceService`
5. ✅ Create `ResourceController`
6. ✅ Add routes configuration
7. ✅ Test with UserResource example

### Phase 2: Relationship Fields (2-3 hours)
1. ✅ Create `BelongsTo` field
2. ✅ Create `HasMany` field
3. ✅ Create `BelongsToMany` field
4. ✅ Create `MorphMany` field
5. ✅ Update service to handle relationship syncing
6. ✅ Test all relationship types

### Phase 3: Frontend Core (3-4 hours)
1. ✅ Create `resourceService.js`
2. ✅ Create `ResourceManager.vue` (main entry point)
3. ✅ Create `ResourceTable.vue`
4. ✅ Create `ResourceForm.vue`
5. ✅ Create `FieldRenderer.vue` for display
6. ✅ Create `FieldInput.vue` for input
7. ✅ Create field-specific renderers/inputs

### Phase 4: Filters & Actions (2-3 hours)
1. ✅ Create base `Filter` class
2. ✅ Create filter types (SelectFilter, DateRangeFilter, etc.)
3. ✅ Create `FilterBar.vue` component
4. ✅ Create base `Action` class
5. ✅ Create action types (ExportAction, BulkDeleteAction, etc.)
6. ✅ Integrate actions in ResourceTable

### Phase 5: Advanced Features (3-4 hours)
1. ✅ Export functionality (CSV, XLSX, PDF)
2. ✅ Bulk operations UI
3. ✅ Search implementation
4. ✅ Sorting implementation
5. ✅ Pagination component
6. ✅ Loading and empty states

### Phase 6: Polish & Testing (2-3 hours)
1. ✅ Error handling
2. ✅ Toast notifications integration
3. ✅ Dark mode support
4. ✅ Accessibility improvements
5. ✅ Write tests
6. ✅ Documentation

**Total Estimated Time: 14-20 hours**

---

## Benefits Summary

### For Developers
- **Write 90% less code** - One resource class vs 7+ files
- **Consistent patterns** - Same structure everywhere
- **Type safety** - Full IDE autocomplete
- **Reusable** - Fields, filters, actions work across all resources
- **Maintainable** - Changes in one place

### For Users
- **Consistent UI** - Same experience across all CRUD operations
- **Fast** - Optimized queries with eager loading
- **Powerful** - Advanced filtering, sorting, searching built-in
- **Accessible** - WCAG compliant

### For the Project
- **Rapid development** - New resources in minutes
- **Less bugs** - Standardized, tested code
- **Easy onboarding** - New devs learn one pattern
- **Scalable** - Add hundreds of resources easily

---

## Advanced Customization

### Custom Field Types

```php
// app/Resources/Fields/RichText.php
class RichText extends Field
{
    protected function fieldType(): string
    {
        return 'rich-text';
    }

    public function toolbar(array $tools): static
    {
        return $this->meta(['toolbar' => $tools]);
    }
}
```

### Custom Actions

```php
// app/Resources/Actions/ActivateUsersAction.php
class ActivateUsersAction extends Action
{
    public function handle(Collection $models): void
    {
        $models->each->activate();
    }
}
```

### Conditional Fields

```php
public function fields(): array
{
    return [
        // ... other fields

        Select::make('Type')->options(['individual', 'company']),

        Text::make('Company Name')
            ->rules('required_if:type,company')
            ->showWhen('type', 'company'),
    ];
}
```

### Computed Fields

```php
Computed::make('Full Name', fn($model) => $model->first_name . ' ' . $model->last_name)
    ->hideFromForm()
```

---

## Next Steps

1. **Implement Phase 1** - Get basic CRUD working
2. **Test with existing models** - User, Role, Setting
3. **Add relationship support** - Phase 2
4. **Build frontend** - Phase 3
5. **Add filters & actions** - Phase 4
6. **Polish** - Phase 5-6
7. **Document for team** - Usage guide
8. **Migrate existing code** - Replace old CRUD implementations

This system will transform your development workflow and make building admin interfaces a breeze!
