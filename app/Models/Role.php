<?php

namespace App\Models;

use App\Enums\RoleName;
use Database\Factories\RoleFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Scopes declared with #[Scope] receive their $query argument from Eloquent, so
 * they are advertised here for static analysis and editor completion.
 *
 * @method static Builder<static> named(RoleName $role)
 */
class Role extends Model
{
    /** @use HasFactory<RoleFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = ['role_name', 'description'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Matches a built-in role regardless of how its name was capitalised when
     * it was first created.
     */
    #[Scope]
    protected function named(Builder $query, RoleName $role): Builder
    {
        return $query->whereRaw('LOWER(role_name) = ?', [$role->value]);
    }

    /**
     * Built-in roles drive authorization, so they may not be renamed or deleted.
     */
    public function isBuiltIn(): bool
    {
        return RoleName::tryFrom(Str::lower($this->role_name)) !== null;
    }
}
