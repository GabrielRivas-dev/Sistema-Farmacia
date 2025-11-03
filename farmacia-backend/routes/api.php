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