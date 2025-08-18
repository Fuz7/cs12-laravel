<?php

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function(){

  Route::get('/customers',function(Request $request){
    return Customer::get();
  });
});