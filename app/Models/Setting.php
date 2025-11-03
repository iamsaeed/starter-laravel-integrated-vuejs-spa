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
            'value' => 'array',
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

        // The value column is cast as array, so Laravel already JSON-decoded it
        // If it's a string, it means it was a simple JSON string value
        if (is_string($rawValue)) {
            $rawValue = json_decode($rawValue, true);
        }

        return match ($this->type) {
            'string' => is_array($rawValue) ? (string) current($rawValue) : (string) $rawValue,
            'integer' => is_array($rawValue) ? (int) current($rawValue) : (int) $rawValue,
            'boolean' => is_array($rawValue) ? (bool) current($rawValue) : (bool) $rawValue,
            'array', 'json' => is_array($rawValue) ? $rawValue : json_decode($rawValue, true),
            'reference' => $rawValue,
            default => $rawValue,
        };
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
