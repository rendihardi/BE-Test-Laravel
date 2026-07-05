<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StockTransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/me', [AuthController::class, 'me'])->name('me');

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
    });
});
