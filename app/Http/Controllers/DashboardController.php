<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\StockTransaction;
use App\Http\Resources\ProductResource;
use App\Http\Resources\StockTransactionResource;
use App\Helpers\ResponseHelper;

class DashboardController extends Controller
{
    /**
     * Get summary cards data.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function cards()
    {
        try {
            $totalProducts = Product::count();
            $totalCategories = Category::count();
            $currentStock = (int) Product::sum('current_stock');
            $todayTransactions = StockTransaction::whereDate('transaction_date', today())->count();

            $data = [
                'total_products' => $totalProducts,
                'total_categories' => $totalCategories,
                'current_stock' => $currentStock,
                'today_transactions' => $todayTransactions,
            ];

            return ResponseHelper::jsonResponse(true, 'Dashboard Cards Data', $data, 200);
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }

    /**
     * Get pie chart data for product distribution per category.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function pieChart()
    {
        try {
            $data = Category::withCount('products')
                ->get()
                ->map(function ($category) {
                    return [
                        'category_id' => $category->id,
                        'category_name' => $category->name,
                        'product_count' => $category->products_count,
                    ];
                });

            return ResponseHelper::jsonResponse(true, 'Dashboard Pie Chart Data', $data, 200);
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }

    /**
     * Get top 5 low stock products (< 15).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function lowStock()
    {
        try {
            $lowStockProducts = Product::with('category')
                ->where('current_stock', '<', 15)
                ->orderBy('current_stock', 'asc')
                ->limit(5)
                ->get();

            return ResponseHelper::jsonResponse(
                true,
                'Dashboard Low Stock Products Data',
                ProductResource::collection($lowStockProducts),
                200
            );
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }

    /**
     * Get bar chart data for stock in vs stock out per month (last 12 months).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function stockChart()
    {
        try {
            $startDate = now()->subMonths(11)->startOfMonth();
            $endDate = now()->endOfMonth();

            $transactions = StockTransaction::whereBetween('transaction_date', [$startDate, $endDate])
                ->whereIn('type', ['IN', 'OUT'])
                ->selectRaw("DATE_FORMAT(transaction_date, '%Y-%m') as month, type, SUM(quantity) as total_qty")
                ->groupBy('month', 'type')
                ->orderBy('month', 'asc')
                ->get();

            $data = [];
            for ($i = 11; $i >= 0; $i--) {
                $monthKey = now()->subMonths($i)->format('Y-m');
                $monthLabel = now()->subMonths($i)->format('F Y');
                $data[$monthKey] = [
                    'month' => $monthKey,
                    'label' => $monthLabel,
                    'stock_in' => 0,
                    'stock_out' => 0,
                ];
            }

            foreach ($transactions as $tx) {
                if (isset($data[$tx->month])) {
                    if ($tx->type === 'IN') {
                        $data[$tx->month]['stock_in'] = (int) $tx->total_qty;
                    } elseif ($tx->type === 'OUT') {
                        $data[$tx->month]['stock_out'] = (int) $tx->total_qty;
                    }
                }
            }

            return ResponseHelper::jsonResponse(true, 'Dashboard Stock Chart Data', array_values($data), 200);
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }

    /**
     * Get top 5 recent stock transactions.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function recentTransactions()
    {
        try {
            $recent = StockTransaction::with(['product', 'creator'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            return ResponseHelper::jsonResponse(
                true,
                'Dashboard Recent Transactions Data',
                StockTransactionResource::collection($recent),
                200
            );
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }
}
