<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar: {{ $empresa->razon_social }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8 text-gray-900">
                    
                    <form method="POST" action="{{ route('empresas.update', $empresa->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="flex justify-between items-center border-b pb-2 mb-4">
                            <h3 class="text-lg font-bold text-indigo-600">Datos Generales</h3>
                            <select name="status" class="border-gray-300 rounded-md shadow-sm text-sm font-bold {{ $empresa->status == 'activo' ? 'text-green-600' : 'text-red-600' }}">
                                <option value="activo" {{ $empresa->status == 'activo' ? 'selected' : '' }}>ACTIVA</option>
                                <option value="inactivo" {{ $empresa->status == 'inactivo' ? 'selected' : '' }}>INACTIVA</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div class="col-span-1 md:col-span-2">
                                <label class="block font-medium text-sm text-gray-700">Razón Social *</label>
                                <input type="text" name="razon_social" value="{{ old('razon_social', $empresa->razon_social) }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div>
                                <label class="block font-medium text-sm text-gray-700">RUC *</label>
                                <input type="text" name="ruc" value="{{ old('ruc', $empresa->ruc) }}" required maxlength="11" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100" readonly title="El RUC no se puede modificar por seguridad">
                            </div>
                            <div class="col-span-1 md:col-span-2">
                                <label class="block font-medium text-sm text-gray-700">Nombre Comercial</label>
                                <input type="text" name="nombre_comercial" value="{{ old('nombre_comercial', $empresa->nombre_comercial) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Logo (Dejar vacío para mantener)</label>
                                <input type="file" name="logo" accept="image/png, image/jpeg, image/webp" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            </div>
                        </div>

                        <h3 class="text-lg font-bold border-b pb-2 mb-4 text-indigo-600">Ubicación y Contacto</h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                            <div class="col-span-1 md:col-span-4">
                                <label class="block font-medium text-sm text-gray-700">Dirección *</label>
                                <input type="text" name="direccion" value="{{ old('direccion', $empresa->direccion) }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Departamento</label>
                                <input type="text" name="departamento" value="{{ old('departamento', $empresa->departamento) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Provincia</label>
                                <input type="text" name="provincia" value="{{ old('provincia', $empresa->provincia) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Distrito</label>
                                <input type="text" name="distrito" value="{{ old('distrito', $empresa->distrito) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Ubigeo</label>
                                <input type="text" name="ubigeo" value="{{ old('ubigeo', $empresa->ubigeo) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div class="col-span-1 md:col-span-2">
                                <label class="block font-medium text-sm text-gray-700">Teléfono</label>
                                <input type="text" name="telefono" value="{{ old('telefono', $empresa->telefono) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div class="col-span-1 md:col-span-2">
                                <label class="block font-medium text-sm text-gray-700">Email</label>
                                <input type="email" name="email" value="{{ old('email', $empresa->email) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                        </div>

                        <h3 class="text-lg font-bold border-b pb-2 mb-4 text-indigo-600">Credenciales SUNAT</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 bg-gray-50 p-4 rounded-lg border">
                            <div class="col-span-1 md:col-span-2 bg-yellow-100 text-yellow-800 text-sm p-3 rounded mb-2">
                                <strong>Nota de Seguridad:</strong> Las claves actuales están encriptadas. Solo llena los campos de Clave SOL, Client Secret o Certificado si deseas <strong>reemplazarlos</strong>.
                            </div>
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Usuario SOL *</label>
                                <input type="text" name="sol_user" value="{{ old('sol_user', $empresa->sol_user) }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Nueva Clave SOL</label>
                                <input type="password" name="sol_pass" placeholder="Dejar vacío para mantener actual" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div class="col-span-1 md:col-span-2">
                                <label class="block font-medium text-sm text-gray-700">Nuevo Certificado Digital (.pem / .txt)</label>
                                <input type="file" name="cert" accept=".pem,.txt" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                @if($empresa->cert_path)
                                    <p class="text-xs text-green-600 mt-1">✔ Certificado actual cargado.</p>
                                @endif
                            </div>
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Client ID (API SUNAT)</label>
                                <input type="text" name="client_id" value="{{ old('client_id', $empresa->client_id) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Nuevo Client Secret</label>
                                <input type="password" name="client_secret" placeholder="Dejar vacío para mantener actual" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div class="col-span-1 md:col-span-2 mt-2">
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="production" value="1" {{ old('production', $empresa->production) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm">
                                    <span class="ml-2 text-sm text-gray-700 font-bold">¿Entorno de Producción? (Desmarca para Beta/Pruebas)</span>
                                </label>
                            </div>
                        </div>
                        <h3 class="text-lg font-bold border-b pb-2 mb-4 mt-8 text-indigo-600">Integración API REST</h3>
                        <div class="bg-indigo-50 p-6 rounded-lg border border-indigo-100 mb-6">
                            <p class="text-sm text-indigo-800 mb-4">
                                Usa este token para conectar sistemas externos (como un punto de venta o ERP) a nuestra API de facturación. 
                                <strong class="font-bold">Mantenlo en secreto.</strong>
                            </p>
                            
                            <div class="flex items-center gap-2">
                                <div class="relative w-full md:w-2/3">
                                    <input type="text" id="api_token_field" value="{{ $empresa->api_token }}" readonly 
                                        class="block w-full border-gray-300 bg-white rounded-md shadow-sm font-mono text-sm text-gray-600 pr-10 focus:ring-0">
                                </div>
                                
                                <button type="button" onclick="copyToken()" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition shadow-sm font-semibold text-sm flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" />
                                    </svg>
                                    Copiar
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-8">
                            <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">Cancelar</a>
                            <button type="submit" class="bg-amber-500 text-white px-6 py-2 rounded font-semibold hover:bg-amber-600 shadow">
                                Guardar Cambios
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>