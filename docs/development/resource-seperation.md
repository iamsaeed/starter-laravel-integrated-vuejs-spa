# Resource Fields Separation

## Overview

This document describes the Resource system architecture that uses separate, dedicated methods for different contexts: table display, detail view, and forms.

## Core Concept

Resources now use **three dedicated methods** for field definitions:

1. **`indexFields()`** - Fields shown in the resource table/list view
2. **`showFields()`** - Fields shown in the detail/show view
3. **`formFields()`** - Fields shown in create/edit forms

## Method Structure

### 1. indexFields() - Table Display

Returns fields for the resource index/table view.

**Auto-included fields:**
- `ID` field (sortable)
- `Created At` timestamp (sortable)

You only need to define the fields between ID and timestamps.

```php
public function indexFields(): array
{
    return [
        Text::make('Name')->sortable(),
        Email::make('Email')->sortable(),
        Select::make('Status')->sortable(),
    ];
}
```

**Results in table columns:**
1. ID (auto-added)
2. Name
3. Email
4. Status
5. Created At (auto-added)

### 2. showFields() - Detail View

Returns fields for the resource detail/show page.

**Auto-included fields:**
- `ID` field
- `Created At` timestamp
- `Updated At` timestamp

```php
public function showFields(): array
{
    return [
        Text::make('Name'),
        Email::make('Email'),
        Textarea::make('Bio'),
        Select::make('Status'),
        BelongsToMany::make('Roles'),
    ];
}
```

**Results in detail view:**
1. ID (auto-added)
2. Name
3. Email
4. Bio
5. Status
6. Roles
7. Created At (auto-added)
8. Updated At (auto-added)

### 3. formFields() - Create/Edit Forms

Returns fields for create and edit forms. Supports Sections and Groups for advanced layouts.

```php
public function formFields(): array
{
    return [
        Section::make('Profile Information')
            ->description('Basic user details')
            ->icon('user')
            ->fields([
                Text::make('Name')->cols('col-span-12 md:col-span-6'),
                Email::make('Email')->cols('col-span-12 md:col-span-6'),
                Textarea::make('Bio')->cols('col-span-12'),
            ]),

        Section::make('Security')
            ->icon('lock')
            ->fields([
                Password::make('Password')->cols('col-span-12 md:col-span-6'),
                Select::make('Status')->cols('col-span-12 md:col-span-6'),
            ]),
    ];
}
```

## Benefits

### 1. Clear Separation of Concerns
Each context has its own dedicated method:
- **Index** - Summary columns for quick scanning
- **Show** - Comprehensive view with all details
- **Form** - Organized sections with validation

### 2. No Verbose Flags
❌ **Before:** `->exceptOnForm()`, `->onlyOnIndex()`, `->hideFromDetail()`
✅ **Now:** Just put fields in the appropriate method

### 3. Auto-included Common Fields
No need to repeat ID and timestamps in every resource:
```php
// ❌ Before (repetitive)
ID::make()->sortable()->exceptOnForm(),
Date::make('Created At')->sortable()->exceptOnForm(),
Date::make('Updated At')->sortable()->exceptOnForm(),

// ✅ Now (automatic)
// These are added automatically!
```

### 4. Clean, Readable Code
```php
// Table columns - simple list
public function indexFields(): array
{
    return [
        Text::make('Name')->sortable(),
        Email::make('Email')->sortable(),
    ];
}

// Form layout - rich sections
public function formFields(): array
{
    return [
        Section::make('Profile')->fields([...]),
        Section::make('Security')->fields([...]),
    ];
}
```

## Complete Example

```php
<?php

namespace App\Resources;

use App\Enums\Status;
use App\Models\User;
use App\Resources\Fields\{BelongsToMany, Email, Media, Password, Section, Select, Text, Textarea};

class UserResource extends Resource
{
    public static string $model = User::class;
    public static string $label = 'Users';
    public static string $singularLabel = 'User';
    public static string $title = 'name';
    public static array $search = ['name', 'email'];

    /**
     * Table display - ID and Created At are auto-added.
     */
    public function indexFields(): array
    {
        return [
            Media::make('Avatar'),
            Text::make('Name')->sortable()->searchable(),
            Email::make('Email')->sortable()->searchable(),
            Select::make('Status')->sortable()->toggleable(true, 'active', 'inactive'),
        ];
    }

    /**
     * Detail view - ID, Created At, and Updated At are auto-added.
     */
    public function showFields(): array
    {
        return [
            Media::make('Avatar'),
            Text::make('Name'),
            Email::make('Email'),
            Textarea::make('Bio'),
            Select::make('Status'),
            BelongsToMany::make('Roles'),
            Date::make('Email Verified At'),
        ];
    }

    /**
     * Create/edit form - organized with sections.
     */
    public function formFields(): array
    {
        return [
            Section::make('Profile Information')
                ->description('Basic user profile details')
                ->icon('user')
                ->fields([
                    Media::make('Avatar')
                        ->single()
                        ->collection('avatars')
                        ->images()
                        ->rounded()
                        ->cols('col-span-12'),

                    Text::make('Name')
                        ->rules('required|string|max:255')
                        ->cols('col-span-12 md:col-span-6'),

                    Email::make('Email')
                        ->rules('required|email|unique:users,email')
                        ->cols('col-span-12 md:col-span-6'),

                    Textarea::make('Bio')
                        ->rules('nullable|string|max:500')
                        ->cols('col-span-12'),
                ]),

            Section::make('Security')
                ->description('Password and account status')
                ->icon('lock')
                ->fields([
                    Password::make('Password')
                        ->rules('required|string|min:8')
                        ->creationRules('required|string|min:8')
                        ->updateRules('nullable|string|min:8')
                        ->cols('col-span-12 md:col-span-6'),

                    Select::make('Status')
                        ->options(Status::class)
                        ->rules(['required', 'in:active,inactive'])
                        ->default(Status::Active->value)
                        ->cols('col-span-12 md:col-span-6'),
                ]),

            Section::make('Permissions')
                ->description('User roles and access control')
                ->icon('shield')
                ->collapsible()
                ->fields([
                    BelongsToMany::make('Roles')
                        ->resource(RoleResource::class)
                        ->titleAttribute('name')
                        ->creatable()
                        ->cols('col-span-12'),
                ]),
        ];
    }

    public function filters(): array
    {
        return [
            SelectFilter::make('Status')
                ->options(Status::class)
                ->column('status'),
        ];
    }

    public function actions(): array
    {
        return [
            BulkDeleteAction::make(),
            BulkUpdateAction::make()->fields(['status' => Status::class]),
        ];
    }

    public function with(): array
    {
        return ['roles'];
    }
}
```

## Auto-included Fields Reference

### indexFields() Auto-includes:

```php
// Automatically prepended
ID::make()->sortable()

// Automatically appended
Date::make('Created At')->sortable()
```

### showFields() Auto-includes:

```php
// Automatically prepended
ID::make()

// Automatically appended
Date::make('Created At')
Date::make('Updated At')
```

### formFields() Auto-includes:

None. You have full control over form layout.

## Common Patterns

### Simple CRUD Resource

```php
public function indexFields(): array
{
    return [
        Text::make('Name')->sortable(),
        Text::make('Description'),
    ];
}

public function showFields(): array
{
    return [
        Text::make('Name'),
        Textarea::make('Description'),
    ];
}

public function formFields(): array
{
    return [
        Text::make('Name')->cols('col-span-12'),
        Textarea::make('Description')->cols('col-span-12'),
    ];
}
```

### Resource with Sections

```php
public function formFields(): array
{
    return [
        Section::make('Basic Information')
            ->fields([
                Text::make('Title')->cols('col-span-12'),
                Textarea::make('Description')->cols('col-span-12'),
            ]),

        Section::make('Advanced Settings')
            ->collapsible()
            ->collapsed()
            ->fields([
                Select::make('Category')->cols('col-span-12 md:col-span-6'),
                Number::make('Priority')->cols('col-span-12 md:col-span-6'),
            ]),
    ];
}
```

### Two-Column Form Layout

```php
public function formFields(): array
{
    return [
        Section::make('Product Details')->fields([
            Text::make('Name')->cols('col-span-12'),
            Number::make('Price')->cols('col-span-12 md:col-span-6'),
            Number::make('Stock')->cols('col-span-12 md:col-span-6'),
            Textarea::make('Description')->cols('col-span-12'),
        ]),
    ];
}
```

### Resource with Relationships

```php
public function indexFields(): array
{
    return [
        Text::make('Title')->sortable(),
        BelongsTo::make('Category')->sortable(),
    ];
}

public function showFields(): array
{
    return [
        Text::make('Title'),
        Textarea::make('Content'),
        BelongsTo::make('Category'),
        BelongsToMany::make('Tags'),
    ];
}

public function formFields(): array
{
    return [
        Section::make('Content')->fields([
            Text::make('Title')->cols('col-span-12'),
            Textarea::make('Content')->cols('col-span-12'),
        ]),

        Section::make('Taxonomy')->fields([
            BelongsTo::make('Category')
                ->resource(CategoryResource::class)
                ->creatable()
                ->cols('col-span-12 md:col-span-6'),

            BelongsToMany::make('Tags')
                ->resource(TagResource::class)
                ->creatable()
                ->cols('col-span-12 md:col-span-6'),
        ]),
    ];
}
```

## Responsive Grid System

Use Tailwind's 12-column grid for responsive layouts:

```php
// Full width on mobile, half on tablet+
->cols('col-span-12 md:col-span-6')

// Full on mobile, half on tablet, third on desktop
->cols('col-span-12 md:col-span-6 lg:col-span-4')

// Full on mobile/tablet, half on desktop+
->cols('col-span-12 lg:col-span-6')

// Always full width
->cols('col-span-12')

// 2:1 ratio (8 + 4 = 12)
->cols('col-span-12 lg:col-span-8')  // Main content
->cols('col-span-12 lg:col-span-4')  // Sidebar
```

## Section Features

### Basic Section

```php
Section::make('Title')
    ->fields([...])
```

### Section with Description

```php
Section::make('Profile')
    ->description('Enter your profile information')
    ->fields([...])
```

### Section with Icon

```php
Section::make('Security')
    ->icon('lock')
    ->fields([...])
```

### Collapsible Section

```php
Section::make('Advanced')
    ->collapsible()
    ->collapsed(true)  // Start collapsed
    ->fields([...])
```

### Custom Styling

```php
Section::make('Important')
    ->containerClasses('bg-yellow-50 dark:bg-yellow-900/20 border-2 border-yellow-200')
    ->fields([...])
```

### Custom Grid Gap

```php
Section::make('Compact')
    ->gap('gap-2')  // Smaller gap between fields
    ->fields([...])
```

## Field Layout Options

Every field supports layout customization:

```php
Text::make('Field')
    ->cols('col-span-12 md:col-span-6')           // Grid position
    ->containerClasses('bg-blue-50 p-4 rounded')  // Custom wrapper styling
```

## Validation

Validation rules are extracted from fields in `formFields()`:

```php
public function formFields(): array
{
    return [
        Section::make('User Info')->fields([
            Text::make('Name')
                ->rules('required|string|max:255'),
            Email::make('Email')
                ->rules('required|email|unique:users,email'),
        ]),
    ];
}

// Rules are automatically extracted:
// ['name' => 'required|string|max:255', 'email' => 'required|email|unique:users,email']
```

## Summary

This architecture provides:

✅ **Clear separation** - Dedicated methods for each context
✅ **Less code** - Auto-included ID and timestamps
✅ **No flags** - No need for `exceptOnForm()`, `onlyOnIndex()`
✅ **Flexible** - Different layouts per context
✅ **Clean** - Readable, maintainable code
✅ **Powerful** - Sections, groups, responsive grids

Define your resource fields in the right place, and the system handles the rest!
