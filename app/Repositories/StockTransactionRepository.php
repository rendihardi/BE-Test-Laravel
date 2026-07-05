<?php

namespace App\Repositories;

use App\Interface\StockTransactionInterface;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Helpers\TransactionCodeHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StockTransactionRepository implements StockTransactionInterface
{
    public function getAll(
        ?string $search,
        array $filters,
        ?string $sortBy,
        ?string $sortOrder,
        ?int $limit,
        ?bool $execute = false,
    ) {
        $query = StockTransaction::query()
            ->with(['product', 'creator'])
            ->search($search)
            ->filter($filters)
            ->sort($sortBy, $sortOrder);

        if ($limit) {
            $query->limit($limit);
        }

        return $execute ? $query->get() : $query;
    }

    public function getAllPaginated(
        ?string $search,
        array $filters,
        ?string $sortBy,
        ?string $sortOrder,
        ?int $rowPerPage
    ) {
        $query = $this->getAll($search, $filters, $sortBy, $sortOrder, null, false);
        $perPage = $rowPerPage ?? 15;
        return $query->paginate($perPage);
    }

    public function getById(?string $id): ?StockTransaction
    {
        return StockTransaction::where('id', $id)->with(['product', 'creator'])->first();
    }

    public function create(array $data): StockTransaction
    {
        DB::beginTransaction();
        try {
            $product = Product::with('category')->lockForUpdate()->find($data['product_id']);
            if (!$product) {
                throw new \Exception('Product not found.');
            }

            $type = $data['type'];
            $qty = (int)$data['qty'];
            $stockBefore = (int)$product->current_stock;
            $stockAfter = $stockBefore;

            if ($type === 'IN') {
                $stockAfter = $stockBefore + $qty;
            } elseif ($type === 'OUT') {
                if ($stockBefore < $qty) {
                    throw new \Exception("Insufficient stock. Current stock is {$stockBefore}, cannot issue {$qty}.");
                }
                $stockAfter = $stockBefore - $qty;
            } elseif ($type === 'ADJUSTMTENT') {
                $stockAfter = $qty; // Sets stock directly
            } else {
                throw new \Exception('Invalid transaction type.');
            }

            // Generate transaction code
            $trxCode = TransactionCodeHelper::generate();

            // Create stock transaction record
            $transaction = StockTransaction::create([
                'product_id' => $product->id,
                'trx_code' => $trxCode,
                'type' => $type,
                'qty' => $qty,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'transaction_date' => now(),
                'product_name' => $product->product_name,
                'image' => $product->image,
                'category_name' => $product->category->name ?? 'No Category',
                'price' => $product->price,
                'reference_document' => $data['reference_document'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'created_by' => Auth::id() ?? $data['created_by'] ?? null,
            ]);

            // Update product stock
            $product->update([
                'current_stock' => $stockAfter
            ]);

            DB::commit();
            return $transaction;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
