<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Registrar Nueva Empresa') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8 text-gray-900">
                    
                    <form method="POST" action="{{ route('empresas.store') }}" enctype="multipart/form-data">
                        @csrf

                        <h3 class="text-lg font-bold border-b pb-2 mb-4 text-indigo-600">Datos Generales</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div class="col-span-1 md:col-span-2">
                                <label class="block font-medium text-sm text-gray-700">Razón Social *</label>
                                <input type="text" name="razon_social" value="{{ old('razon_social') }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                @error('razon_social') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block font-medium text-sm text-gray-700">RUC *</label>
                                <input type="text" name="ruc" value="{{ old('ruc') }}" required maxlength="11" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                @error('ruc') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-1 md:col-span-2">
                                <label class="block font-medium text-sm text-gray-700">Nombre Comercial</label>
                                <input type="text" name="nombre_comercial" value="{{ old('nombre_comercial') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Logo (Opcional)</label>
                                <input type="file" name="logo" accept="image/png, image/jpeg, image/webp" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            </div>
                        </div>

                        <h3 class="text-lg font-bold border-b pb-2 mb-4 text-indigo-600">Ubicación y Contacto</h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                            <div class="col-span-1 md:col-span-4">
                                <label class="block font-medium text-sm text-gray-700">Dirección *</label>
                                <input type="text" name="direccion" value="{{ old('direccion') }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Departamento</label>
                                <input type="text" name="departamento" value="{{ old('departamento') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Provincia</label>
                                <input type="text" name="provincia" value="{{ old('provincia') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Distrito</label>
                                <input type="text" name="distrito" value="{{ old('distrito') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Ubigeo</label>
                                <input type="text" name="ubigeo" value="{{ old('ubigeo') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div class="col-span-1 md:col-span-2">
                                <label class="block font-medium text-sm text-gray-700">Teléfono</label>
                                <input type="text" name="telefono" value="{{ old('telefono') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div class="col-span-1 md:col-span-2">
                                <label class="block font-medium text-sm text-gray-700">Email</label>
                                <input type="email" name="email" value="{{ old('email') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                        </div>

                        <h3 class="text-lg font-bold border-b pb-2 mb-4 text-indigo-600">Credenciales SUNAT</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 bg-gray-50 p-4 rounded-lg border">
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Usuario SOL *</label>
                                <input type="text" name="sol_user" value="{{ old('sol_user') }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Clave SOL *</label>
                                <input type="password" name="sol_pass" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div class="col-span-1 md:col-span-2">
                                <label class="block font-medium text-sm text-gray-700">Certificado Digital (.pem / .txt) *</label>
                                <input type="file" name="cert" accept=".pem,.txt" required class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                @error('cert') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Client ID (API SUNAT)</label>
                                <input type="text" name="client_id" value="{{ old('client_id') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Client Secret (API SUNAT)</label>
                                <input type="password" name="client_secret" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div class="col-span-1 md:col-span-2 mt-2">
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="production" value="1" {{ old('production') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm">
                                    <span class="ml-2 text-sm text-gray-700 font-bold">¿Entorno de Producción? (Desmarca para Beta/Pruebas)</span>
                                </label>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-8">
                            <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">Cancelar</a>
                            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded font-semibold hover:bg-blue-700 shadow">
                                Crear y Configurar Empresa
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>