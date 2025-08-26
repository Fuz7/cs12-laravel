<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Estimate;
use App\Models\Lead;
use App\Models\Task;
use App\Models\User;
use GuzzleHttp\Promise\Create;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EstimateController extends Controller
{

  public function getPaginatedEstimate(Request $request)
  {
    $page = $request->get('page', 1);
    $perPage = $request->get('perPage', 10);
    $sortBy = $request->get('sortBy', 'created_at');      // default sort column
    $sortOrder = $request->get('sortOrder', 'desc');
    $search = $request->get('searchTerm', "");
    $query = Estimate::with(
      ['customer:id,first_name,last_name,email'],
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
    };

    if ($sortBy === 'customer_first_name') {
      $query->join('customers', 'estimates.customer_id', '=', 'customers.id')
        ->orderBy('customers.first_name', $sortOrder)
        ->select('estimates.*');
    } else {
      $query->orderBy($sortBy, $sortOrder);
    }

    $estimates = $query->paginate($perPage, ['*'], 'page', $page);

    return response()->json($estimates);
  }

  public function store(Request $request, $customerId)
  {
    $estimate = DB::transaction(function () use ($request, $customerId) {
      $validated = $request->validate([
        'job_name' => 'required|string',
        'tasks'   => 'required|array',
        'tasks.*.description' => 'required|string',
        'tasks.*.price'       => 'required|numeric',
        'status'   => 'required|string',
        'notes'    => 'nullable|string',
      ]);

      $validated['notes'] = $validated['notes'] ?? '';

      $estimate = Estimate::create([
        'customer_id' => $customerId,
        'job_name'    => $validated['job_name'],
        'status'      => $validated['status'],
        'notes'       => $validated['notes'],
      ]);

      $estimate->tasks()->createMany($validated['tasks']);

      return $estimate->load('tasks');
    });

    return response()->json($estimate);
  }

  public function update(Request $request, $customerId, $estimateId)
  {
    $estimate = DB::transaction(function () use ($request, $customerId, $estimateId) {
      $validated = $request->validate([
        'job_name' => 'required|string',
        'status'   => 'required|string',
        'notes'    => 'nullable|string',
        // tasks validation
        'tasks'                 => 'required|array',
        'tasks.*.id'            => 'sometimes|integer|exists:tasks,id',
        'tasks.*.description'   => 'required|string|max:255',
        'tasks.*.price'         => 'required|numeric|min:0',

        'deletedIds'   => 'sometimes|array',
        'deletedIds.*' => 'integer|exists:tasks,id',
      ]);

      $validated['notes'] = $validated['notes'] ?? '';
      $estimate = Estimate::where('id', $estimateId)
        ->where('customer_id', $customerId)
        ->firstOrFail();
      $estimate->update([
        'job_name' => $validated['job_name'],
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
        $estimate->tasks()->createMany($newTasks);
      }
      foreach ($existingTasks as $taskData) {
        $task = Task::find($taskData['id']);
        $task->update([
          'description' => $taskData['description'],
          'price' => $taskData['price'],
        ]);
      }
      if (!empty($validated['deletedIds'])) {
        $estimate->tasks()->whereIn('id', $validated['deletedIds'])->delete();
        // Doesn't Get Called On Batch Delete
        $estimate->recalculateTasksTotals();
      }


      return $estimate->load('tasks');
    });

    return response()->json($estimate);
  }
  public function delete(Request $request, $id)
  {
    $estimate = Estimate::findOrFail($id);



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

    Estimate::whereIn('id', $ids)->delete();

    return response()->json();
  }
}
