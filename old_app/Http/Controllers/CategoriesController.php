<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Models\CategoryImage;
use App\Models\OrderProduct;
use App\Models\ProductCategory;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoriesController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index()
    {
        $categories = ProductCategory::query()
        // ::whereNull('parent_id')
        ->withCount('products')
        ->with([
            'parent',
            'images' => function ($query) {
                $query->select('category_id', 'image'); // assuming 'product_category_id' is the foreign key in the images table
            },
            'children.images' => function ($query) {
                $query->select('category_id', 'image');
            }
        ])
            ->get();

        // Map images for each category and its children to include only the 'image' field
        $categories->each(function ($category) {
            $category->images = $category->images->pluck('image');
            $category->children->each(function ($child) {
                $child->images = $child->images->pluck('image');
            });
        });

        return response()->json($categories);
    }



    public function store(StoreCategoryRequest $request)
    {
        $category = ProductCategory::create($request->validated());

        $imagePaths = [];
        if ($request->has('images')) {
            foreach ($request->images as $base64Image) {

                // Remove base64 prefix if present
                $base64Image = preg_replace('/^data:image\/\w+;base64,/', '', $base64Image);

                // Decode the base64 image data
                $imageData = base64_decode($base64Image);

                // Check MIME type and determine file extension
                $imageType = finfo_buffer(finfo_open(), $imageData, FILEINFO_MIME_TYPE);
                $extension = str_replace('image/', '', $imageType);
                $imageName = uniqid() . '.' . $extension;
                $imagePath = "category_images/{$imageName}";
                Storage::disk('public')->put($imagePath, $imageData);

                // Save the new image path in the category_images table
                CategoryImage::create([
                    'category_id' => $category->id,
                    'image' => "storage/" . $imagePath,
                ]);

                // Add the new image path to the array
                $imagePaths[] = "storage/" . $imagePath;
            }
        }
 
        return response()->json([
            'category' => $category,
            'images' => $imagePaths
        ], 201);
    }


       public function show(ProductCategory $category)
    {

        return response()->json(['category' => $category->load('images')], 200);
        
    }
    public function update(StoreCategoryRequest $request, ProductCategory $category)
    {
        // Update the category data
        $category->update($request->validated());

        // Array to hold new image paths
        $imagePaths = [];

        // Check if there are new images to update
        if ($request->has('images')) {
           

            foreach ($request->images as $base64Image) {

                // Remove base64 prefix if present
                $base64Image = preg_replace('/^data:image\/\w+;base64,/', '', $base64Image);

                // Decode the base64 image data
                $imageData = base64_decode($base64Image);

                // Check MIME type and determine file extension
                $imageType = finfo_buffer(finfo_open(), $imageData, FILEINFO_MIME_TYPE);
                $extension = str_replace('image/', '', $imageType);
                $imageName = uniqid() . '.' . $extension;
                $imagePath = "category_images/{$imageName}";
                Storage::disk('public')->put($imagePath, $imageData);

                // Save the new image path in the category_images table
                CategoryImage::create([
                    'category_id' => $category->id,
                    'image' => "storage/" . $imagePath,
                ]);

                // Add the new image path to the array
                $imagePaths[] = "storage/" . $imagePath;
            }
        }

        return response()->json([
            'message' => 'Category updated successfully',
            'category' => $category,
            'images' => $imagePaths,  // Return the new images
        ], 200);
    }


    public function destroy(ProductCategory $category)
    {
        $category->delete();
        return response()->json(['message' => 'Category deleted successfully']);
    }

    public function ordersOfCategory(ProductCategory $category)
    {
        $productIds = $category->products()->pluck('products.id');

        $totalOrders = $this->orderService->getTotalOrders($productIds);

        return response()->json([
            'category_id' => $category->id,
            'total_orders' => $totalOrders,
        ]);
    }

    public function orderChangesOfCategory(ProductCategory $category)
    {
        // Calculate order changes
        $orderChanges = $this->orderService->calculateOrderChanges($category);

        return response()->json([
            'category_id' => $category->id,
            'total_orders_last_7_days' => $orderChanges['total_orders_last_7_days'],
            'total_orders_before_last_7_days' => $orderChanges['total_orders_before_last_7_days'],
            'change' => $orderChanges['change'],
            'percentage_change' => $orderChanges['percentage_change'],
            'status' => $orderChanges['status'],
        ]);
    }



    public function showProducts(ProductCategory $category)
    {
        $orders = $category->products()->get();
        return response()->json($orders);
    }
}
