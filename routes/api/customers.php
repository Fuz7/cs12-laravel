<?php

use App\Http\Controllers\CustomerController;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {

  Route::get('/customers', [CustomerController::class, 'getPaginatedCustomer']);
  Route::get('/customers/{id}', [CustomerController::class, 'getCustomerById']);
  Route::post('/customers/{id}', [CustomerController::class, 'store']);
  Route::patch('/customers/{id}', [CustomerController::class, 'update']);
  Route::delete('/customers/{id}', [CustomerController::class, 'delete']);
  Route::delete('/customers', [CustomerController::class, 'deleteByBatch']);
});
