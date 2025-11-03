<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResourceStoreRequest extends FormRequest
{
    protected $resourceClass;

    public function setResource(string $resourceClass): self
    {
        $this->resourceClass = $resourceClass;

        return $this;
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        if (! $this->resourceClass) {
            return [];
        }

        $resource = new $this->resourceClass;
        $fields = $resource->flattenFields($resource->getFormFields());
        $rules = [];
        $formData = $this->all();

        foreach ($fields as $field) {
            // Check if field is visible
            if (! $field->isVisible($formData)) {
                continue; // Skip validation for hidden fields
            }

            // Get base rules
            $fieldRules = $field->rules ?? [];

            // Add dynamic required rule
            if ($field->isRequired($formData)) {
                if (is_string($fieldRules)) {
                    if (! str_contains($fieldRules, 'required')) {
                        $fieldRules = 'required|'.$fieldRules;
                    }
                } elseif (is_array($fieldRules)) {
                    if (! in_array('required', $fieldRules)) {
                        array_unshift($fieldRules, 'required');
                    }
                }
            }

            if (! empty($fieldRules)) {
                $rules[$field->attribute] = $fieldRules;
            }
        }

        return $rules;
    }
}
