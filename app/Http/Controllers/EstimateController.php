<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Estimate;
use App\Models\Lead;
use App\Models\User;
use GuzzleHttp\Promise\Create;
use Illuminate\Http\Request;

class EstimateController extends Controller
{

  public function getPaginatedEstimate(Request $request)
  {
    $page = $request->get('page', 1);
    $perPage = $request->get('perPage', 10);
    $sortBy = $request->get('sortBy', 'created_at');      // default sort column
    $sortOrder = $request->get('sortOrder', 'desc');
    $search = $request->get('searchTerm', "");
    $query = Estimate::with(['customer:id,first_name,last_name,email'])
      ->withSum('tasks', 'price'); // named tasks_sum _price


    if ($search) {
      $query->where(function ($q) use ($search) {
        $q->where('job_name', 'like', "%{$search}%")
          ->orWhere('status', 'like', "%{$search}%")
          ->orWhereHas('customer', function ($q2) use ($search) {
            $q2->where('id', 'like', "%{$search}%")
              ->orWhere('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
          })
          ->orWhereRaw("CAST(tasks_sum_price as TEXT) LIKE ?", ["%{$search}%"]);
      });
    }

    $query->orderBy($sortBy, $sortOrder);
    $estimates = $query->paginate($perPage, ['*'], 'page', $page);

    return response()->json($estimates);
  }
}
