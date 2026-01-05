<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasUlid;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static ProductFactory factory()
 */
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;
    use HasUlid;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'price',
        'description',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    /**
     * Get the wishlist items for the product.
     *
     * @return HasMany<Wishlist, $this>
     */
    public function wishlistItems(): HasMany
    {
        return $this->hasMany(Wishlist::class, 'product_id');
    }
}
