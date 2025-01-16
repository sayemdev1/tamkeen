<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Http\Requests\BannerRequest;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    // Display a listing of the banners
    public function index()
    {
        $banners = Banner::select('id', 'title', 'description', 'image')->get();
        return response()->json($banners);
    }

    // Store a newly created banner in storage
    public function store(BannerRequest $request)
    {
        // The validated data is automatically available from the BannerRequest
        $validatedData = $request->validated();

        // Handle base64 image upload
        if ($request->has('image')) {
            $validatedData['image'] = $this->storeBase64Image($request->image);
        }

        // Create the banner
        $banner = Banner::create($validatedData);

        return response()->json($banner, 201);
    }

    // Display the specified banner
    public function show(Banner $banner)
    {
        return response()->json($banner);
    }

    // Update the specified banner in storage
    public function update(BannerRequest $request, Banner $banner)
    {
        // The validated data is automatically available from the BannerRequest
        $validatedData = $request->validated();

        // Handle base64 image update if provided
        if ($request->has('image')) {
            $validatedData['image'] = $this->storeBase64Image($request->image);
        }

        // Update the banner
        $banner->update($validatedData);

        return response()->json($banner);
    }

    // Delete the specified banner
    public function destroy(Banner $banner)
    {
        $banner->delete();
        return response()->json(null, 204);
    }

    // Helper method to handle base64 image storage
    private function storeBase64Image($base64Image)
    {
        // Remove base64 prefix if present
        $base64Image = preg_replace('/^data:image\/\w+;base64,/', '', $base64Image);

        // Decode the base64 image data
        $imageData = base64_decode($base64Image);

        // Determine the MIME type and file extension
        $imageType = finfo_buffer(finfo_open(), $imageData, FILEINFO_MIME_TYPE);
        $extension = str_replace('image/', '', $imageType) ?: 'png';
        $imageName = uniqid() . '.' . $extension;
        $path = "banner_images/{$imageName}";

        // Store the decoded image in the public directory
        Storage::disk('public')->put($path, $imageData);

        // Return the image path
        return "storage/" . $path;
    }
}
