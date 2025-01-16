<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\CouponOrderUser;
use App\Models\MembershipLevel;
use App\Models\Order;
use App\Models\Product;
use App\Models\ReferralProfit;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class CheckoutService
{
    public function calculateReferralFee($user, $totalPrice)
    {
        $referralFees = [];
        $level = 1;

        while ($user->referrer && $level <= 3) {  // Limit to 3 levels, or modify as needed
            // Fetch the referrer's membership level
            $membershipLevel = $user->referrer->membership_levels()->where('is_active', true)->first();

            if ($membershipLevel) {
                // Determine the appropriate percentage field based on the level
                $percentageField = 'percentage_in_level_' . $level;

                // Check if the membership level has this percentage field
                $feePercentage = $membershipLevel->{$percentageField} ?? 0;

                // Calculate the profit for the current level
                $profit = ($totalPrice * $feePercentage) / 100;

                // Store the profit and referrer for each level
                $referralFees[] = [
                    'referrer' => $user->referrer,
                    'profit' => $profit,
                    'level' => $level,
                    'membership_level_id' => $membershipLevel->id,
                ];
            }

            // Move to the next referrer level
            $user = $user->referrer;
            $level++;
        }

        return $referralFees;
    }

    public function checkout($storeId, $couponCode, $address_id, $payment_method_id)
    {
        // Ensure the user is authenticated
        $user = Auth::user();

        // Validate store ID
        if (!$storeId) {
            throw new \Exception("No store selected for checkout.");
        }

        // Retrieve the user's cart
        $cart = Cart::where('user_id', $user->id)->first();
        if (!$cart || empty($cart->items)) {
            throw new \Exception("Cart is empty.");
        }

        // Decode the items stored in the cart
        $items = json_decode($cart->items, true);

        // Check if the selected store exists in the user's cart
        if (!isset($items[$storeId]) || empty($items[$storeId])) {
            throw new \Exception("No items in cart for the selected store.");
        }

        // Initialize variables
        $storeItems = $items[$storeId];
        $totalPrice = 0;
        $discountAmount = 0;

        // Validate stock and calculate total price for the selected store's items
        foreach ($storeItems as $productId => $quantity) {
            $product = Product::where('id', $productId)
                ->where('store_id', $storeId)
                ->first();

            if (!$product) {
                throw new \Exception("Product with ID {$productId} not found in the selected store.");
            }

            // Check stock
            if ($quantity > $product->stock) {
                throw new \Exception("Insufficient stock for product: {$product->name}. Available stock: {$product->stock}.");
            }

            // Calculate price and update stock
            $productPrice = $product->discounted_price ?: $product->price; // Use discounted price if available
            $totalPrice += $productPrice * $quantity;

            // Decrease stock for the product
            $product->stock -= $quantity;
            $product->save();
        }

        // Apply coupon if provided
        if ($couponCode) {
            $coupon = Coupon::where('promotion_code', $couponCode)
                ->where('status', 'active')
                ->where('expired_at', '>', now())
                ->first();

            if (!$coupon) {
                return response()->json(['error' => 'Invalid or expired coupon.'], 400);
            }

            // Check coupon applicability
            if (!$coupon->isApplicableTo('order') || $coupon->number_of_uses <= 0) {
                return response()->json(['error' => 'Coupon is not applicable or has reached its usage limit.'], 400);
            }

            // Calculate discount based on coupon type
            if ($coupon->discount_type === 'percentage') {
                $discountAmount = ($coupon->percentage / 100) * $totalPrice;
            } elseif ($coupon->discount_type === 'fixed') {
                $discountAmount = min($coupon->percentage, $totalPrice);
            }

            // Deduct the discount from the total price
            $totalPrice -= $discountAmount;

            // Update coupon usage
            $coupon->number_of_uses--;
            $coupon->save();
        }

        $referralFees = $this->calculateReferralFee($user, $totalPrice);
        $totalReferralFee = array_sum(array_column($referralFees, 'profit')); // Sum total referral fee

      

       

        // Create the order
        $order = Order::create([
            'user_id' => $user->id,
            'store_id' => $storeId,
            'total_price' => $totalPrice,
            'order_status' => 'pending',
            'address_id' => $address_id,
            'payment_method_id' => $payment_method_id,
        ]);

        $membership = $user->membership_levels()
            ->withPivot('account', 'is_subscribed')
            ->wherePivot('is_subscribed', true) 
            ->first();


        if ($membership) {
            $price_of_membership = $membership->monthly_fee;

          $user->membership_levels()->updateExistingPivot($membership->id, [
                'account' => $membership->pivot->account + $totalPrice,
            ]);

            // Reload the membership level
            $membership = $user->membership_levels()
                ->withPivot('account', 'is_subscribed', 'activated_until', 'activated_from')
                ->wherePivot('is_subscribed', true)
                ->first();

            $months_to_activate = floor($membership->pivot->account/ $price_of_membership);

            if ($months_to_activate > 0) {
                // Initialize new activated_until date based on the current date
                $new_activated_until = now()->addMonths($months_to_activate)->subMonth()->endOfMonth();

                // Set the start date of the activation if not already set
                $activated_from = $membership->pivot->activated_from ?? now();
              

                // Check if activated_until is already set
                if ($membership->pivot->activated_until) {
                    // If it exists, add the months to the existing activated_until date
                    $existing_activated_until = \Carbon\Carbon::parse($membership->pivot->activated_until);

                    // Add months to the existing activation date while keeping the time
                    $new_activated_until = $existing_activated_until->addDays($months_to_activate * 30);

                    // Check if the original date was the last day of the month
                    if ($existing_activated_until->day === $existing_activated_until->endOfMonth()->day) {
                        // If the new date is not the last day of the month, set it to the end of the new month
                        if ($new_activated_until->day !== $new_activated_until->endOfMonth()->day) {
                            $new_activated_until = $new_activated_until->endOfMonth();
                        }
                    }
                }

                // Update the membership with the new active status and the new activated_until date
                $user->membership_levels()->updateExistingPivot($membership->id, [
                    'is_active' => true,
                    'activated_until' => $new_activated_until,
                    'activated_from' => $activated_from,  // Set or keep the start date
                ]);

                // Deduct the used amount from the account balance
                $user->membership_levels()->updateExistingPivot($membership->id, [
                    'account' => $membership->pivot->account - $membership->monthly_fee * $months_to_activate,
                ]);
            }

        }

       
  if($user->membership_levels()
            ->withPivot('is_subscribed')
            ->wherePivot('is_subscribed', true)
            ->first()){
        // Process referral fee distribution for each level
        foreach ($referralFees as $feeData) {
            $referrer = $feeData['referrer'];
            $profit = $feeData['profit'];
            $level = $feeData['level'];
            $membership_level_id = $feeData['membership_level_id'];

            // Increment the referrer's balance by their calculated profit
            $referrer->increment('balance', $profit);

            // Optionally, record profit in a ReferralProfit table
            ReferralProfit::create([
                'referrer_id' => $referrer->id,
                'order_id' => $order->id,
                'level' => $level,
                'profit' => $profit,
                'user_id' => $user->id,
                'membership_level_id' => $membership_level_id,
            ]);
        }
    }

        // Attach products to the order
        foreach ($storeItems as $productId => $quantity) {
            $product = Product::find($productId); // Product already validated earlier
            $order->products()->attach($product->id, [
                'quantity' => $quantity,
                'price' => $product->discounted_price ?: $product->price,
            ]);

            // Create transaction record
            Transaction::create([
                'product_id' => $product->id,
                'user_id' => $user->id,
                'date' => now(),
                'payment_method' =>  'cash',
                // $payment_method_id,  Use the provided payment method
                'amount' => $product->price * $quantity,
                'quantity' => $quantity,
                'order_id' => $order->id,
            ]);
        }

        // Remove the selected store's items from the cart after checkout
        unset($items[$storeId]);
        $cart->items = json_encode($items);
        $cart->save();

        return response()->json([
            'message' => 'Checkout successful for store ' . $storeId,
            'order' => $order,
            'coupon' => $coupon ?? null,
            'total_price_before_discount' => $totalPrice + $discountAmount,
            'discount' => $discountAmount,
        ]);
    }


}
