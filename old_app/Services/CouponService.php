<?php

namespace App\Services;

use App\Models\Coupon;
use Exception;

class CouponService
{
    public function applyCoupon($couponCode, $totalPrice)
    {
        // Find the coupon by code, check if it's active and not expired
        $coupon = Coupon::where('promotion_code', $couponCode)
            ->where('status', 'active')
            ->where('expired_at', '>', now())
            ->first();

        if (!$coupon) {
            throw new Exception('Invalid or expired coupon.');
        }

        // Ensure the coupon is applicable to the package type
        if (!$coupon->isApplicableTo('package')) {
            throw new Exception('Coupon is not applicable to the selected package type.');
        }

        // Check the usage limit
        if ($coupon->number_of_uses <= 0) {
            throw new Exception('Coupon has reached its usage limit.');
        }

        // Calculate the discount amount based on type
        $discountAmount = 0;
        if ($coupon->discount_type === 'percentage') {
            $discountAmount = ($coupon->percentage / 100) * $totalPrice;
        } elseif ($coupon->discount_type === 'fixed') {
            $discountAmount = min($coupon->percentage, $totalPrice); // Fixed discount up to total price
        }

        // Deduct the discount from the total price
        $discountedPrice = $totalPrice - $discountAmount;

        // Update coupon usage count
        $coupon->number_of_uses -= 1;
        $coupon->save();

        return [
            'coupon' => $coupon,
            'discounted_price' => $discountedPrice,
            'discount_amount' => $discountAmount,
        ];
    }
    public function applyCouponForBasket($couponCode, $totalPrice)
    {
        // Find the coupon by code, check if it's active and not expired
        $coupon = Coupon::where('promotion_code', $couponCode)
            ->where('status', 'active')
            ->where('expired_at', '>', now())
            ->first();

        if (!$coupon) {
            throw new Exception('Invalid or expired coupon.');
        }

        // Ensure the coupon is applicable to the package type
        if (!$coupon->isApplicableTo('basket')) {
            throw new Exception('Coupon is not applicable to the selected basket type.');
        }

        // Check the usage limit
        if ($coupon->number_of_uses <= 0) {
            throw new Exception('Coupon has reached its usage limit.');
        }

        // Calculate the discount amount based on type
        $discountAmount = 0;
        if ($coupon->discount_type === 'percentage') {
            $discountAmount = ($coupon->percentage / 100) * $totalPrice;
        } elseif ($coupon->discount_type === 'fixed') {
            $discountAmount = min($coupon->percentage, $totalPrice); // Fixed discount up to total price
        }

        // Deduct the discount from the total price
        $discountedPrice = $totalPrice - $discountAmount;

        // Update coupon usage count
        $coupon->number_of_uses -= 1;
        $coupon->save();

        return [
            'coupon' => $coupon,
            'discounted_price' => $discountedPrice,
            'discount_amount' => $discountAmount,
        ];
    }
}
