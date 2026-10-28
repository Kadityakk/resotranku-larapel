<?php

namespace App\Models;

use Database\Factories\ItemFactory;
use Illuminate\Contracts\Filesystem\Cloud;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Scopes declared with #[Scope] receive their $query argument from Eloquent, so
 * they are advertised here for static analysis and editor completion.
 *
 * @method static Builder<static> active()
 */
class Item extends Model
{
    /** @use HasFactory<ItemFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'description', 'price', 'category_id', 'img', 'is_active'];

    protected $attributes = [
        'is_active' => true,
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'price' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Seeded items carry a remote URL while uploads are stored on the public disk,
     * so resolve whichever form the column holds.
     */
    public function imageUrl(): string
    {
        if (blank($this->img)) {
            return asset('assets/customer/img/placeholder.svg');
        }

        if (Str::startsWith($this->img, ['http://', 'https://'])) {
            return $this->img;
        }

        // Only a Cloud disk exposes url(); the Storage facade types disk() as the
        // narrower Filesystem contract, so narrow it back before asking for one.
        $disk = Storage::disk('public');

        return $disk instanceof Cloud
            ? $disk->url($this->img)
            : asset('storage/'.$this->img);
    }
}
