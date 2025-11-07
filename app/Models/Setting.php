<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'scope',
        'icon',
        'label',
        'description',
        'is_public',
        'is_encrypted',
        'validation_rules',
        'settable_type',
        'settable_id',
        'referenceable_type',
        'referenceable_id',
        'order',
    ];

    protected function casts(): array
    {
        return [
            // Don't cast value - we handle JSON encoding/decoding in SettingsService
            'is_public' => 'boolean',
            'is_encrypted' => 'boolean',
            'validation_rules' => 'array',
            'order' => 'integer',
        ];
    }

    public function owner(): MorphTo
    {
        return $this->morphTo('settable');
    }

    public function referenceable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeGlobal($query)
    {
        return $query->where('scope', 'global')->whereNull('settable_type');
    }

    public function scopeForUser($query, User $user)
    {
        return $query->where('scope', 'user')
            ->where('settable_type', User::class)
            ->where('settable_id', $user->id);
    }

    public function scopeByGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function getTypedValue(): mixed
    {
        $rawValue = $this->value;

        if ($this->is_encrypted && $rawValue) {
            try {
                $rawValue = Crypt::decryptString($rawValue);
            } catch (\Exception $e) {
                return null;
            }
        }

        // If type is not set, try to parse as JSON for backward compatibility
        if (! $this->type) {
            if (is_string($rawValue)) {
                $decoded = json_decode($rawValue, true);

                return $decoded !== null ? $decoded : $rawValue;
            }

            return $rawValue;
        }

        // Parse based on type
        return match ($this->type) {
            'string' => (string) $rawValue,
            'integer' => (int) $rawValue,
            'boolean' => $this->parseBoolean($rawValue),
            'array', 'json' => is_string($rawValue) ? json_decode($rawValue, true) : $rawValue,
            'reference' => $rawValue,
            default => $rawValue,
        };
    }

    /**
     * Parse boolean from string representation
     */
    protected function parseBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            return in_array(strtolower($value), ['true', '1', 'yes', 'on']);
        }

        return (bool) $value;
    }

    public function setTypedValue(mixed $value): void
    {
        $processedValue = match ($this->type) {
            'string' => (string) $value,
            'integer' => (int) $value,
            'boolean' => (bool) $value,
            'array', 'json' => is_array($value) ? $value : json_decode($value, true),
            'reference' => $value,
            default => $value,
        };

        if ($this->is_encrypted) {
            $processedValue = Crypt::encryptString($processedValue);
        }

        $this->value = $processedValue;
    }
}
