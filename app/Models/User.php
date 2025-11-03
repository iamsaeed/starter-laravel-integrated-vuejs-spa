<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class User extends Authenticatable implements HasMedia
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, InteractsWithMedia, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'can_access_admin_panel',
        'is_admin',
        'is_user',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => Status::class,
            'is_admin' => 'boolean',
            'is_user' => 'boolean',
            'can_access_admin_panel' => 'boolean',
        ];
    }

    /**
     * Send the password reset notification using database template.
     */
    public function sendPasswordResetNotification($token): void
    {
        $resetUrl = config('app.url').'/auth/reset-password?token='.$token.'&email='.urlencode($this->email);

        $this->notify(new \App\Notifications\TemplatedNotification('password_reset', [
            'reset_url' => $resetUrl,
            'token' => $token,
        ]));
    }

    public function settings(): MorphMany
    {
        return $this->morphMany(Setting::class, 'settable');
    }

    public function getSetting(string $key, mixed $default = null): mixed
    {
        $setting = $this->settings()->where('key', $key)->first();

        return $setting ? $setting->getTypedValue() : $default;
    }

    public function setSetting(string $key, mixed $value): void
    {
        $setting = $this->settings()->where('key', $key)->first();
        if ($setting) {
            $setting->setTypedValue($value);
            $setting->save();
        }
    }

    public function getSettingsByGroup(string $group): \Illuminate\Database\Eloquent\Collection
    {
        return $this->settings()->where('group', $group)->get();
    }

    public function llmConfigs(): HasMany
    {
        return $this->hasMany(UserLlmConfig::class);
    }

    public function llmConversations(): HasMany
    {
        return $this->hasMany(LlmConversation::class);
    }

    /**
     * Get the roles assigned to the user (user can only have one role).
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user')
            ->withTimestamps();
    }

    /**
     * Get the user's role (since user can only have one role).
     */
    public function role(): ?Role
    {
        return $this->roles()->first();
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $roleSlug): bool
    {
        return $this->roles()->where('slug', $roleSlug)->exists();
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user is a regular user (not admin).
     */
    public function isUser(): bool
    {
        return $this->hasRole('user');
    }

    /**
     * Check if user can access admin panel.
     * Requires both admin role AND user ID to be in the whitelist.
     */
    public function canAccessAdminPanel(): bool
    {
        $allowedAdminIds = config('admin.id', []);

        return $this->isAdmin() && in_array($this->id, $allowedAdminIds);
    }

    /**
     * Get the can_access_admin_panel attribute.
     */
    public function getCanAccessAdminPanelAttribute(): bool
    {
        return $this->canAccessAdminPanel();
    }

    /**
     * Get the is_admin attribute.
     */
    public function getIsAdminAttribute(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Get the is_user attribute.
     */
    public function getIsUserAttribute(): bool
    {
        return $this->isUser();
    }

    /**
     * Assign a role to the user (replaces existing role).
     */
    public function assignRole(int|string $role): void
    {
        // Detach existing role first (user can only have one role)
        $this->roles()->detach();

        if (is_string($role)) {
            $role = Role::where('slug', $role)->firstOrFail();
        } elseif (is_int($role)) {
            $role = Role::findOrFail($role);
        }

        $this->roles()->attach($role->id);
    }

    /**
     * Check if user is active.
     */
    public function isActive(): bool
    {
        return $this->status === Status::Active;
    }

    /**
     * Check if user is inactive.
     */
    public function isInactive(): bool
    {
        return $this->status === Status::Inactive;
    }

    /**
     * Activate the user.
     */
    public function activate(): void
    {
        $this->update(['status' => Status::Active]);
    }

    /**
     * Deactivate the user.
     */
    public function deactivate(): void
    {
        $this->update(['status' => Status::Inactive]);
    }



    /**
     * Register media collections.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatars')
            ->singleFile()
            ->registerMediaConversions(function () {
                $this->addMediaConversion('thumb')
                    ->width(48)
                    ->height(48)
                    ->sharpen(10);

                $this->addMediaConversion('medium')
                    ->width(200)
                    ->height(200)
                    ->sharpen(10);
            });
    }
}
