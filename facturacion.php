<?php
/**
 * FACTURACION.PHP
* 
 * FUNCIONALIDAD PRINCIPAL:
 * - Muestra el historial completo de citas del cliente autenticado
 * - Presenta información detallada de servicios, precios y estados de citas
 * - Integra datos de facturas asociadas a cada cita
 * - Permite visualizar el estado actual de cada servicio contratado
 * 
 * CARACTERÍSTICAS:
 * - Sistema de iniciales para avatar del usuario
 * - Tabla responsiva con información de citas
 * - Estados visuales codificados por colores (pagado, pendiente, cancelado)
 * - Botón para generar facturas (funcionalidad pendiente de implementación)
 * 
 * DEPENDENCIAS:
 * - db.php: Conexión a la base de datos mediante PDO
 * - Sesión PHP activa con usuario autenticado
 * 
 * TABLAS DE BASE DE DATOS UTILIZADAS:
 * - cita: Registro principal de citas (idCita, fechaCita, FK_cliente, FK_servicio, FK_estadoCita)
 * - clientes: Información de clientes (idCliente, nombre, apellido, email)
 * - servicios: Catálogo de servicios (idServicio, nombreServicio, precio)
 * - estado_cita: Estados posibles de citas (idEstado, nombre)
 * - factura: Facturas generadas (idFacturas, fechaGeneracion, montoTotal, FK_cita)
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
 * Esta es una medida de seguridad para proteger información sensible
 */
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

/**
 * OBTENCIÓN DE INFORMACIÓN DEL CLIENTE
 * 
 * Recupera los datos completos del cliente usando el email almacenado en sesión
 * Esta información se utiliza para:
 * - Mostrar el nombre en la interfaz
 * - Generar las iniciales para el avatar
 * - Filtrar las citas específicas del cliente
 */
$email = $_SESSION['user'];
$stmtCliente = $pdo->prepare("SELECT * FROM clientes WHERE email = ?");
$stmtCliente->execute([$email]);
$cliente = $stmtCliente->fetch();

/**
 * FUNCIÓN: obtenerIniciales
 * 
 * Genera las iniciales del usuario para mostrar en el avatar
 * 
 * @param string $nombre - Nombre del cliente
 * @param string $apellido - Apellido del cliente (opcional)
 * @return string - Iniciales en mayúsculas (ej: "JD" para Juan Díaz)
 * 
 * PROCESO:
 * 1. Extrae el primer carácter del nombre
 * 2. Extrae el primer carácter del apellido (si existe)
 * 3. Convierte ambos a mayúsculas
 * 4. Los concatena y retorna
 * 
 * EJEMPLOS:
 * - obtenerIniciales("Juan", "Díaz") -> "JD"
 * - obtenerIniciales("María", "") -> "M"
 * - obtenerIniciales("", "Pérez") -> "P"
 */
function obtenerIniciales($nombre, $apellido = '') {
    // Obtener primera letra del nombre, convertir a mayúscula
    $inicial_nombre = !empty($nombre) ? strtoupper(substr($nombre, 0, 1)) : '';
    
    // Obtener primera letra del apellido, convertir a mayúscula
    $inicial_apellido = !empty($apellido) ? strtoupper(substr($apellido, 0, 1)) : '';
    
    // Concatenar y retornar ambas iniciales
    return $inicial_nombre . $inicial_apellido;
}

// Generar iniciales del cliente actual para mostrar en el avatar
$iniciales = obtenerIniciales($cliente['nombre'], $cliente['apellido']);

/**
 * CONSULTA PRINCIPAL: HISTORIAL DE CITAS Y FACTURACIÓN
 * 
 * Obtiene todas las citas del cliente con información relacionada de:
 * - Servicios contratados
 * - Estados de las citas
 * - Facturas asociadas (si existen)
 * 
 * JOINS REALIZADOS:
 * - INNER JOIN servicios: Para obtener nombre y precio del servicio
 * - INNER JOIN estado_cita: Para obtener el nombre del estado actual
 * - LEFT JOIN factura: Para incluir información de facturación (puede no existir)
 * 
 * ORDENAMIENTO:
 * - Por fecha de cita descendente (más recientes primero)
 * 
 * FILTRO:
 * - Solo citas del cliente actual (WHERE c.FK_cliente = ?)
 */
$query = "
    SELECT 
        c.idCita,                    -- ID único de la cita
        c.fechaCita,                 -- Fecha y hora de la cita
        s.nombreServicio,            -- Nombre del servicio contratado
        s.precio,                    -- Precio del servicio
        e.nombre as estadoNombre,    -- Estado de la cita (Pagado, Pendiente, Cancelado)
        f.idFacturas,                -- ID de la factura (si existe)
        f.fechaGeneracion,           -- Fecha de generación de factura
        f.montoTotal                 -- Monto total de la factura
    FROM cita c
    INNER JOIN servicios s ON c.FK_servicio = s.idServicio
    INNER JOIN estado_cita e ON c.FK_estadoCita = e.idEstado
    LEFT JOIN factura f ON c.idCita = f.FK_cita
    WHERE c.FK_cliente = ?
    ORDER BY c.fechaCita DESC
";

$stmtCitas = $pdo->prepare($query);
$stmtCitas->execute([$cliente['idCliente']]);
$citas = $stmtCitas->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facturación - Perle Noire</title>
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
         * - Fondo con degradado gris suave
         * - Altura mínima para llenar viewport
         */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f5f5, #e8e8e8);
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
         * LOGO CORPORATIVO
         * Incluye emoji decorativo de lápiz labial mediante ::before
         * Color blanco para contrastar con el fondo rosa
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
         * Contiene nombre, avatar y botones de navegación
         * Alineados horizontalmente con espaciado uniforme
         */
        .user-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        /**
         * INFORMACIÓN DEL USUARIO
         * Cápsula con fondo translúcido que contiene avatar y nombre
         * Bordes redondeados para diseño moderno
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
         * Círculo con fondo blanco que muestra las iniciales del usuario
         * Color de texto rosa para mantener coherencia visual
         * letter-spacing para mejorar legibilidad de iniciales
         */
        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background-color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 700;
            color: #e91e63;
            letter-spacing: 0.5px;
        }

        /**
         * BOTONES DEL HEADER
         * Estilo consistente para enlaces de navegación
         * Fondo translúcido con borde blanco
         * Hover invierte los colores (fondo blanco, texto rosa)
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
         * Padding para espaciado con los bordes
         */
        .main-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        /**
         * TÍTULO DE PÁGINA
         * Encabezado principal centrado
         * Tamaño grande y peso bold para jerarquía visual
         */
        .page-title {
            text-align: center;
            font-size: 36px;
            font-weight: 700;
            color: #333;
            margin-bottom: 40px;
        }

        /**
         * TARJETA DE FACTURACIÓN
         * Contenedor principal para el módulo de facturación
         * - Fondo blanco que contrasta con el fondo gris de la página
         * - Bordes redondeados y sombra suave
         * - Ancho máximo para mantener legibilidad
         * - Centrado horizontalmente
         */
        .facturacion-card {
            background-color: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            max-width: 900px;
            margin: 0 auto;
        }

        /**
         * HEADER DE LA TARJETA
         * Sección superior con icono y título
         * Borde inferior para separación visual del contenido
         */
        .card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }

        /**
         * ICONO DE LA TARJETA
         * Emoji decorativo grande para identificación visual rápida
         */
        .card-icon {
            font-size: 28px;
        }

        /**
         * TÍTULO DE LA TARJETA
         * Texto destacado en color corporativo rosa
         */
        .card-title {
            font-size: 28px;
            font-weight: 700;
            color: #e91e63;
        }

        /**
         * TABLA DE FACTURACIÓN
         * Tabla principal que muestra el historial de citas
         * - Border-collapse para eliminar espacios entre celdas
         * - Ancho completo del contenedor
         */
        .facturacion-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        /**
         * CABECERA DE LA TABLA
         * Fila de encabezados con degradado morado
         * Contrasta visualmente con el contenido de la tabla
         */
        .facturacion-table thead {
            background: linear-gradient(135deg, #7b68ee, #9370db);
        }

        /**
         * CELDAS DE ENCABEZADO
         * Texto blanco sobre fondo morado
         * Alineación a la izquierda para consistencia con los datos
         */
        .facturacion-table th {
            padding: 15px;
            text-align: left;
            color: white;
            font-weight: 600;
            font-size: 15px;
        }

        /**
         * FILAS DEL CUERPO DE LA TABLA
         * Borde inferior sutil para separación
         * Hover effect para mejorar la experiencia de usuario
         */
        .facturacion-table tbody tr {
            border-bottom: 1px solid #f0f0f0;
            transition: background-color 0.3s;
        }

        .facturacion-table tbody tr:hover {
            background-color: #f9f9f9;
        }

        /**
         * CELDAS DE DATOS
         * Padding generoso para legibilidad
         * Color de texto gris oscuro
         */
        .facturacion-table td {
            padding: 15px;
            color: #333;
            font-size: 14px;
        }

        /**
         * BADGES DE ESTADO
         * Etiquetas visuales para representar el estado de cada cita
         * Bordes redondeados tipo píldora
         * Display inline-block para mantener dimensiones consistentes
         */
        .estado {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 13px;
            font-weight: 600;
            display: inline-block;
        }

        /**
         * ESTADO: PAGADO
         * Verde claro indicando que el pago fue completado
         */
        .estado.pagado {
            background-color: #d4edda;
            color: #28a745;
        }

        /**
         * ESTADO: PENDIENTE
         * Amarillo indicando que está esperando confirmación o pago
         */
        .estado.pendiente {
            background-color: #fff3cd;
            color: #ffc107;
        }

        /**
         * ESTADO: CANCELADO
         * Rojo claro indicando que la cita fue cancelada
         */
        .estado.cancelado {
            background-color: #f8d7da;
            color: #dc3545;
        }

        /**
         * BOTÓN CERRAR / GENERAR FACTURA
         * Botón de acción principal en color gris oscuro
         * Efecto hover con elevación y sombra
         */
        .btn-cerrar {
            padding: 12px 30px;
            background-color: #455a64;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-cerrar:hover {
            background-color: #37474f;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(69, 90, 100, 0.3);
        }

        /**
         * MENSAJE DE CITAS VACÍAS
         * Estado visual cuando el cliente no tiene citas registradas
         * Texto centrado y en color gris claro
         */
        .no-citas {
            text-align: center;
            padding: 40px;
            color: #999;
            font-size: 16px;
        }

        /**
         * MEDIA QUERIES - RESPONSIVE DESIGN
         * Adaptaciones para diferentes tamaños de pantalla
         */
        
        /**
         * TABLETS Y PANTALLAS MEDIANAS (≤768px)
         * Ajustes para mejorar la experiencia en dispositivos de tamaño medio
         */
        @media (max-width: 768px) {
            /* Navbar adaptativo con wrap para elementos */
            .navbar {
                padding: 15px 20px;
                flex-wrap: wrap;
            }

            /* Reducir tamaño del título principal */
            .page-title {
                font-size: 28px;
            }

            /* Reducir padding de la tarjeta */
            .facturacion-card {
                padding: 20px;
            }

            /* Ajustar tamaño de fuente de la tabla */
            .facturacion-table {
                font-size: 13px;
            }

            /* Reducir padding de celdas para ahorrar espacio */
            .facturacion-table th,
            .facturacion-table td {
                padding: 10px 8px;
            }

            /**
             * CONTENEDOR DE TABLA CON SCROLL
             * Permite scroll horizontal en caso de que la tabla
             * sea más ancha que la pantalla del dispositivo
             */
            .table-container {
                overflow-x: auto;
            }
        }

        /**
         * MÓVILES (≤480px)
         * Ajustes adicionales para pantallas muy pequeñas
         */
        @media (max-width: 480px) {
            /* Reducir aún más el título de la tarjeta */
            .card-title {
                font-size: 22px;
            }

            /* Espaciado más compacto en sección de usuario */
            .user-section {
                gap: 8px;
            }

            /* Botones más pequeños en el header */
            .btn-header {
                padding: 6px 12px;
                font-size: 12px;
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
        <!-- Logo corporativo de Perle Noire -->
        <div class="logo">
            Perle Noire
        </div>
        
        <!-- 
            SECCIÓN DE USUARIO
            Muestra información del usuario y opciones de navegación
        -->
        <div class="user-section">
            <!-- Mostrar nombre completo del cliente -->
            <span><?php echo htmlspecialchars($cliente['nombre']); ?></span>
            
            <!-- 
                INFORMACIÓN DEL USUARIO
                Contiene avatar con iniciales
            -->
            <div class="user-info">
                <!-- Avatar circular con iniciales del cliente -->
                <div class="user-avatar"><?php echo $iniciales; ?></div>
            </div>
            
            <!-- Enlace para regresar a la página de servicios -->
            <a href="servicios.php" class="btn-header">Servicios</a>
            
            <!-- Enlace para cerrar sesión -->
            <a href="logout.php" class="btn-header">Cerrar Sesión</a>
        </div>
    </nav>

    <!-- 
        CONTENIDO PRINCIPAL
        Sección que contiene el módulo de facturación
    -->
    <div class="main-content">
        <!-- Título principal de la página -->
        <h1 class="page-title">Nuestros Servicios</h1>

        <!-- 
            TARJETA DE FACTURACIÓN
            Contenedor principal para el historial de citas y facturas
        -->
        <div class="facturacion-card">
            <!-- 
                HEADER DE LA TARJETA
                Sección superior con icono y título
            -->
            <div class="card-header">
                <span class="card-icon">📄</span>
                <h2 class="card-title">Facturación</h2>
            </div>

            <?php if (count($citas) > 0): ?>
                <!-- 
                    CASO 1: HAY CITAS REGISTRADAS
                    Mostrar tabla con el historial completo
                -->
                
                <!-- 
                    CONTENEDOR DE TABLA
                    Wrapper que permite scroll horizontal en dispositivos móviles
                -->
                <div class="table-container">
                    <!-- 
                        TABLA DE FACTURACIÓN
                        Muestra todas las citas con sus detalles
                    -->
                    <table class="facturacion-table">
                        <!-- 
                            CABECERA DE LA TABLA
                            Define las columnas: Servicio, Fecha, Precio, Estado
                        -->
                        <thead>
                            <tr>
                                <th>Servicio</th>
                                <th>Fecha</th>
                                <th>Precio</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        
                        <!-- 
                            CUERPO DE LA TABLA
                            Itera sobre todas las citas del cliente
                        -->
                        <tbody>
                            <?php foreach ($citas as $cita): ?>
                                <tr>
                                    <!-- 
                                        COLUMNA: SERVICIO
                                        Muestra el nombre del servicio contratado
                                        htmlspecialchars previene XSS
                                    -->
                                    <td><?php echo htmlspecialchars($cita['nombreServicio']); ?></td>
                                    
                                    <!-- 
                                        COLUMNA: FECHA
                                        Formatea la fecha al formato DD/MM/YYYY
                                        strtotime convierte la fecha de MySQL a timestamp
                                        date formatea el timestamp al formato deseado
                                    -->
                                    <td><?php echo date('d/m/Y', strtotime($cita['fechaCita'])); ?></td>
                                    
                                    <!-- 
                                        COLUMNA: PRECIO
                                        Formatea el precio con separadores de miles
                                        number_format(valor, decimales, sep_decimal, sep_miles)
                                    -->
                                    <td>$<?php echo number_format($cita['precio'], 0, ',', '.'); ?></td>
                                    
                                    <!-- 
                                        COLUMNA: ESTADO
                                        Muestra badge con el estado de la cita
                                    -->
                                    <td>
                                        <?php 
                                        /**
                                         * Convertir nombre del estado a minúsculas para usar como clase CSS
                                         * Esto permite aplicar el estilo correcto según el estado:
                                         * - "Pagado" -> clase "pagado" -> verde
                                         * - "Pendiente" -> clase "pendiente" -> amarillo
                                         * - "Cancelado" -> clase "cancelado" -> rojo
                                         */
                                        $estadoClass = strtolower($cita['estadoNombre']);
                                        ?>
                                        <!-- Badge de estado con clase dinámica -->
                                        <span class="estado <?php echo $estadoClass; ?>">
                                            <?php echo htmlspecialchars($cita['estadoNombre']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- 
                    BOTÓN DE GENERAR FACTURA
                    Acción principal del módulo (funcionalidad pendiente de implementación)
                -->
                <button class="btn-cerrar">Generar Factura</button>
                
            <?php else: ?>
                <!-- 
                    CASO 2: NO HAY CITAS REGISTRADAS
                    Mostrar mensaje informativo y botón para ir a servicios
                -->
                
                <!-- Mensaje de estado vacío -->
                <div class="no-citas">
                    No tienes citas registradas aún 😔
                </div>
                
                <!-- 
                    BOTÓN PARA VOLVER A SERVICIOS
                    Redirige al usuario a la página donde puede agendar citas
                    onclick ejecuta JavaScript para navegación
                -->
                <button class="btn-cerrar" onclick="window.location.href='servicios.php'">Volver a Servicios</button>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>