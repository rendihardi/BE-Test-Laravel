<?php

namespace App\Helpers;

use App\Models\Product;

class ProductCodeHelper
{
    /**
     * Generate product code in format: BRG[dd][mm][yyyy][0001]
     * Example: BRG040720260001
     */
    public static function generate(): string
    {
        $prefix = 'BRG' . date('dmY');

        $latestProduct = Product::where('product_code', 'like', $prefix . '%')
            ->withTrashed()
            ->orderBy('product_code', 'desc')
            ->first();

        if ($latestProduct) {
            $sequence = substr($latestProduct->product_code, strlen($prefix));
            $nextSequence = (int)$sequence + 1;
        } else {
            $nextSequence = 1;
        }

        $paddedSequence = str_pad($nextSequence, 4, '0', STR_PAD_LEFT);

        return $prefix . $paddedSequence;
    }
}
