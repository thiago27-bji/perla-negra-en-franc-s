<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cita;
use App\Models\DetalleCita;
use App\Models\Personal;
use App\Models\Disponibilidad;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CitaController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'FK_personal' => 'required|exists:personal,idPersonal',
            'idServicio'  => 'required|exists:servicio,idServicio',
            'fecha'       => 'required|date|after:today',
            'hora'        => 'required',
        ]);

        $fechaSeleccionada = Carbon::parse($request->fecha);
        $horaSeleccionada = Carbon::createFromFormat('H:i:s', $request->hora);

        // 1. Mapear día de la semana a español
        $diasEspañol = [
            0 => 'Domingo',
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miercoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sabado',
        ];
        $diaNombre = $diasEspañol[$fechaSeleccionada->dayOfWeek];

        // 2. Verificar disponibilidad del personal para ese día
        // Usamos whereRaw para evitar problemas con acentos o espacios en el nombre del día si los hubiera
        $disponibilidad = Disponibilidad::where('FK_personal', $request->FK_personal)
            ->where('diaSemana', 'like', $diaNombre . '%')
            ->first();

        if (!$disponibilidad) {
            return back()->with('error', "El profesional seleccionado no trabaja los días " . $diaNombre . ".");
        }

        // 3. Verificar rango de horas (Convertimos a Carbon para asegurar comparación numérica)
        $inicio = Carbon::createFromFormat('H:i:s', $disponibilidad->horaInicio);
        $fin = Carbon::createFromFormat('H:i:s', $disponibilidad->horaFin);

        if ($horaSeleccionada->lt($inicio) || $horaSeleccionada->gt($fin)) {
            return back()->with('error', "El profesional solo está disponible entre las " . $inicio->format('H:i') . " y las " . $fin->format('H:i') . " los " . $diaNombre . ".");
        }

        // 4. Verificar colisión de citas (Misma hora, mismo personal)
        $colision = Cita::where('FK_personal', $request->FK_personal)
            ->where('fechaCita', $request->fecha . ' ' . $request->hora)
            ->whereIn('FK_estadoCita', [1, 2]) // Pendiente o Confirmada
            ->exists();

        if ($colision) {
            return back()->with('error', "Este horario ya está reservado para el profesional seleccionado. Por favor elige otra hora.");
        }

        try {
            DB::beginTransaction();

            // Crear la Cita
            $cita = Cita::create([
                'FK_usuario'    => Auth::id(),
                'FK_personal'   => $request->FK_personal,
                'fechaCita'     => $request->fecha . ' ' . $request->hora,
                'FK_estadoCita' => 1, // Pendiente
            ]);

            // Crear el Detalle
            DetalleCita::create([
                'FK_cita'     => $cita->idCita,
                'FK_servicio' => $request->idServicio,
            ]);

            DB::commit();

            return redirect()->route('cliente.dashboard')->with('success', '¡Cita agendada con éxito!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al procesar la cita: ' . $e->getMessage());
        }
    }

    /**
     * Cancel the specified appointment.
     */
    public function cancel($id)
    {
        $cita = Cita::where('idCita', $id)
                    ->where('FK_usuario', Auth::id())
                    ->firstOrFail();

        // Solo permitir cancelar si está Pendiente (1) o Confirmada (2)
        if (!in_array($cita->FK_estadoCita, [1, 2])) {
            return back()->with('error', 'Esta cita no puede ser cancelada en su estado actual.');
        }

        $cita->update(['FK_estadoCita' => 3]); // 3 = Cancelada

        return back()->with('success', 'Cita cancelada correctamente.');
    }

    /**
     * Remove the specified appointment from storage.
     */
    public function destroy($id)
    {
        $cita = Cita::where('idCita', $id)
                    ->where('FK_usuario', Auth::id())
                    ->firstOrFail();

        $cita->delete();

        return back()->with('success', 'Cita eliminada correctamente del sistema.');
    }
}
