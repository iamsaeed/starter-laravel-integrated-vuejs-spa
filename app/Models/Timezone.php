<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Timezone extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'abbreviation',
        'abbreviation_dst',
        'offset',
        'offset_dst',
        'offset_formatted',
        'uses_dst',
        'display_name',
        'city_name',
        'region',
        'coordinates',
        'population',
        'is_primary',
        'is_active',
        'display_order',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'offset' => 'integer',
            'offset_dst' => 'integer',
            'uses_dst' => 'boolean',
            'coordinates' => 'array',
            'population' => 'integer',
            'is_primary' => 'boolean',
            'is_active' => 'boolean',
            'display_order' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function countries(): BelongsToMany
    {
        return $this->belongsToMany(Country::class, 'country_timezone')
            ->withPivot(['is_primary', 'regions', 'notes'])
            ->withTimestamps();
    }

    public function primaryCountry(): BelongsToMany
    {
        return $this->countries()->wherePivot('is_primary', true);
    }

    public function settings(): MorphMany
    {
        return $this->morphMany(Setting::class, 'referenceable');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCountry($query, int $countryId)
    {
        return $query->whereHas('countries', function ($q) use ($countryId) {
            $q->where('countries.id', $countryId);
        });
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public function scopeByRegion($query, string $region)
    {
        return $query->where('region', $region);
    }

    protected function currentOffset(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->uses_dst) {
                    return $this->offset;
                }

                $isDst = $this->isCurrentlyDst();

                return $isDst ? $this->offset_dst : $this->offset;
            }
        );
    }

    protected function currentAbbreviation(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->uses_dst) {
                    return $this->abbreviation;
                }

                $isDst = $this->isCurrentlyDst();

                return $isDst ? $this->abbreviation_dst : $this->abbreviation;
            }
        );
    }

    protected function displayLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => "{$this->display_name} ({$this->offset_formatted})"
        );
    }

    public function isCurrentlyDst(): bool
    {
        if (! $this->uses_dst) {
            return false;
        }

        try {
            $now = Carbon::now($this->name);

            return $now->isDST();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getOffsetInHours(): float
    {
        return $this->offset / 3600;
    }
}
