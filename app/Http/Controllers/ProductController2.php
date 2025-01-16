<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Http\Requests\RatingRequest;
use App\Http\Requests\StoreProductRequest1;
use App\Http\Requests\UpdateProductRequest1;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Rating;
use App\Models\Store;
use App\Models\User;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductController2 extends Controller
{

    ////// for saler
    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole('admin')) {

            // Fetch top-level products (parent_id = null) and include child products
            $products = Product::whereNull('parent_id')
                ->with([
                    'images',
                    'ratings',
                    'variants' => function ($query) {
                        $query->with(['images', 'ratings']);  // Load images and ratings for child products
                    }
                ])
                ->get();

            return response()->json($products);
        }

        // For non-admin users, fetch products for the user's store where parent_id is null
        if (!$user->store) {
            return response()->json(['message' => 'Store not found'], 404);
        }
        $products = $user->store->products()
            ->whereNull('parent_id')
            ->with([
                'images',
                'ratings',
                'variants' => function ($query) {
                    $query->with(['images', 'ratings']);  // Load images and ratings for child products
                }
            ])
            ->get();

        return response()->json($products);
    }

public function store(StoreProductRequest1 $request)
{
    $user = Auth::user();

    // Determine the store ID
    $storeId = $user->store ? $user->store->id : 15;

    // Decode validated data from the request
    $validatedProduct = $request->validated();

    // Initialize the highest selling price
    $highestSellingPrice = 0;
    $stockM = 0;

    // Initialize the product variable
    $product = null;

    // Handle cover_image and background_image (direct file upload)
    if ($request->hasFile('cover_image')) {
        $validatedProduct['cover_image'] = $this->processUploadedFile($request->file('cover_image'), 'product_images');
    }

    if ($request->hasFile('background_image')) {
        $validatedProduct['background_image'] = $this->processUploadedFile($request->file('background_image'), 'product_images');
    }

    // If variants are provided in the request
    if ($request->has('variants') && count($request->variants) > 0) {
        foreach ($request->variants as $index => $variant) {
            // Process the main product for the first variant
            if ($index === 0) {
                $validatedProduct = array_merge($validatedProduct, [
                    'price' => $variant['selling_price'],
                    'base_price' => $variant['base_price'],
                    'store_id' => $storeId,
                ]);
                
                $stockM += $variant['stock'];

                // Create the main product
                $product = Product::create($validatedProduct);

                // Handle additional images for the main product
                if ($request->hasFile('images')) {
                    foreach ($request->file('images') as $imageFile) {
                        $imagePath = $this->processUploadedFile($imageFile, 'product_images');
                        ProductImage::create([
                            'product_id' => $product->id,
                            'image' => $imagePath,
                        ]);
                    }
                }
            }

            // Handle background_image for variants
            if (isset($variant['background_image_file'])) {
                $variant['background_image'] = $this->processUploadedFile($variant['background_image_file'], 'product_images');
            }

            // Check if this variant has the highest selling price
            if (isset($variant['selling_price']) && $variant['selling_price'] > $highestSellingPrice) {
                $highestSellingPrice = $variant['selling_price'];
            }

            // Create child product
            $childProduct = Product::create([
                'name' => $product->name,
                'description' => $product->description,
                'price' => $variant['selling_price'],
                'base_price' => $variant['base_price'],
                'stock' => $variant['stock'],
                'track_stock' => $variant['track_stock'],
                'track_stock_number' => $variant['stock_number'],
                'store_id' => $storeId,
                'cover_image' => $variant['background_image'] ?? $product->cover_image,
                'size' => $variant['size'],
                'color' => $variant['color'],
                'material' => $variant['material'],
                'style' => $variant['style'],
                'gender' => $variant['gender'],
                'capacity' => $variant['capacity'],
                'weight' => $variant['weight'],
                'barcode' => $variant['barcode'],
                'qr_code' => $variant['qr_code'],
                'serial_number' => $variant['serial_number'],
                'parent_id' => $product->id,
                'discount_type' => $variant['discount_type'] ?? null,
                'discount_value' => $variant['discount_value'] ?? null,
                'start_date' => $variant['start_date'] ?? null,
                'end_date' => $variant['end_date'] ?? null,
            ]);

            // Handle additional images for child products
            if (isset($variant['images']) && !empty($variant['images'])) {
                foreach ($variant['images'] as $imageFile) {
                    $imagePath = $this->processUploadedFile($imageFile, 'product_images');
                    ProductImage::create([
                        'product_id' => $childProduct->id,
                        'image' => $imagePath,
                    ]);
                }
            }
        }

        // Update the main product's price to the highest selling price
        if ($product) {
            $product->update(['price' => $highestSellingPrice, 'stock' => $stockM]);
        }
    }

    // Attach categories if provided
    $categoryIds = $request->input('category_ids');
    if ($categoryIds) {
        $product->categories()->attach($categoryIds);
    }

    // Make base_price visible and return response
    $product->makeVisible('base_price');

    return response()->json([
        'product' => $product->load('images', 'categories', 'variants'),
        'message' => 'Product created successfully',
    ], 201);
}

/**
 * Handle file uploads and return the file path.
 *
 * @param \Illuminate\Http\UploadedFile $file
 * @param string $folder
 * @return string
 */
private function processUploadedFile($file, $folder)
{
    return $file->store($folder, 'public'); // Store in 'storage/app/public/{folder}'
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


public function update(UpdateProductRequest1 $request, Product $product)
{
    $user = Auth::user();

    // Decode validated data from the request
    $validatedProduct = $request->validated();

    // Process cover image if provided
    if ($request->hasFile('cover_image')) {
        $validatedProduct['cover_image'] = $request->file('cover_image')->store('product_images', 'public');
    }

    // Process background image if provided
    if ($request->hasFile('background_image')) {
        $validatedProduct['background_image'] = $request->file('background_image')->store('product_images', 'public');
    }

    // Update the main product
    $product->update($validatedProduct);

    // Process additional images if provided
    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $image) {
            $imagePath = $image->store('product_images', 'public');

            // Save the new image path in the ProductImage table
            ProductImage::create([
                'product_id' => $product->id,
                'image' => $imagePath,
            ]);
        }
    }

    // Process variants if provided
    if ($request->has('variants') && count($request->variants) > 0) {
        $highestSellingPrice = $product->price; // Track the highest selling price

        foreach ($request->variants as $variant) {
            // Find the existing variant or create a new one
            $variantProduct = Product::firstOrNew([
                'id' => $variant['id'] ?? null,
                'parent_id' => $product->id,
            ]);

            // Process variant background image if provided
            $variantBackgroundImage = $variantProduct->cover_image; // Default to existing
            if (isset($variant['background_image_file']) && $variant['background_image_file']->isValid()) {
                $variantBackgroundImage = $variant['background_image_file']->store('product_images', 'public');
            }

            // Update variant details
            $variantProduct->fill([
                'name' => $product->name,
                'description' => $product->description,
                'price' => $variant['selling_price'] ?? $product->price,
                'base_price' => $variant['base_price'] ?? $product->price,
                'stock' => $variant['stock'],
                'track_stock' => $variant['track_stock'] ?? 0,
                'track_stock_number' => $variant['stock_number'] ?? null,
                'store_id' => $user->store ? $user->store->id : $product->store_id,
                'cover_image' => $variantBackgroundImage,
                'size' => $variant['size'],
                'color' => $variant['color'],
                'material' => $variant['material'],
                'style' => $variant['style'],
                'gender' => $variant['gender'],
                'capacity' => $variant['capacity'],
                'weight' => $variant['weight'],
                'barcode' => $variant['barcode'],
                'qr_code' => $variant['qr_code'],
                'serial_number' => $variant['serial_number'],
                'discount_type' => $variant['discount_type'] ?? null,
                'discount_value' => $variant['discount_value'] ?? null,
                'start_date' => $variant['start_date'] ?? null,
                'end_date' => $variant['end_date'] ?? null,
            ])->save();

            // Process additional images for this variant if provided
            if (isset($variant['images']) && is_array($variant['images'])) {
                foreach ($variant['images'] as $variantImage) {
                    if ($variantImage->isValid()) {
                        $variantImagePath = $variantImage->store('product_images', 'public');

                        ProductImage::create([
                            'product_id' => $variantProduct->id,
                            'image' => $variantImagePath,
                        ]);
                    }
                }
            }

            // Track the highest selling price among the variants
            if (isset($variant['selling_price']) && $variant['selling_price'] > $highestSellingPrice) {
                $highestSellingPrice = $variant['selling_price'];
            }
        }

        // Update the main product's price to the highest selling price
        $product->update(['price' => $highestSellingPrice]);
    }

    // Attach categories if provided
    $categoryIds = $request->input('category_ids');
    if ($categoryIds) {
        $product->categories()->sync($categoryIds); // Update categories
    }

    // Return the updated product with related data
    return response()->json([
        'product' => $product->load('images', 'categories', 'variants'),
        'message' => 'Product updated successfully',
    ], 200);
}

    
    
    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json(['message' => 'Product deleted successfully']);
    }


public function show(Product $product)
{
    // Load related data
    $product->load(['images', 'ratings', 'categories']);

    if (!$product->parent) {
        $product->load('variants.images');
        unset($product->parent);
    }

    // Collect all images from product and variants
    $images = [];
    if ($product->cover_image) {
        $images[] = $product->cover_image;
    }
    if ($product->background_image) {
        $images[] = $product->background_image;
    }
    if ($product->variants) {
        foreach ($product->variants as $variant) {
            if ($variant->cover_image) {
                $images[] = $variant->cover_image;
            }
        }
    }

    // Ensure only unique values in the images array
    $images = array_unique($images);

    // Initialize response data
    $responseData = [
        'message' => 'Product fetched successfully',
        'product' => $product,
    ];

    unset($product->images);
    // Attach the images array to the product
    $product->images = $images;

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
        }
        ;


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
        $query = Product::where('store_id', $store->id)->whereNull('parent_id')->with('images', 'variants.images'); // Only products belonging to this store

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
            if (request()->query('store_id')) {
                $products = Product::where('store_id', request()->query('store_id'));
            } else {
                $products = Product::query();
            }

        } else if (!$user->store) {
            return response()->json(['message' => 'Store not found'], 404);
        } else {
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
            'parent_images' => $product->images,
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
            ->with('images', 'variants.images')
            ->take(4)
            ->get();

        return response()->json($lastProducts);
    }

public function all()
    {
        $lastProducts = Product::orderBy('created_at', 'desc')->withStockCheck()
            ->whereNull('parent_id')
            ->with('images', 'variants.images')
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
        $products = Product::where('discount_value', '>', 0)->whereNull('parent_id')->with('images', 'variants.images')->withStockCheck()->get();
        return response()->json($products);
    }
}
