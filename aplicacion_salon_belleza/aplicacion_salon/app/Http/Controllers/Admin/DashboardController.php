<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Factura;
use App\Models\Cita;
use App\Models\User;

class DashboardController extends Controller
{
    /**
     * Display the main admin dashboard with high-level stats.
     */
    public function index()
    {
        $totalIngresos = Factura::sum('montoTotal');
        $citasPendientes = Cita::where('FK_estadoCita', 1)->count();
        $totalClientes = User::where('FK_rol', 2)->count();
        
        return view('admin.dashboard', compact('totalIngresos', 'citasPendientes', 'totalClientes'));
    }

    /**
     * Display the detailed revenue report (paid invoices).
     */
    public function ingresos()
    {
        $facturas = Factura::with(['usuario', 'detalles.servicio'])
                          ->orderBy('fechaGeneracion', 'desc')
                          ->get();
        
        $totalIngresos = Factura::sum('montoTotal');
        $citasPendientes = Cita::where('FK_estadoCita', 1)->count();
        $totalClientes = User::where('FK_rol', 2)->count();

        return view('admin.ingresos', compact('facturas', 'totalIngresos', 'citasPendientes', 'totalClientes'));
    }
}
