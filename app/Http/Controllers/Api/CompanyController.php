<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Rules\UniqueCompanyRule;
use App\Rules\UniqueRucRule;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = Company::where('user_id', auth()->id())->get();
        return response()->json([
            'company' => $companies,
        ], 200);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'razon_social'  => 'required|string|max:255',
            'ruc'           => [
                'required',
                'regex:/^(10|20)\d{9}$/',
                'digits:11',
                new UniqueRucRule(),
            ],
            'direccion'     => 'required|string|max:255',
            'telefono'      => 'nullable|string|max:20',
            'email'         => 'nullable|email|max:255',
            'logo'     => 'nullable|file|mimes:jpg,png,web|max:2048',
            'production'    => 'nullable|boolean',
            'sol_user'      => 'required|string',
            'sol_pass'      => 'required|string',
            'cert'     => 'required|file|mimes:pem,txt',
            'client_id'     => 'nullable|string',
            'client_secret' => 'nullable|string',
        ]);

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('logos');
        }

        $data['cert_path'] = $request->file('cert')->store('certs');
        $data['api_token'] = 'kipu_' . Str::random(40);
        $data['user_id'] = JWTAuth::user()->id;

        $company = Company::create($data);

        return response()->json([
            'message' => 'Empresa creada correctamente',
            'api_token' => $company->api_token,
            'company' => $company->makeHidden(['created_at', 'updated_at'])
        ], 201);
    }

    public function show($company)
    {
        $company = Company::where('ruc', $company)
            ->where('user_id', auth()->user()->id)
            ->first();
        return response()->json([
            'company' => $company->makeHidden(['created_at', 'updated_at'])
        ], 200);
    }

    public function update(Request $request, $company)
    {
        $company = Company::where('ruc', $company)
            ->where('user_id', auth()->user()->id)
            ->first();

        $data = $request->validate([
            'razon_social'  => 'nullable|string|max:255',
            'ruc'           => [
                'nullable',
                'string',
                'regex:/^(10|20)\d{9}$/',
                'digits:11',
                new UniqueRucRule($company->id),
            ],
            'direccion'     => 'nullable|string|max:255',
            'telefono'      => 'nullable|string|max:20',
            'email'         => 'nullable|email|max:255',
            'logo'     => 'nullable|file|mimes:jpg,png,web|max:2048',
            'production'    => 'nullable|boolean',
            'sol_user'      => 'nullable|string',
            'sol_pass'      => 'nullable|string',
            'cert'     => 'nullable|file|mimes:pem,txt',
            'client_id'     => 'nullable|string',
            'client_secret' => 'nullable|string',
        ]);

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('logos');
        }

        if ($request->hasFile('cert')) {
            $data['cert_path'] = $request->file('cert')->store('certs');
        }

        $company->update($data);

        return response()->json([
            'message' => 'Empresa actualizada correctamente',
            'company' => $company->makeHidden(['created_at', 'updated_at'])
        ], 200);
    }

    public function destroy($company)
    {
        $company = Company::where('ruc', $company)
            ->where('user_id', auth()->user()->id)
            ->first();

        $company->delete();
        return response()->json([
            'message' => 'Empresa eliminada correctamente'
        ], 200);
    }
}
