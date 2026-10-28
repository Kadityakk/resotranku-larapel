<?php

namespace App\Models;

use App\Enums\RoleName;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'username',
        'password',
        'fullname',
        'email',
        'phone',
        'role_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Roles are editable rows, so names are compared case-insensitively.
     */
    public function hasRole(RoleName|string ...$roles): bool
    {
        $roleName = Str::lower((string) $this->role?->role_name);

        if ($roleName === '') {
            return false;
        }

        foreach ($roles as $role) {
            $candidate = $role instanceof RoleName ? $role->value : $role;

            if ($roleName === Str::lower($candidate)) {
                return true;
            }
        }

        return false;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(RoleName::Admin);
    }

    public function isCashier(): bool
    {
        return $this->hasRole(RoleName::Cashier);
    }

    public function isChef(): bool
    {
        return $this->hasRole(RoleName::Chef);
    }

    public function isStaff(): bool
    {
        return $this->hasRole(...RoleName::staffValues());
    }

    /**
     * Built-in role this user maps onto, or null for a custom role.
     */
    public function roleName(): ?RoleName
    {
        return RoleName::tryFrom(Str::lower((string) $this->role?->role_name));
    }

    #[Scope]
    protected function staff(Builder $query): Builder
    {
        return $query->whereHas('role', fn (Builder $role) => $role->whereIn('role_name', RoleName::staffValues()));
    }
}
