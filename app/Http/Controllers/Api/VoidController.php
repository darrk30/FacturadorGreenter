<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Service\SunatService;
use Illuminate\Http\Request;

class VoidController extends Controller
{
    public function send(Request $request)
    {
        $request->validate([
            'correlativo' => 'required',
            'fechaGeneracion' => 'required|date',
            'fechaComunicacion' => 'required|date',
            'details' => 'required|array',
            'details.*.tipoDoc' => 'required',
            'details.*.serie' => 'required',
            'details.*.correlativo' => 'required',
            'details.*.motivo' => 'required',
        ]);

        $company = $request->auth_company;
        $sunat = new SunatService();
        $see = $sunat->getSee($company);
        
        $voided = $sunat->getVoided($request->all(), $company);

        // Enviar a SUNAT
        $result = $see->send($voided);

        $response = $sunat->sunatTicketResponse($result);
        
        if ($response['success']) {
            $response['xml'] = base64_encode($see->getFactory()->getLastXml());
        }

        return response()->json($response, 200);
    }

    public function status(Request $request)
    {
        $request->validate(['ticket' => 'required|string']);

        $company = $request->auth_company;
        $sunat = new SunatService();
        $see = $sunat->getSee($company);

        $result = $see->getStatus($request->ticket);

        // Reutilizamos el procesador de respuesta normal para obtener el CDR
        $response = $sunat->sunatResponse($result);

        return response()->json($response, 200);
    }
}
