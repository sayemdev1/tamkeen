<?php

namespace App\Http\Controllers;

use App\Http\Requests\PackageRequest;
use App\Http\Requests\PackageReviewRequest;
use App\Models\Package;
use App\Models\PackageItem;
use App\Models\PackageUser;
use App\Models\PackageUserReview;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\CouponService;
use Illuminate\Support\Facades\Auth;

class PackageController extends Controller
{
  
    public function index()
    {
         $user = Auth::user();
         if($user->hasRole('admin')) {
            $packages = Package::select('id', 'name', 'total_price', 'number_of_uses')->get();
                    return response()->json($packages);


         }

         $packages = $user->store->packages()->select('id', 'name', 'total_price', 'number_of_uses')->get();

      if ($packages->isEmpty()) {
    return response()->json('This store does not have any packages');
     }

       return response()->json($packages);

    }

    public function store(PackageRequest $request, $storeId = null)
    {
        $validated = $request->validated();

        $user = Auth::user();
        $store = $user->hasRole('admin') && $storeId ? Store::find($storeId) : $user->store;

        if (!$store) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Create a new package
        $package = Package::create([
            'name' => $validated['package_name'],
            'total_price' => $validated['total_price'], // Will calculate total later
            'number_of_uses' => $validated['number_of_uses'],
            'store_id' => $store->id
          
        ]);

        // Loop through the products and create package items
        foreach ($validated['products'] as $productData) {
            $product = Product::findOrFail($productData['id']);

            // Create package item with custom quantity and price
            $packageItem = new PackageItem([
                'product_id' => $product->id,
                'quantity' => $productData['quantity'],
            ]);
            $package->items()->save($packageItem);
          
        }

        if ($request->has('images')) {
            foreach ($request->images as $base64Image) {
                // Remove the base64 prefix if present
                $base64Image = preg_replace('/^data:image\/\w+;base64,/', '', $base64Image);

                // Decode the base64 image data
                $imageData = base64_decode($base64Image);

                // Determine the MIME type and file extension (or default to 'png')
                $imageType = finfo_buffer(finfo_open(), $imageData, FILEINFO_MIME_TYPE);
                $extension = str_replace('image/', '', $imageType) ?: 'png';
                $imageName = uniqid() . '.' . $extension;
                $path = "packages/{$imageName}";

                // Store the decoded image in 'public/packages' directory
                Storage::disk('public')->put($path, $imageData);

                // Create a record in the images table
                $package->images()->create([
                    'image' => "storage/" . $path,
                ]);

                $imagePaths[] = "storage/" . $path;
            }
        }

     

        return response()->json(['message' => 'Package created successfully', 'package' => $package, 'images' => $imagePaths], 201);
    }

 
    public function show(Package $package)
    {
        $package->load(['items.product.images', 'images' => function ($query) {
            $query->select('package_id', 'image'); // assuming 'package_id' is the foreign key in the images table
        }]);

        // Map images to only include the 'image' field
        $package->images = $package->images->pluck('image');
        if($user = Auth::guard('api')->user())
        {
           $purchaseStatus = PackageUser::where('user_id',$user->id)->exists();
            return response()->json(['basket'=> $package , 'purshased'=> $purchaseStatus ],200);

           
        }
       
                   return response()->json(['basket'=> $package],200);
    }


    public function update(Request $request, Package $package)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Package  $package
     * @return \Illuminate\Http\Response
     */
    public function destroy(Package $package)
    {
        $package->delete();
        return response()->json(['success' => true],200);
    }

    ////backet
    public function checkoutForPackage(Request $request, Package $package, CouponService $couponService)
    {
        $couponCode = $request->input('coupon_code');
        $totalPrice = $package->total_price;

        if ($couponCode) {
            $couponData = $couponService->applyCouponForBasket($couponCode, $totalPrice);

            $data = PackageUser::create([
                'package_id' => $package->id,
                'user_id' => auth()->id(),
                'coupon_id' => $couponData['coupon']->id,
                'price_of_package_before_coupon' => $totalPrice,
                'price_of_package_after_coupon' => $couponData['discounted_price'],
            ]);

            return response()->json([
                'message' => 'Package subscribed successfully',
                'data' => $data
            ]);
        }

        $data = PackageUser::create([
            'package_id' => $package->id,
            'user_id' => auth()->id(),
            'coupon_id' => null,
            'price_of_package_before_coupon' => $totalPrice,
            'price_of_package_after_coupon' => $totalPrice,
        ]);

        return response()->json([
            'message' => 'Package subscribed successfully',
            'data' => $data
        ]);
    }

    public function reviewPackage(PackageReviewRequest $request, Package $package)
    {
        $validated = $request->validated();
        $review = new PackageUserReview();
        $review->package_id = $package->id;
        $review->user_id = auth()->user()->id;
        $review->review = $validated['review'];
        $review->rating = $validated['rating'];
        $review->save();

        return response()->json([
            'message' => 'Package reviewed successfully',
            'data' => $review
        ]);

    }

    public function getReviews( Package $package)
    {
        $reviews = $package->reviews()->with('user')->get();
        return response()->json($reviews,200);
    }

    public function getOrdersOfPackageForUser()
    {
        $user = Auth::user();
       $orders = PackageUser::where('user_id',$user->id)->get();

       return response()->json($orders,200);
      


    }

    public function getOrdersOfPackage($storeId = null)
    {
        $user = Auth::user();
        $store = $user->hasRole('admin') && $storeId ? Store::find($storeId) : $user->store;

        if (!$store) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

       $packages = $store->packages()->get();

       $orders = [];
       foreach ($packages as $package) {
           $orders[] = PackageUser::where('package_id',$package->id)->get();
       }

       return response()->json($orders,200);
    }


   

}