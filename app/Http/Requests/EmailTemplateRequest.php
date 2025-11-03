<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmailTemplateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $templateId = $this->route('template')?->id;

        // For store/upsert: check if template exists by key
        if (! $templateId && $this->isMethod('POST')) {
            $existingTemplate = \App\Models\EmailTemplate::query()
                ->where('key', $this->input('key'))
                ->first();
            $templateId = $existingTemplate?->id;
        }

        return [
            'key' => [
                'required',
                'string',
                'max:255',
                'unique:email_templates,key,'.$templateId,
            ],
            'name' => 'required|string|max:255',
            'subject_template' => ['required', 'string', new \App\Rules\ValidBladeTemplate],
            'body_content' => ['required', 'string', new \App\Rules\ValidBladeTemplate],
            'is_active' => 'sometimes|boolean',
        ];
    }
}
