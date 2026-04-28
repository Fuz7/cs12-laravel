<?php

use App\Http\Controllers\CustomerController;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {

  Route::get('/customers', [CustomerController::class, 'getPaginatedCustomers']);
  Route::get('/customers/all', [CustomerController::class, 'getCustomers']);

  Route::get("/customers/analytics/getNewCustomers", [CustomerController::class, 'getNewCustomers']);
  Route::get('/customers/{id}', [CustomerController::class, 'getCustomerById']);
  Route::get('/customers/{id}/search', [CustomerController::class, 'getCustomerIfExist']);
  Route::get('/customers/filter/unlink', [CustomerController::class, 'getUnlinkedCustomer']);
  Route::post('/customers', [CustomerController::class, 'store']);
  Route::patch('/customers/{id}', [CustomerController::class, 'update']);
  Route::delete('/customers/{id}', [CustomerController::class, 'delete']);
  Route::delete('/customers', [CustomerController::class, 'deleteByBatch']);

});
