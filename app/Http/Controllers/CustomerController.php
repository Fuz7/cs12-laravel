<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
  public function getPaginatedCustomer(Request $request)
  {
    $page = $request->get('page', 1);
    $perPage = $request->get('perPage', 10);
    $sortBy = $request->get('sortBy', 'created_at');      // default sort column
    $sortOrder = $request->get('sortOrder', 'desc');
    $search = $request->get('searchTerm', "");
    $query = Customer::query();   // default sort order{


    if ($search) {
      $query->where(function ($q) use ($search) {
        $q->where('id', 'like', "%{$search}%")
          ->orWhere('first_name', 'like', "%{$search}%")
          ->orWhere('last_name', 'like', "%{$search}%")
          ->orWhere('company_name', 'like', "%{$search}%")
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
      'company_name' => 'nullable|string',
      'phone' => 'nullable|string',
      'address' => 'nullable|string',
      'lead_source' => 'nullable|string',
    ]);
    foreach (['email', 'company_name', 'phone', 'address', 'lead_source'] as $field) {
      $validated[$field] = $validated[$field] ?? '';
    }
    $customer = Customer::create($validated);
    return response()->json($customer);
  }

  public function getCustomerById(Request $request, $id)
  {
    $customer = Customer::findOrFail($id);


    return response()->json($customer);
  }
  public function update(Request $request, $id)
  {
    $customer = Customer::findOrFail($id);

    $validated = $request->validate([
      'first_name'   => 'sometimes|string',
      'last_name'    => 'sometimes|string',
      'email'        => 'sometimes|email|nullable',
      'company_name' => 'sometimes|string|nullable',
      'phone'        => 'sometimes|string|nullable',
      'address'      => 'sometimes|string|nullable',
      'lead_source'  => 'sometimes|string|nullable',
    ]);

    $customer->update($validated);

    return response()->json($customer);
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

    Customer::whereIn('id', $ids)->delete();

    return response()->json();
  }
  public function delete(Request $request, $id)
  {
    $customer = Customer::findOrFail($id);



    $customer->delete();

    return response()->json();
  }
  public function getCustomerIfExist(Request $request, $id)
  {
    $customer = Customer::where('id', $id)->first();
    return response()->json($customer);
  }
}
