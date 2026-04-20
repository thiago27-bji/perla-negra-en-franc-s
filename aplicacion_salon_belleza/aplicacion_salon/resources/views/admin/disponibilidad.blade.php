@extends('layouts.app')
@section('content')

<div class="container mt-4">
    
    <h2 class="mb-4">Gestión de Horarios</h2>

    {{-- BOTONES --}}
    <div class="mb-3 d-flex gap-2 flex-wrap">
        <button class="btn btn-primary" onclick="showForm('crear')">Crear Horario</button>
        <button class="btn btn-warning" onclick="showForm('editar_lista')">Editar Horario</button>
        <button class="btn btn-danger" onclick="showForm('eliminar_lista')">Eliminar Horario</button>
        <button class="btn btn-info" onclick="showForm('ver')">Ver Horarios</button>
        <a href="{{ url('/admin') }}" class="btn btn-secondary">Volver al Panel</a>
    </div>

    {{-- ===================== --}}
    {{-- FORM CREAR --}}
    {{-- ===================== --}}
    <div id="crear" class="card p-3 form-box">
        <h4>Asignar Horario</h4>

        <form method="POST" action="{{ route('disponibilidad.store') }}">
            @csrf
            <label>Seleccionar Personal</label>
            <select name="FK_personal" class="form-control mb-2" required>
                <option value="">Seleccione un empleado...</option>
                @foreach($personales as $p)
                    <option value="{{ $p->idPersonal }}">{{ $p->nombre }} {{ $p->apellido }}</option>
                @endforeach
            </select>

            <label>Día de la semana</label>
            <select name="diaSemana" class="form-control mb-2" required>
                <option value="Lunes">Lunes</option>
                <option value="Martes">Martes</option>
                <option value="Miercoles">Miércoles</option>
                <option value="Jueves">Jueves</option>
                <option value="Viernes">Viernes</option>
                <option value="Sabado">Sábado</option>
                <option value="Domingo">Domingo</option>
            </select>

            <label>Hora de Inicio</label>
            <input type="time" name="horaInicio" class="form-control mb-2" required>

            <label>Hora de Fin</label>
            <input type="time" name="horaFin" class="form-control mb-2" required>

            <button class="btn btn-success">Guardar</button>
        </form>
    </div>

    {{-- ===================== --}}
    {{-- LISTA PARA EDITAR --}}
    {{-- ===================== --}}
    <div id="editar_lista" class="card p-3 form-box" style="display:none;">
        <h4>Seleccione Horario para Editar</h4>
        <div class="list-group">
            @foreach($disponibilidades as $d)
                <button type="button" class="list-group-item list-group-item-action" onclick="cargarEdicion({{ json_encode($d) }})">
                    {{ $d->personal->nombre }} - {{ $d->diaSemana }} ({{ $d->horaInicio }} - {{ $d->horaFin }})
                </button>
            @endforeach
        </div>
    </div>

    {{-- ===================== --}}
    {{-- FORM REAL DE EDICIÓN --}}
    {{-- ===================== --}}
    <div id="editar_form" class="card p-3 form-box" style="display:none;">
        <h4>Editar Horario</h4>
        <form id="form-actualizar" method="POST" action="">
            @csrf
            @method('PUT')
            
            <label>Personal</label>
            <select name="FK_personal" id="edit_personal" class="form-control mb-2" required>
                @foreach($personales as $p)
                    <option value="{{ $p->idPersonal }}">{{ $p->nombre }} {{ $p->apellido }}</option>
                @endforeach
            </select>

            <label>Día</label>
            <select name="diaSemana" id="edit_dia" class="form-control mb-2" required>
                <option value="Lunes">Lunes</option>
                <option value="Martes">Martes</option>
                <option value="Miercoles">Miércoles</option>
                <option value="Jueves">Jueves</option>
                <option value="Viernes">Viernes</option>
                <option value="Sabado">Sábado</option>
                <option value="Domingo">Domingo</option>
            </select>

            <label>Hora Inicio</label>
            <input type="time" name="horaInicio" id="edit_inicio" class="form-control mb-2" required>

            <label>Hora Fin</label>
            <input type="time" name="horaFin" id="edit_fin" class="form-control mb-2" required>

            <button class="btn btn-warning text-white">Actualizar</button>
            <button type="button" class="btn btn-secondary" onclick="showForm('editar_lista')">Volver</button>
        </form>
    </div>

    {{-- ===================== --}}
    {{-- LISTA PARA ELIMINAR --}}
    {{-- ===================== --}}
    <div id="eliminar_lista" class="card p-3 form-box" style="display:none;">
        <h4>Eliminar Horario</h4>
        <div class="list-group">
            @foreach($disponibilidades as $d)
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    {{ $d->personal->nombre }} - {{ $d->diaSemana }} ({{ $d->horaInicio }} - {{ $d->horaFin }})
                    <form action="{{ route('disponibilidad.destroy', $d->idDisponibilidad) }}" method="POST" onsubmit="return confirm('¿Eliminar este horario?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm">Eliminar</button>
                    </form>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ===================== --}}
    {{-- SECCIÓN VER --}}
    {{-- ===================== --}}
    <div id="ver" class="card p-3 form-box" style="display:none;">
        <h4>Horarios Asignados</h4>
        <div class="table-responsive">
            <table class="table table-bordered text-dark">
                <thead>
                    <tr>
                        <th>Personal</th>
                        <th>Día</th>
                        <th>Desde</th>
                        <th>Hasta</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($disponibilidades as $d)
                    <tr>
                        <td>{{ $d->personal->nombre }} {{ $d->personal->apellido }}</td>
                        <td>{{ $d->diaSemana }}</td>
                        <td>{{ $d->horaInicio }}</td>
                        <td>{{ $d->horaFin }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
<style>
.form-box {
    background: rgba(255,255,255,0.95);
    border-radius: 15px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

/* 📱 RESPONSIVE */
@media (max-width: 768px) {

    h2 {
        font-size: 20px;
        text-align: center;
    }

    .btn {
        width: 100%;
    }

    .form-box {
        padding: 15px;
    }

    .table {
        font-size: 12px;
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

function cargarEdicion(disponibilidad) {
    showForm('editar_form');
    document.getElementById('form-actualizar').action = "/admin/disponibilidad/" + disponibilidad.idDisponibilidad;
    document.getElementById('edit_personal').value = disponibilidad.FK_personal;
    document.getElementById('edit_dia').value = disponibilidad.diaSemana;
    document.getElementById('edit_inicio').value = disponibilidad.horaInicio;
    document.getElementById('edit_fin').value = disponibilidad.horaFin;
}
</script>
@endsection
