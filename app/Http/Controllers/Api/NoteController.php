<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Service\SunatService;
use App\Traits\SunatTraits;
use Greenter\Report\XmlUtils;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    use SunatTraits;

    public function send(Request $request)
    {
        $request->validate([
            'company' => 'required|array',
            'company.address' => 'required|array',
            'client' => 'required|array',
            'details' => 'required|array',
            'details.*' => 'required|array',
        ]);

        $data = $request->all();

        // 1. Obtenemos el Modelo Company desde el middleware
        $company = $request->auth_company;

        // Validamos por seguridad que el RUC coincida
        if ($company->ruc !== $data['company']['ruc']) {
            return response()->json(['error' => 'El RUC no coincide con el API Key proporcionado.'], 403);
        }

        $this->setTotales($data);
        $this->setLegends($data);

        // 2. Instanciamos servicios de Greenter
        $sunat = new SunatService();
        $see = $sunat->getSee($company);
        $note = $sunat->getNote($data);

        // 3. ENVÍO DIRECTO A SUNAT (Las notas siempre se envían)
        $result = $see->send($note);
        $xmlCrudo = $see->getFactory()->getLastXml();

        // 4. PREPARAMOS LA RESPUESTA
        $response = [];
        $response['sunatResponse'] = $sunat->sunatResponse($result);
        $response['hash'] = (new XmlUtils())->getHashSign($xmlCrudo);
        $response['xml'] = base64_encode($xmlCrudo);

        // Extraemos el total en letras de las leyendas generadas
        $response['total_letras'] = $data['legends'][0]['value'] ?? '';

        // 5. GENERACIÓN DEL CÓDIGO QR
        $fechaEmision = (new \DateTime($data['fechaEmision']))->format('Y-m-d');

        $qrString = implode('|', [
            $company->ruc,
            $data['tipoDoc'], // '07' (Crédito) u '08' (Débito)
            $data['serie'],
            $data['correlativo'],
            number_format($data['mtoIGV'] ?? 0, 2, '.', ''),
            number_format($data['mtoImpVenta'] ?? 0, 2, '.', ''),
            $fechaEmision,
            $data['client']['tipoDoc'],
            $data['client']['numDoc'],
            $response['hash']
        ]) . '|';

        $response['qr_data'] = $qrString;

        return response()->json($response, 200);
    }

    public function xml(Request $request)
    {
        $request->validate([
            'company' => 'required|array',
            'company.address' => 'required|array',
            'client' => 'required|array',
            'details' => 'required|array',
            'details.*' => 'required|array',
        ]);
        $data = $request->all();

        $company = $request->auth_company;
        if ($company->ruc !== $data['company']['ruc']) {
            return response()->json(['error' => 'RUC inválido'], 403);
        }

        $this->setTotales($data);
        $this->setLegends($data);

        $sunat = new SunatService();
        $see = $sunat->getSee($company);
        $note = $sunat->getNote($data);
        $xmlCrudo = $see->getXmlSigned($note);
        $response['xml'] = base64_encode($xmlCrudo);
        $response['hash'] = (new XmlUtils())->getHashSign($xmlCrudo);

        return response()->json($response, 200);
    }

    public function pdf(Request $request)
    {
        $request->validate([
            'company' => 'required|array',
            'company.address' => 'required|array',
            'client' => 'required|array',
            'details' => 'required|array',
            'details.*' => 'required|array',
        ]);
        $data = $request->all();

        $company = $request->auth_company;
        if ($company->ruc !== $data['company']['ruc']) {
            return response()->json(['error' => 'RUC inválido'], 403);
        }

        $this->setTotales($data);
        $this->setLegends($data);

        $sunat = new SunatService();
        $note = $sunat->getNote($data);
        return $sunat->getHtmlReport($note);
    }
}
