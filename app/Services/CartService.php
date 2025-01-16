<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\CheckoutService;

class CartService
{

    protected $checkoutService;

    public function __construct(checkoutService $checkoutService)
    {
        $this->checkoutService = $checkoutService;
    }
    public function addToCart(array $cartItems)
    {
        $user = Auth::user();
        if (!$user) {
            throw new \Exception("User not authenticated.");
        }

        if (empty($cartItems)) {
            throw new \InvalidArgumentException("Cart items cannot be empty.");
        }

        // Retrieve or create the cart for the user
        $cart = Cart::firstOrCreate(['user_id' => $user->id]);

        // Decode existing cart items
        $items = $cart->items ? json_decode($cart->items, true) : [];

        // Process each item in the cart
        foreach ($cartItems as $item) {
            $storeId = $item['store_id'];
            $productId = $item['product_id'];
            $quantity = $item['quantity'];

            // Initialize store entry if not exists
            if (!isset($items[$storeId])) {
                $items[$storeId] = [];
            }

            // Initialize product entry for the specific store if not exists
            if (!isset($items[$storeId][$productId])) {
                $items[$storeId][$productId] = 0; // Initialize quantity to 0
            }

            // Increment the quantity for the specific product
            $items[$storeId][$productId] += $quantity;
        }

        // Encode the updated items and save them to the cart
        $cart->items = json_encode($items);
        $cart->save();
    }

    public function viewCart()
    {
        $user = Auth::user();

        // Retrieve the user's cart
        $cart = Cart::where('user_id', $user->id)->first();
        if (!$cart) {
            return []; // Return an empty array if no cart exists
        }

        // Decode items and ensure it is an array
        $cartItems = json_decode($cart->items, true);
        if (!is_array($cartItems)) {
            $cartItems = []; // Initialize as empty if not a valid array
        }

        $result = [];
        $totalCartPrice = 0; 

        // Iterate through each store in the cart
        foreach ($cartItems as $storeId => $products) {
            $store = Store::find($storeId);
            if (!$store) {
                continue; // Skip if the store does not exist
            }

            $storeData = [
                'store_id' => $store->id,
                'store_name' => $store->store_name,
                'store_image' => $store->image,
                'products' => [],
                'store_total_price' => 0,
              
            ];

            // Iterate through products (structured as product_id => quantity)
            foreach ($products as $productId => $quantity) {
                $product = Product::find($productId);
                if (!$product) {
                    continue; // Skip if the product does not exist
                }

                $product_price = $product->discounted_price ?? $product->price;
                // Calculate product total based on quantity and price
                $productTotal = $product_price * $quantity;

                // Prepare product data
                $productData = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_cover_image' => $product->cover_image,
                    'product_background_image' => $product->background_image,
                    'product_description' => $product->description,
                    'product_price' => $product->price,
                    'product_discounted_price' => $product->discounted_price,
                    'discount_type' => $product->discount_type,
                    'discount_value' => $product->discount_value,
                    'rating' => $product->rating,
                    'parent_id' => $product->parent_id,
                    'color' => $product->color,
                    'material' => $product->material,
                    'style' => $product->style,
                    'gender' => $product->gender,
                    'capacity' => $product->capacity,
                    'weight' => $product->weight,
                    'quantity' => $quantity,
                    'product_total' => $productTotal
                ];

                // Add product total to the store's total price before fee
                $storeData['store_total_price'] += $productTotal;

                // Add product data to the store data
                $storeData['products'][] = $productData;
            }

           
            $totalCartPrice += $storeData['store_total_price'];
          

            // Add store data to the result
            $result[] = $storeData;
        }

        // Return cart data with total prices before and after fee
        return [
            'cart_total_price' => $totalCartPrice,
            'stores' => $result
        ];
    }


}

