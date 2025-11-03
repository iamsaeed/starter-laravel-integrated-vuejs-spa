# Resource Form Layout Enhancement

## Overview

This document describes the **Tailwind-native form layout system** for the Resource CRUD system. This enhancement allows you to control form layouts directly from the backend using Tailwind CSS classes, with full support for sections, groups, and responsive design.

### Key Features:

- **Sections** - Organize fields into visual sections with headers and descriptions
- **Groups** - Logical field grouping without visual chrome
- **Tailwind-native** - Use Tailwind grid classes directly (no mapping needed)
- **Mobile-first** - Responsive by default, fields stack on mobile
- **Collapsible sections** - Save space with collapsible sections
- **Zero JavaScript mapping** - Backend classes pass through to frontend unchanged

---

## Section API

Sections are visual containers that group fields with a title, optional description, and styling.

### Creating a Section

```php
Section::make('Section Title')
    ->description('Optional description text')
    ->icon('user')                    // Optional icon name
    ->collapsible()                   // Make section collapsible
    ->collapsed(false)                // Start collapsed (default: false)
    ->cols('col-span-12')             // Tailwind grid classes (default: col-span-12)
    ->gap('gap-4 md:gap-6')          // Tailwind gap classes (default: gap-4)
    ->containerClasses('bg-white dark:bg-gray-800 p-6 rounded-lg shadow')
    ->fields([
        // Array of fields
    ])
```

### Section Methods

| Method | Parameters | Description |
|--------|------------|-------------|
| `make()` | `string $title` | Create new section with title |
| `description()` | `string $text` | Add description below title |
| `icon()` | `string $iconName` | Add icon to section header |
| `collapsible()` | `bool $collapsible = true` | Make section collapsible |
| `collapsed()` | `bool $collapsed = false` | Start section collapsed |
| `cols()` | `string $classes` | Tailwind grid column classes |
| `gap()` | `string $classes` | Tailwind gap classes for inner grid |
| `containerClasses()` | `string $classes` | Custom container classes |
| `fields()` | `array $fields` | Array of fields to display |

### Example

```php
Section::make('Personal Information')
    ->description('Enter your personal details')
    ->icon('user')
    ->collapsible()
    ->cols('col-span-12')
    ->gap('gap-6')
    ->fields([
        Text::make('First Name')->cols('col-span-12 md:col-span-6'),
        Text::make('Last Name')->cols('col-span-12 md:col-span-6'),
        Email::make('Email')->cols('col-span-12'),
    ])
```

---

## Group API

Groups provide logical field grouping without visual separation (no border, header, or styling).

### Creating a Group

```php
Group::make([
    Text::make('City')->cols('col-span-12 md:col-span-6'),
    Text::make('Zip')->cols('col-span-12 md:col-span-6'),
])
    ->label('Location')              // Optional label
    ->cols('col-span-12')            // Group width
    ->gap('gap-4')                   // Gap between fields
```

### Group Methods

| Method | Parameters | Description |
|--------|------------|-------------|
| `make()` | `array $fields` | Create group with fields |
| `label()` | `string $label` | Optional label (not visually prominent) |
| `cols()` | `string $classes` | Tailwind grid column classes |
| `gap()` | `string $classes` | Tailwind gap classes |

---

## Field Layout Controls

All fields inherit these layout control methods from the base `Field` class.

### Field Methods

```php
Text::make('Field Name')
    ->cols('col-span-12 md:col-span-6')           // Grid column classes
    ->containerClasses('bg-yellow-50 p-4 rounded') // Custom wrapper classes
```

| Method | Parameters | Description |
|--------|------------|-------------|
| `cols()` | `string $classes` | Tailwind grid column classes (default: `col-span-12`) |
| `containerClasses()` | `string $classes` | Custom classes for field wrapper div |

### Default Behavior

- Fields default to `col-span-12` (full width)
- Mobile-first: Fields stack on small screens unless specified otherwise

---

## Tailwind Grid Reference

The form uses a **12-column grid system** with Tailwind CSS.

### Common Column Spans

| Class | Width | Use Case |
|-------|-------|----------|
| `col-span-12` or `col-span-full` | 100% | Full width fields |
| `col-span-6` | 50% | Two-column layout |
| `col-span-4` | 33.33% | Three-column layout |
| `col-span-3` | 25% | Four-column layout |
| `col-span-8` | 66.67% | Main content in 2:1 ratio |
| `col-span-9` | 75% | 3:1 ratio |

### Responsive Breakpoints

| Prefix | Breakpoint | Device |
|--------|------------|--------|
| (none) | 0px+ | Mobile (default) |
| `sm:` | 640px+ | Large mobile |
| `md:` | 768px+ | Tablet |
| `lg:` | 1024px+ | Desktop |
| `xl:` | 1280px+ | Large desktop |
| `2xl:` | 1536px+ | Extra large |

### Common Responsive Patterns

```php
// Full on mobile, half on tablet+
->cols('col-span-12 md:col-span-6')

// Full on mobile, half on tablet, third on desktop
->cols('col-span-12 md:col-span-6 lg:col-span-4')

// Full on mobile/tablet, half on desktop
->cols('col-span-12 lg:col-span-6')

// Always full width
->cols('col-span-12')

// Full on mobile, 1/3 on tablet+
->cols('col-span-12 md:col-span-4')
```

---

## Complete Examples

### Example 1: Simple Two-Column Form

```php
public function fields(): array
{
    return [
        Section::make('User Information')
            ->fields([
                Text::make('First Name')->cols('col-span-12 md:col-span-6'),
                Text::make('Last Name')->cols('col-span-12 md:col-span-6'),
                Email::make('Email')->cols('col-span-12'),
                Text::make('Phone')->cols('col-span-12 md:col-span-6'),
                Date::make('Birth Date')->cols('col-span-12 md:col-span-6'),
            ]),
    ];
}
```

**Result:**
- **Mobile:** All fields stack vertically (full width)
- **Tablet+:** First Name + Last Name side-by-side, Email full width, Phone + Birth Date side-by-side

---

### Example 2: Multi-Section Form

```php
public function fields(): array
{
    return [
        Section::make('Personal Information')
            ->description('Basic user details')
            ->icon('user')
            ->fields([
                Text::make('First Name')->cols('col-span-12 md:col-span-6'),
                Text::make('Last Name')->cols('col-span-12 md:col-span-6'),
                Email::make('Email')->cols('col-span-12'),
                Text::make('Phone')->cols('col-span-12 md:col-span-6'),
                Date::make('Birth Date')->cols('col-span-12 md:col-span-6'),
            ]),

        Section::make('Address')
            ->collapsible()
            ->fields([
                Text::make('Street Address')->cols('col-span-12'),
                Text::make('Apartment/Unit')->cols('col-span-12 md:col-span-4'),
                Text::make('City')->cols('col-span-12 md:col-span-6 lg:col-span-4'),
                Text::make('State')->cols('col-span-12 md:col-span-3 lg:col-span-2'),
                Text::make('Zip')->cols('col-span-12 md:col-span-3 lg:col-span-2'),
            ]),

        Section::make('Account Settings')
            ->icon('settings')
            ->fields([
                Select::make('Status')->cols('col-span-12 md:col-span-6'),
                Password::make('Password')->cols('col-span-12 md:col-span-6'),
                BelongsToMany::make('Roles')->cols('col-span-12'),
            ]),
    ];
}
```

---

### Example 3: Complex Asymmetric Layout

```php
Section::make('Product Details')
    ->fields([
        // Main content (2/3 width on desktop)
        Textarea::make('Description')
            ->cols('col-span-12 lg:col-span-8'),

        // Sidebar (1/3 width on desktop)
        Select::make('Status')
            ->cols('col-span-12 lg:col-span-4'),

        // Three columns on desktop
        Number::make('Price')
            ->cols('col-span-12 md:col-span-4'),
        Number::make('Cost')
            ->cols('col-span-12 md:col-span-4'),
        Number::make('Stock')
            ->cols('col-span-12 md:col-span-4'),
    ])
```

---

### Example 4: Large Form with 15+ Fields

```php
Section::make('Complete Profile')
    ->description('Fill in all your details')
    ->collapsible()
    ->gap('gap-6')
    ->fields([
        // Name (3 columns on desktop)
        Text::make('First Name')->cols('col-span-12 md:col-span-4'),
        Text::make('Middle Name')->cols('col-span-12 md:col-span-4'),
        Text::make('Last Name')->cols('col-span-12 md:col-span-4'),

        // Contact (2 columns)
        Email::make('Primary Email')->cols('col-span-12 md:col-span-6'),
        Email::make('Secondary Email')->cols('col-span-12 md:col-span-6'),

        Text::make('Phone')->cols('col-span-12 md:col-span-6'),
        Text::make('Mobile')->cols('col-span-12 md:col-span-6'),

        // Address
        Text::make('Street Address')->cols('col-span-12'),
        Text::make('Apartment')->cols('col-span-12 md:col-span-4'),
        Text::make('City')->cols('col-span-12 md:col-span-4'),
        Text::make('State')->cols('col-span-12 md:col-span-2'),
        Text::make('Zip')->cols('col-span-12 md:col-span-2'),

        // Personal
        Date::make('Birth Date')->cols('col-span-12 md:col-span-6'),
        Select::make('Gender')->cols('col-span-12 md:col-span-6'),

        // Bio
        Textarea::make('Biography')->cols('col-span-12'),
    ])
```

---

### Example 5: Using Groups

```php
Section::make('Contact Information')
    ->fields([
        // Primary contact group
        Group::make([
            Email::make('Primary Email')->cols('col-span-12 md:col-span-6'),
            Text::make('Primary Phone')->cols('col-span-12 md:col-span-6'),
        ])->label('Primary Contact'),

        // Secondary contact group
        Group::make([
            Email::make('Secondary Email')->cols('col-span-12 md:col-span-6'),
            Text::make('Secondary Phone')->cols('col-span-12 md:col-span-6'),
        ])->label('Secondary Contact'),

        // Social media group
        Group::make([
            Text::make('LinkedIn')->cols('col-span-12 md:col-span-6'),
            Text::make('Twitter')->cols('col-span-12 md:col-span-6'),
        ])->label('Social Media'),
    ])
```

---

## Mobile-First Approach

### Best Practices

1. **Always start with mobile** - Default to `col-span-12`
2. **Add breakpoints progressively** - Add `md:`, `lg:` as needed
3. **Test on mobile first** - Ensure forms work on small screens
4. **Use collapsible sections** - Save vertical space on mobile

### Mobile Optimizations

```php
Section::make('Advanced Settings')
    ->collapsible()              // Let users collapse on mobile
    ->collapsed(true)            // Start collapsed
    ->fields([
        // Complex fields that take up space
    ])
```

### Responsive Pattern Examples

```php
// Stack on mobile, 2-column on tablet+
Text::make('Field')->cols('col-span-12 md:col-span-6')

// Stack on mobile, 2-col on tablet, 3-col on desktop
Text::make('Field')->cols('col-span-12 md:col-span-6 lg:col-span-4')

// Stack on mobile and tablet, 2-col on desktop only
Text::make('Field')->cols('col-span-12 lg:col-span-6')

// Always full width on all devices
Text::make('Field')->cols('col-span-12')
```

---

## Common Layout Patterns

### Two-Column Form
```php
Text::make('Left Field')->cols('col-span-12 md:col-span-6'),
Text::make('Right Field')->cols('col-span-12 md:col-span-6'),
```

### Three-Column Form
```php
Text::make('Field 1')->cols('col-span-12 lg:col-span-4'),
Text::make('Field 2')->cols('col-span-12 lg:col-span-4'),
Text::make('Field 3')->cols('col-span-12 lg:col-span-4'),
```

### Main + Sidebar (2:1 ratio)
```php
Textarea::make('Content')->cols('col-span-12 lg:col-span-8'),
Select::make('Category')->cols('col-span-12 lg:col-span-4'),
```

### City, State, Zip Pattern
```php
Text::make('City')->cols('col-span-12 md:col-span-6'),
Text::make('State')->cols('col-span-12 md:col-span-3'),
Text::make('Zip')->cols('col-span-12 md:col-span-3'),
```

### Full-Half-Quarter Pattern
```php
Text::make('Street')->cols('col-span-12'),
Text::make('City')->cols('col-span-12 md:col-span-6'),
Text::make('State')->cols('col-span-12 md:col-span-3'),
Text::make('Zip')->cols('col-span-12 md:col-span-3'),
```

---

## Implementation Plan

### Backend Changes

#### 1. Create `Section.php`

Location: `app/Resources/Fields/Section.php`

```php
<?php

namespace App\Resources\Fields;

class Section
{
    public string $title;
    public ?string $description = null;
    public ?string $icon = null;
    public bool $collapsible = false;
    public bool $collapsed = false;
    public string $cols = 'col-span-12';
    public string $gap = 'gap-4';
    public ?string $containerClasses = null;
    public array $fields = [];

    public function __construct(string $title)
    {
        $this->title = $title;
    }

    public static function make(string $title): static
    {
        return new static($title);
    }

    public function description(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function icon(string $icon): static
    {
        $this->icon = $icon;
        return $this;
    }

    public function collapsible(bool $collapsible = true): static
    {
        $this->collapsible = $collapsible;
        return $this;
    }

    public function collapsed(bool $collapsed = true): static
    {
        $this->collapsed = $collapsed;
        return $this;
    }

    public function cols(string $cols): static
    {
        $this->cols = $cols;
        return $this;
    }

    public function gap(string $gap): static
    {
        $this->gap = $gap;
        return $this;
    }

    public function containerClasses(string $classes): static
    {
        $this->containerClasses = $classes;
        return $this;
    }

    public function fields(array $fields): static
    {
        $this->fields = $fields;
        return $this;
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
            'fields' => array_map(fn($field) => $field->toArray(), $this->fields),
        ];
    }
}
```

#### 2. Create `Group.php`

Location: `app/Resources/Fields/Group.php`

```php
<?php

namespace App\Resources\Fields;

class Group
{
    public ?string $label = null;
    public string $cols = 'col-span-12';
    public string $gap = 'gap-4';
    public array $fields = [];

    public function __construct(array $fields)
    {
        $this->fields = $fields;
    }

    public static function make(array $fields): static
    {
        return new static($fields);
    }

    public function label(string $label): static
    {
        $this->label = $label;
        return $this;
    }

    public function cols(string $cols): static
    {
        $this->cols = $cols;
        return $this;
    }

    public function gap(string $gap): static
    {
        $this->gap = $gap;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'type' => 'group',
            'label' => $this->label,
            'cols' => $this->cols,
            'gap' => $this->gap,
            'fields' => array_map(fn($field) => $field->toArray(), $this->fields),
        ];
    }
}
```

#### 3. Update `Field.php`

Add these methods to `app/Resources/Fields/Field.php`:

```php
protected string $cols = 'col-span-12';
protected ?string $containerClasses = null;

public function cols(string $cols): static
{
    $this->cols = $cols;
    return $this;
}

public function containerClasses(string $classes): static
{
    $this->containerClasses = $classes;
    return $this;
}

// Update toArray() to include these properties
public function toArray(): array
{
    return [
        // ... existing properties
        'cols' => $this->cols,
        'containerClasses' => $this->containerClasses,
    ];
}
```

#### 4. Update `Resource.php`

Add method to flatten nested Section/Group structures for validation:

```php
public function flattenFields(): array
{
    $flattened = [];

    foreach ($this->fields() as $item) {
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
```

### Frontend Changes

#### 5. Update `ResourceForm.vue`

Replace the simple field loop with Section-aware rendering:

```vue
<template>
  <div class="resource-form bg-transparent">
    <!-- Form Header -->
    <div class="mb-6">
      <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
        {{ itemId ? 'Edit' : 'Create' }} {{ meta?.singularLabel || 'Record' }}
      </h2>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600" />
    </div>

    <!-- Form with Sections -->
    <form v-else-if="meta" @submit.prevent="handleSubmit" class="space-y-6">
      <!-- Render sections or fields -->
      <div class="grid grid-cols-12 gap-4 md:gap-6">
        <template v-for="(item, index) in formStructure" :key="index">
          <!-- Section -->
          <div v-if="item.type === 'section'" :class="item.cols || 'col-span-12'">
            <div
              :class="[
                'space-y-4',
                item.containerClasses || 'bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700'
              ]"
            >
              <!-- Section Header -->
              <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-3">
                <div class="flex items-center space-x-2">
                  <Icon v-if="item.icon" :name="item.icon" :size="20" class="text-gray-500 dark:text-gray-400" />
                  <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                      {{ item.title }}
                    </h3>
                    <p v-if="item.description" class="text-sm text-gray-500 dark:text-gray-400">
                      {{ item.description }}
                    </p>
                  </div>
                </div>
                <button
                  v-if="item.collapsible"
                  type="button"
                  @click="toggleSection(index)"
                  class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                >
                  <Icon :name="sectionCollapsed[index] ? 'chevron-down' : 'chevron-up'" :size="20" />
                </button>
              </div>

              <!-- Section Fields -->
              <div v-show="!sectionCollapsed[index]" :class="['grid grid-cols-12', item.gap || 'gap-4']">
                <div
                  v-for="field in item.fields"
                  :key="field.attribute"
                  :class="field.cols || 'col-span-12'"
                >
                  <!-- Render field (reuse existing field rendering logic) -->
                  <FieldRenderer :field="field" :form-data="formData" :errors="errors" />
                </div>
              </div>
            </div>
          </div>

          <!-- Regular field (no section) -->
          <div v-else :class="item.cols || 'col-span-12'">
            <FieldRenderer :field="item" :form-data="formData" :errors="errors" />
          </div>
        </template>
      </div>

      <!-- Form Actions -->
      <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
        <button type="button" @click="$emit('cancel')" class="btn-secondary">
          Cancel
        </button>
        <button type="submit" :disabled="submitting" class="btn-primary">
          <div v-if="submitting" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white" />
          <span>{{ itemId ? 'Update' : 'Create' }}</span>
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const sectionCollapsed = ref({})

const formStructure = computed(() => {
  // Meta.fields can now contain Section objects or regular fields
  return meta.value?.fields || []
})

function toggleSection(index) {
  sectionCollapsed.value[index] = !sectionCollapsed.value[index]
}
</script>
```

---

## Migration Guide

### Converting Existing Resources

**Before (flat field list):**
```php
public function fields(): array
{
    return [
        ID::make()->sortable(),
        Text::make('Name')->sortable(),
        Email::make('Email')->sortable(),
        Select::make('Status'),
        BelongsToMany::make('Roles'),
    ];
}
```

**After (with sections):**
```php
public function fields(): array
{
    return [
        Section::make('User Details')
            ->fields([
                ID::make()->sortable(),
                Text::make('Name')
                    ->cols('col-span-12 md:col-span-6')
                    ->sortable(),
                Email::make('Email')
                    ->cols('col-span-12 md:col-span-6')
                    ->sortable(),
            ]),

        Section::make('Account Settings')
            ->collapsible()
            ->fields([
                Select::make('Status')->cols('col-span-12 md:col-span-6'),
                BelongsToMany::make('Roles')->cols('col-span-12'),
            ]),
    ];
}
```

### Backwards Compatibility

Fields without sections will still work - they'll render in a simple grid as before. This enhancement is **opt-in**.

---

## Summary

This form layout system provides:

✅ **Backend control** - Define layouts in Resource classes
✅ **Tailwind-native** - Use familiar CSS classes
✅ **Mobile-first** - Responsive by default
✅ **Flexible** - Mix sections, groups, and fields
✅ **Zero mapping** - Classes pass through unchanged
✅ **Backwards compatible** - Existing resources still work

Start using sections and responsive columns to create professional, mobile-friendly forms directly from your Resource definitions!
