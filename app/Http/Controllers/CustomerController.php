<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
  public function getPaginatedCustomers(Request $request)
  {
    $page = $request->get('page', 1);
    $perPage = $request->get('perPage', 10);
    $sortBy = $request->get('sortBy', 'created_at');      // default sort column
    $sortOrder = $request->get('sortOrder', 'desc');
    $search = $request->get('searchTerm', "");
    $query = Customer::query();   // default sort order{


   if ($search) {
    $query->where(function ($q) use ($search) {
        $q->where('id', 'ilike', "%{$search}%")
          ->orWhere('first_name', 'ilike', "%{$search}%")
          ->orWhere('last_name', 'ilike', "%{$search}%")
          ->orWhere('company_name', 'ilike', "%{$search}%")
          ->orWhere('phone', 'ilike', "%{$search}%")
          ->orWhere('email', 'ilike', "%{$search}%")

          // 🔥 search linked user
          ->orWhereHas('user', function ($q2) use ($search) {
              $q2->where('name', 'ilike', "%{$search}%")
                 ->orWhere('email', 'ilike', "%{$search}%");
          });
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
      'property_address' => 'required|string',
      'email' => 'required|email',
      'company_name' => 'nullable|string',
      'phone' => 'nullable|string',
      'billing_address' => 'nullable|string',
      'lead_source' => 'nullable|string',
    ]);
    foreach (['email', 'company_name', 'phone', 'billing_address', 'lead_source'] as $field) {
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

    foreach (['email', 'company_name', 'phone', 'address', 'lead_source'] as $field) {
      $validated[$field] = $validated[$field] ?? '';
    }

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

  public function getNewCustomers(Request $request)
  {
    $startOfLastMonth = Carbon::now()->subMonth()->startOfMonth();
    $endOfLastMonth   = Carbon::now()->subMonth()->endOfMonth();
    $startOfPeriod = Carbon::now()->subMonths(13)->startOfMonth();
    $endOfPeriod   = $startOfLastMonth->copy()->subDay(); // up to the month before last month

    // Customers created last month
    $lastMonthCustomers = Customer::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
      ->count();

    $customersPerMonth = Customer::whereBetween('created_at', [$startOfPeriod, $endOfPeriod])
      ->selectRaw('DATE_TRUNC(\'month\', created_at) as month, COUNT(*) as total')
      ->groupBy('month')
      ->pluck('total');

    $averagePrev12Months = $customersPerMonth->avg();
    $growthRate = null;
    if ($averagePrev12Months > 0) {
      $growthRate = (($lastMonthCustomers - $averagePrev12Months) / $averagePrev12Months) * 100;
    }

    return [
      'last_month_customers' => $lastMonthCustomers,
      'growth_rate_percent'  => round($growthRate, 2),
    ];
  }

  public function getUnlinkedCustomers()
{
    $customers = Customer::whereNull('user_id')->get();

    return response()->json($customers);
}

 public function getCustomers()
{
    $customers = Customer::get();

    return response()->json($customers);
}
}
