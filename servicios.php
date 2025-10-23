<?php
/**
 * SERVICIOS.PHP
 * 
 * FUNCIONALIDAD PRINCIPAL:
 * - Muestra el catálogo completo de servicios disponibles
 * - Permite a los clientes autenticados agendar citas para servicios específicos
 * - Gestiona la selección de personal disponible para cada cita
 * 
 * DEPENDENCIAS:
 * - db.php: Conexión a la base de datos mediante PDO
 * - Sesión PHP activa con usuario autenticado
 * 
 * TABLAS DE BASE DE DATOS UTILIZADAS:
 * - servicios: Catálogo de servicios (idServicio, nombreServicio, descripcion, precio, duracionMinuto)
 * - clientes: Información de clientes (idCliente, nombre, email)
 * - personal: Personal disponible (idPersonal, nombre, apellido, estadoDisponible)
 * - cita: Registro de citas (FK_cliente, FK_personal, FK_servicio, FK_estadoCita, fechaCita)
 * 
 * @author Sistema Perle Noire
 * @version 1.0
 */

// Inicializar sesión para mantener el estado del usuario
session_start();

// Incluir archivo de conexión a la base de datos
require 'db.php';

/**
 * VALIDACIÓN DE AUTENTICACIÓN
 * 
 * Verifica si existe una sesión activa con usuario autenticado.
 * Si no existe, redirige al login (index.php)
 */
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

/**
 * OBTENCIÓN DE SERVICIOS
 * 
 * Consulta todos los servicios disponibles en el catálogo
 * Ordenados alfabéticamente por nombre del servicio
 */
$stmt = $pdo->query("SELECT * FROM servicios ORDER BY nombreServicio");
$servicios = $stmt->fetchAll();

/**
 * OBTENCIÓN DE INFORMACIÓN DEL CLIENTE
 * 
 * Recupera los datos completos del cliente usando el email almacenado en sesión
 * Esta información se utiliza para:
 * - Mostrar el nombre en la interfaz
 * - Vincular las citas al cliente correcto
 */
$email = $_SESSION['user'];
$stmtCliente = $pdo->prepare("SELECT * FROM clientes WHERE email = ?");
$stmtCliente->execute([$email]);
$cliente = $stmtCliente->fetch();

/**
 * GESTIÓN DEL MODAL DE AGENDAMIENTO
 * 
 * Variables de control para mostrar/ocultar el modal de agendamiento
 * y almacenar el servicio seleccionado
 */
$mostrarModal = false;
$servicioSeleccionado = null;

/**
 * PROCESAMIENTO DE SOLICITUD DE AGENDAMIENTO
 * 
 * Cuando se recibe un parámetro 'agendar' por GET:
 * 1. Activa el modal de agendamiento
 * 2. Carga los detalles del servicio seleccionado
 * 3. Obtiene la lista de personal disponible para asignar
 */
if (isset($_GET['agendar']) && $_GET['agendar'] > 0) {
    // Activar visualización del modal
    $mostrarModal = true;
    
    // Convertir ID a entero por seguridad
    $idServicio = intval($_GET['agendar']);
    
    // Obtener detalles del servicio seleccionado
    $stmtServicio = $pdo->prepare("SELECT * FROM servicios WHERE idServicio = ?");
    $stmtServicio->execute([$idServicio]);
    $servicioSeleccionado = $stmtServicio->fetch();
    
    // Obtener personal disponible (solo aquellos con estado activo)
    $stmtPersonal = $pdo->query("SELECT * FROM personal WHERE estadoDisponible = TRUE ORDER BY nombre");
    $personalDisponible = $stmtPersonal->fetchAll();
}

/**
 * VARIABLES DE RETROALIMENTACIÓN
 * 
 * Almacenan mensajes de éxito o error para mostrar al usuario
 */
$mensaje = '';
$error = '';

/**
 * PROCESAMIENTO DEL FORMULARIO DE AGENDAMIENTO
 * 
 * Cuando se envía el formulario (POST) con el botón 'agendar_cita':
 * 1. Valida y recopila todos los datos del formulario
 * 2. Combina fecha y hora en formato datetime
 * 3. Inserta el registro de cita en la base de datos
 * 4. FK_estadoCita = 1 (estado inicial: probablemente "Pendiente")
 * 5. Muestra mensaje de confirmación y redirige después de 2 segundos
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agendar_cita'])) {
    // Recopilación de datos del formulario
    $fecha = $_POST['fecha'];                                              // Fecha seleccionada (YYYY-MM-DD)
    $hora = $_POST['hora'];                                                // Hora seleccionada (HH:MM:SS)
    $idPersonal = intval($_POST['personal']);                              // ID del estilista seleccionado
    $idServicioPost = intval($_POST['servicio_id']);                       // ID del servicio (hidden input)
    $comentarios = isset($_POST['comentarios']) ? $_POST['comentarios'] : ''; // Comentarios adicionales (opcional)
    
    // Combinar fecha y hora en formato datetime para MySQL
    $fechaCita = $fecha . ' ' . $hora;
    
    try {
        /**
         * INSERCIÓN DE CITA
         * 
         * Campos insertados:
         * - FK_cliente: ID del cliente desde la sesión
         * - FK_personal: ID del estilista seleccionado
         * - FK_servicio: ID del servicio a realizar
         * - FK_estadoCita: 1 (estado inicial por defecto)
         * - fechaCita: Fecha y hora combinadas
         */
        $stmtInsert = $pdo->prepare("INSERT INTO cita (FK_cliente, FK_personal, FK_servicio, FK_estadoCita, fechaCita) VALUES (?, ?, ?, 1, ?)");
        $stmtInsert->execute([$cliente['idCliente'], $idPersonal, $idServicioPost, $fechaCita]);
        
        // Mensaje de éxito y redirección automática después de 2 segundos
        $mensaje = 'Cita agendada exitosamente';
        header("refresh:2;url=servicios.php");
    } catch(PDOException $e) {
        // Captura y muestra errores de base de datos
        $error = 'Error al agendar la cita: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuestros Servicios - Perle Noire</title>
    <style>
        /**
         * ESTILOS GENERALES
         * Reset de márgenes y modelo de caja para consistencia cross-browser
         */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /**
         * ESTILOS DEL BODY
         * - Fuente moderna y legible
         * - Fondo con degradado suave
         * - Altura mínima para llenar viewport
         */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            min-height: 100vh;
        }

        /**
         * BARRA DE NAVEGACIÓN
         * Diseño horizontal con logo a la izquierda y acciones a la derecha
         * Degradado rosa corporativo con sombra para profundidad
         */
        .navbar {
            background: linear-gradient(135deg, #e91e63, #ec407a);
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        /**
         * LOGO
         * Incluye emoji decorativo mediante ::before
         */
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            color: white;
            font-size: 22px;
            font-weight: 700;
        }

        .logo::before {
            content: "💄";
            font-size: 28px;
        }

        /**
         * SECCIÓN DE USUARIO
         * Contiene avatar, nombre e información del usuario logueado
         */
        .user-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        /**
         * INFORMACIÓN DEL USUARIO
         * Cápsula con fondo translúcido para mejor legibilidad
         */
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            background-color: rgba(255, 255, 255, 0.2);
            padding: 8px 15px;
            border-radius: 25px;
            color: white;
            font-size: 14px;
        }

        /**
         * AVATAR DEL USUARIO
         * Círculo con fondo blanco para contener emoji o inicial
         */
        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background-color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        /**
         * BOTONES DEL HEADER
         * Estilo consistente para enlaces de navegación con hover effect
         */
        .btn-header {
            background: rgba(255,255,255,0.2);
            padding: 8px 20px;
            border-radius:8px;
            border: 2px solid white;
            color: white;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-header:hover {
            background-color: white;
            color: #e91e63;
        }

        /**
         * CONTENEDOR PRINCIPAL
         * Limita el ancho máximo y centra el contenido
         */
        .main-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        /**
         * TÍTULO DE PÁGINA
         * Encabezado principal centrado y destacado
         */
        .page-title {
            text-align: center;
            font-size: 36px;
            font-weight: 700;
            color: #333;
            margin-bottom: 50px;
        }

        /**
         * GRID DE SERVICIOS
         * Sistema de grilla responsivo que se adapta al tamaño de pantalla
         * - Mínimo 280px por tarjeta
         * - Se ajusta automáticamente el número de columnas
         */
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
            padding: 0 20px;
        }

        /**
         * TARJETA DE SERVICIO
         * Cada servicio se muestra en una tarjeta con:
         * - Sombra suave
         * - Bordes redondeados
         * - Efecto hover de elevación
         */
        .service-card {
            background-color: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
            cursor: pointer;
        }

        .service-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        /**
         * HEADER DE TARJETA DE SERVICIO
         * Sección superior con degradado y espacio para icono
         */
        .service-header {
            background: linear-gradient(135deg, #7b68ee, #9370db);
            padding: 60px 20px;
            text-align: center;
        }

        /**
         * ICONO DE SERVICIO
         * Emoji grande con sombra para representar visualmente el servicio
         */
        .service-icon {
            font-size: 60px;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
        }

        /**
         * CUERPO DE TARJETA DE SERVICIO
         * Contiene toda la información textual del servicio
         */
        .service-body {
            padding: 20px;
        }

        /**
         * NOMBRE DEL SERVICIO
         * Título destacado del servicio
         */
        .service-name {
            font-size: 20px;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
        }

        /**
         * DESCRIPCIÓN DEL SERVICIO
         * Texto explicativo con altura mínima para mantener alineación
         */
        .service-description {
            font-size: 13px;
            color: #666;
            line-height: 1.5;
            margin-bottom: 15px;
            min-height: 40px;
        }

        /**
         * DETALLES DEL SERVICIO
         * Fila que contiene precio y duración
         */
        .service-details {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        /**
         * PRECIO DEL SERVICIO
         * Destacado en color corporativo y tamaño grande
         */
        .service-price {
            font-size: 22px;
            font-weight: 700;
            color: #e91e63;
        }

        /**
         * DURACIÓN DEL SERVICIO
         * Tiempo estimado en minutos
         */
        .service-duration {
            font-size: 13px;
            color: #666;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /**
         * BOTÓN DE AGENDAR
         * Call-to-action principal de cada tarjeta de servicio
         * Degradado rosa con hover effect
         */
        .btn-agendar {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #e91e63, #ec407a);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-agendar:hover {
            background: linear-gradient(135deg, #d81b60, #e91e63);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(233, 30, 99, 0.4);
        }

        /**
         * OVERLAY DEL MODAL
         * Capa oscura que cubre toda la pantalla cuando el modal está activo
         * Permite cerrar el modal al hacer clic fuera de él
         */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 20px;
        }

        /**
         * ESTADO ACTIVO DEL MODAL
         * Cuando la clase 'active' está presente, el modal se muestra
         */
        .modal-overlay.active {
            display: flex;
        }

        /**
         * CONTENEDOR DEL MODAL
         * Ventana principal del formulario de agendamiento
         * Con scroll interno si el contenido es muy largo
         */
        .modal-container {
            background-color: white;
            border-radius: 20px;
            max-width: 600px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }

        /**
         * HEADER DEL MODAL
         * Barra superior con título y botón de cierre
         */
        .modal-header {
            background: linear-gradient(135deg, #e91e63, #ec407a);
            padding: 25px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 20px 20px 0 0;
        }

        /**
         * TÍTULO DEL MODAL
         * Texto blanco destacado
         */
        .modal-title {
            color: white;
            font-size: 24px;
            font-weight: 700;
        }

        /**
         * BOTÓN DE CIERRE DEL MODAL
         * X grande en la esquina superior derecha
         */
        .close-btn {
            background: none;
            border: none;
            color: white;
            font-size: 32px;
            cursor: pointer;
            transition: all 0.3s;
            padding: 0;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .close-btn:hover {
            transform: rotate(90deg);
        }

        /**
         * CUERPO DEL MODAL
         * Contiene todo el formulario de agendamiento
         */
        .modal-body {
            padding: 30px;
        }

        /**
         * GRUPO DE FORMULARIO
         * Contenedor estándar para cada campo del formulario
         */
        .form-group {
            margin-bottom: 20px;
        }

        /**
         * ETIQUETAS DE FORMULARIO
         * Labels para identificar cada campo
         */
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        /**
         * INPUTS Y SELECTS DEL FORMULARIO
         * Estilo consistente para todos los campos de entrada
         */
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s;
        }

        /**
         * FOCUS DE INPUTS
         * Borde rosa cuando el campo está seleccionado
         */
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #e91e63;
        }

        /**
         * INPUTS DESHABILITADOS
         * Estilo para campos de solo lectura
         */
        .form-group input:disabled {
            background-color: #f5f5f5;
            cursor: not-allowed;
        }

        /**
         * ETIQUETA DE ESTILISTA
         * Label especial para la sección de selección de personal
         */
        .estilista-label {
            display: block;
            margin-bottom: 15px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        /**
         * GRID DE ESTILISTAS
         * Grilla responsiva para mostrar el personal disponible
         * 3 columnas en pantallas grandes, 2 en móviles
         */
        .estilistas-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }

        /**
         * TARJETA DE ESTILISTA
         * Cada miembro del personal se muestra en una tarjeta clickeable
         * Cambia de estilo al ser seleccionado
         */
        .estilista-card {
            padding: 15px;
            text-align: center;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
            background-color: white;
        }

        .estilista-card:hover {
            border-color: #e91e63;
            transform: translateY(-2px);
        }

        /**
         * ESTADO SELECCIONADO DE ESTILISTA
         * Borde rosa y fondo suave cuando está seleccionado
         */
        .estilista-card.selected {
            border-color: #e91e63;
            background-color: #fff0f5;
        }

        /**
         * AVATAR DEL ESTILISTA
         * Círculo con degradado morado conteniendo emoji
         */
        .estilista-avatar {
            width: 65px;
            height: 65px;
            border-radius: 50%;
            background: linear-gradient(135deg, #7b68ee, #9370db);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 8px;
            font-size: 32px;
        }

        /**
         * NOMBRE DEL ESTILISTA
         * Texto del nombre debajo del avatar
         */
        .estilista-name {
            font-size: 13px;
            font-weight: 600;
            color: #333;
        }

        /**
         * BOTÓN DE CONFIRMAR CITA
         * Botón de envío principal del formulario
         */
        .btn-confirmar {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #e91e63, #ec407a);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-confirmar:hover {
            background: linear-gradient(135deg, #d81b60, #e91e63);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(233, 30, 99, 0.4);
        }

        /**
         * MENSAJES DE RETROALIMENTACIÓN
         * Alertas de éxito o error después de acciones
         */
        .mensaje {
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            text-align: center;
            font-weight: 600;
            font-size: 14px;
        }

        /**
         * MENSAJE DE ÉXITO
         * Verde para indicar operación exitosa
         */
        .mensaje.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        /**
         * MENSAJE DE ERROR
         * Rojo para indicar problemas
         */
        .mensaje.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /**
         * SCROLLBAR PERSONALIZADA
         * Scrollbar rosa para el contenedor del modal
         */
        .modal-container::-webkit-scrollbar {
            width: 6px;
        }

        .modal-container::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .modal-container::-webkit-scrollbar-thumb {
            background: #e91e63;
            border-radius: 10px;
        }

        /**
         * MEDIA QUERIES - RESPONSIVE
         * Adaptación para pantallas móviles (< 768px)
         */
        @media (max-width: 768px) {
            /* Reducir grid de estilistas a 2 columnas en móvil */
            .estilistas-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <!-- 
        BARRA DE NAVEGACIÓN
        Contiene logo, información del usuario y enlaces de navegación
    -->
    <nav class="navbar">
        <div class="logo">
            Perle Noire
        </div>
        <div class="user-section">
            <!-- Mostrar nombre del cliente logueado -->
              <span><?php echo htmlspecialchars($cliente['nombre'] ?? 'Usuario'); ?></span>
            <div class="user-info">
                <div class="user-avatar">👤</div>
            </div>
            <!-- Enlace a facturación -->
            <a href="facturacion.php" class="btn-header">📅 Facturación</a>
            <!-- Enlace para cerrar sesión -->
            <a href="logout.php" class="btn-header">Cerrar Sesión</a>
        </div>
    </nav>

    <!-- 
        CONTENIDO PRINCIPAL
        Sección que contiene el catálogo de servicios
    -->
    <div class="main-content">
        <h1 class="page-title">Nuestros Servicios</h1>

        <!-- 
            GRID DE SERVICIOS
            Muestra todos los servicios en formato de tarjetas
        -->
        <div class="services-grid">
            <?php if (count($servicios) > 0): ?>
                <?php 
                /**
                 * ARRAY DE ICONOS
                 * Emojis rotativos para dar variedad visual a las tarjetas
                 * Se asignan circularmente usando módulo del índice
                 */
                $iconos = ['🎀', '🎨', '💅', '👠', '👗', '💄', '✨', '💇‍♀️', '🌸', '💎', '🦋', '🌺'];
                
                // Iterar sobre cada servicio
                foreach ($servicios as $index => $servicio): 
                    // Seleccionar icono de forma circular
                    $icono = $iconos[$index % count($iconos)];
                ?>
                    <!-- 
                        TARJETA DE SERVICIO
                        Cada servicio se renderiza como una tarjeta independiente
                    -->
                    <div class="service-card">
                        <!-- Header con degradado e icono -->
                        <div class="service-header">
                            <div class="service-icon"><?php echo $icono; ?></div>
                        </div>
                        
                        <!-- Cuerpo con información del servicio -->
                        <div class="service-body">
                            <!-- Nombre del servicio -->
                            <h3 class="service-name"><?php echo htmlspecialchars($servicio['nombreServicio']); ?></h3>
                            
                            <!-- Descripción del servicio -->
                            <p class="service-description"><?php echo htmlspecialchars($servicio['descripcion']); ?></p>
                            
                            <!-- Fila con precio y duración -->
                            <div class="service-details">
                                <!-- Precio formateado con separadores de miles -->
                                <div class="service-price">
                                    $<?php echo number_format($servicio['precio'], 0, ',', '.'); ?>
                                </div>
                                <!-- Duración en minutos -->
                                <div class="service-duration">
                                    <?php echo $servicio['duracionMinuto']; ?> min
                                </div>
                            </div>
                            
                            <!-- 
                                BOTÓN AGENDAR
                                Redirige a la misma página con parámetro GET 'agendar'
                                Esto activa el modal de agendamiento
                            -->
                            <button class="btn-agendar" onclick="window.location.href='servicios.php?agendar=<?php echo $servicio['idServicio']; ?>'">
                                Agendar Ahora
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- 
                    MENSAJE DE SERVICIOS VACÍOS
                    Se muestra cuando no hay servicios en la base de datos
                -->
                <div style="text-align: center; padding: 60px 20px; color: #999; font-size: 18px;">
                    No hay servicios disponibles en este momento 😔
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 
        MODAL DE AGENDAR CITA
        Ventana emergente para el formulario de agendamiento
        Se muestra solo cuando $mostrarModal es true
    -->
    <div class="modal-overlay <?php echo $mostrarModal ? 'active' : ''; ?>" id="modalAgendar">
        <?php if ($mostrarModal && $servicioSeleccionado): ?>
        <div class="modal-container">
            <!-- 
                HEADER DEL MODAL
                Título y botón de cierre
            -->
            <div class="modal-header">
                <h2 class="modal-title">Agendar Cita</h2>
                <!-- Botón X que cierra el modal redirigiendo sin parámetros -->
                <button class="close-btn" onclick="window.location.href='servicios.php'">×</button>
            </div>

            <!-- 
                CUERPO DEL MODAL
                Contiene mensajes de feedback y formulario
            -->
            <div class="modal-body">
                <!-- 
                    MENSAJE DE ÉXITO
                    Se muestra después de agendar exitosamente
                -->
                <?php if ($mensaje): ?>
                    <div class="mensaje success"><?php echo $mensaje; ?></div>
                <?php endif; ?>

                <!-- 
                    MENSAJE DE ERROR
                    Se muestra si hay problemas al agendar
                -->
                <?php if ($error): ?>
                    <div class="mensaje error"><?php echo $error; ?></div>
                <?php endif; ?>

                <!-- 
                    FORMULARIO DE AGENDAMIENTO
                    Envía datos por POST a la misma página
                -->
                <form method="POST" action="">
                    <!-- 
                        CAMPO OCULTO: ID DEL SERVICIO
                        Mantiene el ID del servicio seleccionado
                    -->
                    <input type="hidden" name="servicio_id" value="<?php echo $servicioSeleccionado['idServicio']; ?>">
                    
                    <!-- 
                        SERVICIO SELECCIONADO (Solo lectura)
                        Muestra el nombre del servicio elegido
                    -->
                    <div class="form-group">
                        <label>Servicio Seleccionado</label>
                        <input type="text" value="<?php echo htmlspecialchars($servicioSeleccionado['nombreServicio']); ?>" disabled>
                    </div>

                    <!-- 
                        PRECIO (Solo lectura)
                        Muestra el precio formateado del servicio
                    -->
                    <div class="form-group">
                        <label>Precio</label>
                        <input type="text" value="$<?php echo number_format($servicioSeleccionado['precio'], 0, ',', '.'); ?>" disabled>
                    </div>

                    <!-- 
                        SELECTOR DE FECHA
                        Input tipo date con fecha mínima = hoy
                        Previene agendar citas en el pasado
                    -->
                    <div class="form-group">
                        <label for="fecha">Fecha</label>
                        <input type="date" id="fecha" name="fecha" required min="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <!-- 
                        SELECTOR DE HORA
                        Dropdown con horarios predefinidos
                        Horarios disponibles: 9:00 AM - 6:00 PM (sin 1:00 PM)
                    -->
                    <div class="form-group">
                        <label for="hora">Hora</label>
                        <select id="hora" name="hora" required>
                            <option value="">Seleccionar hora...</option>
                            <option value="09:00:00">09:00 AM</option>
                            <option value="10:00:00">10:00 AM</option>
                            <option value="11:00:00">11:00 AM</option>
                            <option value="12:00:00">12:00 PM</option>
                            <option value="14:00:00">02:00 PM</option>
                            <option value="15:00:00">03:00 PM</option>
                            <option value="16:00:00">04:00 PM</option>
                            <option value="17:00:00">05:00 PM</option>
                            <option value="18:00:00">06:00 PM</option>
                        </select>
                    </div>

                    <!-- 
                        GRID DE SELECCIÓN DE ESTILISTAS
                        Tarjetas clickeables para elegir el personal
                    -->
                    <label class="estilista-label">Seleccionar Estilista</label>
                    <div class="estilistas-grid">
                        <?php foreach ($personalDisponible as $personal): ?>
                            <!-- 
                                TARJETA DE ESTILISTA
                                onClick llama a función JS que:
                                1. Remueve clase 'selected' de otras tarjetas
                                2. Agrega clase 'selected' a esta tarjeta
                                3. Asigna el ID al input hidden
                            -->
                            <div class="estilista-card" onclick="selectEstilista(<?php echo $personal['idPersonal']; ?>, this)">
                                <div class="estilista-avatar">👩</div>
                                <div class="estilista-name"><?php echo htmlspecialchars($personal['nombre'] . ' ' . $personal['apellido']); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- 
                        CAMPO OCULTO: ID DEL PERSONAL
                        Almacena el ID del estilista seleccionado
                        Se llena mediante JavaScript al hacer clic en una tarjeta
                    -->
                    <input type="hidden" id="personal" name="personal" required>

                    <!-- 
                        BOTÓN DE CONFIRMACIÓN
                        Submit del formulario para agendar la cita
                    -->
                    <button type="submit" name="agendar_cita" class="btn-confirmar">Confirmar Cita</button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
        /**
         * FUNCIÓN: selectEstilista
         * 
         * Gestiona la selección de estilistas en el modal
         * 
         * @param {number} id - ID del personal seleccionado
         * @param {HTMLElement} element - Elemento DOM de la tarjeta clickeada
         * 
         * PROCESO:
         * 1. Remueve la clase 'selected' de todas las tarjetas de estilista
         * 2. Agrega la clase 'selected' solo a la tarjeta clickeada
         * 3. Asigna el ID del estilista al input hidden 'personal'
         * 
         * Esto permite validación del formulario (required) y envío del ID correcto
         */
        function selectEstilista(id, element) {
            // Remover selección previa de todas las tarjetas
            document.querySelectorAll('.estilista-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Agregar clase 'selected' a la tarjeta clickeada
            element.classList.add('selected');
            
            // Asignar ID al input hidden para envío del formulario
            document.getElementById('personal').value = id;
        }
    </script>
</body>
</html>