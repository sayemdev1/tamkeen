<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function addToCart(Request $request)
    {
        $validated = $request->validate([
            'cartItems' => 'required|array|min:1',
            'cartItems.*.store_id' => 'required|integer',
            'cartItems.*.product_id' => 'required|integer',
            'cartItems.*.quantity' => 'required|integer|min:1',
        ]);
    
        $cartItems = $validated['cartItems'];
    
        // Call the CartService method
        try {
            app(CartService::class)->addToCart($cartItems);
            return response()->json(['message' => 'Cart updated successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function viewCart()
    {
       
            $cart = $this->cartService->viewCart();
            return response()->json(['cart' => $cart]);
       
    }
}
