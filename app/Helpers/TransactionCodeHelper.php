<?php

namespace App\Helpers;

use App\Models\StockTransaction;

class TransactionCodeHelper
{
    /**
     * Generate transaction code in format: TRX[dd][mm][yyyy][0001]
     * Example: TRX040720260001
     */
    public static function generate(): string
    {
        $prefix = 'TRX' . date('dmY');

        $latestTransaction = StockTransaction::where('trx_code', 'like', $prefix . '%')
            ->withTrashed()
            ->orderBy('trx_code', 'desc')
            ->first();

        if ($latestTransaction) {
            $sequence = substr($latestTransaction->trx_code, strlen($prefix));
            $nextSequence = (int)$sequence + 1;
        } else {
            $nextSequence = 1;
        }

        $paddedSequence = str_pad($nextSequence, 4, '0', STR_PAD_LEFT);

        return $prefix . $paddedSequence;
    }
}
