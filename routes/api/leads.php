<?php

use App\Http\Controllers\LeadController;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {

  Route::get('/leads', [LeadController::class, 'getPaginatedLead']);
  Route::get('/leads/{id}', [LeadController::class, 'getCustomerById']);
  Route::post('/leads/{id}', [LeadController::class, 'store']);
  Route::patch('/leads/{id}', [LeadController::class, 'update']);
  Route::delete('/leads/{id}', [LeadController::class, 'delete']);
  Route::delete('/leads', [LeadController::class, 'deleteByBatch']);
});
