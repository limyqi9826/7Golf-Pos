<?php

use App\Http\Controllers\CustomerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\POSController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CardController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// items
Route::get('/items', [POSController::class, 'items']);
Route::post('/items', [POSController::class, 'store']);
Route::put('/items/{id}', [POSController::class, 'update']);
Route::delete('/items/{id}', [POSController::class, 'delete']);

// transactions
Route::get('/transactions', [POSController::class, 'transactions']);
Route::post('/transactions', [POSController::class, 'storeTransaction']);
Route::get('/reports/details/{id}', [ReportController::class, 'transactionitems']);
Route::delete('/reports/{id}', [ReportController::class, 'deleteTransaction']);

// customers
Route::get('/customers', [CustomerController::class, 'customers']);
Route::post('/customers', [CustomerController::class,'store']);
Route::put('/customers/{id}', [CustomerController::class, 'update']);
Route::delete('/customers/{id}', [CustomerController::class, 'delete']);

// users
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::put('/users/{id}', [UserController::class, 'update'])->middleware('auth:sanctum');

// card
Route::get('/card-payment/{transactionId}', [CardController::class, 'show']);
Route::post('/card-payment', [CardController::class, 'store']);
