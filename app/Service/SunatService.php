<?php

namespace App\Service;

use App\Models\Company as ModelsCompany;
use DateTime;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Address;
use Greenter\Model\Company\Company;
use Greenter\Model\Sale\FormaPagos\FormaPagoContado;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\Legend;
use Greenter\Model\Sale\Note;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Summary\Summary;
use Greenter\Model\Summary\SummaryDetail;
use Greenter\Model\Voided\Voided;
use Greenter\Model\Voided\VoidedDetail;
use Greenter\Report\HtmlReport;
use Greenter\Report\Resolver\DefaultTemplateResolver;
use Greenter\See;
use Greenter\Ws\Services\SunatEndpoints;
use Illuminate\Support\Facades\Storage;

class SunatService
{

    public function getSee($company)
    {
        $see = new See();
        $see->setCertificate(Storage::get($company->cert_path));
        $see->setService($company->production ? SunatEndpoints::FE_PRODUCCION : SunatEndpoints::FE_BETA);
        $see->setClaveSOL($company->ruc, $company->sol_user, $company->sol_pass);
        return $see;
    }

    public function getInvoice($data)
    {
        $invoice = (new Invoice())
            ->setUblVersion($data['ublVersion'] ?? '2.1')
            ->setTipoOperacion($data['tipoOperacion'] ?? null) // Venta - Catalog. 51
            ->setTipoDoc($data['tipoDoc'] ?? null) // RUC - Catalog. Factura - Catalog. 01 
            ->setSerie($data['serie'] ?? null)
            ->setCorrelativo($data['correlativo'] ?? null)
            ->setFechaEmision(new DateTime($data['fechaEmision']) ?? null) // Zona horaria: Lima
            ->setFormaPago(new FormaPagoContado()) // FormaPago: Contado
            ->setTipoMoneda($data['tipoMoneda'] ?? null) // Sol - Catalog. 02
            ->setCompany($this->getCompany($data['company']))
            ->setClient($this->getClient($data['client']))
            //MtoOper
            ->setMtoOperGravadas($data['mtoOperGravadas'])
            ->setMtoOperExoneradas($data['mtoOperExoneradas'])
            ->setMtoOperInafectas($data['mtoOperInafectas'])
            ->setMtoOperExoneradas($data['mtoOperExportacion'])
            ->setMtoOperGratuitas($data['mtoOperGratuitas'])
            //Inpuestos
            ->setMtoIGV($data['mtoIGV'])
            ->setMtoIGVGratuitas($data['mtoIGVGratuitas'])
            ->setIcbper($data['icbper'])
            ->setTotalImpuestos($data['totalImpuestos'])
            //Totales
            ->setValorVenta($data['valorVenta'])
            ->setSubTotal($data['subTotal'])
            ->setRedondeo($data['redondeo'])
            ->setMtoImpVenta($data['mtoImpVenta'])
            //Productos
            ->setDetails($this->getDetails($data['details']))
            //Leyendas
            ->setLegends($this->getLegends($data['legends']));
        return $invoice;
    }

    public function getNote($data)
    {
        return (new Note)
            ->setUblVersion($data['ublVersion'] ?? '2.1')
            ->setTipoDoc($data['tipoDoc'] ?? null)
            ->setSerie($data['serie'] ?? null)
            ->setCorrelativo($data['correlativo'] ?? null)
            ->setFechaEmision(new DateTime($data['fechaEmision']) ?? null)
            ->setTipDocAfectado($data['tipDocAfectado'] ?? null)
            ->setNumDocfectado($data['numDocAfectado'] ?? null)
            ->setCodMotivo($data['codMotivo'] ?? null)
            ->setDesMotivo($data['desMotivo'] ?? null)
            ->setTipoMoneda($data['tipoMoneda'] ?? null)
            ->setCompany($this->getCompany($data['company']))
            ->setClient($this->getClient($data['client']))
            //MtoOper
            ->setMtoOperGravadas($data['mtoOperGravadas'])
            ->setMtoOperExoneradas($data['mtoOperExoneradas'])
            ->setMtoOperInafectas($data['mtoOperInafectas'])
            ->setMtoOperExoneradas($data['mtoOperExportacion'])
            ->setMtoOperGratuitas($data['mtoOperGratuitas'])
            //Inpuestos
            ->setMtoIGV($data['mtoIGV'])
            ->setMtoIGVGratuitas($data['mtoIGVGratuitas'])
            ->setIcbper($data['icbper'])
            ->setTotalImpuestos($data['totalImpuestos'])
            //Totales
            ->setValorVenta($data['valorVenta'])
            ->setSubTotal($data['subTotal'])
            ->setRedondeo($data['redondeo'])
            ->setMtoImpVenta($data['mtoImpVenta'])
            //Productos
            ->setDetails($this->getDetails($data['details']))
            //Leyendas
            ->setLegends($this->getLegends($data['legends']));
    }

    public function getSummary($data, $companyModel)
    {
        $summary = new Summary();

        $summary->setFecGeneracion(new \DateTime($data['fechaGeneracion']))
            ->setFecResumen(new \DateTime($data['fechaResumen']))
            ->setCorrelativo($data['correlativo'])
            ->setCompany($this->getCompany([
                'ruc' => $companyModel->ruc,
                'razonSocial' => $companyModel->razon_social,
                'nombreComercial' => $companyModel->nombre_comercial,
                'address' => [
                    'ubigeo' => $companyModel->ubigeo,
                    'direccion' => $companyModel->direccion,
                    'departamento' => $companyModel->departamento,
                    'provincia' => $companyModel->provincia,
                    'distrito' => $companyModel->distrito,
                ]
            ]))
            ->setDetails($this->getSummaryDetails($data['details']));

        return $summary;
    }

    public function getSummaryDetails($details)
    {
        $green_details = [];
        foreach ($details as $detail) {
            $item = new SummaryDetail();

            $item->setTipoDoc($detail['tipoDoc'])
                ->setSerieNro($detail['serieNro'])
                ->setEstado($detail['estado'])
                ->setClienteTipo($detail['clienteTipo'])
                ->setClienteNro($detail['clienteNro'])
                ->setTotal((float) $detail['total'])
                ->setMtoOperGravadas((float) ($detail['mtoOperGravadas'] ?? 0))
                ->setMtoOperInafectas((float) ($detail['mtoOperInafectas'] ?? 0))
                ->setMtoOperExoneradas((float) ($detail['mtoOperExoneradas'] ?? 0))
                ->setMtoOperExportacion((float) ($detail['mtoOperExportacion'] ?? 0))
                ->setMtoOtrosCargos((float) ($detail['mtoOtrosCargos'] ?? 0))
                ->setPorcentajeIgv((float) ($detail['porcentajeIgv'] ?? 18.0))
                ->setMtoIGV((float) ($detail['mtoIGV'] ?? 0));

            $green_details[] = $item;
        }
        return $green_details;
    }

    public function getCompany($company)
    {
        $company = (new Company())
            ->setRuc($company['ruc'] ?? null)
            ->setRazonSocial($company['razonSocial'] ?? null)
            ->setNombreComercial($company['nombreComercial'] ?? null)
            ->setEmail($company['email'] ?? null)
            ->setTelephone($company['telefono'] ?? null)
            ->setAddress($this->getAddress($company['address'] ?? []));
        return $company;
    }

    public function getAddress($address)
    {
        $address = (new Address())
            ->setUbigueo($address['ubigeo'] ?? null)
            ->setDepartamento($address['departamento'] ?? null)
            ->setProvincia($address['provincia'] ?? null)
            ->setDistrito($address['distrito'] ?? null)
            ->setUrbanizacion($address['urbanizacion'] ?? null)
            ->setDireccion($address['direccion'] ?? null)
            ->setCodLocal($address['codLocal'] ?? null);
        return $address;
    }

    public function getClient($clientData)
    {
        $client = (new Client())
            ->setTipoDoc($clientData['tipoDoc'] ?? null)
            ->setNumDoc($clientData['numDoc'] ?? null)
            ->setRznSocial($clientData['razonSocial'] ?? null)
            ->setEmail($clientData['email'] ?? null)
            ->setTelephone($clientData['telefono'] ?? null);
        if (!empty($clientData['address'])) {
            $client->setAddress($this->getAddress($clientData['address']));
        }

        return $client;
    }

    public function getDetails($details)
    {
        $green_details = [];
        foreach ($details as $detail) {
            $green_details[] = (new SaleDetail())
                ->setTipAfeIgv($detail['tipAfeIgv'] ?? null) // Gravado Op. Onerosa - Catalog. 07
                ->setCodProducto($detail['codProducto'] ?? null)
                ->setUnidad($detail['unidad'] ?? null) // Unidad - Catalog. 03
                ->setDescripcion($detail['descripcion'] ?? null)
                ->setCantidad($detail['cantidad'] ?? null)
                ->setMtoValorUnitario($detail['mtoValorUnitario'] ?? null)
                ->setMtoValorVenta($detail['mtoValorVenta'] ?? null)
                ->setMtoBaseIgv($detail['mtoBaseIgv'] ?? null)
                ->setPorcentajeIgv($detail['porcentajeIgv'] ?? null)
                ->setIgv($detail['igv'] ?? null)
                ->setFactorIcbper($detail['factorIcbper'] ?? null)
                ->setIcbper($detail['icbper'] ?? null)
                ->setTotalImpuestos($detail['totalImpuestos'] ?? null) // Suma de impuestos en el detalle
                ->setMtoPrecioUnitario($detail['mtoPrecioUnitario'] ?? null);
        }
        return $green_details;
    }

    public function getLegends($legends)
    {
        $green_legends = [];
        foreach ($legends as $legend) {

            $green_legends[] = (new Legend())
                ->setCode($legend['code'] ?? null) // Monto en letras - Catalog. 52
                ->setValue($legend['value'] ?? null);
        }
        return $green_legends;
    }

    public function sunatResponse($result)
    {
        $response['success'] = $result->isSuccess();
        if (!$response['success']) {
            $response['error'] = [
                'code' => $result->getError()->getCode(),
                'message' => $result->getError()->getMessage(),
            ];
            return $response;
        }

        $response['cdrZip'] = base64_encode($result->getCdrZip());

        $cdr = $result->getCdrResponse();

        $response['cdrResponse'] = [
            'code' => (int)$cdr->getCode(),
            'description' => $cdr->getDescription(),
            'notes' => $cdr->getNotes()
        ];
        return $response;
    }

    public function getHtmlReport($invoice)
    {
        $report = new HtmlReport();
        $resolver = new DefaultTemplateResolver();
        $report->setTemplate($resolver->getTemplate($invoice));

        // 🟢 CORRECCIÓN: Tomamos la empresa directamente del Request 
        // (inyectada por el ApiKeyMiddleware). Si no existe ahí, la buscamos solo por RUC.
        $company = request()->auth_company ?? ModelsCompany::where('ruc', $invoice->getCompany()->getRuc())->firstOrFail();

        $params = [
            'system' => [
                'logo' => Storage::get($company->logo_path),
                'hash' => 'qqnr2dN4p/HmaEA/CJuVGo7dv5g=',
            ],
            'user' => [
                'header'     => 'Telf: <b>(01) 123375</b>',
                'extras'     => [
                    ['name' => 'CONDICION DE PAGO', 'value' => 'Efectivo'],
                    ['name' => 'VENDEDOR', 'value' => 'SISTEMA POS'],
                ],
                'footer' => '<p>Emisor Electrónico Autorizado</p>'
            ]
        ];
        return $report->render($invoice, $params);
    }

    public function getVoided($data, $companyModel)
    {
        $voided = new Voided();
        $voided->setCorrelativo($data['correlativo'])
            ->setFecGeneracion(new \DateTime($data['fechaGeneracion']))
            ->setFecComunicacion(new \DateTime($data['fechaComunicacion']))
            ->setCompany($this->getCompany([
                'ruc' => $companyModel->ruc,
                'razonSocial' => $companyModel->razon_social,
                'nombreComercial' => $companyModel->nombre_comercial,
                'address' => [
                    'ubigeo' => $companyModel->ubigeo,
                    'direccion' => $companyModel->direccion,
                    'departamento' => $companyModel->departamento,
                    'provincia' => $companyModel->provincia,
                    'distrito' => $companyModel->distrito,
                ]
            ]))
            ->setDetails($this->getVoidedDetails($data['details']));

        return $voided;
    }

    public function getVoidedDetails($details)
    {
        $green_details = [];
        foreach ($details as $detail) {
            $item = new VoidedDetail();
            $item->setTipoDoc($detail['tipoDoc'])
                ->setSerie($detail['serie'])
                ->setCorrelativo($detail['correlativo'])
                ->setDesMotivoBaja($detail['motivo']);

            $green_details[] = $item;
        }
        return $green_details;
    }

    // Método para procesar la respuesta de Ticket (Bajas y Resúmenes)
    public function sunatTicketResponse($result)
    {
        $response['success'] = $result->isSuccess();
        if (!$response['success']) {
            $response['error'] = [
                'code' => $result->getError()->getCode(),
                'message' => $result->getError()->getMessage(),
            ];
            return $response;
        }

        $response['ticket'] = $result->getTicket();
        return $response;
    }
}
