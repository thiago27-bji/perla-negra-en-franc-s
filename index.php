<?php
/**
 * INDEX.PHP - PÁGINA DE INICIO DE SESIÓN
 * 
 * Sistema de autenticación de usuarios para Perle Noire
 * 
 * FUNCIONALIDAD PRINCIPAL:
 * - Formulario de inicio de sesión para clientes registrados
 * - Validación de credenciales contra la base de datos
 * - Establecimiento de sesión de usuario autenticado
 * - Redirección al sistema después de login exitoso
 * 
 * CARACTERÍSTICAS:
 * - Diseño split-screen con branding y formulario
 * - Validación de campos del lado del servidor
 * - Mensajes de error descriptivos
 * - Sistema de sesiones PHP
 * - Diseño responsive
 * 
 * FLUJO DE AUTENTICACIÓN:
 * 1. Usuario ingresa email y contraseña
 * 2. Se valida que ambos campos estén completos
 * 3. Se busca el usuario en la base de datos
 * 4. Si las credenciales coinciden, se crea la sesión
 * 5. Se redirige a servicios.php
 * 6. Si fallan, se muestra mensaje de error
 * 
 * DEPENDENCIAS:
 * - db.php: Conexión a la base de datos mediante PDO
 * - Tabla 'clientes' en la base de datos
 * 
 * SEGURIDAD:
 * NOTA: Este código almacena contraseñas en texto plano, lo cual NO es seguro.
 * En producción debería usar password_hash() y password_verify()
 * 
 * @author Sistema Perle Noire
 * @version 1.0
 */

// Iniciar sesión PHP para mantener el estado del usuario entre páginas
session_start();

// Incluir archivo de conexión a la base de datos
require 'db.php';

/**
 * INICIALIZACIÓN DE VARIABLE DE ERROR
 * 
 * Se inicializa como string vacío para evitar warnings de PHP
 * cuando se intenta usar la variable antes de ser definida
 */
$error = '';

/**
 * PROCESAMIENTO DEL FORMULARIO DE LOGIN
 * 
 * Este bloque se ejecuta solo cuando el usuario envía el formulario (método POST)
 * Realiza la validación de credenciales y establece la sesión
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    /**
     * RECOLECCIÓN SEGURA DE DATOS DEL FORMULARIO
     * 
     * Se utiliza isset() para verificar que los campos existan
     * antes de intentar acceder a ellos, evitando errores de PHP
     * Si no existen, se asigna string vacío como valor por defecto
     */
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $pass = isset($_POST['password']) ? $_POST['password'] : '';

    /**
     * VALIDACIÓN DE CAMPOS NO VACÍOS
     * 
     * Verifica que ambos campos tengan contenido antes de
     * intentar la autenticación en la base de datos
     */
    if ($email && $pass) {
        /**
         * CONSULTA DE AUTENTICACIÓN
         * 
         * Busca en la tabla 'clientes' un registro que coincida
         * exactamente con el email y password proporcionados
         * 
         * IMPORTANTE: Esta implementación NO es segura ya que:
         * 1. Compara passwords en texto plano
         * 2. No usa hashing (password_hash/password_verify)
         * 3. Es vulnerable a SQL injection si no se usan prepared statements
         * 
         * Los prepared statements (?) protegen contra SQL injection
         * al separar la consulta de los datos
         */
        $stmt = $pdo->prepare("SELECT * FROM clientes WHERE email = ? AND password = ?");
        $stmt->execute([$email, $pass]);
        $user = $stmt->fetch();

        /**
         * VERIFICACIÓN DE CREDENCIALES EXITOSA
         * 
         * Si fetch() retorna un resultado, significa que se encontró
         * un usuario con las credenciales proporcionadas
         */
        if ($user) {
            /**
             * ESTABLECIMIENTO DE SESIÓN
             * 
             * Se almacenan datos críticos del usuario en la sesión:
             * - $_SESSION['user']: Email del usuario (usado como identificador)
             * - $_SESSION['idCliente']: ID numérico del cliente en la BD
             * 
             * Estos datos se mantienen mientras la sesión esté activa
             * y se usan en otras páginas para identificar al usuario
             */
            $_SESSION['user'] = $user['email'];
            $_SESSION['idCliente'] = $user['idCliente'];
            
            /**
             * REDIRECCIÓN POST-LOGIN
             * 
             * Redirige al usuario a la página de servicios
             * header('Location: ...') debe ejecutarse antes de cualquier salida HTML
             * exit asegura que no se ejecute más código después de la redirección
             */
            header('Location: servicios.php');
            exit;
        } else {
            /**
             * CREDENCIALES INVÁLIDAS
             * 
             * Si no se encuentra coincidencia en la base de datos,
             * se establece un mensaje de error genérico
             * 
             * NOTA DE SEGURIDAD: Es buena práctica usar mensajes genéricos
             * para no revelar si el email existe o si la contraseña es incorrecta
             */
            $error = 'Usuario o contraseña incorrectos';
        }
    } else {
        /**
         * CAMPOS VACÍOS
         * 
         * Si alguno de los campos está vacío, se solicita completar
         * todos los campos del formulario
         */
        $error = 'Por favor complete todos los campos';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Perle Noire</title>
    <style>
        /**
         * RESET GLOBAL DE ESTILOS
         * Elimina estilos por defecto del navegador para consistencia
         */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        /**
         * ESTILOS DEL BODY
         * 
         * - Fuente moderna y legible
         * - Degradado de fondo azul-morado
         * - Flexbox para centrar contenido vertical y horizontalmente
         * - min-height: 100vh asegura que ocupe toda la pantalla
         * - padding: espaciado en móviles
         */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #6b8dd6, #8e7cc3);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        /**
         * CONTENEDOR PRINCIPAL DE LOGIN
         * 
         * Diseño split-screen (dos paneles lado a lado):
         * - Panel izquierdo: Branding e información
         * - Panel derecho: Formulario de login
         * 
         * Características:
         * - Flexbox para layout horizontal
         * - Fondo blanco que contrasta con el body
         * - Bordes redondeados generosos (24px)
         * - Sombra dramática para profundidad
         * - Animación de aparición suave
         */
        .login-wrapper {
            display: flex;
            background-color: white;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            max-width: 1000px;
            width: 100%;
            min-height: 600px;
            animation: aparecer 0.6s ease;
        }

        /**
         * PANEL IZQUIERDO - BRANDING
         * 
         * Sección visual que muestra la marca y mensaje corporativo
         * 
         * Características:
         * - flex: 1 (ocupa 50% del espacio disponible)
         * - Degradado rosa-coral corporativo
         * - Centrado vertical y horizontal de contenido
         * - Texto blanco para contraste
         */
        .brand-panel {
            flex: 1;
            background: linear-gradient(135deg, #ff6b9d, #ffa07a);
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: white;
        }

        /**
         * ICONO DE MARCA
         * 
         * Emoji decorativo grande que representa la marca
         * filter: drop-shadow añade sombra para profundidad
         */
        .brand-icon {
            font-size: 80px;
            margin-bottom: 30px;
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.2));
        }

        /**
         * TÍTULO DE MARCA
         * 
         * Nombre principal "Perle Noire" en tipografía grande y bold
         * text-shadow añade profundidad al texto
         */
        .brand-title {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 20px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /**
         * SUBTÍTULO DE MARCA
         * 
         * Eslogan o tagline de la empresa
         * Peso medio para jerarquía visual
         */
        .brand-subtitle {
            font-size: 18px;
            font-weight: 500;
            margin-bottom: 10px;
            opacity: 0.95;
        }

        /**
         * DESCRIPCIÓN DE MARCA
         * 
         * Texto descriptivo sobre el sistema
         * Ancho máximo para mantener legibilidad
         */
        .brand-description {
            font-size: 16px;
            opacity: 0.9;
            line-height: 1.6;
            max-width: 400px;
        }

        /**
         * PANEL DERECHO - FORMULARIO
         * 
         * Sección que contiene el formulario de inicio de sesión
         * 
         * Características:
         * - flex: 1 (ocupa 50% del espacio disponible)
         * - Fondo gris muy claro para diferenciación sutil
         * - Centrado vertical del contenido
         */
        .form-panel {
            flex: 1;
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background-color: #fafafa;
        }

        /**
         * ENCABEZADO DEL FORMULARIO
         * 
         * Sección superior con título y descripción del formulario
         * Centrado y con margen inferior generoso
         */
        .form-header {
            margin-bottom: 40px;
            text-align: center;
        }

        /**
         * TÍTULO DEL FORMULARIO
         * 
         * "Bienvenido" - mensaje de saludo al usuario
         * Color oscuro para máximo contraste
         */
        .form-title {
            font-size: 36px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        /**
         * SUBTÍTULO DEL FORMULARIO
         * 
         * Instrucción breve sobre la acción a realizar
         * Color gris para menor jerarquía visual
         */
        .form-subtitle {
            font-size: 16px;
            color: #7f8c8d;
        }

        /**
         * GRUPO DE FORMULARIO
         * 
         * Contenedor para cada par label+input
         * Margen inferior para espaciado entre campos
         */
        .form-group {
            margin-bottom: 25px;
        }

        /**
         * ETIQUETAS DE FORMULARIO
         * 
         * Labels que identifican cada campo
         * Display block para ocupar línea completa
         * Font-weight semi-bold para legibilidad
         */
        label {
            display: block;
            color: #2c3e50;
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        /**
         * INPUTS DE EMAIL Y PASSWORD
         * 
         * Campos de entrada con estilo consistente
         * 
         * Características:
         * - Ancho completo del contenedor
         * - Padding generoso para táctil
         * - Borde gris claro
         * - Bordes redondeados
         * - Transiciones suaves en interacciones
         * - Fondo blanco para contraste con panel gris
         */
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s;
            background-color: white;
        }

        /**
         * ESTADO FOCUS DE INPUTS
         * 
         * Cuando el usuario hace clic en un campo:
         * - Borde cambia a color corporativo rosa
         * - Aparece un "glow" rosa alrededor del campo
         * - Se remueve el outline por defecto del navegador
         * 
         * Esto mejora la experiencia visual y muestra claramente
         * qué campo está activo
         */
        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: #ff6b9d;
            box-shadow: 0 0 0 4px rgba(255, 107, 157, 0.1);
            outline: none;
        }

        /**
         * PLACEHOLDER DE INPUTS
         * 
         * Texto de ejemplo dentro de los campos
         * Color gris claro para indicar que no es contenido real
         */
        input::placeholder {
            color: #bdc3c7;
        }

        /**
         * BOTÓN DE INICIO DE SESIÓN
         * 
         * Call-to-action principal del formulario
         * 
         * Características:
         * - Ancho completo
         * - Degradado rosa-coral corporativo
         * - Texto blanco en mayúsculas para énfasis
         * - Letter-spacing para legibilidad
         * - Cursor pointer para indicar interactividad
         * - Transiciones suaves para hover y active
         */
        .btn-login {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #ff6b9d, #ffa07a);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 17px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /**
         * EFECTO HOVER DEL BOTÓN
         * 
         * Cuando el cursor pasa sobre el botón:
         * - Degradado se vuelve más intenso
         * - Se eleva ligeramente (translateY negativo)
         * - Aparece sombra pronunciada
         * 
         * Esto proporciona feedback visual de interactividad
         */
        .btn-login:hover {
            background: linear-gradient(135deg, #ff5a8c, #ff8f69);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 107, 157, 0.4);
        }

        /**
         * EFECTO ACTIVE DEL BOTÓN
         * 
         * Cuando el usuario hace clic:
         * - El botón vuelve a su posición original
         * - Simula un "press" físico
         */
        .btn-login:active {
            transform: translateY(0);
        }

        /**
         * ENLACE DE REGISTRO
         * 
         * Sección inferior con texto y enlace para usuarios nuevos
         * Centrado y con espaciado superior
         */
        .register-link {
            text-align: center;
            margin-top: 25px;
            font-size: 15px;
            color: #7f8c8d;
        }

        /**
         * ESTILO DEL ENLACE DE REGISTRO
         * 
         * Link "Regístrate aquí" con color corporativo
         * Hover añade subrayado para indicar interactividad
         */
        .register-link a {
            color: #ff6b9d;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }

        .register-link a:hover {
            color: #ff5a8c;
            text-decoration: underline;
        }

        /**
         * MENSAJE DE ERROR
         * 
         * Alert box que se muestra cuando hay errores de autenticación
         * 
         * Características:
         * - Fondo rojo muy claro
         * - Texto rojo oscuro
         * - Bordes redondeados
         * - Borde rojo claro
         * - Centrado para visibilidad
         * 
         * Se muestra solo cuando la variable $error tiene contenido
         */
        .error {
            background-color: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
            border: 1px solid #fcc;
        }

        /**
         * ANIMACIÓN DE APARICIÓN
         * 
         * Keyframes que definen la animación del login-wrapper
         * 
         * Efecto:
         * - Inicia invisible y ligeramente más pequeño (scale 0.95)
         * - Termina visible y a tamaño normal (scale 1)
         * - Duración: 0.6s con easing suave
         * 
         * Esto crea una entrada elegante y profesional
         */
        @keyframes aparecer {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        /**
         * MEDIA QUERIES - DISEÑO RESPONSIVE
         * 
         * Adaptaciones para diferentes tamaños de pantalla
         */
        
        /**
         * TABLETS Y PANTALLAS MEDIANAS (≤768px)
         * 
         * Cambios principales:
         * - Layout cambia de horizontal a vertical (flex-direction: column)
         * - Panel de branding arriba, formulario abajo
         * - Reducción de tamaños de fuente
         * - Ajuste de padding para ahorrar espacio
         */
        @media (max-width: 768px) {
            /* Cambiar a layout vertical */
            .login-wrapper {
                flex-direction: column;
            }

            /* Reducir espacio del panel de branding */
            .brand-panel {
                padding: 40px 30px;
                min-height: 300px;
            }

            /* Título más pequeño */
            .brand-title {
                font-size: 36px;
            }

            /* Reducir padding del formulario */
            .form-panel {
                padding: 40px 30px;
            }

            /* Título del formulario más pequeño */
            .form-title {
                font-size: 28px;
            }
        }

        /**
         * MÓVILES PEQUEÑOS (≤480px)
         * 
         * Ajustes adicionales para pantallas muy pequeñas:
         * - Padding mínimo en body
         * - Tamaños de fuente reducidos
         * - Icono más pequeño
         */
        @media (max-width: 480px) {
            /* Padding mínimo para maximizar espacio */
            body {
                padding: 10px;
            }

            /* Reducir aún más el padding del branding */
            .brand-panel {
                padding: 30px 20px;
            }

            /* Título más compacto */
            .brand-title {
                font-size: 32px;
            }

            /* Icono más pequeño */
            .brand-icon {
                font-size: 60px;
            }

            /* Formulario con menos padding */
            .form-panel {
                padding: 30px 20px;
            }

            /* Título del formulario compacto */
            .form-title {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <!-- 
        CONTENEDOR PRINCIPAL DE LOGIN
        Estructura split-screen con branding y formulario
    -->
    <div class="login-wrapper">
        <!-- 
            PANEL IZQUIERDO - BRANDING
            Sección visual con información de la marca
        -->
        <div class="brand-panel">
            <!-- Icono decorativo de lápiz labial -->
            <div class="brand-icon">💄</div>
            
            <!-- Nombre de la marca -->
            <h1 class="brand-title">Perle Noire</h1>
            
            <!-- Eslogan corporativo -->
            <p class="brand-subtitle">Tu belleza, nuestra pasión.</p>
            
            <!-- Descripción breve del sistema -->
            <p class="brand-description">Sistema integral de gestión para salones de belleza.</p>
        </div>

        <!-- 
            PANEL DERECHO - FORMULARIO
            Sección que contiene el formulario de inicio de sesión
        -->
        <div class="form-panel">
            <!-- 
                ENCABEZADO DEL FORMULARIO
                Título y descripción de la acción
            -->
            <div class="form-header">
                <h2 class="form-title">Bienvenido</h2>
                <p class="form-subtitle">Inicia sesión en tu cuenta</p>
            </div>

            <!-- 
                FORMULARIO DE LOGIN
                Envía datos por POST a la misma página (action="")
            -->
            <form method="POST" action="">
                <!-- 
                    MENSAJE DE ERROR
                    Se muestra solo si la variable $error tiene contenido
                    Utiliza output directo de PHP (NOTA: podría ser vulnerable a XSS,
                    en producción usar htmlspecialchars())
                -->
                <?php if ($error) echo "<div class='error'>$error</div>"; ?>
                
                <!-- 
                    CAMPO: CORREO ELECTRÓNICO
                    Input tipo email con validación HTML5
                    - required: campo obligatorio
                    - placeholder: texto de ejemplo
                -->
                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" id="email" name="email" placeholder="tu@email.com" required>
                </div>

                <!-- 
                    CAMPO: CONTRASEÑA
                    Input tipo password (oculta caracteres)
                    - required: campo obligatorio
                    - placeholder: muestra bullets como ejemplo
                -->
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                </div>

                <!-- 
                    BOTÓN DE ENVÍO
                    type="submit" envía el formulario al hacer clic
                -->
                <button type="submit" class="btn-login">Iniciar Sesión</button>

                <!-- 
                    ENLACE DE REGISTRO
                    Opción para usuarios que no tienen cuenta
                    Redirige a register.php
                -->
                <div class="register-link">
                    ¿No tienes cuenta? <a href="register.php">Regístrate aquí</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>