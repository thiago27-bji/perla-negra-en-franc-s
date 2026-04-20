@extends('layouts.app')

@section('content')

<div class="container-fluid px-4 py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Panel de Administración</h2>
    </div>

    <!-- 🛠 ACCIONES DE GESTIÓN -->
    <div class="row g-4">
        <!-- PERSONAL -->
        <div class="col-md-3">
            <div class="card admin-card shadow-sm text-center p-4 h-100 border-0">
                <div class="bg-primary bg-opacity-10 rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <span class="fs-3">🧑‍💼</span>
                </div>
                <h4>Personal</h4>
                <p class="text-muted small">Gestión de empleados y perfiles.</p>
                <a href="{{ url('/admin/personal') }}" class="btn btn-primary w-100 mt-auto">Ir a Personal</a>
            </div>
        </div>

        <!-- SERVICIOS -->
        <div class="col-md-3">
            <div class="card admin-card shadow-sm text-center p-4 h-100 border-0">
                <div class="bg-success bg-opacity-10 rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <span class="fs-3">💇</span>
                </div>
                <h4>Servicios</h4>
                <p class="text-muted small">Catálogo y precios del salón.</p>
                <a href="{{ url('/admin/servicio') }}" class="btn btn-success w-100 mt-auto">Ir a Servicios</a>
            </div>
        </div>

        <!-- HORARIO -->
        <div class="col-md-3">
            <div class="card admin-card shadow-sm text-center p-4 h-100 border-0">
                <div class="bg-dark bg-opacity-10 rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <span class="fs-3">📅</span>
                </div>
                <h4>Horarios</h4>
                <p class="text-muted small">Turnos y disponibilidad semanal.</p>
                <a href="{{ url('/admin/disponibilidad') }}" class="btn btn-dark w-100 mt-auto">Ir a Horarios</a>
            </div>
        </div>

        <!-- INGRESOS -->
        <div class="col-md-3">
            <div class="card admin-card shadow-sm text-center p-4 h-100 border-0">
                <div class="bg-warning bg-opacity-10 rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <span class="fs-3">📊</span>
                </div>
                <h4>Ingresos</h4>
                <p class="text-muted small">Reportes de pagos y facturación.</p>
                <a href="{{ route('admin.ingresos') }}" class="btn btn-warning w-100 mt-auto">Ver Ingresos</a>
            </div>
        </div>
    </div>

</div>

<style>
.admin-card {
    border-radius: 20px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.admin-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important;
}
.rounded-4 { border-radius: 20px !important; }
</style>

@endsection