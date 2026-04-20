@extends('layouts.app')

@section('content')

<div class="container mt-4">

    <h2 class="mb-4">Gestión de Personal</h2>

    {{-- BOTONES --}}
    <div class="mb-3 d-flex gap-2 flex-wrap">
        <button class="btn btn-primary" onclick="showForm('crear')">Crear Personal</button>
        <button class="btn btn-warning" onclick="showForm('editar_lista')">Editar Personal</button>
        <button class="btn btn-danger" onclick="showForm('eliminar_lista')">Eliminar Personal</button>
        <button class="btn btn-info" onclick="showForm('ver')">Ver Personal</button>
        <button class="btn btn-success" onclick="showForm('asignar_servicio')">Asignar Servicio</button>
        <a href="{{ url('/admin') }}" class="btn btn-secondary">Volver al Panel</a>
    </div>

    {{-- ===================== --}}
    {{-- FORM CREAR --}}
    {{-- ===================== --}}
    <div id="crear" class="card p-3 form-box" style="display:none;">
        <h4>Crear Personal</h4>
        <form method="POST" action="{{ route('personal.store') }}">
            @csrf
            <input type="number" name="idPersonal" class="form-control mb-2" placeholder="Cedula" required>
            <input type="text" name="nombre" class="form-control mb-2" placeholder="Nombre" required>
            <input type="text" name="apellido" class="form-control mb-2" placeholder="Apellido" required>
            <input type="text" name="especialidad" class="form-control mb-2" placeholder="Especialidad" required>
            <select name="estadoDisponible" class="form-control mb-2" required>
                <option value="1">Sí (Disponible)</option>
                <option value="0">No (No disponible)</option>
            </select>
            <button class="btn btn-success">Guardar</button>
        </form>
    </div>

    {{-- ===================== --}}
    {{-- ASIGNAR SERVICIO --}}
    {{-- ===================== --}}
    <div id="asignar_servicio" class="card p-3 form-box" style="display:none;">
        <h4>Vincular Servicio a Personal</h4>
        <form method="POST" action="{{ route('personal.servicio.store') }}">
            @csrf
            <label class="text-dark">Seleccionar Personal</label>
            <select name="FK_personal" class="form-control mb-2" required>
                <option value="">Seleccione un empleado...</option>
                @foreach($personales as $p)
                    <option value="{{ $p->idPersonal }}">{{ $p->nombre }} {{ $p->apellido }}</option>
                @endforeach
            </select>

            <label class="text-dark">Seleccionar Servicio</label>
            <select name="FK_servicio" class="form-control mb-2" required>
                <option value="">Seleccione un servicio...</option>
                @foreach($servicios as $s)
                    <option value="{{ $s->idServicio }}">{{ $s->nombresServicio }}</option>
                @endforeach
            </select>

            <button class="btn btn-success">Vincular</button>
        </form>

        <hr>
        <h5>Vínculos Actuales</h5>
        <div class="table-responsive">
            <table class="table table-sm text-dark">
                <thead>
                    <tr>
                        <th>Personal</th>
                        <th>Servicio</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($personales as $p)
                        @foreach($p->servicios as $s)
                        <tr>
                            <td>{{ $p->nombre }}</td>
                            <td>{{ $s->nombresServicio }}</td>
                            <td>
                                <form action="{{ route('personal.servicio.destroy') }}" method="POST" onsubmit="return confirm('¿Eliminar vínculo?')">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="FK_personal" value="{{ $p->idPersonal }}">
                                    <input type="hidden" name="FK_servicio" value="{{ $s->idServicio }}">
                                    <button class="btn btn-danger btn-sm">Quitar</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ===================== --}}
    {{-- SECCIÓN EDITAR (LISTA) --}}
    {{-- ===================== --}}
    <div id="editar_lista" class="card p-3 form-box" style="display:none;">
        <h4>Seleccione Personal para Editar</h4>
        <div class="list-group">
            @forelse($personales as $p)
                <button type="button" class="list-group-item list-group-item-action text-dark" onclick="cargarEdicion({{ json_encode($p) }})">
                    {{ $p->idPersonal }} - {{ $p->nombre }} {{ $p->apellido }}
                </button>
            @empty
                <p class="text-dark">No hay personal registrado.</p>
            @endforelse
        </div>
    </div>

    {{-- ===================== --}}
    {{-- FORM REAL DE EDICIÓN (OCULTO) --}}
    {{-- ===================== --}}
    <div id="editar_form" class="card p-3 form-box" style="display:none;">
        <h4>Editar Personal</h4>
        <form id="form-actualizar" method="POST" action="">
            @csrf
            @method('PUT')
            <label class="text-dark">Cédula (No editable)</label>
            <input type="number" id="edit_id" class="form-control mb-2" disabled>
            <input type="text" name="nombre" id="edit_nombre" class="form-control mb-2" placeholder="Nombre" required>
            <input type="text" name="apellido" id="edit_apellido" class="form-control mb-2" placeholder="Apellido" required>
            <input type="text" name="especialidad" id="edit_especialidad" class="form-control mb-2" placeholder="Especialidad" required>
            <select name="estadoDisponible" id="edit_estado" class="form-control mb-2" required>
                <option value="1">Disponible</option>
                <option value="0">No disponible</option>
            </select>
            <button class="btn btn-warning text-white">Actualizar</button>
            <button type="button" class="btn btn-secondary" onclick="showForm('editar_lista')">Cancelar</button>
        </form>
    </div>

    {{-- ===================== --}}
    {{-- SECCIÓN ELIMINAR --}}
    {{-- ===================== --}}
    <div id="eliminar_lista" class="card p-3 form-box" style="display:none;">
        <h4>Eliminar Personal</h4>
        <div class="list-group">
            @forelse($personales as $p)
                <div class="list-group-item d-flex justify-content-between align-items-center text-dark">
                    {{ $p->idPersonal }} - {{ $p->nombre }} {{ $p->apellido }}
                    <form action="{{ route('personal.destroy', $p->idPersonal) }}" method="POST" onsubmit="return confirm('¿Eliminar a {{ $p->nombre }}?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm">Eliminar</button>
                    </form>
                </div>
            @empty
                <p class="text-dark">No hay personal registrado.</p>
            @endforelse
        </div>
    </div>

    {{-- ===================== --}}
    {{-- SECCIÓN VER --}}
    {{-- ===================== --}}
    <div id="ver" class="card p-3 form-box">
        <h4>Lista de Personal</h4>
        <div class="table-responsive">
            <table class="table table-bordered text-dark">
                <thead>
                    <tr>
                        <th>Cédula</th>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Especialidad</th>
                        <th>Servicios</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($personales as $p)
                    <tr>
                        <td>{{ $p->idPersonal }}</td>
                        <td>{{ $p->nombre }}</td>
                        <td>{{ $p->apellido }}</td>
                        <td>{{ $p->especialidad }}</td>
                        <td>
                            @foreach($p->servicios as $s)
                                <span class="badge bg-secondary">{{ $s->nombresServicio }}</span>
                            @endforeach
                        </td>
                        <td>{{ $p->estadoDisponible ? 'Disponible' : 'No disponible' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

<style>
    .form-box { background: white; color: black; margin-top: 20px; }
    .badge { margin-right: 2px; }

    /* 📱 RESPONSIVE - GESTIÓN PERSONAL */
@media (max-width: 768px) {

    h2 {
        font-size: 20px;
        text-align: center;
    }

    h4 {
        font-size: 16px;
        text-align: center;
    }

    /* Botones en columna */
    .d-flex {
        flex-direction: column !important;
        gap: 10px;
    }

    .d-flex .btn {
        width: 100%;
        font-size: 14px;
        padding: 10px;
    }

    /* Formularios */
    .form-box {
        padding: 15px;
        border-radius: 10px;
    }

    .form-control {
        font-size: 14px;
        padding: 8px;
    }

    /* Tablas */
    .table {
        font-size: 12px;
    }

    .table th, 
    .table td {
        padding: 6px;
        white-space: nowrap;
    }

    /* Scroll horizontal en tablas */
    .table-responsive {
        overflow-x: auto;
    }

    /* Badges (servicios) */
    .badge {
        display: inline-block;
        margin-bottom: 4px;
        font-size: 11px;
        padding: 4px 6px;
    }

    /* Lista (editar / eliminar) */
    .list-group-item {
        font-size: 13px;
        padding: 10px;
    }

    /* Botones pequeños */
    .btn-sm {
        font-size: 12px;
        padding: 4px 8px;
    }
}
</style>

{{-- JS --}}
<script>
function showForm(id) {
    document.querySelectorAll('.form-box').forEach(div => {
        div.style.display = 'none';
    });
    document.getElementById(id).style.display = 'block';
}

function cargarEdicion(p) {
    showForm('editar_form');
    document.getElementById('form-actualizar').action = "/admin/personal/" + p.idPersonal;
    document.getElementById('edit_id').value = p.idPersonal;
    document.getElementById('edit_nombre').value = p.nombre;
    document.getElementById('edit_apellido').value = p.apellido;
    document.getElementById('edit_especialidad').value = p.especialidad;
    document.getElementById('edit_estado').value = p.estadoDisponible;
}
</script>

@endsection