@extends('layouts.app')

@section('content')

<div class="row justify-content-center">
    <div class="col-md-5">

        <div class="card p-4 shadow">
            <h3 class="text-center mb-3">Registro</h3>

            <p class="text-center text-muted small mb-4">Crea tu cuenta para empezar a agendar servicios.</p>

            <form method="POST" action="/register">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label text-dark">Nombre</label>
                        <input type="text" name="nombre" class="form-control" value="{{ old('nombre') }}" placeholder="Ej: Maria" required autofocus>
                        @error('nombre') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-dark">Apellido</label>
                        <input type="text" name="apellido" class="form-control" value="{{ old('apellido') }}" placeholder="Ej: Perez" required>
                        @error('apellido') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-dark">Correo Electrónico</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="correo@ejemplo.com" required>
                    @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label text-dark">Contraseña</label>
                    <input type="password" name="password" class="form-control" placeholder="Mínimo 6 caracteres" required>
                    @error('password') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label text-dark">Confirmar Contraseña</label>
                    <input type="password" name="password_confirmation" class="form-control" placeholder="Repite tu contraseña" required>
                </div>

                <button class="btn btn-primary w-100 mb-3">Crear Cuenta</button>

                <div class="text-center">
                    <a href="/login" class="text-decoration-none">¿Ya tienes cuenta? Inicia sesión aquí</a>
                </div>
            </form>

        </div>

    </div>
</div>

<style>
    .card { background: rgba(255, 255, 255, 0.95); border: none; }
    .form-label { font-weight: 500; }
</style>

@endsection
