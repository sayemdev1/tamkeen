<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAddressRequest;
use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{

    public function index()
    {
        $user = auth()->user();

        return response(['addresses' => $user->addresses]);
    }

    public function store(StoreAddressRequest $request)
    {
        $user = auth()->user();
        $user->addresses()->create($request->validated());

        return response(['message' => 'Address created successfully'], 201);
    }

    public function update(StoreAddressRequest $request, Address $address)
    {
        $user = auth()->user();
        $address->update($request->validated());
        return response()->json(['message' => 'Address updated successfully']);
    }

    public function destroy(Address $address)
    {
        $address->delete();
        return response()->json(['message' => 'Address deleted successfully']);
    }
}
