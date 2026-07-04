<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'category_id',
        'product_code',
        'product_name',
        'image',
        'price',
        'current_stock',
        'attributes',
        'specification_pdf',
        'is_active',
    ];

    protected $casts = [
        'attributes' => 'array',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'current_stock' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function stockTransactions(): HasMany
    {
        return $this->hasMany(StockTransaction::class);
    }

    public function scopeSearch($query, ?string $search)
    {
        return $query->when($search, function ($q) use ($search) {
            $q->where(function ($sq) use ($search) {
                $sq->where('product_name', 'like', "%{$search}%")
                    ->orWhere('product_code', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%");
                    });
            });
        });
    }

    public function scopeFilter($query, array $filters)
    {
        return $query->when(isset($filters['category_id']), function ($q) use ($filters) {
            $q->where('category_id', $filters['category_id']);
        })->when(isset($filters['is_active']), function ($q) use ($filters) {
            $q->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        })->when(isset($filters['min_price']), function ($q) use ($filters) {
            $q->where('price', '>=', $filters['min_price']);
        })->when(isset($filters['max_price']), function ($q) use ($filters) {
            $q->where('price', '<=', $filters['max_price']);
        });
    }

    public function scopeSort($query, ?string $sortBy, ?string $sortOrder)
    {
        $allowedSorts = ['product_name', 'product_code', 'price', 'current_stock', 'created_at'];
        $sort = in_array($sortBy, $allowedSorts) ? $sortBy : 'created_at';
        $order = strtolower($sortOrder) === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($sort, $order);
    }
}
