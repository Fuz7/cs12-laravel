<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {

  Route::get('/users', [UserController::class, 'getPaginatedUsers']);
  Route::patch("/users/{customerId}/link/{userId}", [UserController::class, 'linkUserToCustomer']);
  Route::patch('/users/{userId}/unlink', [UserController::class, 'unlinkUser']);
  Route::delete('/users/{id}', [UserController::class, 'delete']);
  Route::delete('/users', [UserController::class, 'deleteByBatch']);
  });
