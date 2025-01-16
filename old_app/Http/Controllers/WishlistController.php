<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
   ////// for user
    public function getAll()
    {
        $user = Auth::user();
        $wishes = $user->wishlists()->with('product')->get();
        return response()->json(['wishes' => $wishes]);
    }


    public function add(Request $request)
    {

        $user = Auth::user(); 
        if ($user->wishlists()->where('product_id', $request->product_id)->exists()) {
            return response()->json(['message' => 'product already in your wishlist']);
        }
        $wish = $user->wishlists()->create([
            'product_id' => $request->product_id
        ]);
     
        return response()->json(['product added successfully to your wishlist' => $wish]);
    }

    

    public function remove(Request $request)
    {

        $user = Auth::user();
        if(!$user->wishlists()->where('product_id', $request->product_id)->exists()){
            return response()->json(['message' => 'product not in your wishlist']);
        }
        $wish = $user->wishlists()->where('product_id', $request->product_id)->first();
        $wish->delete();
        return response()->json(['message' => 'product deleted from your wishlist successfully']);
    }


    //////////////////////////////////////////////////////////////////////

    //  public function update(Request $request, $id)
    // {
    //     //
    // }

    // public function destroy(Wishlist $wishlist)
    // {
     
    //     $wishlist->delete();
    //     return response()->json(['message' => 'Wishlist deleted successfully']);
    // }
}
