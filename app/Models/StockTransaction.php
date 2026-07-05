<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class StockTransaction extends Model implements Auditable
{
    use HasFactory, HasUuids, SoftDeletes, AuditableTrait;

    protected $fillable = [
        'product_id',
        'trx_code',
        'type',
        'qty',
        'stock_before',
        'stock_after',
        'transaction_date',
        'product_name',
        'image',
        'category_name',
        'price',
        'reference_document',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'qty' => 'integer',
        'stock_before' => 'integer',
        'stock_after' => 'integer',
        'transaction_date' => 'datetime',
        'price' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeSearch($query, ?string $search)
    {
        return $query->when($search, function ($q) use ($search) {
            $q->where(function ($sq) use ($search) {
                $sq->where('trx_code', 'like', "%{$search}%")
                  ->orWhere('product_name', 'like', "%{$search}%")
                  ->orWhere('category_name', 'like', "%{$search}%")
                  ->orWhere('reference_document', 'like', "%{$search}%");
            });
        });
    }

    public function scopeFilter($query, array $filters)
    {
        return $query->when(isset($filters['product_id']), function ($q) use ($filters) {
            $q->where('product_id', $filters['product_id']);
        })->when(isset($filters['type']), function ($q) use ($filters) {
            $q->where('type', $filters['type']);
        })->when(isset($filters['created_by']), function ($q) use ($filters) {
            $q->where('created_by', $filters['created_by']);
        })->when(isset($filters['start_date']), function ($q) use ($filters) {
            $q->whereDate('transaction_date', '>=', $filters['start_date']);
        })->when(isset($filters['end_date']), function ($q) use ($filters) {
            $q->whereDate('transaction_date', '<=', $filters['end_date']);
        });
    }

    public function scopeSort($query, ?string $sortBy, ?string $sortOrder)
    {
        $allowedSorts = ['trx_code', 'qty', 'transaction_date', 'price', 'created_at'];
        $sort = in_array($sortBy, $allowedSorts) ? $sortBy : 'created_at';
        $order = strtolower($sortOrder) === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($sort, $order);
    }
}
