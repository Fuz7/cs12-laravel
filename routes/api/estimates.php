<?php

use App\Http\Controllers\EstimateController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {

  Route::get('/estimates', [EstimateController::class, 'getPaginatedEstimate']);
});
