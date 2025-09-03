<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\User;
use Carbon\Carbon;
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
    $leads = $query->paginate($perPage, ['*'], 'page', $page);

    return response()->json($leads);
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

    // Always keep email from lead
    $email = $request->input("email");

    Customer::updateOrCreate(
      ['email' => $email], // search by lead email
      [
        'first_name'       => $request->input('first_name') ?: ($lead->first_name ?: ""),
        'last_name'        => $request->input('last_name') ?: ($lead->last_name ?: ""),
        'company_name'     => $request->input('company_name') ?: ($lead->company ?: ""),
        'phone'            => $request->input('phone') ?: ($lead->phone ?: ""),
        'property_address' => $request->input('property_address') ?: "",
        'billing_address'  => "",
        'lead_source'      => $request->input('lead_source') ?: ($lead->source ?: ""),
        'email'            => $email,
      ]
    );

    // Update lead status
    $lead->update([
      'status' => 'converted',
      'email' => $request->input('email', $email)
    ]);

    return response()->json([
      'message' => 'Lead converted successfully',
    ]);
  }

  public function getNewLeads(Request $request)
  {
    $startOfLastMonth = Carbon::now()->subMonth()->startOfMonth();
    $endOfLastMonth   = Carbon::now()->subMonth()->endOfMonth();
    $startOfPeriod = Carbon::now()->subMonths(13)->startOfMonth();
    $endOfPeriod   = $startOfLastMonth->copy()->subDay(); // up to the month before last month

    // Leads created last month
    $lastMonthLeads = Lead::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
      ->count();

    $leadsPerMonth = Lead::whereBetween('created_at', [$startOfPeriod, $endOfPeriod])
      ->selectRaw('DATE_TRUNC(\'month\', created_at) as month, COUNT(*) as total')
      ->groupBy('month')
      ->pluck('total');

    $averagePrev12Months = $leadsPerMonth->avg();
    $growthRate = null;
    if ($averagePrev12Months > 0) {
      $growthRate = (($lastMonthLeads - $averagePrev12Months) / $averagePrev12Months) * 100;
    }

    return [
      'last_month_leads' => $lastMonthLeads,
      'growth_rate_percent'  => round($growthRate, 2),
    ];
  }
  function getConvertionRate()
  {
    $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
    $lastMonthEnd   = Carbon::now()->subMonth()->endOfMonth();

    $totalLastMonth = Lead::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();
    $convertedLastMonth = Lead::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
      ->where('status', 'converted')
      ->count();

    $lastMonthConversion = $totalLastMonth > 0
      ? ($convertedLastMonth / $totalLastMonth) * 100
      : 0;

    $yearStart = Carbon::now()->subYear()->startOfMonth();
    $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();

    $monthlyStats = Lead::selectRaw("
        DATE_TRUNC('month', created_at) as month,
        COUNT(*) as total,
        SUM(CASE WHEN status = 'converted' THEN 1 ELSE 0 END) as converted
    ")
      ->whereBetween('created_at', [$yearStart, $lastMonthStart->subDay()])
      ->groupBy('month')
      ->orderBy('month')
      ->get();

    $monthCount = $monthlyStats->count();

    $unweightedAvg = $monthCount > 0
      ? $monthlyStats->map(function ($row) {
        return $row->total > 0 ? ($row->converted / $row->total) * 100 : 0;
      })->avg()
      : 0;

    $weightedAvg = $monthlyStats->sum('total') > 0
      ? ($monthlyStats->sum('converted') / $monthlyStats->sum('total')) * 100
      : 0;


    $growthRatePercent = $weightedAvg > 0
      ? (($lastMonthConversion - $weightedAvg) / $weightedAvg) * 100
      : 0;

    return [
      'last_month_conversion_rate'   => round($lastMonthConversion, 2),
      'growth_rate_percent'          => round($growthRatePercent, 2),
    ];
  }
}
