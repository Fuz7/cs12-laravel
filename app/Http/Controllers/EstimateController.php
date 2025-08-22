<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Estimate;
use App\Models\Lead;
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
}
