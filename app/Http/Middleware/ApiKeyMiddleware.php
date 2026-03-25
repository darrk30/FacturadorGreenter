<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['error' => 'Token no proporcionado en los Headers.'], 401);
        }
        $company = Company::where('api_token', $token)->first();
        if (!$company) {
            return response()->json([
                'error' => 'API Key inválida. No se encontró ninguna empresa con ese token.',
                'token_recibido' => $token // Lo imprimimos para que veas qué está llegando
            ], 401);
        }
        $request->merge(['auth_company' => $company]);
        return $next($request);
    }
}
