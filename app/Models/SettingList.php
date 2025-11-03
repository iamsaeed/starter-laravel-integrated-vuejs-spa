<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class SettingList extends Model
{
    protected $fillable = [
        'key',
        'label',
        'value',
        'metadata',
        'is_active',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'is_active' => 'boolean',
            'order' => 'integer',
        ];
    }

    public function settings(): MorphMany
    {
        return $this->morphMany(Setting::class, 'referenceable');
    }

    public function scopeByKey($query, string $key)
    {
        return $query->where('key', $key);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
