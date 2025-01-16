<?php

namespace App\Http\Controllers;

use App\Http\Requests\NewsletterRequest;
use App\Models\Newsletter;
use Illuminate\Http\Request;

class NewslettersController extends Controller
{
    public function index()
    {
        return response()->json(['newsletters' => Newsletter::where('is_active', 1)->get()]);
    }

    public function show(Newsletter $newsletter)
    {
        return response()->json($newsletter);
    }

    public function store(NewsletterRequest $request)
    {
        $validatedData = $request->validated();
        $newsletter = Newsletter::create($validatedData);
        return response()->json(['message' => 'Newsletter created successfully', 'newsletter' => $newsletter]);
    }

    public function destroy(Newsletter $newsletter)
    {
        $newsletter->delete();
        return response()->json(['message' => 'Newsletter deleted successfully']);
    }
}
