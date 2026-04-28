<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{

  public function getPaginatedUsers(Request $request)
{
    $page = $request->get('page', 1);
    $perPage = $request->get('perPage', 10);

    $sortBy = $request->get('sortBy', 'created_at');
    $sortOrder = $request->get('sortOrder', 'desc');
    $search = $request->get('searchTerm', "");

    // ✅ whitelist sortable fields
    $allowedSorts = ['name', 'email', 'is_linked', 'created_at'];

    if (!in_array($sortBy, $allowedSorts)) {
        $sortBy = 'created_at';
    }

    // ✅ only role = 'user'
    $query = User::query()->where('role', 'user')->
        with('customer')->withExists(['customer as is_linked']);

    // 🔍 Search
    if (!empty($search)) {
        $query->where(function ($q) use ($search) {
            $q->where('name', 'ilike', "%{$search}%")
              ->orWhere('email', 'ilike', "%{$search}%");
        });
    }

    // 🔃 Sorting
    $query->orderBy($sortBy, $sortOrder);

    // 📄 Pagination
    $users = $query->paginate($perPage, ['*'], 'page', $page);

    return response()->json($users);
}

    public function linkUserToCustomer($customerId, $userId)
    {
        $customer = Customer::findOrFail($customerId);
        $user = User::findOrFail($userId);

        // 🚫 prevent customer already linked
        if ($customer->user_id) {
            return response()->json([
                'message' => 'Customer already linked'
            ], 400);
        }

        // 🚫 prevent user already linked to another customer
        if (Customer::where('user_id', $userId)->exists()) {
            return response()->json([
                'message' => 'User already linked to another customer'
            ], 400);
        }


    $customer->user()->associate($user);
    $customer->save();

    return response()->json();
    }

    public function unlinkUser($userId)
    {
        $customer = Customer::where('user_id', $userId)->first();

        if (!$customer) {
            return response()->json(['message' => 'No link found'], 404);
        }

        $customer->user()->dissociate(); // 🔥 Laravel way
        $customer->save();

        return response()->json(['message' => 'Unlinked']);
}
  public function delete(Request $request, $id)
  {
    $user = User::findOrFail($id);



    $user->delete();

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

    User::whereIn('id', $ids)->delete();

    return response()->json();
  }


}