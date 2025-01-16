<?php

namespace App\Http\Controllers;

use App\Http\Requests\CartRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\InvoiceDetailsResource;
use App\Http\Resources\InvoiceResource;
use App\Http\Resources\OrderProductResource;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Services\OrderService;
use Illuminate\Http\Request;
use App\Http\Resources\OrderResource;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{


    public function userOrders(Request $request)
    {
        $user = Auth::user();

        // Get the optional order status from the request, default to null if not provided
        $orderStatus = $request->query('order_status');

        // Build the query to get the user's orders
        $ordersQuery = $user->orders()->with('products');

        // Apply the filter if order status is provided
        if ($orderStatus) {
            $ordersQuery->where('order_status', $orderStatus);
        }

        // Execute the query and get the orders
        $orders = $ordersQuery->get()->map(function ($order) {
            // Map over each product to exclude the pivot data
            $order->products->each(function ($product) {
                unset($product->pivot);
            });
            return $order;
        });

        return response()->json(['Orders' => $orders]);
    }

    public function userPendingOrders()
    {
        $user = Auth::user();
        $orders = $user->orders()
            ->where('order_status', 'pending') // Filter by pending status
            ->with('products') // Eager load related data
            ->get()
            ->map(function ($order) {
                // Remove the pivot data from each product
                $order->products->each(function ($product) {
                    unset($product->pivot);
                });
                return $order;
            });

        return response()->json(['Orders' => $orders]);
    }


    public function index(Request $request)
    {
        $status = $request->input('status');
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            // If user is admin, fetch orders across all stores
            $orders = Order::query()
                ->when($status, fn($query) => $query->where('order_status', $status))
                ->with(['products', 'store', 'couponOrderUser.coupon']) // Load products, store, and coupon relationships
                ->get();
        } else {
            // If user is not admin, fetch orders for the user's store only
            $orders = $user->store->orders()
                ->when($status, fn($query) => $query->where('order_status', $status))
                ->with(['products', 'couponOrderUser.coupon']) // Load products and coupon relationships
                ->get();
        }

        foreach ($orders as $order) {
            $totalProfit = 0;
            $totalRevenue = 0;

            foreach ($order->products as $product) {
                $basePrice = $product->base_price;
                $price = $product->discounted_price ?? $product->price;

                // Calculate profit for this product and add to total profit for the order
                $productProfit = ($price - $basePrice) * $product->pivot->quantity;
                $totalProfit += $productProfit;

                // Calculate revenue for this product and add to total revenue for the order
                $totalRevenue += $basePrice * $product->pivot->quantity;
            }

            // Set the accumulated total profit and revenue for the order
            $order->total_profit = $totalProfit;
            $order->total_revenue = $totalRevenue;

            // Calculate profit percentage for the order
            $order->profit_percentage = $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0;

            if ($order->couponOrderUser) {
                $coupon = $order->couponOrderUser->coupon;
                $total_price_before_discount = $order->total_price / (1 - $coupon->percentage / 100);
                $discount_amount = $total_price_before_discount - $order->total_price;

                // Adjust total profit and profit percentage for discount
                $order->total_profit -= $discount_amount;
                $order->profit_percentage = $totalRevenue > 0 ? ($order->total_profit / $totalRevenue) * 100 : 0;
            }
        }

        return response()->json([
            'message' => 'Orders fetched successfully',
            'orders' => $orders,
        ]);
    }




    public function show(Order $order)
    {
        return response()->json(['message'=>'Order fetched successfully' ,'order' => new OrderProductResource($order)]);
    }
    public function update(UpdateOrderRequest $request, Order $order)
    {
        $order->update($request->validated());

        return response()->json(['message' => 'Order updated successfully' ,'order' => $order], 200);
    }
    public function destroy(Order $order)
    {
        $order->delete();
        return response()->json(['message'=>'Order deleted successfully']);
    }

    public function approveOrder(Order $order)
    {
        $order->update(['order_status' => 'processing']);
        $products = $order->products;

        foreach ($products as $product) {
            $product->update(['stock' => $product->stock - $order->orderItems()->where('product_id', $product->id)->first()->quantity]);

    }
        return response()->json(['message' => 'Order approved successfully']);
    }

    public function cancelOrder(Order $order)
    {
        $order->update(['order_status' => 'canceled']);
        return response()->json(['message' => 'Order cancelled successfully']);
    }
    
    public function pendingOrder(Order $order)
    {
        $order->update(['order_status' => 'pending']);
        return response()->json(['message' => 'Order pending successfully']);
    }
    public function completedOrder(Order $order)
    {
        $order->update(['order_status' => 'completed']);
        return response()->json(['message' => 'Order completed successfully']);
    }



    public function addReview(Request $request, Order $order)
    {

        $order->update(['review' => $request->input('review')]);    

        return response()->json(['message' => 'Review added successfully']);

    }


    ////for user 
    public function cancelOrderForUser(Request $request , Order $order)
    {
        if ($order->user_id != Auth::user()->id) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        if($order->order_status == 'pending' ) {
            $order->update(['order_status' => 'canceled', 'cancel_reason' => $request->input('cancel_reason')]);
            
            return response()->json(['message' => 'Order cancelled successfully']);
        }
        return response()->json(['message' => 'Order cannot be cancelled'], 400);
       
    }
}