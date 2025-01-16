<?php

namespace App\Services;

use App\Models\OrderProduct;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function getTotalOrders($productIds)
    {
        return OrderProduct::whereIn('product_id', $productIds)
            ->distinct('order_id')
            ->count('order_id');
    }

    public function calculateOrderChanges($category)
    {
        // Get the IDs of products in the specified category
        $productIds = $category->products()->pluck('products.id');

        // Define the date ranges
        $now = now();
        $sevenDaysAgo = $now->subDays(7);

        // Count unique order IDs for the last 7 days
        $ordersLast7Days = OrderProduct::whereIn('product_id', $productIds)
            ->where('created_at', '>=', $sevenDaysAgo)
            ->distinct('order_id')
            ->count('order_id');

        // Count unique order IDs for the period before the last 7 days
        $ordersBeforeLast7Days = OrderProduct::whereIn('product_id', $productIds)
            ->where('created_at', '<', $sevenDaysAgo)
            ->distinct('order_id')
            ->count('order_id');

        // Calculate the absolute change in number of orders
        $change = $ordersLast7Days - $ordersBeforeLast7Days;

        // Calculate the percentage change
        $percentageChange = $ordersBeforeLast7Days > 0
            ? ($change / $ordersBeforeLast7Days) * 100
            : ($ordersLast7Days > 0 ? 100 : 0);

        // Determine the status of change
        $status = $change > 0 ? 'increased' : ($change < 0 ? 'decreased' : 'no change');

        return [
            'total_orders_last_7_days' => $ordersLast7Days,
            'total_orders_before_last_7_days' => $ordersBeforeLast7Days,
            'change' => $change,
            'percentage_change' => $percentageChange,
            'status' => $status,
        ];
    }
}
