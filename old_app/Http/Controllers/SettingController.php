<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        return response()->json(['settings' => Setting::select('id', 'key', 'value')->get()]);
    }

    public function store(Request $request)
    {
        Setting::create($request->all());
        return response()->json(['message' => 'Setting created successfully']);
    }

    public function show(Setting $setting)
    {
        return response()->json(['setting' => $setting]);
    }

    public function update(Setting $setting, Request $request)
    {
        $setting->update($request->all());
        return response()->json(['message' => 'Setting updated successfully']);
    }

    public function destroy(Setting $setting)
    {
        $setting->delete();
        return response()->json(['message' => 'Setting deleted successfully']);
    }
}
