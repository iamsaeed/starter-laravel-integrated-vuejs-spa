<?php

namespace App\Http\Requests;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Check if user is authenticated
        if (! $this->user()) {
            return false;
        }

        // Check each setting for permissions
        $settings = $this->input('settings', []);

        foreach ($settings as $key => $value) {
            $setting = Setting::where('key', $key)->first();

            if ($setting) {
                // Check if setting is public or if user is admin
                if (! $setting->is_public && ! $this->user()->isAdmin()) {
                    return false;
                }

                // Check scope-based permissions
                if ($setting->scope === 'admin' && ! $this->user()->isAdmin()) {
                    return false;
                }
            }
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'settings' => ['required', 'array'],
            'settings.*' => ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'settings.required' => 'Settings data is required.',
            'settings.array' => 'Settings must be an array.',
            'settings.*.required' => 'Each setting must have a value.',
        ];
    }
}
