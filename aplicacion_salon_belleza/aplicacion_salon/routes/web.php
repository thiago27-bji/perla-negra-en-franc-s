<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PersonalController;
use App\Http\Controllers\Admin\DisponibilidadController;
use App\Http\Controllers\Admin\ServicioController;
use App\Http\Controllers\Admin\PersonalServicioController;
use App\Http\Controllers\Cliente\CitaController;
use App\Http\Controllers\Cliente\FacturaController;
use App\Models\Servicio;
use App\Models\Personal;
use App\Models\Cita;
use App\Models\Factura;

/* HOME */
Route::get('/', function () {
    $servicios = Servicio::has('personales')->get();
    return view('home', compact('servicios'));
});


/* REGISTRO */
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

/* LOGIN */
Route::get('/login', function () {
    return view('auth.login');
});


Route::post('/login', [AuthController::class, 'login']);

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/login');
})->name('logout');


/* CLIENTE */
Route::middleware(['auth'])->group(function () {
    Route::get('/cliente', function () {
        $servicios = Servicio::has('personales')->get();
        $personales = Personal::with(['servicios', 'disponibilidades'])->get(); 
        
        $citas = Cita::with(['personal', 'detalle.servicio', 'estado'])
                    ->where('FK_usuario', Auth::id())
                    ->get();
        
        $facturas = Factura::with(['detalles.servicio', 'estado'])
                          ->where('FK_usuario', Auth::id())
                          ->orderBy('fechaGeneracion', 'desc')
                          ->get();

        return view('cliente.cliente', compact('servicios', 'personales', 'citas', 'facturas'));
    })->name('cliente.dashboard');

    Route::post('/cliente/agendar', [CitaController::class, 'store'])->name('cita.store');
    Route::post('/cliente/agendar/cancelar/{id}', [CitaController::class, 'cancel'])->name('cita.cancel');
    Route::delete('/cliente/agendar/{id}', [CitaController::class, 'destroy'])->name('cita.destroy');
    Route::post('/cliente/facturar', [FacturaController::class, 'store'])->name('factura.store');
});

/* 🔐 RUTAS ADMIN PROTEGIDAS */
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin', [DashboardController::class, 'index']);
    Route::get('/admin/ingreso', [DashboardController::class, 'ingresos'])->name('admin.ingresos');
    Route::resource('/admin/personal', PersonalController::class);
    Route::resource('/admin/disponibilidad', DisponibilidadController::class);
    Route::resource('/admin/servicio', ServicioController::class);

    Route::post('/admin/personal-servicio', [PersonalServicioController::class, 'store'])->name('personal.servicio.store');
    Route::delete('/admin/personal-servicio', [PersonalServicioController::class, 'destroy'])->name('personal.servicio.destroy');
});