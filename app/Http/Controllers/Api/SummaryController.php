<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Service\SunatService;
use Greenter\Report\XmlUtils;
use Illuminate\Http\Request;

class SummaryController extends Controller
{
    // ENVIAR RESUMEN (Genera el Ticket)
    public function send(Request $request)
    {
        $request->validate([
            'company' => 'required|array',
            'company.ruc' => 'required',
            'fechaGeneracion' => 'required|date',
            'fechaResumen' => 'required|date',
            'correlativo' => 'required|string',
            'details' => 'required|array',
            'details.*.tipoDoc' => 'required|string',
            'details.*.serieNro' => 'required|string',
            'details.*.estado' => 'required|string',
        ]);

        $data = $request->all();

        $company = $request->auth_company;

        if ($company->ruc !== $data['company']['ruc']) {
            return response()->json(['error' => 'El RUC no coincide con el API Key proporcionado.'], 403);
        }

        $sunat = new SunatService();
        $see = $sunat->getSee($company);
        $summary = $sunat->getSummary($data, $company);
        $result = $see->send($summary);
        $xmlCrudo = $see->getFactory()->getLastXml();
        
        $response['hash'] = (new XmlUtils())->getHashSign($xmlCrudo);
        $response['xml'] = base64_encode($xmlCrudo);
        $response['success'] = $result->isSuccess();

        if ($result->isSuccess()) {
            $response['ticket'] = $result->getTicket();
        } else {
            $response['error'] = [
                'code' => $result->getError()->getCode(),
                'message' => $result->getError()->getMessage(),
            ];
        }

        return response()->json($response, 200);
    }

    // CONSULTAR TICKET (Te devuelve el CDR)
    public function status(Request $request)
    {
        $request->validate([
            'company.ruc' => 'required',
            'ticket' => 'required|string',
        ]);
        $company = $request->auth_company;
        if ($company->ruc !== $request->input('company.ruc')) {
            return response()->json(['error' => 'El RUC no coincide con el API Key proporcionado.'], 403);
        }
        $sunat = new SunatService();
        $see = $sunat->getSee($company);
        $result = $see->getStatus($request->ticket);
        $response = $sunat->sunatResponse($result);
        $statusCode = $response['success'] ? 200 : 400;
        
        return response()->json($response, $statusCode);
    }
}