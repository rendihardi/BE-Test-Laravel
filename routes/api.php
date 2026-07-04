<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/me', [AuthController::class, 'me'])->name('me');

    // CRUD Roles - Dapat diakses oleh role apa pun setelah login
    Route::get('/roles/paginated', [RoleController::class, 'getAllPaginated']);
    Route::apiResource('/roles', RoleController::class);

    // CRUD Users - Hanya dapat diakses oleh role "Administrator" setelah login
    Route::middleware('role:Administrator')->group(function () {
        Route::get('/users/paginated', [UserController::class, 'getAllPaginated']);
        Route::apiResource('/users', UserController::class);
    });
});
