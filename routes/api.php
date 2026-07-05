<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StockTransactionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExcelController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/me', [AuthController::class, 'me'])->name('me');
    Route::get('/dashboard/cards', [DashboardController::class, 'cards']);
    Route::get('/dashboard/pie-chart', [DashboardController::class, 'pieChart']);
    Route::get('/dashboard/low-stock', [DashboardController::class, 'lowStock']);
    Route::get('/dashboard/stock-chart', [DashboardController::class, 'stockChart']);
    Route::get('/dashboard/recent-transactions', [DashboardController::class, 'recentTransactions']);

    // CRUD Roles - Dapat diakses oleh role apa pun setelah login
    Route::get('/roles/paginated', [RoleController::class, 'getAllPaginated']);
    Route::apiResource('/roles', RoleController::class);

    // CRUD Categories - Dapat diakses oleh role apa pun setelah login
    Route::get('/categories/paginated', [CategoryController::class, 'getAllPaginated']);
    Route::apiResource('/categories', CategoryController::class);

    // CRUD Products - Dapat diakses oleh role apa pun setelah login
    Route::get('/products/paginated', [ProductController::class, 'getAllPaginated']);
    Route::apiResource('/products', ProductController::class);

    // Stock Transactions - Dapat diakses oleh role apa pun setelah login
    Route::get('/stock-transactions/paginated', [StockTransactionController::class, 'getAllPaginated']);
    Route::apiResource('/stock-transactions', StockTransactionController::class)->only(['index', 'show', 'store']);
    Route::post('/products/{id}/stock-in', [StockTransactionController::class, 'stockIn']);
    Route::post('/products/{id}/stock-out', [StockTransactionController::class, 'stockOut']);
    Route::post('/products/{id}/adjust-stock', [StockTransactionController::class, 'adjustStock']);

    // CRUD Users - Hanya dapat diakses oleh role "Administrator" setelah login
    Route::middleware('role:Administrator')->group(function () {
        Route::get('/users/paginated', [UserController::class, 'getAllPaginated']);
        Route::apiResource('/users', UserController::class);

        // Audit Trails
        Route::get('/audits/users', [AuditController::class, 'userAudits']);
        Route::get('/audits/roles', [AuditController::class, 'roleAudits']);
        Route::get('/audits/categories', [AuditController::class, 'categoryAudits']);
        Route::get('/audits/products', [AuditController::class, 'productAudits']);
    });

    // Excel Import & Export
    Route::post('/excel/export/products', [ExcelController::class, 'exportProducts']);
    Route::post('/excel/export/stock-in', [ExcelController::class, 'exportStockIn']);
    Route::post('/excel/export/stock-out', [ExcelController::class, 'exportStockOut']);
    Route::post('/excel/export/adjustments', [ExcelController::class, 'exportAdjustments']);
    Route::post('/excel/export/stock-transactions', [ExcelController::class, 'exportStockTransactions']);
    Route::post('/excel/import', [ExcelController::class, 'import']);
    Route::get('/excel/templates/product', [ExcelController::class, 'downloadProductTemplate']);
    Route::get('/excel/jobs', [ExcelController::class, 'listJobs']);
    Route::get('/excel/jobs/{id}', [ExcelController::class, 'showJob']);
});
