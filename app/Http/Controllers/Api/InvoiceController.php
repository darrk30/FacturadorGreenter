<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Service\SunatService;
use Greenter\Report\XmlUtils;
use Illuminate\Http\Request;
use Luecano\NumeroALetras\NumeroALetras;

class InvoiceController extends Controller
{
    public function send(Request $request)
    {
        $request->validate([
            'company' => 'required|array',
            'company.address' => 'required|array',
            'client' => 'required|array',
            'details' => 'required|array',
            'details.*' => 'required|array',
        ]);

        $data = $request->all(); // Aquí está el JSON intacto para el getInvoice()

        // 1. Obtenemos el Modelo Company desde el middleware (API Key)
        $company = $request->auth_company;

        // 2. Validación de seguridad (Recomendado)
        if ($company->ruc !== $data['company']['ruc']) {
            return response()->json(['error' => 'El RUC no coincide con el API Key proporcionado.'], 403);
        }

        $this->setTotales($data);
        $this->setLegends($data);

        $sunat = new SunatService();
        $see = $sunat->getSee($company);
        $invoice = $sunat->getInvoice($data);

        $result = $see->send($invoice);

        $xmlCrudo = $see->getFactory()->getLastXml();

        $response['hash'] = (new XmlUtils())->getHashSign($xmlCrudo);
        $response['xml'] = base64_encode($xmlCrudo);
        $response['sunatResponse'] = $sunat->sunatResponse($result);
        $response['total_letras'] = $data['legends'][0]['value'];

        $fechaEmision = (new \DateTime($data['fechaEmision']))->format('Y-m-d');

        $qrString = implode('|', [
            $company->ruc,
            $data['tipoDoc'],
            $data['serie'],
            $data['correlativo'],
            number_format($data['mtoIGV'], 2, '.', ''),
            number_format($data['mtoImpVenta'], 2, '.', ''),
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

        // 🟢 CORRECCIÓN: Usar la empresa del middleware
        $company = $request->auth_company;
        if ($company->ruc !== $data['company']['ruc']) {
            return response()->json(['error' => 'RUC inválido'], 403);
        }

        $this->setTotales($data);
        $this->setLegends($data);

        $sunat = new SunatService();
        $see = $sunat->getSee($company);
        $invoice = $sunat->getInvoice($data);
        $response['xml'] = $see->getXmlSigned($invoice);
        $response['hash'] = (new XmlUtils())->getHashSign($response['xml']);

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

        // 🟢 CORRECCIÓN: Usar la empresa del middleware
        $company = $request->auth_company;
        if ($company->ruc !== $data['company']['ruc']) {
            return response()->json(['error' => 'RUC inválido'], 403);
        }

        $this->setTotales($data);
        $this->setLegends($data);

        $sunat = new SunatService();
        $invoice = $sunat->getInvoice($data);
        return $sunat->getHtmlReport($invoice);
    }

    public function setTotales(&$data)
    {
        $details = collect($data['details']);
        $data['mtoOperGravadas'] = $details->where('tipAfeIgv', 10)->sum('mtoValorVenta');
        $data['mtoOperExoneradas'] = $details->where('tipAfeIgv', 20)->sum('mtoValorVenta');
        $data['mtoOperInafectas'] = $details->where('tipAfeIgv', 30)->sum('mtoValorVenta');
        $data['mtoOperExportacion'] = $details->where('tipAfeIgv', 40)->sum('mtoValorVenta');
        $data['mtoOperGratuitas'] = $details->whereNotIn('tipAfeIgv', [10, 20, 30, 40])->sum('mtoValorVenta');
        $data['mtoIGV'] = $details->whereIn('tipAfeIgv', [10, 20, 30, 40])->sum('igv');
        $data['mtoIGVGratuitas'] = $details->whereNotIn('tipAfeIgv', [10, 20, 30, 40])->sum('igv');
        $data['icbper'] = $details->sum('icbper');
        $data['totalImpuestos'] = $data['mtoIGV'] + $data['icbper'];
        $data['valorVenta'] = $details->whereIn('tipAfeIgv', [10, 20, 30, 40])->sum('mtoValorVenta');
        $data['subTotal'] = $data['valorVenta'] + $data['mtoIGV'];
        $data['mtoImpVenta'] = floor($data['subTotal'] * 10) / 10;
        $data['redondeo'] = $data['mtoImpVenta'] - $data['subTotal'];
    }

    public function setLegends(&$data)
    {
        $formatter = new NumeroALetras();
        $data['legends'] = [
            [
                'code' => '1000',
                'value' => $formatter->toInvoice($data['mtoImpVenta'], 2, 'SOLES'),
            ]
        ];
    }
}
