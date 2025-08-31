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

  public function store(Request $request, $customerId)
  {
    $invoice = DB::transaction(function () use ($request, $customerId) {
      $validated = $request->validate([
        'job_name' => 'required|string',
        'tasks'   => 'required|array',
        'site_address' => 'nullable|string',
        "paid_amount" => 'required|numeric',
        "due_date" => "required|date",
        'tasks.*.description' => 'required|string',
        'tasks.*.price'       => 'required|numeric',
        'status'   => 'required|string',
        'notes'    => 'nullable|string',
      ]);

      $validated['notes'] = $validated['notes'] ?? '';
      $validated['site_address'] = $validated['site_address'] ?? '';

      $invoice = Invoice::create([
        'customer_id' => $customerId,
        'job_name'    => $validated['job_name'],
        'site_address' => $validated['site_address'],
        'due_date' => $validated['due_date'],
        'paid_amount' => $validated['paid_amount'],
        'status'      => $validated['status'],
        'notes'       => $validated['notes'],
      ]);

      $invoice->tasks()->createMany($validated['tasks']);

      return $invoice->load('tasks');
    });

    return response()->json($invoice);
  }

  public function update(Request $request, $invoiceId)
  {
    $invoice = DB::transaction(function () use ($request, $invoiceId) {
      $validated = $request->validate([
        'job_name' => 'required|string',
        'status'   => 'required|string',
        'due_date' => "required|date",
        'paid_amount' => 'required|numeric',
        'notes'    => 'nullable|string',
        'site_address' => 'nullable|string',
        // tasks validation
        'tasks'                 => 'required|array',
        'tasks.*.id'            => 'sometimes|integer|exists:tasks,id',
        'tasks.*.description'   => 'required|string|max:255',
        'tasks.*.price'         => 'required|numeric|min:0',

        'deletedIds'   => 'sometimes|array',
        'deletedIds.*' => 'integer|exists:tasks,id',
      ]);

      $validated['notes'] = $validated['notes'] ?? '';
      $validated['site_address'] = $validated['site_address'] ?? '';

      $invoice = Invoice::where('id', $invoiceId)
        ->firstOrFail();
      $invoice->update([
        'job_name' => $validated['job_name'],
        'site_address' => $validated['site_address'],
        'due_date' => $validated['due_date'],
        'paid_amount' => $validated['paid_amount'],
        'status'   => $validated['status'],
        'notes'    => $validated['notes'],
      ]);

      $newTasks = [];
      $existingTasks = [];

      foreach ($validated['tasks'] as $taskData) {
        if (isset($taskData['id'])) {
          $existingTasks[] = $taskData;
        } else {
          $newTasks[] = [
            'description' => $taskData['description'],
            'price' => $taskData['price'],

          ];
        }
      }
      if (!empty($newTasks)) {
        $invoice->tasks()->createMany($newTasks);
      }
      foreach ($existingTasks as $taskData) {
        $task = Task::find($taskData['id']);
        $task->update([
          'description' => $taskData['description'],
          'price' => $taskData['price'],
        ]);
      }
      if (!empty($validated['deletedIds'])) {
        $invoice->tasks()->whereIn('id', $validated['deletedIds'])->delete();
        // Doesn't Get Called On Batch Delete
        $invoice->recalculateTasksTotals();
      }


      return $invoice->load('tasks');
    });

    return response()->json($invoice);
  }

  public function delete(Request $request, $id)
  {
    $estimate = Invoice::findOrFail($id);



    $estimate->delete();

    return response()->json();
  }

  public function deleteByBatch(Request $request,)
  {
    $ids = $request->input('ids', []); // expects: [1,2,3,...]

    if (empty($ids)) {
      return response()->json([
        'status' => 'error',
        'message' => 'No IDs provided'
      ], 400);
    }

    Invoice::whereIn('id', $ids)->delete();

    return response()->json();
  }
}
