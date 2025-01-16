<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function store(StoreContactRequest $request)
    {
     
        Contact::create($request->validated());

        return response()->json(['message' => 'Contact created successfully']);
    }
}
