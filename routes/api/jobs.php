<?php

use App\Http\Controllers\JobController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {

  Route::get('/jobs', [JobController::class, 'getPaginatedJob']);
  Route::post('/jobs/{customerId}', [JobController::class, 'store']);
  Route::patch('/jobs/{estimateId}', [JobController::class, 'update']);
  Route::delete('/jobs/{id}', [JobController::class, 'delete']);
  Route::delete('/jobs', [JobController::class, 'deleteByBatch']);
});
