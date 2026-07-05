<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;
use App\Helpers\ProductCodeHelper;

class ProductImport implements ToCollection, WithHeadingRow
{
    /**
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Check for minimum required fields: product_name
            $productName = $row['product_name'] ?? $row['name'] ?? null;
            if (!$productName) {
                continue;
            }

            // Resolve Category
            $categoryName = $row['category_name'] ?? $row['category'] ?? null;
            $categoryId = $row['category_id'] ?? null;
            $category = null;

            if ($categoryId) {
                $category = Category::find($categoryId);
            }

            if (!$category && $categoryName) {
                // Find or create category dynamically
                $category = Category::firstOrCreate(
                    ['name' => trim($categoryName)],
                    ['description' => 'Imported via excel', 'is_active' => true]
                );
            }

            // If still no category, resolve to first active or create a default one
            if (!$category) {
                $category = Category::firstOrCreate(
                    ['name' => 'General'],
                    ['description' => 'Default category for imports', 'is_active' => true]
                );
            }

            // Resolve Product Code
            $productCode = $row['product_code'] ?? $row['code'] ?? null;
            if (!$productCode) {
                $productCode = ProductCodeHelper::generate();
            }

            // Price validation and parsing
            $priceVal = $row['price'] ?? 0;
            $price = is_numeric($priceVal) ? (float) $priceVal : 0.0;

            // Current stock parsing
            $stockVal = $row['current_stock'] ?? $row['stock'] ?? 0;
            $currentStock = is_numeric($stockVal) ? (int) $stockVal : 0;

            // Active status parsing
            $activeVal = $row['is_active'] ?? $row['active'] ?? 'yes';
            $isActive = filter_var($activeVal, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true;
            if (is_string($activeVal)) {
                $lowerVal = strtolower(trim($activeVal));
                if ($lowerVal === 'no' || $lowerVal === 'false' || $lowerVal === '0') {
                    $isActive = false;
                }
            }

            // Attributes (JSON)
            $attributes = null;
            $attrsVal = $row['attributes'] ?? $row['specs'] ?? null;
            if ($attrsVal) {
                if (is_array($attrsVal)) {
                    $attributes = $attrsVal;
                } elseif (is_string($attrsVal)) {
                    $decoded = json_decode($attrsVal, true);
                    $attributes = is_array($decoded) ? $decoded : ['info' => $attrsVal];
                }
            }

            // Find existing product by code, or create new one
            Product::updateOrCreate(
                ['product_code' => $productCode],
                [
                    'category_id' => $category->id,
                    'product_name' => trim($productName),
                    'price' => $price,
                    'current_stock' => $currentStock,
                    'is_active' => $isActive,
                    'attributes' => $attributes,
                ]
            );
        }
    }
}
