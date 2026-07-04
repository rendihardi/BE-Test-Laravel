<?php

namespace App\Repositories;

use App\Interface\ProductInterface;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ProductRepository implements ProductInterface
{
    public function getAll(
        ?string $search,
        array $filters,
        ?string $sortBy,
        ?string $sortOrder,
        ?int $limit,
        ?bool $execute = false,
    ) {
        $query = Product::query()->with('category');

        if ($search) {
            $query->search($search);
        }

        $query->filter($filters);
        $query->sort($sortBy, $sortOrder);

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

    public function getById(?string $id): ?Product
    {
        return Product::where('id', $id)->with('category')->first();
    }

    public function create(array $data): Product
    {
        DB::beginTransaction();
        try {
            $productCode = $data['product_code'] ?? \App\Helpers\ProductCodeHelper::generate();

            $product = Product::create([
                'category_id' => $data['category_id'],
                'product_code' => $productCode,
                'product_name' => $data['product_name'],
                'image' => $data['image'] ?? null,
                'price' => $data['price'],
                'current_stock' => $data['current_stock'] ?? 0,
                'attributes' => $data['attributes'] ?? null,
                'specification_pdf' => $data['specification_pdf'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            DB::commit();
            return $product;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(array $data, ?string $id): ?Product
    {
        DB::beginTransaction();
        try {
            $product = $this->getById($id);
            if (!$product) {
                DB::rollBack();
                return null;
            }

            $productData = [];
            $fields = [
                'category_id', 'product_name', 'image',
                'price', 'current_stock', 'attributes', 'specification_pdf', 'is_active'
            ];

            foreach ($fields as $field) {
                if (array_key_exists($field, $data)) {
                    $productData[$field] = $data[$field];
                }
            }

            $product->update($productData);

            DB::commit();
            return $product;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete(?string $id): bool
    {
        DB::beginTransaction();
        try {
            $product = $this->getById($id);
            if (!$product) {
                DB::rollBack();
                return false;
            }

            $deleted = $product->delete();
            DB::commit();
            return $deleted;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
