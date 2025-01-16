<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Http\Requests\RatingRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductImage;
use App\Models\Rating;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{

    ////// for saler
    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole('admin')) {

            // Fetch top-level products (parent_id = null) and include child products
            $products = Product::whereNull('parent_id')
            ->with(['images', 'ratings', 'variants' => function ($query) {
                $query->with(['images', 'ratings']);  // Load images and ratings for child products
            }])
                ->get();

            return response()->json($products);
        }

        // For non-admin users, fetch products for the user's store where parent_id is null
        if(!$user->store) {
            return response()->json(['message' => 'Store not found'], 404);
        }
        $products = $user->store->products()
        ->whereNull('parent_id')
        ->with(['images',
            'ratings',
            'variants' => function ($query) {
                $query->with(['images', 'ratings']);  // Load images and ratings for child products
            }
        ])
        ->get();

        return response()->json($products);
    }


    public function store(StoreProductRequest $request)
    {
        $user = Auth::user();
        $storeId =  $user->store->id;
        // Decode validated data from 'product_data'
        $validatedProduct = $request->validated();

        // Process cover_image if provided
        if ($request->has('cover_image')) {
            $validatedProduct['cover_image'] = $this->processBase64Image($request->cover_image, 'product_images');
        }

        // Process background_image if provided
        if ($request->has('background_image')) {
            $validatedProduct['background_image'] = $this->processBase64Image($request->background_image, 'product_images');
        }

        $validatedProduct['store_id'] = $storeId;
        // Create the product using validated data
        $product = Product::create($validatedProduct);

        // Process each image in the images array
        if ($request->has('images')) {
            foreach ($request->images as $imageBase64) {
                // Process and save the image, returning its path
                $imagePath = $this->processBase64Image($imageBase64, 'product_images');

                // Create a new ProductImage record
                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $imagePath,
                ]);
            }
        }

        // Attach categories
        $categoryIds = $request->input('category_ids');
        if ($categoryIds) {
            $product->categories()->attach($categoryIds);
        }

        // Make base_price visible and return response
        $product->makeVisible('base_price');

        return response()->json([
            'product' => $product->load('images'),
            'message' => 'Product created successfully'
        ], 201);
    }

   
    protected function processBase64Image($base64Image, $directory)
    {
        $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $base64Image);
        $imageData = base64_decode($imageData);

        // Determine file extension
        $imageType = finfo_buffer(finfo_open(), $imageData, FILEINFO_MIME_TYPE);
        $extension = str_replace('image/', '', $imageType) ?: 'png';
        $imageName = uniqid() . '.' . $extension;
        $imagePath = "$directory/$imageName";

        // Store image
        Storage::disk('public')->put($imagePath, $imageData);

        return "storage/" . $imagePath;
    }


    public function show(Product $product)
    {
        // Load related data
        $product->load(['images', 'ratings']);


        if (!$product->parent) {
            $product->load('variants.images');
            unset($product->parent);
        }

        // Initialize response data
        $responseData = [
            'message' => 'Product fetched successfully',
            'product' => $product,
        ];

        // Check if the user is authenticated
        if ($user = Auth::guard('api')->user()) { // Retrieve the authenticated user with JWT


            // Check if the product is added to wishlist
            $wishlistStatus = $user->wishlists()->where('product_id', $product->id)->exists();

            // Check if the product is added to cart
            $cartStatus = false; // Default to false
            $cart = $user->cart()->first(); // Get the user's cart
            if ($cart && $cart->items) {
                $items = json_decode($cart->items, true); // Decode the JSON items
                // Check if the product ID is in the items array
                foreach ($items as $cartKey => $products) {
                    if (array_key_exists($product->id, $products)) {
                        $cartStatus = true; // Product is found in cart
                        break; // No need to check further
                    }
                }
            }

            // Check if the product is purchased
            $purchaseStatus = $user->orders()->whereHas('products', function ($query) use ($product) {
                $query->where('product_id', $product->id);
            })->exists();

            // Add statuses to the response
            $responseData['product'] = array_merge($responseData['product']->toArray(), [
                'added_to_wishlist' => $wishlistStatus,
                'added_to_cart' => $cartStatus,
                'purchased' => $purchaseStatus,
            ]);
        }

        return response()->json($responseData, 200);
    }


    public function update(UpdateProductRequest $request, Product $product)
    {
        // Decode validated data from the request
        $validatedProduct = $request->validated();

        // Process cover image if provided
        if ($request->has('cover_image')) {
            $validatedProduct['cover_image'] = $this->processBase64Image($request->cover_image, 'product_images');
        }

        // Process background image if provided
        if ($request->has('background_image')) {
            $validatedProduct['background_image'] = $this->processBase64Image($request->background_image, 'product_images');
        }

        // Update the product using validated data
        $product->update($validatedProduct);

        // Process each image in the images array
        if ($request->has('images')) {
            foreach ($request->images as $base64Image) {
                $imagePath = $this->processBase64Image($base64Image, 'product_images');

                // Save the new image path in the ProductImage table
                ProductImage::create([
                    'product_id' => $product->id, 
                    'image' =>  $imagePath,
                ]);
            }
        }

        // Return response with the updated product
        return response()->json([
            'product' => $product->load('images'), // Load images for the response
            'message' => 'Product updated successfully'
        ], 200);
    }
    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json(['message' => 'Product deleted successfully']);
    }



    public function showProducts(Request $request)
    {
        // Start the product query
        $query = Product::query();

        $query->whereNull('parent_id')->with('variants');

        // Retrieve and filter by category IDs if provided
        $categoryIds = $request->query('category_id'); // Expecting a single ID or an array
        if (!empty($categoryIds)) {
            // If category_ids is a comma-separated string, convert it to an array
            if (is_string($categoryIds)) {
                $categoryIds = explode(',', $categoryIds);
            }

            $query->whereHas('categories', function ($query) use ($categoryIds) {
                $query->whereIn('category_product.category_id', $categoryIds); // Filter based on category IDs
            });
        }

        // Filter by minimum price if provided
        $minPrice = $request->query('min_price');
        if (!empty($minPrice)) {
            $query->where('price', '>=', $minPrice);
        }

        // Filter by maximum price if provided
        $maxPrice = $request->query('max_price');
        if (!empty($maxPrice)) {
            $query->where('price', '<=', $maxPrice);
        }

        // Filter by colors if provided
        $colors = $request->query('color'); // Expecting a comma-separated string
        if (!empty($colors)) {
            $colorsArray = explode(',', $colors);
           $query->whereIn('color', $colorsArray); 
            };
        

        // Filter by sizes if provided
        $sizes = $request->query('size'); // Expecting a comma-separated string
        if (!empty($sizes)) {
            $sizesArray = explode(',', $sizes);
        
            $query->whereIn('size', $sizesArray);
            
        }

        // $products = $query->with('variants', 'stockCheck')->get();


        // Execute the query and get the products
        $products = $query->withStockCheck()->paginate(10);

        // Return the filtered or unfiltered products
        return response()->json($products);
    }



    public function showProductsForStore(Store $store, Request $request)
    {
        $categoryIds = $request->input('category_ids'); // Array of category IDs

        // Start a query for the products of the specified store
        $query = Product::where('store_id', $store->id)->whereNull('parent_id')->with('images','variants.images'); // Only products belonging to this store

        // Filter by category IDs if provided
        if (!empty($categoryIds) && is_array($categoryIds)) {
            $query->whereHas('categories', function ($query) use ($categoryIds) {
                $query->whereIn('category_product.category_id', $categoryIds); // Use the pivot table to filter
            });
        }

        // Execute the query and get the products
        $products = $query->withStockCheck()->get();

        // Return the filtered or unfiltered products
        return response()->json(['products' => $products]);
    }


    public function bestSelling()
    {
        // Fetch best-selling products and calculate total sales
        $bestSellingProducts = Product::withCount('orders')
        ->whereNull('parent_id')
        ->with('variants.images', 'images')
        ->withStockCheck()
           ->get()
            ->map(function ($product) {
                // Calculate total sales from the pivot table
                $totalSales = $product->orders->sum('pivot.quantity');

                // Return an associative array with the desired fields
                return $this->formatProductResponse($product, $totalSales);
            })
            ->sortByDesc('total_sales') // Sort products by total sales in descending order
            ->take(10); // Limit to top 10 best sellers

            
        // Return the best-selling products as a JSON response
        return response()->json($bestSellingProducts->values());
    }
    
    
    public function dashboardBestSelling()
    {
        $user = Auth::user();
        if ($user->hasRole('admin')) {
            if(request()->query('store_id')){
                $products = Product::where('store_id', request()->query('store_id'));
            }else{
                $products = Product::query();    
            }
            
        }
        else if(!$user->store) {
            return response()->json(['message' => 'Store not found'], 404);
        }else{
            $products = $user->store->products();
        }
        $bestSellingProducts = $products->withCount('orders')
        ->whereNull('parent_id')
        ->with('variants.images', 'images')
        ->withStockCheck()
           ->get()
            ->map(function ($product) {
                // Calculate total sales from the pivot table
                $totalSales = $product->orders->sum('pivot.quantity');

                // Return an associative array with the desired fields
                return $this->formatProductResponse($product, $totalSales);
            })
            ->sortByDesc('total_sales') // Sort products by total sales in descending order
            ->take(10); // Limit to top 10 best sellers

            
        // Return the best-selling products as a JSON response
        return response()->json($bestSellingProducts->values());
    }


    private function formatProductResponse($product, $totalSales)
    {
        return [
            'id' => $product->id,
            'store_id' => $product->store_id,
            'parent_id' => $product->parent_id,
            'name' => $product->name,
            'description' => $product->description,
            'price' => $product->price,
            'stock' => $product->stock,
            'cover_image' => $product->cover_image,
            'background_image' => $product->background_image,
            'color' => $product->color,
            'size' => $product->size,
            'capacity' => $product->capacity,
            'material' => $product->material,
            'weight' => $product->weight,
            'style' => $product->style,
            'discount_type' => $product->discount_type,
            'discount_value' => $product->discount_value,
            'discounted_price' => $product->discounted_price,
            'parent_images'=> $product->images,
            'rating' => $product->rating,
            'orders_count' => $product->orders_count,
            'total_sales' => $totalSales,
            'variants' => $product->variants,

        ];
    }

    public function getLastProducts(Request $request)
    {
        $lastProducts = Product::orderBy('created_at', 'desc')->withStockCheck()
            ->whereNull('parent_id')
            ->with('images','variants.images')
            ->take(4)
            ->get();

        return response()->json($lastProducts);
    }

    public function rateProduct(Product $product, RatingRequest $request)
    {
        // Validate the request input
        $validated = $request->validated();

        // Add the authenticated user's ID and the product's ID to the validated data
        $validated['user_id'] = auth()->id();
        $validated['product_id'] = $product->id;

        // Create the rating for the product
        $rating = Rating::create($validated);

        // Calculate the average rating
        $averageRating = $product->ratings()->avg('rating');

        // Update the product's rating
        $product->update(['rating' => $averageRating]);

        // Return a response with the created rating and the updated average rating
        return response()->json([
            "rating" => $rating,
        ], 201);
    }

    public function happyCustomers()
    {
        $bestSellingProducts = Product::with(['ratings', 'orders']) // Load ratings and orders relationships
            ->withCount('orders') // Count the number of orders for each product
            ->get()
            ->map(function ($product) {
                // Calculate total sales from the pivot table
                $totalSales = $product->orders->sum('pivot.quantity');

                // Get the highest rating for the product
                $highestRating = $product->ratings()->orderByDesc('rating')->first();

                if (!$highestRating) {
                    return null;
                }

                // Format the response, including highest rating and total sales
                return $highestRating->load('user');
            })
            ->filter()
            ->sortByDesc('total_sales') // Sort products by total sales in descending order
            ->take(5); // Limit to top 5 best-selling products

        // Return the top 5 best-selling products as a JSON response
        return response()->json($bestSellingProducts->values(), 200);
    }

    public function flashSale()
    {
        $products = Product::where('discount_value', '>', 0)->whereNull('parent_id')->with('images','variants.images')->withStockCheck()->get();
        return response()->json($products);
    }
}
