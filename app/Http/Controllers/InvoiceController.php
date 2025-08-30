<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Estimate;
use App\Models\Invoice;
use App\Models\Job;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{

  public function getPaginatedInvoice(Request $request)
  {
    $page = $request->get('page', 1);
    $perPage = $request->get('perPage', 10);
    $sortBy = $request->get('sortBy', 'created_at');      // default sort column
    $sortOrder = $request->get('sortOrder', 'desc');
    $search = $request->get('searchTerm', "");
    $query = Invoice::with(
      ['customer:id,first_name,last_name,email,property_address'],
    )->with(['tasks']);


    if ($search) {
      $query->where(function ($q) use ($search) {
        $q->where('job_name', 'ilike', "%{$search}%")
          ->orWhere('status', 'ilike', "%{$search}%")
          ->orWhereHas('customer', function ($q2) use ($search) {
            $q2->where('id', 'like', "%{$search}%")
              ->orWhere('first_name', 'ilike', "%{$search}%")
              ->orWhere('last_name', 'ilike', "%{$search}%")
              ->orWhere('email', 'ilike', "%{$search}%");
          });
      });
    }
    if ($search && is_numeric($search)) {
      $query->orWhere('tasks_total_price', 'ilike', "%{$search}%");
      $query->orWhere('paid_amount', "ilike", "%{$search}%");
    };

    if ($sortBy === 'customer_first_name') {
      $query->join('customers', 'invoices.customer_id', '=', 'customers.id')
        ->orderBy('customers.first_name', $sortOrder)
        ->select('invoices.*');
    } else if ($sortBy === 'status') {
      $query->orderByRaw("
        CASE
            WHEN status != 'paid' AND due_date < NOW() THEN 'overdue'
            ELSE status
        END {$sortOrder}
    ");
    } else {
      $query->orderBy($sortBy, $sortOrder);
    }

    $invoices = $query->paginate($perPage, ['*'], 'page', $page);

    // To Make Sure Overdue is used if the time is already due
    $invoices->getCollection()->transform(function ($invoice) {
      if ($invoice->status !== 'paid' && $invoice->due_date < now()) {
        $invoice->status = 'overdue';
      }
      return $invoice;
    });
    return response()->json($invoices);
  }
}
