<?php

namespace App\Traits;

use Luecano\NumeroALetras\NumeroALetras;

trait SunatTraits
{

    public function setTotales(&$data)
    {
        $details = collect($data['details']);

        // ✅ IGV dinámico: lo saca del primer ítem gravado
        $porcentajeIgv = $details->where('tipAfeIgv', 10)->first()['porcentajeIgv'] ?? 18;
        $factorIgv     = $porcentajeIgv / 100;
        $divisorIgv    = 1 + $factorIgv;

        // 1. Bases por tipo de afectación
        $gravadas    = round($details->where('tipAfeIgv', 10)->sum('mtoValorVenta'), 2);
        $exoneradas  = round($details->where('tipAfeIgv', 20)->sum('mtoValorVenta'), 2);
        $inafectas   = round($details->where('tipAfeIgv', 30)->sum('mtoValorVenta'), 2);
        $exportacion = round($details->where('tipAfeIgv', 40)->sum('mtoValorVenta'), 2);
        $gratuitas   = round($details->whereNotIn('tipAfeIgv', [10, 20, 30, 40])->sum('mtoValorVenta'), 2);

        // 2. Procesar descuentos globales
        $descuentoGravado = 0;
        if (!empty($data['descuentos'])) {
            foreach ($data['descuentos'] as &$desc) {
                if ($desc['codTipo'] === '02') {
                    // ✅ Usa el divisor dinámico
                    $montoSinIGV = ($desc['conIGV'] ?? false)
                        ? round($desc['monto'] / $divisorIgv, 2)
                        : round($desc['monto'], 2);

                    $desc['montoBase'] = $gravadas;
                    $desc['monto']     = $montoSinIGV;
                    $desc['factor']    = $gravadas > 0 ? round($montoSinIGV / $gravadas, 5) : 0;

                    $descuentoGravado += $montoSinIGV;
                }
            }
            unset($desc);
        }

        // 3. Base gravada final
        $data['mtoOperGravadas']    = round($gravadas - $descuentoGravado, 2);
        $data['mtoOperExoneradas']  = $exoneradas;
        $data['mtoOperInafectas']   = $inafectas;
        $data['mtoOperExportacion'] = $exportacion;
        $data['mtoOperGratuitas']   = $gratuitas;

        // 4. ✅ IGV dinámico
        $data['mtoIGV']          = round($data['mtoOperGravadas'] * $factorIgv, 2);
        $data['mtoIGVGratuitas'] = round($details->whereNotIn('tipAfeIgv', [10, 20, 30, 40])->sum('igv'), 2);
        $data['icbper']          = round($details->sum('icbper') ?? 0, 2);
        $data['totalImpuestos']  = round($data['mtoIGV'] + $data['icbper'], 2);

        // 5. Totales
        $data['valorVenta']  = round($data['mtoOperGravadas'] + $exoneradas + $inafectas + $exportacion, 2);
        $data['subTotal']    = round($data['valorVenta'] + $data['mtoIGV'], 2);
        $data['mtoImpVenta'] = floor($data['subTotal'] * 10) / 10;
        $data['redondeo']    = round($data['mtoImpVenta'] - $data['subTotal'], 2);
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
