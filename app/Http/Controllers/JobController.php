<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;

class JobController extends Controller
{

  public function getPaginatedJob(Request $request)
  {
    $page = $request->get('page', 1);
    $perPage = $request->get('perPage', 10);
    $sortBy = $request->get('sortBy', 'created_at');      // default sort column
    $sortOrder = $request->get('sortOrder', 'desc');
    $search = $request->get('searchTerm', "");
    $query = Job::with(
      ['customer:id,first_name,last_name,email,property_address'],
    );


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

    if ($sortBy === 'customer_first_name') {
      $query->join('customers', 'jobs.customer_id', '=', 'customers.id')
        ->orderBy('customers.first_name', $sortOrder)
        ->select('jobs.*');
    } else {
      $query->orderBy($sortBy, $sortOrder);
    }

    $estimates = $query->paginate($perPage, ['*'], 'page', $page);

    return response()->json($estimates);
  }
  public function store(Request $request, $customerId)
  {
    $validated = $request->validate([
      'job_name' => 'required|string',
      'site_address' => 'nullable|string',
      'due_date' => 'required|date',
      'status'   => 'required|string',
      'notes'    => 'nullable|string',
    ]);
    foreach (['notes', 'site_address'] as $field) {
      $validated[$field] = $validated[$field] ?? '';
    }
    $job = Job::create([
      'customer_id' => $customerId,
      'job_name'    => $validated['job_name'],
      'site_address' => $validated['site_address'],
      'due_date'    => $validated['due_date'],
      'status'      => $validated['status'],
      'notes'       => $validated['notes'],
    ]);
    return response()->json($job);
  }

  public function update(Request $request, $jobId)
  {
    $job = Job::findOrFail($jobId);

    $validated = $request->validate([
      'job_name' => 'required|string',
      'site_address' => 'nullable|string',
      'due_date' => 'required|date',
      'status'   => 'required|string',
      'notes'    => 'nullable|string',
    ]);

    foreach (['notes', 'site_address'] as $field) {
      $validated[$field] = $validated[$field] ?? '';
    }
    $job->update([
      'job_name' => $validated['job_name'],
      'site_address' => $validated['site_address'],
      'due_date' => $validated['due_date'],
      'status'   => $validated['status'],
      'notes'    => $validated['notes'],
    ]);

    return response()->json($job);
  }
  public function delete(Request $request, $jobId)
  {
    $job = Job::findOrFail($jobId);



    $job->delete();

    return response()->json();
  }

  public function deleteByBatch(Request $request)
  {
    $ids = $request->input('ids', []); // expects: [1,2,3,...]

    if (empty($ids)) {
      return response()->json([
        'status' => 'error',
        'message' => 'No IDs provided'
      ], 400);
    }

    Job::whereIn('id', $ids)->delete();

    return response()->json();
  }
}
