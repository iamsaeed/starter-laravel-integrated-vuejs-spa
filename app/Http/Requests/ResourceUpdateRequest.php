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
                    // Replace unique:table,column with unique:table,column,{id}
                    $rules[$field] = preg_replace(
                        '/unique:([^,|]+),([^,|]+)/',
                        'unique:$1,$2,'.$this->route('id'),
                        $fieldRules
                    );
                }
            }
        }

        return $rules;
    }
}
