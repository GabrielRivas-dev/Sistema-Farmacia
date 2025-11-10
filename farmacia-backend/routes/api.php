<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductoController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;

// Rutas públicas
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/checkout', [OrderController::class, 'store']);
  

// Rutas de productos (públicas)
Route::get('/productos', [ProductoController::class, 'index']);
Route::get('/productos/{id}', [ProductoController::class, 'show']);
Route::get('/productos-categorias', [ProductoController::class, 'categorias']);
Route::get('/productos-laboratorios', [ProductoController::class, 'laboratorios']);

Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);


// Rutas protegidas - SOLO UN middleware
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    
    // Nuevas rutas para el perfil
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    
    // Aquí puedes agregar más rutas protegidas en el futuro
});

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard-stats', [AdminController::class, 'dashboardStats']);
    
    // User management
    Route::get('/users', [AdminController::class, 'getUsers']);
    Route::put('/users/{id}', [AdminController::class, 'updateUser']);
    
    // Product management
    Route::get('/products', [AdminController::class, 'getProducts']);
    Route::post('/products', [AdminController::class, 'createProduct']);
    Route::put('/products/{id}', [AdminController::class, 'updateProduct']);
    Route::delete('/products/{id}', [AdminController::class, 'deleteProduct']);
    
    // Order management
    Route::get('/orders', [AdminController::class, 'getOrders']);
    Route::put('/orders/{id}/status', [AdminController::class, 'updateOrderStatus']);
});