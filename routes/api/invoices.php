<?php

use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {

  Route::get('/invoices', [InvoiceController::class, 'getPaginatedInvoice']);
  Route::post('/invoices/{customerId}', [InvoiceController::class, 'store']);
  Route::patch('/invoices/{estimateId}/approve', [InvoiceController::class, 'approveEstimate']);
  // Make Sure path var are lower down to not match
  Route::delete('/invoices/{id}', [InvoiceController::class, 'delete']);
  Route::delete('/invoices', [InvoiceController::class, 'deleteByBatch']);
});

