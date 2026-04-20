<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Personal;
use App\Models\Servicio;

class PersonalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $personales = Personal::with('servicios')->get();
        $servicios = Servicio::all();
        return view('admin.personal', compact('personales', 'servicios'));
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
            'idPersonal' => 'required|unique:personal,idPersonal',
            'nombre'     => 'required|string|max:100',
            'apellido'   => 'required|string|max:100',
            'especialidad' => 'required|string|max:100',
            'estadoDisponible' => 'required|boolean'
        ]);

        Personal::create($validated);
        return redirect()->route('personal.index')->with('success', 'Personal creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $personal = Personal::findOrFail($id);
        return response()->json($personal);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $personal = Personal::findOrFail($id);
        return response()->json($personal);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'nombre'     => 'required|string|max:100',
            'apellido'   => 'required|string|max:100',
            'especialidad' => 'required|string|max:100',
            'estadoDisponible' => 'required|boolean'
        ]);

        $personal = Personal::findOrFail($id);
        $personal->update($validated);

        return redirect()->route('personal.index')->with('success', 'Datos de personal actualizados.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $personal = Personal::findOrFail($id);
        $personal->delete();

        return redirect()->route('personal.index')->with('success', 'Personal eliminado correctamente.');
    }
}
