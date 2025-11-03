<?php

namespace App\Http\Requests;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Check if user is authenticated
        if (! $this->user()) {
            return false;
        }

        $key = $this->route('key') ?? $this->input('key');
        $setting = Setting::where('key', $key)->first();

        if (! $setting) {
            return true; // Allow creating new settings
        }

        // Check if setting is public or if user is admin
        if (! $setting->is_public && ! $this->user()->isAdmin()) {
            return false;
        }

        // Check scope-based permissions
        if ($setting->scope === 'admin' && ! $this->user()->isAdmin()) {
            return false;
        }

        return true;
    }

    public function rules(): array
    {
        $key = $this->route('key') ?? $this->input('key');
        $setting = Setting::where('key', $key)->first();

        $rules = [
            'key' => ['sometimes', 'required', 'string', 'max:255'],
            'value' => ['nullable'],
        ];

        // Apply custom validation rules from database if they exist
        if ($setting && $setting->validation_rules) {
            // Convert string rules to array if needed
            $validationRules = $setting->validation_rules;
            if (is_string($validationRules)) {
                // Try JSON decode first
                $decoded = json_decode($validationRules, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $validationRules = $decoded;
                } else {
                    // Fall back to pipe-separated format
                    $validationRules = explode('|', $validationRules);
                }
            }
            $rules['value'] = is_array($validationRules) ? $validationRules : [$validationRules];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'key.required' => 'Setting key is required.',
            'key.string' => 'Setting key must be a string.',
            'key.max' => 'Setting key must not exceed 255 characters.',
            'value.required' => 'Setting value is required.',
        ];
    }
}
