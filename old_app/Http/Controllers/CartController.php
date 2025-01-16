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
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not authenticated.'], 401);
        }

        $cartItems = $request->input('cart'); // Expected format: [{ "store_id": 1, "product_id": 1, "quantity": 2 }, ...]
        try {
            $this->cartService->addToCart($cartItems);
            return response()->json(['success' => true, 'message' => 'Items added to cart.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function viewCart()
    {
       
            $cart = $this->cartService->viewCart();
            return response()->json(['cart' => $cart]);
       
    }
}
