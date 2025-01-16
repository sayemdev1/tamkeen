<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentRequest;
use App\Models\PaymentMethod;
use Faker\Provider\ar_EG\Payment;
use Illuminate\Http\Request;

class PaymentMethodsController extends Controller
{

    public function index()
    {
      return response()->json(['payment_methods' => PaymentMethod::all()]) ;
    }

  
    public function store(StorePaymentRequest $request)
    {
        if(!auth()->user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }
        $payment_method = PaymentMethod::create($request->validated());
        return response()->json($payment_method, 201);
    }

  
    public function showPayment(PaymentMethod $payment_method)
    {
        return response()->json($payment_method);
    }

   
    public function update(StorePaymentRequest $request, PaymentMethod $payment_method)
    {
        if (!auth()->user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }
        $payment_method->update($request->validated());
        return response()->json($payment_method);
    }

  
    public function destroy(PaymentMethod $payment_method)
    {
        if (!auth()->user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }
        $payment_method->delete();
        return response()->json(['message' => 'Payment method deleted successfully']);
    }
}
