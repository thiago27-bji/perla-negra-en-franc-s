@extends('layouts.app')

@section('content')

<div class="container-fluid px-4 py-4">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold mb-0">Reporte de Ingresos</h2>
        </div>
        <a href="{{ url('/admin') }}" class="btn btn-secondary">Volver al Panel</a>
    </div>

    <!-- 📊 RESUMEN DE ESTADÍSTICAS (COPIA DEL DASHBOARD) -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100" style="background: linear-gradient(135deg, #198754, #20c997); color: white;">
                <p class="mb-1 opacity-75 small">Total Recaudado</p>
                <div class="d-flex align-items-center">
                    <h2 class="fw-bold mb-0 me-2">${{ number_format($totalIngresos, 0, ',', '.') }}</h2>
                    <span class="fs-4">💰</span>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100" style="background: linear-gradient(135deg, #fd7e14, #ffc107); color: white;">
                <p class="mb-1 opacity-75 small">Citas Pendientes</p>
                <div class="d-flex align-items-center">
                    <h2 class="fw-bold mb-0 me-2">{{ $citasPendientes }}</h2>
                    <span class="fs-4">⏳</span>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100" style="background: linear-gradient(135deg, #0d6efd, #0dcaf0); color: white;">
                <p class="mb-1 opacity-75 small">Clientes Registrados</p>
                <div class="d-flex align-items-center">
                    <h2 class="fw-bold mb-0 me-2">{{ $totalClientes }}</h2>
                    <span class="fs-4">👥</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 📊 TABLA DE INGRESOS -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="text-muted small text-uppercase">
                            <th class="ps-4">ID Factura</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Servicios</th>
                            <th class="text-end pe-4">Monto Total</th>
                            <th>PDF</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($facturas as $f)
                        <tr>
                            <td class="ps-4 py-3">
                                <span class="fw-bold text-dark">{{ $f->idFacturas }}</span>
                            </td>
                            <td>
                                <span class="text-muted">{{ \Carbon\Carbon::parse($f->fechaGeneracion)->format('d/m/Y') }}</span>
                                <br>
                                <small class="text-opacity-50 text-dark">{{ \Carbon\Carbon::parse($f->fechaGeneracion)->format('h:i A') }}</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                        <span class="text-primary small fw-bold">{{ substr($f->usuario->nombre, 0, 1) }}</span>
                                    </div>
                                    <div>
                                        <span class="d-block fw-bold text-dark">{{ $f->usuario->nombre }} {{ $f->usuario->apellido }}</span>
                                        <small class="text-muted">{{ $f->usuario->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($f->detalles as $det)
                                        <span class="badge bg-light text-dark border font-weight-normal">{{ $det->servicio->nombresServicio }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end align-items-center gap-2">
                                    <span class="fw-bold text-success fs-5">${{ number_format($f->montoTotal, 0, ',', '.') }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex justify-content-end align-items-center gap-2">
                                    <button class="btn btn-sm btn-primary" onclick="verDetalleFactura({{ json_encode($f->load('detalles.servicio', 'usuario')) }})">
                                        👁️ Ver
                                    </button>
                            </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <span class="fs-1 d-block mb-2">🌫️</span>
                                No se han registrado facturas pagadas todavía.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- 📄 CONTENEDOR VISUAL DE FACTURA (MODAL SIMULADO) -->
<div id="modal_factura" class="modal-overlay" style="display:none;">
    <div class="invoice-box shadow-lg">
        <div class="d-flex flex-column flex-sm-row justify-content-between border-bottom pb-3 mb-4 gap-3">
            <div>
                <h2 class="text-primary mb-0 fs-4">SALÓN PERLA NEGRA</h2>
                <p class="text-muted small m-0">Nit: 123456789-0 | Tel: 555-0199</p>
            </div>
            <div class="text-sm-end">
                <h5 class="mb-0">FACTURA DE VENTA</h5>
                <p id="factura_id_display" class="fw-bold text-danger m-0"></p>
                <small id="factura_fecha_display" class="text-muted"></small>
            </div>
        </div>

        <div class="mb-4">
            <h6 class="text-uppercase text-muted border-bottom pb-1 small">Cliente</h6>
            <p id="factura_cliente_display" class="fw-bold m-0"></p>
        </div>

        <div class="table-responsive">
            <table class="table table-sm table-striped">
                <thead class="bg-light">
                    <tr>
                        <th>Descripción</th>
                        <th class="text-center">Cant.</th>
                        <th class="text-end">Precio</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody id="factura_detalles_body"></tbody>
                <tfoot>
                    <tr class="fs-6 fw-bold">
                        <td colspan="3" class="text-end">TOTAL:</td>
                        <td id="factura_total_display" class="text-end text-success"></td>
                    </tr>
                </tfoot>
            </table>

        </div>

        <div class="text-center mt-5">
            <p class="small text-muted mb-4">¡Gracias por su visita!</p>
            <div class="no-print d-flex justify-content-center gap-2">
                <button class="btn btn-secondary btn-sm" onclick="cerrarFactura()">Cerrar</button>
                <button class="btn btn-primary btn-sm" onclick="window.print()">🖨️ Imprimir PDF</button>
            </div>
        </div>
    </div>
</div>

<style>
    .rounded-4 { border-radius: 20px !important; }
    .table thead th { border-top: none; padding-top: 15px; padding-bottom: 15px; }
    .badge { font-weight: 500; }

    /* Estilos Factura Modal Responsivo */
    .modal-overlay { 
        position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
        background: rgba(0,0,0,0.85); z-index: 9999; 
        display: flex; justify-content: center; align-items: start; padding: 10px;
        overflow-y: auto;
    }
    .invoice-box { 
        background: white; width: 100%; max-width: 650px; 
        padding: 20px; border-radius: 10px; font-family: 'Courier New', Courier, monospace;
        position: relative;
        margin-top: 20px;
    }
    @media (min-width: 768px) {
        .invoice-box { padding: 40px; margin-top: 50px; }
    }
    
    @media print {
        .no-print, nav, .nav-tabs, footer, .btn-close, .navbar { display: none !important; }
        .modal-overlay { position: absolute; background: white; padding: 0; }
        .invoice-box { width: 100%; box-shadow: none !important; border: none !important; margin: 0; }
        body { background: white !important; }
    }
</style>

<script>
function verDetalleFactura(factura) {
    document.getElementById('factura_id_display').innerText = '#' + factura.idFacturas;
    document.getElementById('factura_fecha_display').innerText = factura.fechaGeneracion;
    document.getElementById('factura_cliente_display').innerText = factura.usuario.nombre + ' ' + factura.usuario.apellido;
    document.getElementById('factura_total_display').innerText = '$' + factura.montoTotal.toLocaleString('es-CO');
    const body = document.getElementById('factura_detalles_body');
    body.innerHTML = '';
    factura.detalles.forEach(d => {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td>${d.servicio.nombresServicio}</td><td class="text-center">${d.cantidad}</td><td class="text-end">$${d.precioUnitario.toLocaleString('es-CO')}</td><td class="text-end">$${(d.cantidad * d.precioUnitario).toLocaleString('es-CO')}</td>`;
        body.appendChild(tr);
    });
    document.getElementById('modal_factura').style.display = 'flex';
}
function cerrarFactura() { document.getElementById('modal_factura').style.display = 'none'; }
</script>

@endsection
