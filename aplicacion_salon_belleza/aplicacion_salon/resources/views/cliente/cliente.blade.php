@extends('layouts.app')

@section('content')

<div class="container mt-4">

    <!-- 🔝 MENÚ TIPO BARRA -->
    <ul class="nav nav-tabs justify-content-center mb-4">
        <li class="nav-item">
            <a class="nav-link active" id="btn-servicios" onclick="showTab('servicios')">💇 Servicios</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="btn-citas" onclick="showTab('citas')">📅 Agendados</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="btn-facturas" onclick="showTab('facturas')">💳 Facturación</a>
        </li>
    </ul>

    <!-- 📦 CONTENIDO SERVICIOS -->
    <div id="servicios" class="tab-content-box">
        <div class="row">
            @forelse($servicios as $s)
            <div class="col-md-4">
                <div class="card mb-4 shadow-sm border-0 h-100">
                    @if($s->imagen)
                        <img src="{{ asset('storage/' . $s->imagen) }}" class="card-img-top" style="height: 180px; object-fit: cover;">
                    @else
                        <div class="bg-light text-muted d-flex align-items-center justify-content-center" style="height: 180px;">Sin imagen</div>
                    @endif

                    <div class="card-body text-center d-flex flex-column">
                        <h5 class="fw-bold">{{ $s->nombresServicio }}</h5>
                        <p class="small text-muted flex-grow-1">{{ $s->descripcion }}</p>
                        <p class="mb-1 text-primary fw-bold fs-5">${{ number_format($s->precio, 0, ',', '.') }}</p>

                        <button class="btn btn-primary w-100 mt-2" onclick="abrirAgendamiento({{ json_encode($s) }})">
                            📅 Agendar Ahora
                        </button>
                    </div>
                </div>
            </div>
            @empty
                <p class="text-center">No hay servicios disponibles.</p>
            @endforelse
        </div>
    </div>

    <!-- 🗓 CONTENEDOR DE AGENDAMIENTO (OCULTO) -->
    <div id="agendar_contenedor" class="card p-4 shadow-lg mb-5" style="display:none; border-radius: 15px;">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 id="agendar_titulo">Agendar Cita</h3>
            <button class="btn-close" onclick="cerrarAgendamiento()"></button>
        </div>

        <div id="info_disponibilidad" class="alert alert-info mb-3" style="display:none;">
            <strong>Horarios de trabajo:</strong> <span id="disponibilidad_texto"></span>
        </div>

        <form method="POST" action="{{ route('cita.store') }}" id="form_reserva">
            @csrf
            <input type="hidden" name="idServicio" id="input_servicio_id">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Personal Disponible</label>
                    <select name="FK_personal" id="select_personal" class="form-control" required onchange="actualizarInfoDisponibilidad()">
                        <option value="">Seleccione un profesional...</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Fecha Disponible</label>
                    <select name="fecha" id="select_fecha" class="form-control" required onchange="generarHoras()">
                        <option value="">Primero elige un profesional...</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Hora Disponible</label>
                    <select name="hora" id="select_hora" class="form-control" required>
                        <option value="">Elije una fecha...</option>
                    </select>
                </div>

                <div class="col-md-6 d-flex align-items-end mb-3">
                    <button type="submit" class="btn btn-success w-100 py-2 fw-bold">Confirmar Cita</button>
                </div>
            </div>
        </form>
    </div>

    <!-- 📦 CONTENIDO CITAS AGENDADAS -->
    <div id="citas" class="tab-content-box" style="display:none;">
        <h3 class="mb-4">Mis citas agendadas</h3>
        <div class="table-responsive">
            <table class="table table-hover shadow-sm bg-white">
                <thead class="bg-dark text-white">
                    <tr>
                        <th>Fecha y Hora</th>
                        <th>Servicio</th>
                        <th>Personal</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($citas as $c)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($c->fechaCita)->format('d/m/Y - h:i A') }}</td>
                        <td>{{ $c->detalle->servicio->nombresServicio ?? 'N/A' }}</td>
                        <td>{{ $c->personal->nombre }} {{ $c->personal->apellido }}</td>
                        <td>
                            @php
                                $badgeClass = match($c->FK_estadoCita) {
                                    1 => 'bg-warning text-dark',
                                    2 => 'bg-success',
                                    3 => 'bg-danger',
                                    4 => 'bg-info', 
                                    default => 'bg-secondary'
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $c->estado->nombre }}</span>
                        </td>
                        <td>
                            @if($c->FK_estadoCita == 1)
                                <form action="{{ route('cita.cancel', $c->idCita) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas cancelar esta cita?')">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-danger">Cancelar Cita</button>
                                </form>
                            @elseif($c->FK_estadoCita == 4)
                                <span class="text-success small fw-bold">✓ Pagada / Facturada</span>
                            @else
                                <span class="text-muted small italic">Sin acciones</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">No tienes citas agendadas actualmente.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 📦 CONTENIDO FACTURACIÓN -->
    <div id="facturas" class="tab-content-box" style="display:none;">
        <div class="row">
            <div class="col-md-7">
                <h3 class="mb-3">Servicios Pendientes de Pago</h3>
                <p class="text-muted">Selecciona las citas que deseas incluir en tu próxima factura.</p>

                <form method="POST" action="{{ route('factura.store') }}">
                    @csrf
                    <div class="table-responsive border rounded bg-light p-2 mb-3" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-sm text-dark">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Servicio</th>
                                    <th>Precio</th>
                                    <th>Personal</th>
                                    <th>Pagar</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($citas->whereIn('FK_estadoCita', [1, 2]) as $c)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($c->fechaCita)->format('d/m/y') }}</td>
                                    <td>{{ $c->detalle->servicio->nombresServicio ?? 'N/A' }}</td>
                                    <td class="fw-bold text-primary">${{ number_format($c->detalle->servicio->precio ?? 0, 0, ',', '.') }}</td>
                                    <td>{{ $c->personal->nombre }}</td>
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input check-factura" type="checkbox" name="citas[]" 
                                                   value="{{ $c->idCita }}" 
                                                   data-precio="{{ $c->detalle->servicio->precio }}"
                                                   onchange="calcularTotalFactura()">
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No tienes servicios pendientes para facturar.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center p-3 bg-white border rounded shadow-sm">
                        <div>
                            <span class="text-muted">Monto Total:</span>
                            <h4 id="total_factura_label" class="mb-0 text-success fw-bold">$0</h4>
                        </div>
                        <button type="submit" class="btn btn-success btn-lg px-5 shadow" id="btn_generar_factura" disabled>Generar y Pagar</button>
                    </div>
                </form>
            </div>

            <div class="col-md-5 mt-4 mt-md-0">
                <h3 class="mb-3">Historial de Facturas</h3>
                <div class="list-group">
                    @forelse($facturas as $f)
                    <div class="list-group-item list-group-item-action shadow-sm mb-3 border-0 rounded p-3" style="border-left: 6px solid #198754 !important;">
                        <div class="d-flex w-100 justify-content-between align-items-center">
                            <h6 class="mb-1 fw-bold text-dark">{{ $f->idFacturas }}</h6>
                            <small class="badge bg-light text-dark border">{{ \Carbon\Carbon::parse($f->fechaGeneracion)->format('d/m/Y') }}</small>
                        </div>
                        <p class="mb-2 fs-5 text-success fw-bold">${{ number_format($f->montoTotal, 0, ',', '.') }}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">{{ $f->detalles->count() }} servicios</span>
                            <button class="btn btn-sm btn-primary" onclick="verDetalleFactura({{ json_encode($f->load('detalles.servicio', 'usuario')) }})">
                                👁️ Ver Detalle
                            </button>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-5 bg-light rounded text-muted">Aún no hay facturas generadas.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

</div>

<!-- 📄 CONTENEDOR VISUAL DE FACTURA (MODAL SIMULADO) -->
<div id="modal_factura" class="modal-overlay" style="display:none;">
    <div class="invoice-box shadow-lg animate__animated animate__zoomIn">
        <div class="d-flex justify-content-between border-bottom pb-3 mb-4">
            <div>
                <h2 class="text-primary mb-0">SALÓN PERLA NEGRA</h2>
                <p class="text-muted small m-0">Nit: 123456789-0 | Tel: 555-0199</p>
            </div>
            <div class="text-end">
                <h4 class="mb-0">FACTURA DE VENTA</h4>
                <p id="factura_id_display" class="fw-bold text-danger m-0"></p>
                <small id="factura_fecha_display" class="text-muted"></small>
            </div>
        </div>

        <div class="mb-4">
            <h6 class="text-uppercase text-muted border-bottom pb-1">Cliente</h6>
            <p id="factura_cliente_display" class="fw-bold m-0"></p>
        </div>

        <table class="table table-sm table-striped">
            <thead class="bg-light">
                <tr>
                    <th>Descripción Servicio</th>
                    <th class="text-center">Cant.</th>
                    <th class="text-end">Precio</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody id="factura_detalles_body">
                {{-- Se llena por JS --}}
            </tbody>
            <tfoot>
                <tr class="fs-5 fw-bold">
                    <td colspan="3" class="text-end">TOTAL A PAGAR:</td>
                    <td id="factura_total_display" class="text-end text-success"></td>
                </tr>
            </tfoot>
        </table>

        <div class="text-center mt-5">
            <p class="small text-muted">¡Gracias por preferir Perla Negra! Tu belleza es nuestra pasión.</p>
            <div class="no-print">
                <button class="btn btn-secondary me-2" onclick="cerrarFactura()">Cerrar</button>
                <button class="btn btn-primary" onclick="window.print()">🖨️ Imprimir PDF</button>
            </div>
        </div>
    </div>
</div>

<style>
    .tab-content-box {
    background: rgba(255, 255, 255, 0.08); /* transparencia */
    backdrop-filter: blur(10px); /* efecto vidrio */
    -webkit-backdrop-filter: blur(10px);
    border-radius: 20px;
    padding: 25px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    border: 1px solid rgba(255,255,255,0.2);
}
    .nav-tabs .nav-link { cursor: pointer; color: #555; font-weight: 500; border: none; }
    .nav-tabs .nav-link.active { color: #007bff; border-bottom: 3px solid #007bff; background: transparent; }
    .nav-tabs { border-bottom: 2px solid rgba(0,0,0,0.05); }
    .table { color: #333 !important; }
    .table thead th { background: #f8f9fa !important; color: #333 !important; }
    .table tbody td { color: #333 !important; }
    .card { background: white; color: #333; }
    .row{background: transparent;}

    /* Estilos Factura Modal */
    .modal-overlay { 
        position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
        background: rgba(0,0,0,0.6); z-index: 9999; 
        display: flex; justify-content: center; align-items: start; padding-top: 50px;
        overflow-y: auto;
    }
    .invoice-box { 
        background: white; width: 90%; max-width: 700px; 
        padding: 40px; border-radius: 5px; font-family: 'Courier New', Courier, monospace;
        position: relative;
    }
    
    @media print {
        .no-print, nav, .nav-tabs, footer, .btn-close { display: none !important; }
        .modal-overlay { position: absolute; background: white; padding: 0; }
        .invoice-box { width: 100%; box-shadow: none !important; border: none !important; }
        body { background: white !important; }
    }
    /* 📱 RESPONSIVE GENERAL */
@media (max-width: 768px) {

    .container {
        padding: 10px;
    }

    /* MENÚ */
    .nav-tabs {
        flex-direction: column;
        align-items: center;
        gap: 10px;
    }

    .nav-tabs .nav-item {
        width: 100%;
    }

    .nav-tabs .nav-link {
        width: 100%;
        text-align: center;
        border-radius: 10px;
        background: rgba(255,255,255,0.1);
    }

    /* CARDS SERVICIOS */
    .card img {
        height: 150px !important;
    }

    .card-body h5 {
        font-size: 16px;
    }

    .card-body p {
        font-size: 13px;
    }

    .card-body .btn {
        font-size: 14px;
        padding: 8px;
    }

    /* GRID SERVICIOS */
    .col-md-4 {
        width: 100%;
    }

    /* FORMULARIO AGENDAR */
    #agendar_contenedor .row {
        flex-direction: column;
    }

    #agendar_contenedor .col-md-6 {
        width: 100%;
    }

    /* TABLAS */
    .table {
        font-size: 12px;
    }

    .table th, .table td {
        padding: 6px;
    }

    /* FACTURACIÓN */
    .invoice-box {
        padding: 20px;
    }

    /* BOTONES GRANDES */
    .btn-lg {
        font-size: 14px;
        padding: 10px;
    }
}
</style>

<script>
const personales = @json($personales);
const diasNombres = ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'];

function showTab(tab) {
    document.querySelectorAll('.tab-content-box').forEach(div => div.style.display = 'none');
    document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
    document.getElementById(tab).style.display = 'block';
    const btnId = 'btn-' + tab;
    if(document.getElementById(btnId)) document.getElementById(btnId).classList.add('active');
}

function calcularTotalFactura() {
    let total = 0;
    const checks = document.querySelectorAll('.check-factura:checked');
    const btn = document.getElementById('btn_generar_factura');
    checks.forEach(c => total += parseFloat(c.dataset.precio));
    document.getElementById('total_factura_label').innerText = '$' + total.toLocaleString('es-CO');
    btn.disabled = checks.length === 0;
}

function verDetalleFactura(factura) {
    document.getElementById('factura_id_display').innerText = '#' + factura.idFacturas;
    document.getElementById('factura_fecha_display').innerText = factura.fechaGeneracion;
    document.getElementById('factura_cliente_display').innerText = factura.usuario.nombre + ' ' + factura.usuario.apellido;
    document.getElementById('factura_total_display').innerText = '$' + factura.montoTotal.toLocaleString('es-CO');

    const body = document.getElementById('factura_detalles_body');
    body.innerHTML = '';
    factura.detalles.forEach(d => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${d.servicio.nombresServicio}</td>
            <td class="text-center">${d.cantidad}</td>
            <td class="text-end">$${d.precioUnitario.toLocaleString('es-CO')}</td>
            <td class="text-end">$${(d.cantidad * d.precioUnitario).toLocaleString('es-CO')}</td>
        `;
        body.appendChild(tr);
    });

    document.getElementById('modal_factura').style.display = 'flex';
}

function cerrarFactura() {
    document.getElementById('modal_factura').style.display = 'none';
}

// ... Resto de funciones JS del agendamiento (igual que antes) ...
function abrirAgendamiento(servicio) {
    document.getElementById('servicios').style.display = 'none';
    document.getElementById('agendar_contenedor').style.display = 'block';
    document.getElementById('agendar_titulo').innerText = 'Agendar: ' + servicio.nombresServicio;
    document.getElementById('input_servicio_id').value = servicio.idServicio;
    const select = document.getElementById('select_personal');
    select.innerHTML = '<option value="">Seleccione un profesional...</option>';
    personales.forEach(p => {
        if (p.servicios.some(s => s.idServicio === servicio.idServicio)) {
            const option = document.createElement('option');
            option.value = p.idPersonal;
            option.text = p.nombre + ' ' + p.apellido + ' (' + p.especialidad + ')';
            select.appendChild(option);
        }
    });
}
function actualizarInfoDisponibilidad() {
    const pId = document.getElementById('select_personal').value;
    const selectFecha = document.getElementById('select_fecha');
    if (!pId) return;
    const personal = personales.find(p => p.idPersonal == pId);
    selectFecha.innerHTML = '<option value="">Seleccione una fecha...</option>';
    const diasPermitidos = personal.disponibilidades.map(d => d.diaSemana);
    const hoy = new Date();
    for (let i = 1; i <= 15; i++) {
        let fl = new Date(); fl.setDate(hoy.getDate() + i);
        let dn = diasNombres[fl.getDay()];
        if (diasPermitidos.includes(dn)) {
            const opt = document.createElement('option');
            const dateStr = `${fl.getFullYear()}-${String(fl.getMonth()+1).padStart(2,'0')}-${String(fl.getDate()).padStart(2,'0')}`;
            opt.value = dateStr; opt.text = `${dn} ${fl.getDate()}/${fl.getMonth()+1}`; opt.dataset.dia = dn;
            selectFecha.appendChild(opt);
        }
    }
}
function generarHoras() {
    const pId = document.getElementById('select_personal').value;
    const fs = document.getElementById('select_fecha');
    const dn = fs.options[fs.selectedIndex].dataset.dia;
    const sh = document.getElementById('select_hora');
    if (!pId || !dn) return;
    const p = personales.find(p => p.idPersonal == pId);
    const disp = p.disponibilidades.find(d => d.diaSemana == dn);
    sh.innerHTML = '<option value="">Seleccione una hora...</option>';
    if (disp) {
        let act = disp.horaInicio; let fin = disp.horaFin;
        while (act < fin) {
            const opt = document.createElement('option'); opt.value = act; opt.text = act.substring(0, 5);
            sh.appendChild(opt);
            let [h, m, s] = act.split(':').map(Number); m += 30; if (m >= 60) { h++; m = 0; }
            act = `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:00`;
        }
    }
}
function cerrarAgendamiento() { document.getElementById('agendar_contenedor').style.display = 'none'; document.getElementById('servicios').style.display = 'block'; }
</script>

@endsection