<?php

use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
})->middleware('role:user');

require __DIR__.'/auth.php';
