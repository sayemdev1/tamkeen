<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStoreRequest;
use App\Http\Requests\UpdateStoreRequest;
use App\Http\Resources\StoreResource;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class StoreController extends Controller
{
    public function index()
    {
        $stores = Store::select('id','store_name','location','type','working_hours','owner_id')->get();


        return response()->json(['stores' => StoreResource::collection($stores)],200);

    }

    public function store(StoreStoreRequest $request)
    {
        // Validate and get the validated data
        $validatedData = $request->validated();

        // Handle base64 image
        if ($request->has('image')) {
            // Remove base64 prefix if present
            $base64Image = preg_replace('/^data:image\/\w+;base64,/', '', $request->image);

            // Decode the base64 image data
            $imageData = base64_decode($base64Image);

            // Determine the MIME type and file extension
            $imageType = finfo_buffer(finfo_open(), $imageData, FILEINFO_MIME_TYPE);
            $extension = str_replace('image/', '', $imageType) ?: 'png';
            $imageName = uniqid() . '.' . $extension;
            $path = "store_images/{$imageName}";

            // Store the decoded image in the public directory
            Storage::disk('public')->put($path, $imageData);

            // Set the image path in the validated data
            $validatedData['image'] = "storage/" . $path;
        }

        // Create the store with the validated data
        $store = Store::create($validatedData);

        // Return a JSON response with the created store
        return response()->json($store, 201);
    }



    public function show(Store $store)
    {
        // $user = auth()->user();
        // if ($user->id !== $store->owner_id) {
        //     return response()->json(['message' => 'Unauthorized'], 401);
        // }

        // Count distinct users who have orders from the store
        $followers = $store->orders()
            ->distinct('user_id')
            ->count('user_id');

            $rating = $store->orders()->avg('review');
        
        return response()->json([
            'message' => 'Store fetched successfully',
            'store' => [
                'data' => $store,
                'personal_phone' => $store->owner->phone,
                'personal_email' => $store->owner->email,
                'followers' => $followers,
                'rating' => $store->getReviewAttribute($rating)
            ]
        ], 200);
    }

   
    public function update(UpdateStoreRequest $request, Store $store)
    {
        $store->update($request->validated());
        return response()->json(['message' => 'Store updated successfully', 'store' => $store], 200);
    }

   
    public function destroy(Store $store)
    {
        $store->delete();
        return response()->json(['message' => 'Store deleted successfully']);
    }


 
    public function similarStores(Store $store)
    {
        // Fetch all products of the given store and their categories
       $productCategories = $store->products()->with('categories')->get()
    ->pluck('categories.*.id') // Get category IDs from the many-to-many relationship
    ->flatten() // Flatten nested arrays
    ->unique() // Ensure categories are unique
    ->toArray(); // Convert to array
        // / Convert to array

        // Fetch stores that have products in the same categories
        $similarStores = Store::whereHas('products.categories', function ($query) use ($productCategories) {
            $query->whereIn('product_categories.id', $productCategories);
        })
        ->where('id', '!=', $store->id) // Exclude the current store
        ->get();



        return response()->json([
            'store' => $store->store_name,
            'similar_stores' => $similarStores
        ]);
    }




    ///// all stats for saler 

    public function stats($storeId = null)

    {
        $user = Auth::user();
        $store = $user->hasRole('admin') && $storeId ? Store::find($storeId) : $user->store;

        if (!$store) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $sales = $store->orders()->sum('total_price');
        $costs =  $store->products()->sum('base_price');
        $profits = $sales - $costs;
        return response()->json(['sales' => $sales, 'costs' => $costs, 'profits' => $profits]);
    }


    public function dailyRevenue($storeId = null)
    {

        $user = Auth::user();
        // $store = $user->hasRole('admin') && $storeId ? Store::find($storeId) : $user->store;

        $store = $storeId ? Store::find($storeId) : $user->store;

        if (!$store) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        // Retrieve orders and group by day
        $dailyRevenue = $store->orders()
            ->where('order_status', 'completed')
            ->selectRaw('SUM(total_price) as revenue, DATE_FORMAT(created_at, "%Y-%m-%d") as day')
            ->groupBy('day')
            ->orderBy('day', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'day' => $item->day,
                    'revenue' => $item->revenue,
                ];
            });

        // Calculate increases and decreases compared to the previous day
        $revenueData = [];
        $previousRevenue = null;

        foreach ($dailyRevenue as $dayData) {
            $change = null;
            $changePercentage = null;

            if ($previousRevenue !== null) {
                $change = $dayData['revenue'] - $previousRevenue;
                $changePercentage = round(($change / $previousRevenue) * 100, 2);
            }

            $revenueData[] = [
                'day' => $dayData['day'],
                'revenue' => $dayData['revenue'],
                'change' => $change,
                'change_percentage' => $changePercentage
            ];

            $previousRevenue = $dayData['revenue'];
        }

        return response()->json($revenueData);
    }


    public function salesPercentageByCountry($storeId = null)
    {
        $user = Auth::user();

        $store = $user->hasRole('admin') && $storeId ? Store::find($storeId) : $user->store;

      if(!$store){
        return response()->json(['message' => 'Unauthorized'], 401);
    }
       
       
        // Calculate total sales for all countries
        $totalSales = $store->orders()
            ->where('order_status', 'completed')
            ->sum('total_price');

        // Join orders with address to get sales grouped by country
        $salesByCountry = $store->orders()
            ->where('order_status', 'completed')
            ->join('addresses', 'orders.address_id', '=', 'addresses.id')
            ->selectRaw('addresses.country, SUM(orders.total_price) as revenue')
            ->groupBy('addresses.country')
            ->get()
            ->map(function ($item) use ($totalSales) {
                return [
                    'country' => $item->country,
                    'sales' => $item->revenue,
                    'percentage' => $totalSales > 0 ? round(($item->revenue / $totalSales) * 100, 2) : 0
                ];
            });

        return response()->json($salesByCountry);
    }





}
