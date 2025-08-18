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
}
