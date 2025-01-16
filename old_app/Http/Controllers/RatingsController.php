<?php

namespace App\Http\Controllers;

use App\Http\Requests\RatingRequest;
use App\Http\Resources\RatingsResource;
use App\Models\Product;
use App\Models\Rating;
use Illuminate\Http\Request;

class RatingsController extends Controller
{
    public function rate(Product $product , RatingRequest $request)
    {

        $validated = $request->validated();
        $validated['user_id'] = auth()->id();
        $validated['product_id'] = $product->id;
       return response()->json(["rating" => Rating::create($validated)],201);
    }
    public function getReviewsOfProduct(Product $product)
    {

        return response()->json(["reviews" => RatingsResource::collection($product->ratings()->get()) ],200);
    }

}
