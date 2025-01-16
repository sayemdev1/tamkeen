<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCouponRequest;
use App\Http\Requests\UpdateCouponRequest;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{

    /////// for admin ///////

    public function index()
    {
        return response()->json(['coupons' => Coupon::all()]);
    }


    public function store(StoreCouponRequest $request)
    {
       $coupon = Coupon::create($request->validated());
        return response()->json(['coupon' => $coupon , 'message' => 'Coupon created successfully']);
    }

    
    public function show(Coupon $coupon)
    {
        return response()->json(['coupon' => $coupon]);
    }

        public function update(UpdateCouponRequest $request, Coupon $coupon)
    {
        $coupon->update($request->validated());
        return response()->json(['message' => 'Coupon updated successfully', 'coupon' => $coupon]);
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return response()->json(['message' => 'Coupon deleted successfully']);
    }


    /////// for user ///////
    
}
