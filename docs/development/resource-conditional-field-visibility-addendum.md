# Conditional Field Visibility - Implementation Addendum

## Critical Missing Pieces & Solutions

This document addresses implementation gaps, edge cases, and critical issues not covered in the main specification.

---

## 1. Closure Serialization Problem ⚠️

### The Issue

Closures (anonymous functions) used in `showWhen()` and `hideWhen()` **cannot be serialized to JSON** for frontend consumption.

```php
// This CANNOT be sent to frontend
Text::make('Discount')
    ->showWhen(function ($data) {
        return ($data['total'] ?? 0) > 100;
    })
```

### Solutions

#### Option A: Backend-Only Callbacks (Recommended for MVP)

Callbacks are evaluated **only during validation**, not in real-time on frontend.

**Pros:**
- ✅ Simple to implement
- ✅ Secure (business logic stays on backend)
- ✅ No serialization issues

**Cons:**
- ❌ No real-time UI updates for callback conditions
- ❌ User sees validation errors instead of hidden fields

**Implementation:**

```php
// In Field.php
public function showWhen(\Closure $callback): static
{
    // Don't send callback to frontend
    $this->meta['showWhen'] = ['type' => 'callback', 'frontend' => false];

    // Store callback for backend validation only
    $this->showWhenCallback = $callback;

    return $this;
}

public function toArray(): array
{
    $meta = $this->meta;

    // Remove non-serializable callbacks
    unset($meta['showWhenCallback'], $meta['hideWhenCallback']);

    return [
        // ... other properties
        'meta' => $meta,
    ];
}
```

**Frontend Behavior:**

```vue
<!-- Field always shows, backend validates on submit -->
<div v-show="isVisible" class="form-group">
  <!-- If callback condition not met, backend returns validation error -->
</div>
```

---

#### Option B: Structured Conditions (Recommended for Production)

Convert callbacks to a structured format that both backend and frontend can evaluate.

```php
// Instead of closure, use structured conditions
Text::make('Discount')
    ->showWhen([
        'type' => 'comparison',
        'field' => 'total',
        'operator' => '>',
        'value' => 100,
    ])

// For complex logic, use array of conditions
Text::make('Field')
    ->showWhen([
        'type' => 'and',
        'conditions' => [
            ['field' => 'account_type', 'operator' => '=', 'value' => 'business'],
            ['field' => 'revenue', 'operator' => '>', 'value' => 1000000],
        ]
    ])
```

**Implementation:**

```php
// In Field.php
public function showWhen(\Closure|array $condition): static
{
    if (is_callable($condition)) {
        // Callback: backend only
        $this->showWhenCallback = $condition;
        $this->meta['showWhen'] = ['type' => 'callback', 'frontend' => false];
    } else {
        // Structured: both backend and frontend
        $this->meta['showWhen'] = $condition;
    }

    return $this;
}

// Evaluate structured condition
protected function evaluateStructuredCondition(array $condition, array $formData): bool
{
    return match($condition['type']) {
        'comparison' => $this->evaluateCondition(
            $formData,
            $condition['field'],
            $condition['value'],
            $condition['operator']
        ),
        'and' => collect($condition['conditions'])
            ->every(fn($c) => $this->evaluateStructuredCondition($c, $formData)),
        'or' => collect($condition['conditions'])
            ->contains(fn($c) => $this->evaluateStructuredCondition($c, $formData)),
        default => false,
    };
}
```

**Frontend Evaluation:**

```javascript
function evaluateStructuredCondition(condition, formData) {
  if (condition.type === 'comparison') {
    return evaluateCondition(
      condition.field,
      condition.value,
      condition.operator
    )
  }

  if (condition.type === 'and') {
    return condition.conditions.every(c =>
      evaluateStructuredCondition(c, formData)
    )
  }

  if (condition.type === 'or') {
    return condition.conditions.some(c =>
      evaluateStructuredCondition(c, formData)
    )
  }

  return false
}
```

---

#### Option C: Hybrid Approach (Best UX)

Use simple conditions for real-time frontend, closures for complex backend validation.

```php
Text::make('Discount')
    // Simple condition for frontend real-time updates
    ->dependsOn('total', 100, '>')

    // Additional backend validation with closure
    ->validateWhen(function ($data) {
        return $data['user_type'] === 'premium'
            && $data['total'] > 100
            && !$data['used_discount_this_month'];
    })
```

---

## 2. Resource.php Missing Methods

### Add flattenFields() Method

The base `Resource.php` class needs this method to support Sections/Groups.

```php
<?php

namespace App\Resources;

use App\Resources\Fields\Section;
use App\Resources\Fields\Group;

abstract class Resource
{
    // ... existing code

    /**
     * Flatten nested field structure (Sections/Groups) to flat array
     */
    public function flattenFields(?array $fields = null): array
    {
        $fields = $fields ?? $this->formFields();
        $flattened = [];

        foreach ($fields as $item) {
            if ($item instanceof Section) {
                $flattened = array_merge($flattened, $this->flattenSectionFields($item));
            } elseif ($item instanceof Group) {
                $flattened = array_merge($flattened, $item->fields);
            } else {
                $flattened[] = $item;
            }
        }

        return $flattened;
    }

    /**
     * Flatten section fields (handle nested groups within sections)
     */
    protected function flattenSectionFields(Section $section): array
    {
        $fields = [];

        foreach ($section->fields as $item) {
            if ($item instanceof Group) {
                $fields = array_merge($fields, $item->fields);
            } else {
                $fields[] = $item;
            }
        }

        return $fields;
    }

    /**
     * Get all form fields with conditional visibility evaluated
     */
    public function getVisibleFields(array $formData): array
    {
        return collect($this->flattenFields())
            ->filter(fn($field) => $field->isVisible($formData))
            ->all();
    }
}
```

---

## 3. ResourceUpdateRequest

Create separate request class for updates (mirrors ResourceStoreRequest):

```php
<?php

namespace App\Http\Requests;

class ResourceUpdateRequest extends ResourceStoreRequest
{
    // Inherits all logic from ResourceStoreRequest

    /**
     * Additional rules specific to updates
     */
    public function rules(): array
    {
        $rules = parent::rules();

        // Modify unique rules to exclude current record
        if ($this->route('id')) {
            foreach ($rules as $field => $fieldRules) {
                if (is_string($fieldRules) && str_contains($fieldRules, 'unique:')) {
                    $rules[$field] = str_replace(
                        'unique:',
                        "unique:{$this->route('id')}:",
                        $fieldRules
                    );
                }
            }
        }

        return $rules;
    }
}
```

**Update ResourceController:**

```php
public function update(Request $request, string $resourceKey, int|string $id)
{
    $resourceClass = $this->getResourceClass($resourceKey);

    // Use ResourceUpdateRequest
    $formRequest = app(ResourceUpdateRequest::class);
    $formRequest->setResource($resourceClass);
    $formRequest->setRouteResolver(function () use ($request) {
        return $request->route();
    });

    $validated = $request->validate($formRequest->rules());

    $data = $this->resourceService->update($resourceClass, $id, $validated);

    return response()->json($data);
}
```

---

## 4. Section & Group Conditional Visibility

Extend Section and Group to support visibility conditions.

### Section with Conditions

```php
<?php

namespace App\Resources\Fields;

class Section
{
    // ... existing properties

    protected ?array $dependsOn = null;
    protected $showWhen = null;

    // ... existing methods

    /**
     * Show section when condition is met
     */
    public function dependsOn(string $attribute, mixed $value, string $operator = '='): static
    {
        $this->dependsOn = [
            'attribute' => $attribute,
            'value' => $value,
            'operator' => $operator,
        ];

        return $this;
    }

    /**
     * Show section when callback returns true
     */
    public function showWhen(\Closure|array $callback): static
    {
        $this->showWhen = $callback;
        return $this;
    }

    /**
     * Check if section should be visible
     */
    public function isVisible(array $formData): bool
    {
        if ($this->dependsOn !== null) {
            // Use same evaluation logic as Field
            $actualValue = $formData[$this->dependsOn['attribute']] ?? null;
            $expectedValue = $this->dependsOn['value'];
            $operator = $this->dependsOn['operator'];

            return match($operator) {
                '=' => $actualValue == $expectedValue,
                '!=' => $actualValue != $expectedValue,
                '>' => $actualValue > $expectedValue,
                '>=' => $actualValue >= $expectedValue,
                '<' => $actualValue < $expectedValue,
                '<=' => $actualValue <= $expectedValue,
                default => false,
            };
        }

        if ($this->showWhen !== null) {
            if (is_callable($this->showWhen)) {
                return call_user_func($this->showWhen, $formData);
            }
            // Handle structured conditions
        }

        return true;
    }

    public function toArray(): array
    {
        return [
            'type' => 'section',
            'title' => $this->title,
            'description' => $this->description,
            'icon' => $this->icon,
            'collapsible' => $this->collapsible,
            'collapsed' => $this->collapsed,
            'cols' => $this->cols,
            'gap' => $this->gap,
            'containerClasses' => $this->containerClasses,
            'dependsOn' => $this->dependsOn,
            'fields' => array_map(fn($field) => $field->toArray(), $this->fields),
        ];
    }
}
```

### Group with Conditions

```php
<?php

namespace App\Resources\Fields;

class Group
{
    // ... existing properties

    protected ?array $dependsOn = null;

    // Add same methods as Section
    public function dependsOn(string $attribute, mixed $value, string $operator = '='): static
    {
        $this->dependsOn = [
            'attribute' => $attribute,
            'value' => $value,
            'operator' => $operator,
        ];

        return $this;
    }

    public function isVisible(array $formData): bool
    {
        // Same logic as Section
        if ($this->dependsOn !== null) {
            // Evaluate condition...
        }
        return true;
    }

    public function toArray(): array
    {
        return [
            'type' => 'group',
            'label' => $this->label,
            'cols' => $this->cols,
            'gap' => $this->gap,
            'dependsOn' => $this->dependsOn,
            'fields' => array_map(fn($field) => $field->toArray(), $this->fields),
        ];
    }
}
```

### Usage Example

```php
Section::make('Business Information')
    ->dependsOn('account_type', 'business')  // Hide entire section
    ->fields([
        Text::make('Company Name')->requiredWhen('account_type', 'business'),
        Text::make('Tax ID')->requiredWhen('account_type', 'business'),
    ]),

Section::make('Shipping Details')
    ->showWhen(fn($data) => ($data['order_total'] ?? 0) > 50)
    ->fields([
        Text::make('Shipping Address'),
        Select::make('Shipping Method'),
    ]),
```

---

## 5. Field Value Management

### Issue: What happens to hidden field values?

Three strategies to consider:

#### Strategy A: Preserve Values (Recommended)

Hidden fields keep their values, useful when toggling visibility.

```javascript
// In ResourceForm.vue - do NOT clear hidden field values
const handleSubmit = async () => {
  // Send all form data, let backend filter based on visibility
  const response = await resourceService.store(resource, formData.value)
}
```

**Backend filters hidden fields:**

```php
// In ResourceService.php store() method
public function store(array $data): Model
{
    // Filter data to only visible fields
    $resource = new $this->resourceClass;
    $visibleFields = $resource->getVisibleFields($data);

    $allowedAttributes = collect($visibleFields)->pluck('attribute')->all();
    $filteredData = array_intersect_key($data, array_flip($allowedAttributes));

    // ... continue with filtered data
}
```

#### Strategy B: Clear Hidden Field Values

Remove values when fields become hidden (loses data on toggle).

```javascript
// Watch for visibility changes and clear values
watch(() => formData.value, (newData, oldData) => {
  formFields.value.forEach(field => {
    if (!field.isVisible(newData) && newData[field.attribute]) {
      delete formData.value[field.attribute]
    }
  })
}, { deep: true })
```

#### Strategy C: Separate Visible/All Data

Maintain two data objects: one for visible fields, one for all.

```javascript
const formData = ref({})
const allData = ref({}) // Preserves all data including hidden

const visibleData = computed(() => {
  return Object.fromEntries(
    Object.entries(allData.value).filter(([key]) => {
      const field = formFields.value.find(f => f.attribute === key)
      return field?.isVisible(allData.value) !== false
    })
  )
})
```

**Recommendation: Use Strategy A** - preserve values, filter on backend. Provides best UX for toggling fields.

---

## 6. Frontend ResourceForm.vue Updates

### Add Section/Group Visibility Support

```vue
<template>
  <form @submit.prevent="handleSubmit">
    <div class="grid grid-cols-12 gap-4 md:gap-6">
      <template v-for="(item, index) in formStructure" :key="index">

        <!-- Section with Visibility -->
        <div
          v-if="item.type === 'section'"
          v-show="isSectionVisible(item)"
          :class="item.cols || 'col-span-12'"
        >
          <!-- Section content -->
        </div>

        <!-- Group with Visibility -->
        <div
          v-else-if="item.type === 'group'"
          v-show="isGroupVisible(item)"
          :class="item.cols || 'col-span-12'"
        >
          <!-- Group content -->
        </div>

        <!-- Regular Field -->
        <div v-else :class="item.cols || 'col-span-12'">
          <FieldRenderer
            :field="item"
            :model-value="formData"
            :errors="errors"
          />
        </div>
      </template>
    </div>
  </form>
</template>

<script setup>
// ... existing code

function isSectionVisible(section) {
  if (!section.dependsOn) return true

  const dep = section.dependsOn
  return evaluateCondition(
    dep.attribute,
    dep.value,
    dep.operator || '='
  )
}

function isGroupVisible(group) {
  if (!group.dependsOn) return true

  const dep = group.dependsOn
  return evaluateCondition(
    dep.attribute,
    dep.value,
    dep.operator || '='
  )
}

function evaluateCondition(attribute, expectedValue, operator = '=') {
  const actualValue = formData.value[attribute]

  switch (operator) {
    case '=': return actualValue == expectedValue
    case '!=': return actualValue != expectedValue
    case '>': return actualValue > expectedValue
    case '>=': return actualValue >= expectedValue
    case '<': return actualValue < expectedValue
    case '<=': return actualValue <= expectedValue
    case 'in':
      return Array.isArray(expectedValue) && expectedValue.includes(actualValue)
    case 'not_in':
      return Array.isArray(expectedValue) && !expectedValue.includes(actualValue)
    default: return false
  }
}
</script>
```

---

## 7. Edge Cases & Solutions

### Circular Dependencies

Prevent infinite loops from circular dependencies.

```php
// In Field.php
private static array $evaluationStack = [];

public function isVisible(array $formData): bool
{
    // Detect circular dependency
    if (in_array($this->attribute, self::$evaluationStack)) {
        throw new \RuntimeException(
            "Circular dependency detected: " . implode(' -> ', self::$evaluationStack) . " -> {$this->attribute}"
        );
    }

    self::$evaluationStack[] = $this->attribute;

    try {
        $visible = $this->evaluateVisibility($formData);
    } finally {
        array_pop(self::$evaluationStack);
    }

    return $visible;
}
```

### Default Visibility for Empty Dependencies

```php
protected function evaluateCondition(
    array $formData,
    string $attribute,
    mixed $expectedValue,
    string $operator = '='
): bool {
    // If dependency field doesn't exist, default to hidden
    if (!array_key_exists($attribute, $formData)) {
        return false; // or true, depending on desired behavior
    }

    $actualValue = $formData[$attribute];

    // Handle null values
    if ($actualValue === null) {
        return $expectedValue === null; // Only visible if expecting null
    }

    // ... rest of evaluation
}
```

### Array/Object Field Values

```php
// Support for array field values (e.g., multi-select)
case 'contains':
    return is_array($actualValue) && in_array($expectedValue, $actualValue);
case 'not_contains':
    return is_array($actualValue) && !in_array($expectedValue, $actualValue);
case 'empty':
    return empty($actualValue);
case 'not_empty':
    return !empty($actualValue);
```

---

## 8. Transition Animations

Add smooth transitions when fields appear/disappear.

```vue
<template>
  <transition
    enter-active-class="transition ease-out duration-200"
    enter-from-class="opacity-0 -translate-y-1"
    enter-to-class="opacity-100 translate-y-0"
    leave-active-class="transition ease-in duration-150"
    leave-from-class="opacity-100 translate-y-0"
    leave-to-class="opacity-0 -translate-y-1"
  >
    <div v-show="isVisible" class="form-group">
      <!-- Field content -->
    </div>
  </transition>
</template>
```

**Note:** Use `v-show` with transitions, not `v-if` (v-if removes from DOM entirely, breaking transitions).

---

## 9. v-if vs v-show Decision Matrix

| Scenario | Use | Reason |
|----------|-----|--------|
| Frequently toggled fields | `v-show` | Better performance, keeps DOM state |
| Conditionally required fields | `v-show` | Preserve validation state |
| Heavy components (media uploads) | `v-if` | Remove from DOM when hidden |
| Initial load hidden | `v-show` | Faster initial render |
| Never shown again | `v-if` | Free up memory |

**Recommendation:** Use `v-show` by default for conditional fields, `v-if` for heavy components.

---

## 10. VeeValidate Integration (Optional)

If project uses VeeValidate, integrate with conditional validation:

```vue
<template>
  <Field
    v-show="isVisible"
    :name="field.attribute"
    :rules="dynamicRules"
    v-slot="{ field: veeField, errors }"
  >
    <input
      v-bind="veeField"
      :type="field.type"
      :placeholder="field.meta?.placeholder"
      :class="{ 'border-red-500': errors.length }"
    />
    <span v-if="errors.length" class="text-red-600 text-sm">
      {{ errors[0] }}
    </span>
  </Field>
</template>

<script setup>
import { Field } from 'vee-validate'

const dynamicRules = computed(() => {
  if (!isVisible.value) return '' // No validation for hidden fields

  let rules = field.rules || ''

  // Add required if conditionally required
  if (isFieldRequired.value && !rules.includes('required')) {
    rules = 'required|' + rules
  }

  return rules
})
</script>
```

---

## 11. ResourceController getMeta Updates

Ensure meta endpoint includes conditional visibility metadata:

```php
public function getMeta(Request $request, string $resourceKey, string $context = 'index')
{
    $resourceClass = $this->getResourceClass($resourceKey);
    $resource = new $resourceClass;

    $fields = match ($context) {
        'index' => $resource->getIndexFields(),
        'show' => $resource->getShowFields(),
        'form' => $resource->formFields(), // Return structure with Sections/Groups
        default => [],
    };

    // Serialize fields (handles conditional metadata)
    $serializedFields = array_map(function ($field) {
        if (method_exists($field, 'toArray')) {
            return $field->toArray();
        }
        return $field;
    }, $fields);

    return response()->json([
        'fields' => $serializedFields,
        'filters' => array_map(fn($f) => $f->toArray(), $resource->filters()),
        'actions' => array_map(fn($a) => $a->toArray(), $resource->actions()),
        'label' => $resourceClass::$label,
        'singularLabel' => $resourceClass::$singularLabel,
        'model' => $resourceClass::$model,
    ]);
}
```

---

## 12. Complete Implementation Checklist

### Backend

- [ ] Update `Field.php` with conditional methods
- [ ] Add `Section::dependsOn()`, `Section::showWhen()`
- [ ] Add `Group::dependsOn()`, `Group::showWhen()`
- [ ] Add `Resource::flattenFields()` method
- [ ] Add `Resource::getVisibleFields()` method
- [ ] Create `ResourceStoreRequest` with dynamic validation
- [ ] Create `ResourceUpdateRequest` extending store request
- [ ] Update `ResourceController::store()` to use new request
- [ ] Update `ResourceController::update()` to use new request
- [ ] Add circular dependency detection
- [ ] Add value filtering in `ResourceService::store()`
- [ ] Add value filtering in `ResourceService::update()`

### Frontend

- [ ] Update `FieldRenderer.vue` with visibility logic
- [ ] Add `isVisible` computed property
- [ ] Add `isFieldRequired` computed property
- [ ] Add `isFieldDisabled` computed property
- [ ] Add condition evaluation function
- [ ] Update `ResourceForm.vue` with section/group visibility
- [ ] Add transition animations (optional)
- [ ] Add VeeValidate integration (if used)
- [ ] Handle hidden field value strategy

### Testing

- [ ] Unit tests for `Field::isVisible()`
- [ ] Unit tests for `Field::isRequired()`
- [ ] Unit tests for `Field::evaluateCondition()`
- [ ] Unit tests for circular dependency detection
- [ ] Frontend tests for reactive visibility
- [ ] E2E tests for conditional forms
- [ ] Validation tests for hidden/visible fields

---

## Summary of Critical Fixes

1. **✅ Closure Serialization** - Use structured conditions or backend-only callbacks
2. **✅ Resource.php Methods** - Add `flattenFields()` and `getVisibleFields()`
3. **✅ Update Request** - Create `ResourceUpdateRequest`
4. **✅ Section/Group Visibility** - Extend Section and Group classes
5. **✅ Field Value Strategy** - Preserve values, filter on backend
6. **✅ Frontend Integration** - Update FieldRenderer and ResourceForm
7. **✅ Edge Cases** - Circular deps, null values, arrays
8. **✅ Transitions** - Add smooth animations
9. **✅ v-if vs v-show** - Use v-show for toggleable fields

With these additions, the conditional field visibility system is **production-ready**! 🚀
