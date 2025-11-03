<?php

namespace App\Resources;

use App\Resources\Fields\Date;
use App\Resources\Fields\ID;
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
     * Get the fields shown in the index/table view.
     * ID and Created At are automatically added.
     */
    abstract public function indexFields(): array;

    /**
     * Get the fields shown in the detail/show view.
     * ID, Created At, and Updated At are automatically added.
     */
    abstract public function showFields(): array;

    /**
     * Get the fields shown in create/edit forms.
     * No fields are automatically added.
     */
    abstract public function formFields(): array;

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
     * Get index fields with auto-included fields (ID and Created At).
     */
    public function getIndexFields(): array
    {
        return array_merge(
            [ID::make()->sortable()],
            $this->indexFields(),
            [Date::make('Created At')->sortable()]
        );
    }

    /**
     * Get show fields with auto-included fields (ID, Created At, Updated At).
     */
    public function getShowFields(): array
    {
        return array_merge(
            [ID::make()],
            $this->showFields(),
            [
                Date::make('Created At'),
                Date::make('Updated At'),
            ]
        );
    }

    /**
     * Get form fields (no auto-included fields).
     */
    public function getFormFields(): array
    {
        return $this->formFields();
    }

    /**
     * Flatten nested Section/Group structures into a flat array of fields.
     */
    public function flattenFields(array $fields): array
    {
        $flattened = [];

        foreach ($fields as $item) {
            if ($item instanceof Fields\Section) {
                $flattened = array_merge($flattened, $this->flattenSectionFields($item));
            } elseif ($item instanceof Fields\Group) {
                $flattened = array_merge($flattened, $item->fields);
            } else {
                $flattened[] = $item;
            }
        }

        return $flattened;
    }

    /**
     * Flatten fields within a section.
     */
    protected function flattenSectionFields(Fields\Section $section): array
    {
        $fields = [];

        foreach ($section->fields as $item) {
            if ($item instanceof Fields\Group) {
                $fields = array_merge($fields, $item->fields);
            } else {
                $fields[] = $item;
            }
        }

        return $fields;
    }

    /**
     * Get all form fields with conditional visibility evaluated.
     */
    public function getVisibleFields(array $formData): array
    {
        return collect($this->flattenFields($this->getFormFields()))
            ->filter(fn ($field) => $field->isVisible($formData))
            ->all();
    }

    /**
     * Get validation rules from form fields.
     */
    public function rules(string $context = 'create'): array
    {
        $rules = [];
        $formFields = $this->flattenFields($this->getFormFields());

        foreach ($formFields as $field) {
            if ($field->rules) {
                $rules[$field->attribute] = $field->rules;
            }
        }

        return $rules;
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [];
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

    /**
     * Transform model data for response.
     */
    public function transform(Model $model): array
    {
        $data = $model->toArray();

        // Use index fields for transformation
        $fields = $this->flattenFields($this->getIndexFields());

        foreach ($fields as $field) {
            if (method_exists($field, 'transformValue')) {
                $data[$field->attribute] = $field->transformValue($model->{$field->attribute}, $model);
            }
        }

        return $data;
    }
}
