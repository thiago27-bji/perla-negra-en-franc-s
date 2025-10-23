<?php
/**
 * REGISTER.PHP - PÁGINA DE REGISTRO DE NUEVOS USUARIOS
 * 
 * FUNCIONALIDAD PRINCIPAL:
 * - Formulario de registro para nuevos clientes
 * - Validación de datos del lado del servidor
 * - Inserción de nuevos usuarios en la tabla 'clientes'
 * - Verificación de emails duplicados
 * - Redirección automática al login después del registro exitoso
 * 
 * CARACTERÍSTICAS:
 * - Diseño consistente con index.php (login)
 * - Validación de campos obligatorios
 * - Mensajes de error y éxito descriptivos
 * - Diseño responsive
 * - Campos: nombre, apellido, teléfono, email, contraseña
 * 
 * FLUJO DE REGISTRO:
 * 1. Usuario completa el formulario
 * 2. Se valida que todos los campos estén completos
 * 3. Se verifica que el email no esté registrado
 * 4. Se inserta el nuevo cliente en la base de datos
 * 5. Se redirige a login con mensaje de éxito
 * 
 * DEPENDENCIAS:
 * - db.php: Conexión a la base de datos mediante PDO
 * 
 * TABLA DE BASE DE DATOS:
 * - clientes (idCliente, nombre, apellido, telefono, email, password, fechaRegistro)
 * 
 * SEGURIDAD:
 * NOTA: Este código almacena contraseñas en texto plano, lo cual NO es seguro.
 * En producción debería usar password_hash() para encriptar contraseñas
 * 
 * @author Sistema Perle Noire
 * @version 1.0
 */

// Incluir archivo de conexión a la base de datos
require 'db.php';

/**
 * INICIALIZACIÓN DE VARIABLES DE RETROALIMENTACIÓN
 * 
 * Previene warnings de PHP cuando las variables son usadas
 * antes de ser definidas
 */
$error = '';
$success = '';

/**
 * PROCESAMIENTO DEL FORMULARIO DE REGISTRO
 * 
 * Este bloque se ejecuta solo cuando el usuario envía el formulario (método POST)
 * Realiza todas las validaciones y guarda el nuevo cliente
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    /**
     * RECOLECCIÓN SEGURA DE DATOS DEL FORMULARIO
     * 
     * Se utiliza isset() para verificar que los campos existan
     * antes de intentar acceder a ellos, evitando errores de PHP
     * Se aplica trim() para eliminar espacios en blanco al inicio y final
     */
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $apellido = isset($_POST['apellido']) ? trim($_POST['apellido']) : '';
    $telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    /**
     * VALIDACIÓN 1: CAMPOS OBLIGATORIOS
     * 
     * Verifica que todos los campos tengan contenido
     * Si algún campo está vacío, muestra mensaje de error
     */
    if (empty($nombre) || empty($apellido) || empty($telefono) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error = 'Todos los campos son obligatorios';
    }
    /**
     * VALIDACIÓN 2: CONFIRMACIÓN DE CONTRASEÑA
     * 
     * Verifica que ambas contraseñas coincidan
     * Previene errores de tipeo del usuario
     */
    elseif ($password !== $confirmPassword) {
        $error = 'Las contraseñas no coinciden';
    }
    /**
     * VALIDACIÓN 3: LONGITUD DE CONTRASEÑA
     * 
     * Verifica que la contraseña tenga al menos 6 caracteres
     * Mejora la seguridad básica de las cuentas
     */
    elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres';
    }
    /**
     * SI TODAS LAS VALIDACIONES PASAN
     * 
     * Procede a verificar email duplicado e insertar en base de datos
     */
    else {
        try {
            /**
             * VERIFICACIÓN DE EMAIL DUPLICADO
             * 
             * Consulta si ya existe un cliente con ese email
             * Los emails deben ser únicos en el sistema
             */
            $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM clientes WHERE email = ?");
            $stmtCheck->execute([$email]);
            $emailExists = $stmtCheck->fetchColumn();

            /**
             * SI EL EMAIL YA EXISTE
             * 
             * Rechaza el registro y solicita usar otro email
             */
            if ($emailExists > 0) {
                $error = 'Este correo electrónico ya está registrado';
            }
            /**
             * SI EL EMAIL ES ÚNICO
             * 
             * Procede a insertar el nuevo cliente en la base de datos
             */
            else {
                /**
                 * INSERCIÓN DEL NUEVO CLIENTE
                 * 
                 * Campos insertados:
                 * - nombre: Nombre del cliente
                 * - apellido: Apellido del cliente
                 * - telefono: Número de teléfono
                 * - email: Correo electrónico (único)
                 * - password: Contraseña (⚠️ en texto plano - NO seguro)
                 * - fechaRegistro: Fecha actual del registro
                 * 
                 * NOTA DE SEGURIDAD:
                 * En producción, usar: password_hash($password, PASSWORD_DEFAULT)
                 * 
                 * idCliente no se incluye porque es AUTO_INCREMENT
                 */
                $stmtInsert = $pdo->prepare("INSERT INTO clientes (nombre, apellido, telefono, email, password, fechaRegistro) VALUES (?, ?, ?, ?, ?, NOW())");
                $stmtInsert->execute([$nombre, $apellido, $telefono, $email, $password]);

                /**
                 * REGISTRO EXITOSO
                 * 
                 * Establece mensaje de éxito y redirige después de 2 segundos
                 * header("refresh:2;...") espera 2 segundos antes de redirigir
                 * Esto permite al usuario ver el mensaje de éxito
                 */
                $success = '¡Registro exitoso! Redirigiendo al inicio de sesión...';
                header("refresh:2;url=index.php");
            }

        } catch(PDOException $e) {
            /**
             * MANEJO DE ERRORES DE BASE DE DATOS
             * 
             * Captura cualquier error durante la inserción
             * Ejemplos: problemas de conexión, violación de constraints, etc.
             */
            $error = 'Error al registrar el usuario: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Perle Noire</title>
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
         * Idénticos a index.php para consistencia visual
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
         * CONTENEDOR PRINCIPAL DE REGISTRO
         * Similar a login pero adaptado para más campos
         */
        .register-wrapper {
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
         * Idéntico al de index.php para consistencia
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

        .brand-icon {
            font-size: 80px;
            margin-bottom: 30px;
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.2));
        }

        .brand-title {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 20px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .brand-subtitle {
            font-size: 18px;
            font-weight: 500;
            margin-bottom: 10px;
            opacity: 0.95;
        }

        .brand-description {
            font-size: 16px;
            opacity: 0.9;
            line-height: 1.6;
            max-width: 400px;
        }

        /**
         * PANEL DERECHO - FORMULARIO DE REGISTRO
         * Más espacio vertical para acomodar campos adicionales
         */
        .form-panel {
            flex: 1;
            padding: 40px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background-color: #fafafa;
            overflow-y: auto;
        }

        .form-header {
            margin-bottom: 30px;
            text-align: center;
        }

        .form-title {
            font-size: 36px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .form-subtitle {
            font-size: 16px;
            color: #7f8c8d;
        }

        /**
         * GRUPO DE FORMULARIO
         * Margen reducido para acomodar más campos
         */
        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            color: #2c3e50;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 6px;
        }

        /**
         * INPUTS DEL FORMULARIO
         * Soporte para text, email, tel y password
         */
        input[type="text"],
        input[type="email"],
        input[type="tel"],
        input[type="password"] {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s;
            background-color: white;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="tel"]:focus,
        input[type="password"]:focus {
            border-color: #ff6b9d;
            box-shadow: 0 0 0 4px rgba(255, 107, 157, 0.1);
            outline: none;
        }

        input::placeholder {
            color: #bdc3c7;
        }

        /**
         * GRID DE DOS COLUMNAS
         * Para mostrar nombre y apellido lado a lado
         * Se colapsa a una columna en móviles
         */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        /**
         * BOTÓN DE REGISTRO
         * Estilo corporativo consistente con login
         */
        .btn-register {
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

        .btn-register:hover {
            background: linear-gradient(135deg, #ff5a8c, #ff8f69);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 107, 157, 0.4);
        }

        .btn-register:active {
            transform: translateY(0);
        }

        /**
         * ENLACE AL LOGIN
         * Para usuarios que ya tienen cuenta
         */
        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 15px;
            color: #7f8c8d;
        }

        .login-link a {
            color: #ff6b9d;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }

        .login-link a:hover {
            color: #ff5a8c;
            text-decoration: underline;
        }

        /**
         * MENSAJES DE ERROR
         * Fondo rojo claro con texto rojo oscuro
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
         * MENSAJES DE ÉXITO
         * Fondo verde claro con texto verde oscuro
         */
        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
            border: 1px solid #c3e6cb;
        }

        /**
         * ANIMACIÓN DE APARICIÓN
         * Entrada suave del contenedor
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
         * MEDIA QUERIES RESPONSIVE
         */
        @media (max-width: 768px) {
            .register-wrapper {
                flex-direction: column;
            }

            .brand-panel {
                padding: 40px 30px;
                min-height: 250px;
            }

            .brand-title {
                font-size: 36px;
            }

            .brand-icon {
                font-size: 60px;
            }

            .form-panel {
                padding: 30px 25px;
            }

            .form-title {
                font-size: 28px;
            }

            /**
             * COLAPSAR GRID A UNA COLUMNA EN MÓVIL
             * Nombre y apellido se muestran uno debajo del otro
             */
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 10px;
            }

            .brand-panel {
                padding: 30px 20px;
            }

            .brand-title {
                font-size: 32px;
            }

            .form-panel {
                padding: 25px 20px;
            }

            .form-title {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <!-- 
        CONTENEDOR PRINCIPAL DE REGISTRO
        Estructura similar a login para consistencia
    -->
    <div class="register-wrapper">
        <!-- 
            PANEL IZQUIERDO - BRANDING
            Idéntico al de login
        -->
        <div class="brand-panel">
            <div class="brand-icon">💄</div>
            <h1 class="brand-title">Perle Noire</h1>
            <p class="brand-subtitle">Tu belleza, nuestra pasión.</p>
            <p class="brand-description">Únete a nuestra comunidad y comienza tu transformación.</p>
        </div>

        <!-- 
            PANEL DERECHO - FORMULARIO DE REGISTRO
        -->
        <div class="form-panel">
            <div class="form-header">
                <h2 class="form-title">Crear Cuenta</h2>
                <p class="form-subtitle">Completa tus datos para registrarte</p>
            </div>

            <!-- 
                FORMULARIO DE REGISTRO
                Envía datos por POST a la misma página
            -->
            <form method="POST" action="">
                <!-- 
                    MENSAJE DE ERROR
                    Se muestra solo si hay errores de validación
                -->
                <?php if ($error): ?>
                    <div class="error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <!-- 
                    MENSAJE DE ÉXITO
                    Se muestra cuando el registro es exitoso
                -->
                <?php if ($success): ?>
                    <div class="success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                
                <!-- 
                    FILA 1: NOMBRE Y APELLIDO
                    Grid de dos columnas en desktop, una en móvil
                -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="nombre">Nombre *</label>
                        <input 
                            type="text" 
                            id="nombre" 
                            name="nombre" 
                            placeholder="Ej: María"
                            value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="apellido">Apellido *</label>
                        <input 
                            type="text" 
                            id="apellido" 
                            name="apellido" 
                            placeholder="Ej: García"
                            value="<?php echo isset($_POST['apellido']) ? htmlspecialchars($_POST['apellido']) : ''; ?>"
                            required>
                    </div>
                </div>

                <!-- 
                    CAMPO: TELÉFONO
                    Input tipo tel para teclado numérico en móviles
                -->
                <div class="form-group">
                    <label for="telefono">Teléfono *</label>
                    <input 
                        type="tel" 
                        id="telefono" 
                        name="telefono" 
                        placeholder="Ej: 3001234567"
                        value="<?php echo isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : ''; ?>"
                        required>
                </div>

                <!-- 
                    CAMPO: EMAIL
                    Input tipo email con validación HTML5
                -->
                <div class="form-group">
                    <label for="email">Correo Electrónico *</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        placeholder="tu@email.com"
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                        required>
                </div>

                <!-- 
                    CAMPO: CONTRASEÑA
                    Input tipo password (oculta caracteres)
                -->
                <div class="form-group">
                    <label for="password">Contraseña *</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Mínimo 6 caracteres"
                        required>
                </div>

                <!-- 
                    CAMPO: CONFIRMAR CONTRASEÑA
                    Previene errores de tipeo del usuario
                -->
                <div class="form-group">
                    <label for="confirm_password">Confirmar Contraseña *</label>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        placeholder="Repite tu contraseña"
                        required>
                </div>

                <!-- 
                    BOTÓN DE ENVÍO
                    Crea la cuenta y guarda en base de datos
                -->
                <button type="submit" class="btn-register">Crear Cuenta</button>

                <!-- 
                    ENLACE AL LOGIN
                    Para usuarios que ya tienen cuenta
                -->
                <div class="login-link">
                    ¿Ya tienes cuenta? <a href="index.php">Inicia sesión aquí</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>