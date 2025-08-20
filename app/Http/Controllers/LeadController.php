<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Lead;
use GuzzleHttp\Promise\Create;
use Illuminate\Http\Request;

class LeadController extends Controller
{
  public function getPaginatedLead(Request $request)
  {
    $page = $request->get('page', 1);
    $perPage = $request->get('perPage', 10);
    $sortBy = $request->get('sortBy', 'created_at');      // default sort column
    $sortOrder = $request->get('sortOrder', 'desc');
    $search = $request->get('searchTerm', "");
    $query = Lead::query();   // default sort order{


    if ($search) {
      $query->where(function ($q) use ($search) {
        $q->where('id', 'like', "%{$search}%")
          ->orWhere('first_name', 'like', "%{$search}%")
          ->orWhere('last_name', 'like', "%{$search}%")
          ->orWhere('status', 'like', "%{$search}%")
          ->orWhere('phone', 'like', "%{$search}%")
          ->orWhere('email', 'like', "%{$search}%");
      });
    }

    $query->orderBy($sortBy, $sortOrder);
    $customers = $query->paginate($perPage, ['*'], 'page', $page);

    return response()->json($customers);
  }

  public function store(Request $request)
  {
    $validated = $request->validate([
      'first_name' => 'required|string',
      'last_name' => 'required|string',
      'email' => 'nullable|email',
      'phone' => 'nullable|string',
      'company' => 'nullable|string',
      'status' => 'nullable|string',
      'source' => 'required|string',
      'notes' => 'nullable|string',
    ]);
    foreach (['email', 'phone', 'company', 'status', 'notes'] as $field) {
      $validated[$field] = $validated[$field] ?? '';
    }
    $lead = Lead::create($validated);
    return response()->json($lead);
  }

  public function update(Request $request, $id)
  {
    $lead = Lead::findOrFail($id);

    $validated = $request->validate([
      'first_name'   => 'sometimes|string',
      'last_name'    => 'sometimes|string',
      'email'        => 'sometimes|email|nullable',
      'company' => 'sometimes|string|nullable',
      'status' => 'sometimes|string|nullable',
      'phone'        => 'sometimes|string|nullable',
      'source'      => 'sometimes|string',
      'notes'  => 'sometimes|string|nullable',
    ]);

    foreach (['email', 'phone', 'company', 'status', 'notes'] as $field) {
      $validated[$field] = $validated[$field] ?? '';
    }
    $lead->update($validated);

    return response()->json($lead);
  }

  public function delete(Request $request, $id)
  {
    $lead = Lead::findOrFail($id);



    $lead->delete();

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

    Lead::whereIn('id', $ids)->delete();

    return response()->json();
  }
  public function convertToCustomer(Request $request, $id)
  {
    $lead = Lead::findOrFail($id);
    Customer::create([
      'first_name' => $lead->first_name,
      'last_name' => $lead->last_name,
      'email' => $lead->email,
      'company_name' => $lead->company,
      'phone' => $lead->phone,
      'address' => "",
      'lead_source' => $lead->source,
    ]);

    $lead->update(['status' => 'converted']);


    return response()->json();
  }
}
