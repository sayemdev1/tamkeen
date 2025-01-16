<?php

namespace App\Http\Controllers;

use App\Services\CheckoutService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    protected $checkoutService;

    public function __construct(CheckoutService $checkoutService)
    {
        $this->checkoutService = $checkoutService;
    }

    public function checkout(Request $request)
    {

        $storeId = $request->input('store_id');
        $couponCode = $request->input('coupon_code');
        $address_id = $request->input('address_id');
        $payment_method_id = $request->input('payment_method_id');


        return $this->checkoutService->checkout($storeId, $couponCode, $address_id, $payment_method_id);
    }
}
