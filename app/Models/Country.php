<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'code_alpha3',
        'numeric_code',
        'name',
        'native_name',
        'capital',
        'region',
        'subregion',
        'currency_code',
        'currency_name',
        'currency_symbol',
        'phone_code',
        'flag_emoji',
        'flag_svg',
        'languages',
        'tld',
        'latitude',
        'longitude',
        'is_active',
        'is_eu_member',
        'display_order',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'native_name' => 'array',
            'languages' => 'array',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_active' => 'boolean',
            'is_eu_member' => 'boolean',
            'display_order' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function timezones(): BelongsToMany
    {
        return $this->belongsToMany(Timezone::class, 'country_timezone')
            ->withPivot(['is_primary', 'regions', 'notes'])
            ->withTimestamps();
    }

    public function primaryTimezone(): BelongsToMany
    {
        return $this->timezones()->wherePivot('is_primary', true);
    }

    public function settings(): MorphMany
    {
        return $this->morphMany(Setting::class, 'referenceable');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRegion($query, string $region)
    {
        return $query->where('region', $region);
    }

    public function scopePopular($query)
    {
        return $query->orderBy('display_order');
    }

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => ($this->flag_emoji ? $this->flag_emoji.' ' : '').$this->name
        );
    }

    public function hasTimezone(int $timezoneId): bool
    {
        return $this->timezones()->where('timezone_id', $timezoneId)->exists();
    }
}
