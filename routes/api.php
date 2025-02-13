<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\UserController;
Route::apiResource('products', ProductController::class);
Route::apiResource('customers', CustomerController::class);
Route::apiResource('orders', OrderController::class);   
Route::get('/orders/shipped-count', [OrderController::class, 'shippedCount']);
Route::get('/orders/pending-count', [OrderController::class, 'pendingCount']);
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/logout', [UserController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/limitedProducts', [ProductController::class, 'limitedProducts']);
Route::get('/filterByPrice', [ProductController::class, 'filteredProductsByPrice']);
Route::get('/filterByTime', [ProductController::class, 'filteredProductsByTime']);
Route::apiResource('messages', MessageController::class);
