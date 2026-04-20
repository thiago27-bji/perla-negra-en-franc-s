@extends('layouts.app')

@section('content')

<div class="row justify-content-center">
    <div class="col-md-4">

        <div class="card p-4 shadow">
            <h3 class="text-center mb-3">Login</h3>

            <form method="POST" action="/login">
                @csrf

                <input type="email" name="email" class="form-control mb-3" placeholder="Correo">

                <input type="password" name="password" class="form-control mb-3" placeholder="Contraseña">

                <button class="btn btn-primary w-100 mb-3">Ingresar</button>

                <div class="text-center">
                    <a href="/register" class="text-decoration-none text-dark">¿No tienes cuenta? Registrate aquí</a>
                </div>
            </form>

        </div>

    </div>
</div>

@endsection