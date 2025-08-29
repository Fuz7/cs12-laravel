<?php

use App\Http\Controllers\EstimateController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {

  Route::get('/estimates', [EstimateController::class, 'getPaginatedEstimate']);
  Route::post('/estimates/{customerId}', [EstimateController::class, 'store']);
  Route::patch('/estimates/{estimateId}/approve', [EstimateController::class, 'approveEstimate']);
  // Make Sure path var are lower down to not match
  Route::patch('/estimates/{customerId}/{estimateId}', [EstimateController::class, 'update']);
  Route::delete('/estimates/{id}', [EstimateController::class, 'delete']);
  Route::delete('/estimates', [EstimateController::class, 'deleteByBatch']);
});
