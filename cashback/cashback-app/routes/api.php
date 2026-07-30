<?php

use App\Presentation\Http\Controllers\Api\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/transactions', [TransactionController::class, 'index']);
Route::post('/transactions', [TransactionController::class, 'store']);
Route::put('/transactions/{id}', [TransactionController::class, 'update']);
Route::delete('/transactions/{id}', [TransactionController::class, 'destroy']);

Route::get('/categories', [\App\Presentation\Http\Controllers\Api\CategoryController::class, 'index']);
Route::put('/categories/{id}', [\App\Presentation\Http\Controllers\Api\CategoryController::class, 'update']);

Route::get('/summary/{year}/{month}', [\App\Presentation\Http\Controllers\Api\CashbackController::class, 'summary']);
