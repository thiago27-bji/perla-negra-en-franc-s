<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Personal;
use App\Models\Servicio;

class PersonalServicioController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'FK_personal' => 'required|exists:personal,idPersonal',
            'FK_servicio' => 'required|exists:servicio,idServicio',
        ]);

        $personal = Personal::findOrFail($request->FK_personal);
        
        // Adjuntar si no existe ya
        $personal->servicios()->syncWithoutDetaching([$request->FK_servicio]);

        return redirect()->route('personal.index')->with('success', 'Servicio vinculado correctamente al personal.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'FK_personal' => 'required|exists:personal,idPersonal',
            'FK_servicio' => 'required|exists:servicio,idServicio',
        ]);

        $personal = Personal::findOrFail($request->FK_personal);
        $personal->servicios()->detach($request->FK_servicio);

        return redirect()->route('personal.index')->with('success', 'Vinculo eliminado correctamente.');
    }
}
