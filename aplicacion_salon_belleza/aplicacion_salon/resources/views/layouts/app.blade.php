<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salón de Belleza</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Estilos -->
    <style>
        body {
            background: linear-gradient(135deg, #6a0dad, #1e3a8a);
            min-height: 100vh;
            color: #fff;
            padding-bottom: 20px;
        }

        .navbar {
            background: linear-gradient(90deg, #6a0dad, #1e3a8a);
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        .navbar-brand {
            font-weight: bold;
            color: #fff !important;
        }

        .nav-link {
            color: #fff !important;
            transition: 0.3s;
        }

        .nav-link:hover {
            color: #c084fc !important;
        }

        .container-main {
            background: rgba(255, 255, 255, 0.05);
            padding: 15px;
            border-radius: 15px;
            margin-top: 20px;
        }

        @media (min-width: 768px) {
            .container-main { padding: 30px; margin-top: 30px; }
        }

        .btn-primary {
            background-color: #6a0dad;
            border: none;
        }

        .btn-primary:hover {
            background-color: #4c1d95;
        }

        footer {
            background: rgba(0,0,0,0.3);
            color: #ccc;
            padding: 15px;
            text-align: center;
            margin-top: 50px;
            font-size: 0.9rem;
        }

        /* Alertas responsivas */
        #alert-container {
            z-index: 9999;
            width: 90%;
            max-width: 450px;
            left: 50%;
            transform: translateX(-50%);
        }
    </style>
</head>
<body>

<!-- 🔝 NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark px-3">
    <div class="container-fluid max-width-container" style="max-width: 1200px; margin: 0 auto; width: 100%;">
        <a class="navbar-brand" href="/">💄 SALON DE BELLEZA</a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav ms-auto">
                @auth
                    <li class="nav-item">
                        <span class="nav-link text-info">Hola, {{ auth()->user()->nombre }}</span>
                    </li>
                    <li class="nav-item">
                        <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display: none;">
                            @csrf
                        </form>
                        <a href="#" class="nav-link text-danger" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            Cerrar sesión
                        </a>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link" href="/login">Login</a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

<!-- 🔔 ALERTAS -->
<div id="alert-container" class="position-fixed top-0 mt-3">
    @if(session('success'))
        <div class="alert alert-success text-center shadow-lg border-0">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger text-center shadow-lg border-0">
            {{ session('error') }}
        </div>
    @endif
</div>

<!-- 📦 CONTENIDO -->
<div class="container-fluid px-3 px-md-5">
    <div class="container-main mx-auto" style="max-width: 1200px;">
        @yield('content')
    </div>
</div>

<!-- 🔻 FOOTER -->
<footer>
    <p>© {{ date('Y') }} Salón de Belleza </p>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- ⏱️ AUTO OCULTAR ALERTAS -->
<script>
    setTimeout(() => {
        let alertBox = document.getElementById('alert-container');
        if (alertBox) {
            alertBox.style.transition = "opacity 0.5s ease";
            alertBox.style.opacity = "0";
            setTimeout(() => { alertBox.remove(); }, 500);
        }
    }, 3000); 
</script>

</body>
</html>