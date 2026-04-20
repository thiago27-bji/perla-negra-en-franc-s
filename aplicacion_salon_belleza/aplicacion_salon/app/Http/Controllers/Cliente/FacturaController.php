<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Factura;
use App\Models\DetalleFactura;
use App\Models\Cita;
use App\Models\Servicio;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FacturaController extends Controller
{
    /**
     * Store a newly created invoice in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'citas' => 'required|array',
            'citas.*' => 'exists:cita,idCita',
        ]);

        try {
            DB::beginTransaction();

            $total = 0;
            $itemsCitas = Cita::with('detalle.servicio')
                        ->whereIn('idCita', $request->citas)
                        ->where('FK_usuario', Auth::id())
                        ->get();
            
            if ($itemsCitas->isEmpty()) {
                throw new \Exception("No se encontraron citas válidas para facturar.");
            }

            // 1. Generar ID Único para factura
            $idFactura = 'FAC-' . strtoupper(Str::random(6)) . '-' . date('sd');

            // 2. Crear Factura
            $factura = Factura::create([
                'idFacturas'      => $idFactura,
                'fechaGeneracion' => now(),
                'montoTotal'      => 0, // Se actualizará al final
                'FK_usuario'      => Auth::id(),
                'FK_estadoCita'   => 4, // Estado Pagada para la factura
            ]);

            // 3. Crear Detalles y Vincular Citas
            foreach($itemsCitas as $cita) {
                $servicio = $cita->detalle->servicio;
                if ($servicio) {
                    DetalleFactura::create([
                        'FK_factura'     => $idFactura,
                        'FK_servicio'    => $servicio->idServicio,
                        'cantidad'       => 1,
                        'precioUnitario' => $servicio->precio
                    ]);
                    $total += $servicio->precio;
                }

                // ACTUALIZAR CITA: Estado a Pagada (4) y Vincular Factura
                $cita->update([
                    'FK_estadoCita' => 4,
                    'FK_factura'    => $idFactura
                ]);
            }

            // Actualizar total real de la factura
            $factura->update(['montoTotal' => $total]);

            DB::commit();

            return redirect()->route('cliente.dashboard')->with('success', 'Factura ' . $idFactura . ' generada con éxito y citas marcadas como pagadas.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al generar la factura: ' . $e->getMessage());
        }
    }
}
