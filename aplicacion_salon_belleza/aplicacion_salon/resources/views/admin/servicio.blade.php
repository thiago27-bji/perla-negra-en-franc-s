@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <h2 class="mb-4">Gestión de Servicios</h2>

    {{-- BOTONES --}}
    <div class="mb-3 d-flex gap-2 flex-wrap">
        <button class="btn btn-primary" onclick="showForm('crear')">Crear Servicio</button>
        <button class="btn btn-warning" onclick="showForm('editar_lista')">Editar Servicio</button>
        <button class="btn btn-danger" onclick="showForm('eliminar_lista')">Eliminar Servicio</button>
        <button class="btn btn-info" onclick="showForm('ver')">Ver Servicios</button>
        <a href="{{ url('/admin') }}" class="btn btn-secondary">Volver al Panel</a>
    </div>
    
    {{-- ===================== --}}
    {{-- FORM CREAR --}}
    {{-- ===================== --}}
    <div id="crear" class="card p-3 form-box">
        <h4>Crear Servicio</h4>
        <form method="POST" action="{{ route('servicio.store') }}" enctype="multipart/form-data">
            @csrf
            <label class="text-dark">Nombre del Servicio</label>
            <input type="text" name="nombresServicio" class="form-control mb-2" placeholder="Ej: Corte de Cabello" required>
            
            <label class="text-dark">Descripción</label>
            <textarea name="descripcion" class="form-control mb-2" placeholder="Breve descripción..."></textarea>
            
            <label class="text-dark">Duración (Minutos)</label>
            <input type="number" name="duracionMinuto" class="form-control mb-2" placeholder="Ej: 30" required>
            
            <label class="text-dark">Precio</label>
            <input type="number" step="0.01" name="precio" class="form-control mb-2" placeholder="Ej: 15.50" required>
            
            <label class="text-dark">Cargar Imagen</label>
            <input type="file" name="imagen" class="form-control mb-2" accept="image/*">
            
            <button class="btn btn-success">Guardar</button>
        </form>
    </div>

    {{-- ===================== --}}
    {{-- SECCIÓN EDITAR (LISTA) --}}
    {{-- ===================== --}}
    <div id="editar_lista" class="card p-3 form-box" style="display:none;">
        <h4>Seleccione Servicio para Editar</h4>
        <div class="list-group">
            @forelse($servicios as $s)
                <button type="button" class="list-group-item list-group-item-action text-dark" onclick="cargarEdicion({{ json_encode($s) }})">
                    {{ $s->idServicio }} - {{ $s->nombresServicio }} (${{ $s->precio }})
                </button>
            @empty
                <p class="text-dark">No hay servicios registrados.</p>
            @endforelse
        </div>
    </div>
    
    {{-- ===================== --}}
    {{-- FORM REAL DE EDICIÓN (OCULTO) --}}
    {{-- ===================== --}}
    <div id="editar_form" class="card p-3 form-box" style="display:none;">
        <h4>Editar Servicio</h4>
        <form id="form-actualizar" method="POST" action="" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <label class="text-dark">Nombre del Servicio</label>
            <input type="text" name="nombresServicio" id="edit-nombre" class="form-control mb-2" required>
            
            <label class="text-dark">Descripción</label>
            <textarea name="descripcion" id="edit-descripcion" class="form-control mb-2"></textarea>
            
            <label class="text-dark">Duración (Minutos)</label>
            <input type="number" name="duracionMinuto" id="edit-duracion" class="form-control mb-2" required>
            
            <label class="text-dark">Precio</label>
            <input type="number" step="0.01" name="precio" id="edit-precio" class="form-control mb-2" required>
            
            <label class="text-dark">Actualizar Imagen (Dejar vacío para conservar la anterior)</label>
            <input type="file" name="imagen" class="form-control mb-2" accept="image/*">

            <button class="btn btn-warning text-white">Actualizar Cambios</button>
            <button type="button" class="btn btn-secondary" onclick="showForm('editar_lista')">Cancelar</button>
        </form>
    </div>
    
    {{-- ===================== --}}
    {{-- SECCIÓN ELIMINAR (LISTA) --}}
    {{-- ===================== --}}
    <div id="eliminar_lista" class="card p-3 form-box" style="display:none;">
        <h4>Seleccione Servicio para Eliminar</h4>
        <div class="list-group">
            @forelse($servicios as $s)
                <div class="list-group-item d-flex justify-content-between align-items-center text-dark">
                    {{ $s->idServicio }} - {{ $s->nombresServicio }}
                    <form action="{{ route('servicio.destroy', $s->idServicio) }}" method="POST" onsubmit="return confirm('¿Eliminar servicio {{ $s->nombresServicio }}?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                    </form>
                </div>
            @empty
                <p class="text-dark">No hay servicios registrados.</p>
            @endforelse
        </div>
    </div>

    {{-- ===================== --}}
    {{-- SECCIÓN VER --}}
    {{-- ===================== --}}
    <div id="ver" class="card p-3 form-box" style="display:none;">
        <h4>Servicios Registrados</h4>
        <div class="table-responsive">
            <table class="table table-bordered text-dark">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Imagen</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Duración</th>
                        <th>Precio</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($servicios as $s)
                    <tr>
                        <td>{{ $s->idServicio }}</td>
                        <td>
                            @if($s->imagen)
                                <img src="{{ asset('storage/' . $s->imagen) }}" alt="{{ $s->nombresServicio }}" style="max-height: 50px; border-radius: 5px;">
                            @else
                                <span class="text-muted">Sin imagen</span>
                            @endif
                        </td>
                        <td>{{ $s->nombresServicio }}</td>
                        <td>{{ $s->descripcion }}</td>
                        <td>{{ $s->duracionMinuto }} min</td>
                        <td>${{ number_format($s->precio, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

<style>
    .form-box { background: white; color: black; }
    /* 📱 RESPONSIVE - GESTIÓN SERVICIOS */
@media (max-width: 768px) {

    h2 {
        font-size: 20px;
        text-align: center;
    }

    h4 {
        font-size: 16px;
        text-align: center;
    }

    /* Botones en columna tipo app */
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
        border-radius: 12px;
    }

    .form-control {
        font-size: 14px;
        padding: 8px;
    }

    /* Lista de servicios */
    .list-group-item {
        font-size: 13px;
        padding: 10px;
    }

    /* Tabla */
    .table {
        font-size: 12px;
    }

    .table th,
    .table td {
        padding: 6px;
        white-space: nowrap;
    }

    /* Scroll horizontal para tabla */
    .table-responsive {
        overflow-x: auto;
    }

    /* Imagen en tabla */
    .table img {
        max-height: 40px;
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

function cargarEdicion(s) {
    showForm('editar_form');
    document.getElementById('form-actualizar').action = "/admin/servicio/" + s.idServicio;
    document.getElementById('edit-nombre').value = s.nombresServicio;
    document.getElementById('edit-descripcion').value = s.descripcion;
    document.getElementById('edit-duracion').value = s.duracionMinuto;
    document.getElementById('edit-precio').value = s.precio;
}
</script>
@endsection