<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomersController extends Controller
{

    ////////for owner of store
    public function getStoreCustomers()
    {
        $user = Auth::user();
        $store = $user->store;
        $users = [];
        if($store){
            $orders =  $store->orders;    
        }else{
            $orders = Order::all();
        }
        
        foreach ($orders as $order) {
          if(!in_array($order->user, $users)){
            $users[] = $order->user;
          }
            
        }
        return response()->json($users);
    }

  public function showCustomerOrders(User $user , Request $request)
  {

    $storeId = $request->input('store_id');
    $orders = Order::where('user_id', $user->id)
    ->where('store_id', $storeId)
    ->get();
    return response()->json(['message'=>'Orders of customer fetched successfully' ,'orders' => $orders]);
  }
    
}
