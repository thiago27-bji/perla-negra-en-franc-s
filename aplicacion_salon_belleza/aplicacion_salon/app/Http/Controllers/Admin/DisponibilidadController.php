<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Disponibilidad;
use App\Models\Personal;

class DisponibilidadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $disponibilidades = Disponibilidad::with('personal')->get();
        $personales = Personal::all();

        return view('admin.disponibilidad', compact('disponibilidades', 'personales'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'FK_personal' => 'required|exists:personal,idPersonal',
            'diaSemana'   => 'required|string|max:50',
            'horaInicio'  => 'required',
            'horaFin'     => 'required'
        ]);

        Disponibilidad::create($validated);
        return redirect()->route('disponibilidad.index')->with('success', 'Horario asignado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $disponibilidad = Disponibilidad::with('personal')->findOrFail($id);
        return response()->json($disponibilidad);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $disponibilidad = Disponibilidad::findOrFail($id);
        return response()->json($disponibilidad);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'FK_personal' => 'required|exists:personal,idPersonal',
            'diaSemana'   => 'required|string|max:50',
            'horaInicio'  => 'required',
            'horaFin'     => 'required'
        ]);

        $disponibilidad = Disponibilidad::findOrFail($id);
        $disponibilidad->update($validated);

        return redirect()->route('disponibilidad.index')->with('success', 'Horario actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $disponibilidad = Disponibilidad::findOrFail($id);
        $disponibilidad->delete();

        return redirect()->route('disponibilidad.index')->with('success', 'Horario eliminado correctamente.');
    }
}
