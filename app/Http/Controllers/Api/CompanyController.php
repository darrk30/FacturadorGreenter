<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Rules\UniqueCompanyRule;
use App\Rules\UniqueRucRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            'logo'          => 'nullable|file|mimes:jpg,png,web|max:2048',
            'production'    => 'nullable|boolean',
            'sol_user'      => 'required|string',
            'sol_pass'      => 'required|string',
            'cert'          => 'required|file|mimes:pem,txt',
            'client_id'     => 'nullable|string',
            'client_secret' => 'nullable|string',
            'status'        => 'nullable|string|in:activo,inactivo',
            'nombre_comercial' => 'nullable|string|max:255',
            'departamento' => 'nullable|string|max:255',
            'distrito' => 'nullable|string|max:255',
            'provincia' => 'nullable|string|max:255',
            'ubigeo' => 'nullable|string|max:255',
        ]);

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('logos');
        }

        $data['cert_path'] = $request->file('cert')->store('certs');
        $data['api_token'] = 'kipu_' . Str::random(40);
        $data['user_id'] = JWTAuth::user()->id;

        // 🔒 SEGURIDAD: Encriptar la clave SOL y el Client Secret al crear
        $data['sol_pass'] = encrypt($data['sol_pass']);
        if (!empty($data['client_secret'])) {
            $data['client_secret'] = encrypt($data['client_secret']);
        }

        $company = Company::create($data);

        return response()->json([
            'message' => 'Empresa creada correctamente',
            'api_token' => $company->api_token,
            'company' => $company->makeHidden(['created_at', 'updated_at', 'sol_pass', 'client_secret'])
        ], 201);
    }

    public function show($company)
    {
        // 🟢 firstOrFail() devolverá un error 404 automático si la empresa no le pertenece
        $company = Company::where('ruc', $company)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return response()->json([
            // Ocultamos datos sensibles para que no viajen al frontend
            'company' => $company->makeHidden(['created_at', 'updated_at', 'sol_pass', 'client_secret'])
        ], 200);
    }

    public function update(Request $request, $company)
    {
        // 🟢 firstOrFail() protege la ruta
        $companyRecord = Company::where('ruc', $company)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $data = $request->validate([
            'razon_social'  => 'nullable|string|max:255',
            'ruc'           => [
                'nullable',
                'string',
                'regex:/^(10|20)\d{9}$/',
                'digits:11',
                new UniqueRucRule($companyRecord->id),
            ],
            'direccion'     => 'nullable|string|max:255',
            'telefono'      => 'nullable|string|max:20',
            'email'         => 'nullable|email|max:255',
            'logo'          => 'nullable|file|mimes:jpg,png,web|max:2048',
            'production'    => 'nullable|boolean',
            'sol_user'      => 'nullable|string',
            'sol_pass'      => 'nullable|string',
            'cert'          => 'nullable|file|mimes:pem,txt',
            'client_id'     => 'nullable|string',
            'client_secret' => 'nullable|string',
        ]);

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('logos');
        }

        if ($request->hasFile('cert')) {
            $data['cert_path'] = $request->file('cert')->store('certs');
        }

        // 🔒 SEGURIDAD: Encriptar contraseñas solo si se enviaron en la actualización
        if (isset($data['sol_pass'])) {
            $data['sol_pass'] = encrypt($data['sol_pass']);
        }
        if (isset($data['client_secret'])) {
            $data['client_secret'] = encrypt($data['client_secret']);
        }

        $companyRecord->update($data);

        return response()->json([
            'message' => 'Empresa actualizada correctamente',
            'company' => $companyRecord->makeHidden(['created_at', 'updated_at', 'sol_pass', 'client_secret'])
        ], 200);
    }

    public function destroy($company)
    {
        // 🟢 firstOrFail() protege la eliminación
        $companyRecord = Company::where('ruc', $company)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $companyRecord->delete();

        return response()->json([
            'message' => 'Empresa eliminada correctamente'
        ], 200);
    }

    public function updateViaApi(Request $request)
    {
        $token = $request->bearerToken();
        $company = Company::where('api_token', $token)->firstOrFail();

        $data = $request->validate([
            'razon_social'     => 'nullable|string',
            'nombre_comercial' => 'nullable|string',
            'ruc'              => 'nullable|string',
            'direccion'        => 'nullable|string',
            'departamento'     => 'nullable|string',
            'distrito'         => 'nullable|string',
            'provincia'        => 'nullable|string',
            'ubigeo'           => 'nullable|string|size:6',
            'telefono'         => 'nullable|string',
            'email'            => 'nullable|email',
            'sol_user'         => 'nullable|string',
            'sol_pass'         => 'nullable|string',
            'cert'             => 'nullable|file',
        ]);

        if ($request->hasFile('cert')) {
            if ($company->cert_path && Storage::exists($company->cert_path)) {
                Storage::delete($company->cert_path);
            }
            $data['cert_path'] = $request->file('cert')->store('certs');
        }

        if (isset($data['sol_pass'])) {
            $data['sol_pass'] = encrypt($data['sol_pass']);
        }

        $company->update($data);

        return response()->json(['message' => 'Datos de empresa y ubicación actualizados correctamente']);
    }

    public function downloadCertificate(Request $request, $company)
    {
        // 🟢 Buscamos por RUC y por el Token que viene en el Bearer Header
        $token = $request->bearerToken();

        $companyRecord = Company::where('ruc', $company)
            ->where('api_token', $token) // 👈 Cambiamos user_id por api_token
            ->firstOrFail();

        if (!$companyRecord->cert_path || !Storage::exists($companyRecord->cert_path)) {
            return response()->json([
                'code' => 404,
                'message' => 'Archivo físico no encontrado.',
                'data' => null
            ], 404);
        }

        $fileContent = Storage::get($companyRecord->cert_path);

        return response()->json([
            'code' => 200,
            'message' => 'Certificado obtenido correctamente.',
            'data' => [
                'filename' => "certificado_{$companyRecord->ruc}.pem",
                'file_base64' => base64_encode($fileContent)
            ]
        ], 200);
    }
}
