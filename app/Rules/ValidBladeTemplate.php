<?php

namespace App\Rules;

use App\Exceptions\BladeSecurityException;
use App\Exceptions\BladeSyntaxException;
use App\Services\BladeTemplateSecurityService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidBladeTemplate implements ValidationRule
{
    protected string $message = '';

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            app(BladeTemplateSecurityService::class)->validate($value);
        } catch (BladeSecurityException|BladeSyntaxException $e) {
            $fail($e->getMessage());
        }
    }
}
