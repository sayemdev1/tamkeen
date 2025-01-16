<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class   CompaniesController extends Controller
{
    public function index()
    {

        $store = Auth::user()->store;
        $companies = $store->companies;
        return response()->json($companies);

    }

    public function show(Company $company)
    {
        return response()->json($company);
    }

    public function store(StoreCompanyRequest $request)
    {
        $storeId = Auth::user()->store->id;
        $company = Company::create($request->validated() + ['store_id' => $storeId]);
        return response()->json(['message' => 'Company created successfully' , 'company' => $company]);
    }

    public function update(UpdateCompanyRequest $request, Company $company)
    {
        $company->update($request->validated());
        return response()->json(['message' => 'Company updated successfully', 'company' => $company]);
    }

    public function destroy(Company $company)
    {
        $company->delete();
        return response()->json(['message' => 'Company deleted successfully']);
    }

}