<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Product extends Model implements Auditable
{
    use HasFactory, HasUuids, SoftDeletes, AuditableTrait;

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
        })->when(isset($filters['stock_status']), function ($q) use ($filters) {
            $status = $filters['stock_status'];
            if ($status === 'out of stock' || $status === 'out_of_stock') {
                $q->where('current_stock', 0);
            } elseif ($status === 'low stock' || $status === 'low_stock') {
                $q->whereBetween('current_stock', [1, 14]);
            } elseif ($status === 'in stock' || $status === 'in_stock') {
                $q->where('current_stock', '>=', 15);
            }
        });
    }

    public function scopeSort($query, ?string $sortBy, ?string $sortOrder)
    {
        $allowedSorts = ['product_name', 'product_code', 'price', 'current_stock', 'created_at'];
        $sort = in_array($sortBy, $allowedSorts) ? $sortBy : 'created_at';
        $order = strtolower($sortOrder) === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($sort, $order);
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->current_stock <= 0) {
            return 'out of stock';
        }
        if ($this->current_stock < 15) {
            return 'low stock';
        }
        return 'in stock';
    }
}
