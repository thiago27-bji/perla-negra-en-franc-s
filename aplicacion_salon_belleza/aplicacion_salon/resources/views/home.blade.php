@extends('layouts.app')

@section('content')

<div class="container mt-4">

    <h1 class="text-center mb-4">Nuestros Servicios</h1>

    <div class="row">

        @foreach($servicios as $s)

        <div class="col-12 col-md-6 col-lg-4">

            <div class="card mb-4 shadow border-0">

                {{-- IMAGEN --}}
                @if($s->imagen)
                    <img src="{{ asset('storage/' . $s->imagen) }}" class="card-img-top" style="height: 200px; object-fit: cover; border-top-left-radius: 10px; border-top-right-radius: 10px;">
                @else
                    <div class="bg-light text-muted d-flex align-items-center justify-content-center" style="height: 200px; border-top-left-radius: 10px; border-top-right-radius: 10px;">Sin imagen</div>
                @endif

                <div class="card-body text-center">

                    <h5 class="fw-bold">{{ $s->nombresServicio }}</h5>

                    <p class="small text-muted">{{ $s->descripcion }}</p>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="badge bg-light text-dark border">⏱️ {{ $s->duracionMinuto }} min</span>
                        <span class="fw-bold text-primary fs-5">${{ number_format($s->precio, 0, ',', '.') }}</span>
                    </div>

                    {{-- BOTÓN AGENDAR --}}
                    <a href="{{ auth()->check() ? url('/cliente') : url('/login') }}" class="btn btn-primary w-100 py-2 fw-bold">
                        📅 Agendar Cita
                    </a>

                </div>

            </div>

        </div>

        @endforeach

    </div>

</div>

@endsection