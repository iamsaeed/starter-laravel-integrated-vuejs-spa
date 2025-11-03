<?php

namespace App\Services;

use App\Models\User;

class EmailVariableRegistry
{
    public function getVariablesForTemplate(string $key): array
    {
        return match ($key) {
            'user_welcome' => [
                'user' => [
                    'label' => 'User Object',
                    'type' => 'object',
                    'description' => 'The registered user',
                    'properties' => [
                        'name' => 'string - User full name',
                        'email' => 'string - User email address',
                        'created_at' => 'Carbon - Registration date',
                    ],
                    'example' => '{{ $user->name }}, {{ $user->email }}',
                ],
                'verification_url' => [
                    'label' => 'Email Verification URL',
                    'type' => 'string',
                    'example' => '<a href="{{ $verification_url }}">Verify Email</a>',
                ],
            ],
            'password_reset' => [
                'user' => [
                    'label' => 'User Object',
                    'type' => 'object',
                    'properties' => [
                        'name' => 'string - User full name',
                        'email' => 'string - User email address',
                    ],
                    'example' => '{{ $user->name }}',
                ],
                'reset_url' => [
                    'label' => 'Password Reset URL',
                    'type' => 'string',
                    'example' => '<a href="{{ $reset_url }}">Reset Password</a>',
                ],
            ],
            'forgot_password' => [
                'user' => [
                    'label' => 'User Object',
                    'type' => 'object',
                    'properties' => [
                        'name' => 'string - User full name',
                    ],
                    'example' => '{{ $user->name }}',
                ],
                'reset_url' => [
                    'label' => 'Password Reset URL',
                    'type' => 'string',
                    'example' => '<a href="{{ $reset_url }}">Reset Password</a>',
                ],
                'expires_in' => [
                    'label' => 'Expiration Time',
                    'type' => 'string',
                    'example' => '{{ $expires_in }}',
                ],
            ],
            default => []
        };
    }

    public function getSampleData(string $key): array
    {
        return match ($key) {
            'user_welcome' => [
                'user' => User::factory()->make([
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                ]),
                'verification_url' => url('/verify-email/1/sample-hash'),
            ],
            'password_reset' => [
                'user' => User::factory()->make([
                    'name' => 'Jane Smith',
                    'email' => 'jane@example.com',
                ]),
                'reset_url' => url('/reset-password/sample-token'),
            ],
            'forgot_password' => [
                'user' => User::factory()->make([
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                ]),
                'reset_url' => url('/reset-password/abc123xyz'),
                'expires_in' => '60 minutes',
            ],
            default => []
        };
    }
}
