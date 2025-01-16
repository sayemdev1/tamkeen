<?php

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use App\Http\Requests\StoreProductVariantRequest;
use App\Http\Requests\UpdateProductVariantRequest;

class ProductVariantController extends Controller
{
    public function index()
    {
        return ProductVariant::all();
    }

    public function store(StoreProductVariantRequest $request)
    {
        return ProductVariant::create($request->validated());
    }

    public function show(ProductVariant $productVariant)
    {
        return $productVariant;
    }

    public function update(UpdateProductVariantRequest $request, ProductVariant $productVariant)
    {
        $productVariant->update($request->validated());
        return $productVariant;
    }

    public function destroy(ProductVariant $productVariant)
    {
        $productVariant->delete();
        return response()->noContent();
    }
}
