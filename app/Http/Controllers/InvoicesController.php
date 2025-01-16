<?php

namespace App\Http\Controllers;

use App\Http\Resources\InvoiceDetailsResource;
use App\Http\Resources\InvoiceResource;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoicesController extends Controller
{
    public function getInvoices()
    {
        $store = Auth::user();
        
         if ($store->hasRole('admin')) {
             $invoices = Order::all();
        return response()->json(['invoices' => InvoiceResource::collection($invoices)] ); 
         }else{
            $invoices = $store->orders;
        return response()->json(['invoices' => InvoiceResource::collection($invoices)] ); 
         }
        
        

    }
    
    public function viewDetails(Order $order)
    {
        $order->load('user','address','store');
        return response()->json(new InvoiceDetailsResource($order));

    }

}
