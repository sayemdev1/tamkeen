<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Discount;
use App\Models\Product;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    public function getMyDiscount()
    {
        $discounts = auth()->user()->myDiscounts()->get();

        foreach ($discounts as $discount) {
            if ($discount->discountable_type == 'product') {
                $discount->related_item = Product ::find($discount->discountable_id);
            } elseif ($discount->discountable_type == 'coupon') {
                $discount->related_item = Coupon::find($discount->discountable_id);
            }
        }

        return response()->json(['my_discount_items' => $discounts]);
    }


    public function addToMyDiscount(Request $request)
    {
        $user_id = auth()->id();
        $type = $request->type ;
        $id = $request->id;
        if($type == 'product'){
           
          $discount =  Discount::create([
            'user_id' => $user_id, 
            'discountable_id' => $id , 
            'discountable_type' => 'product'
        ]);
          
        }
      if($type == 'coupon'){
    
        $discount =  Discount::create([
            'user_id' => $user_id, 
            'discountable_id' => $id , 
            'discountable_type' => 'coupon'
        ]);
          
      }

        return response()->json(['message' => ' added to my discount successfully']);

    }

   
}
