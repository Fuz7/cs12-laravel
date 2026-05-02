<?php

use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {

  Route::get('/invoices', [InvoiceController::class, 'getPaginatedInvoice']);
  Route::get('/invoices/analytics/getLastMonthRevenue', [InvoiceController::class, 'getLastMonthRevenue']);
  Route::get('/invoices/{customerId}', [InvoiceController::class, 'getInvoicesById']);
  Route::get('/invoices/user/{userId}', [InvoiceController::class, 'getInvoicesByUserId']);
  Route::post('/invoices/{customerId}', [InvoiceController::class, 'store']);
  Route::patch('/invoices/{invoiceId}', [InvoiceController::class, 'update']);
  // Make Sure path var are lower down to not match
  Route::delete('/invoices/{id}', [InvoiceController::class, 'delete']);
  Route::delete('/invoices', [InvoiceController::class, 'deleteByBatch']);
  Route::get("/invoices/analytics/getChartInvoiceRevenue", [InvoiceController::class, 'getChartInvoiceRevenue']);

});
