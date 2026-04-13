<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Rules\UniqueRucRule; // Asegúrate de tener esta regla importada
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EmpresaController extends Controller
{
    public function create()
    {
        return view('empresas.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'razon_social'     => 'required|string|max:255',
            'nombre_comercial' => 'nullable|string|max:255',
            'ruc'              => ['required', 'regex:/^(10|20)\d{9}$/', 'digits:11', new UniqueRucRule()],
            'direccion'        => 'required|string|max:255',
            'departamento'     => 'nullable|string|max:255',
            'provincia'        => 'nullable|string|max:255',
            'distrito'         => 'nullable|string|max:255',
            'ubigeo'           => 'nullable|string|max:255',
            'telefono'         => 'nullable|string|max:20',
            'email'            => 'nullable|email|max:255',
            'logo'             => 'nullable|file|mimes:jpg,png,webp|max:2048',
            'production'       => 'nullable|boolean',
            'sol_user'         => 'required|string',
            'sol_pass'         => 'required|string',
            'cert'             => 'required|file|mimes:pem,txt',
            'client_id'        => 'nullable|string',
            'client_secret'    => 'nullable|string',
            'status'           => 'nullable|string|in:activo,inactivo',
        ]);

        // Procesar Archivos
        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('logos', 'public');
        }
        $data['cert_path'] = $request->file('cert')->store('certs');

        // Datos automáticos
        $data['api_token'] = 'kipu_' . Str::random(40);
        $data['user_id'] = Auth::id(); // Usuario de la sesión web
        $data['production'] = $request->has('production') ? 1 : 0;
        $data['status'] = $data['status'] ?? 'activo';

        // 🔒 SEGURIDAD: Encriptar
        $data['sol_pass'] = encrypt($data['sol_pass']);
        if (!empty($data['client_secret'])) {
            $data['client_secret'] = encrypt($data['client_secret']);
        }

        Company::create($data);

        return redirect()->route('dashboard')->with('success', '¡Empresa registrada correctamente!');
    }

    public function edit($id)
    {
        // Solo puede editar si le pertenece
        $empresa = Company::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        return view('empresas.edit', compact('empresa'));
    }

    public function update(Request $request, $id)
    {
        $companyRecord = Company::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        $data = $request->validate([
            'razon_social'     => 'required|string|max:255',
            'nombre_comercial' => 'nullable|string|max:255',
            'ruc'              => ['required', 'regex:/^(10|20)\d{9}$/', 'digits:11', new UniqueRucRule($companyRecord->id)],
            'direccion'        => 'required|string|max:255',
            'departamento'     => 'nullable|string|max:255',
            'provincia'        => 'nullable|string|max:255',
            'distrito'         => 'nullable|string|max:255',
            'ubigeo'           => 'nullable|string|max:255',
            'telefono'         => 'nullable|string|max:20',
            'email'            => 'nullable|email|max:255',
            'logo'             => 'nullable|file|mimes:jpg,png,webp|max:2048',
            'production'       => 'nullable|boolean',
            'sol_user'         => 'required|string',
            'sol_pass'         => 'nullable|string', // Nullable en update
            'cert'             => 'nullable|file|mimes:pem,txt', // Nullable en update
            'client_id'        => 'nullable|string',
            'client_secret'    => 'nullable|string',
            'status'           => 'required|string|in:activo,inactivo',
        ]);

        // Reemplazo de Logo
        if ($request->hasFile('logo')) {
            if ($companyRecord->logo_path && Storage::disk('public')->exists($companyRecord->logo_path)) {
                Storage::disk('public')->delete($companyRecord->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        // Reemplazo de Certificado
        if ($request->hasFile('cert')) {
            if ($companyRecord->cert_path && Storage::exists($companyRecord->cert_path)) {
                Storage::delete($companyRecord->cert_path);
            }
            $data['cert_path'] = $request->file('cert')->store('certs');
        }

        $data['production'] = $request->has('production') ? 1 : 0;

        // 🔒 SEGURIDAD: Encriptar solo si se enviaron nuevos
        if (!empty($data['sol_pass'])) {
            $data['sol_pass'] = encrypt($data['sol_pass']);
        } else {
            unset($data['sol_pass']); // No sobreescribir si está vacío
        }

        if (!empty($data['client_secret'])) {
            $data['client_secret'] = encrypt($data['client_secret']);
        } else {
            unset($data['client_secret']);
        }

        $companyRecord->update($data);

        return redirect()->route('dashboard')->with('success', 'Datos de la empresa actualizados.');
    }

    public function destroy($id)
    {
        $companyRecord = Company::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        
        // Limpiar archivos
        if ($companyRecord->logo_path) Storage::disk('public')->delete($companyRecord->logo_path);
        if ($companyRecord->cert_path) Storage::delete($companyRecord->cert_path);

        $companyRecord->delete();

        return redirect()->route('dashboard')->with('success', 'Empresa eliminada.');
    }

    public function toggleStatus($id)
    {
        // Verificamos que la empresa sea del usuario autenticado
        $empresa = \App\Models\Company::where('id', $id)
            ->where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->firstOrFail();

        // Cambiamos el estado
        $empresa->status = $empresa->status === 'activo' ? 'inactivo' : 'activo';
        $empresa->save();

        // Devolvemos JSON porque lo llamaremos con Javascript sin recargar
        return response()->json([
            'success' => true,
            'new_status' => $empresa->status,
            'message' => 'El estado de la empresa ha sido actualizado.'
        ]);
    }
}