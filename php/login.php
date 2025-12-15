<?php
//Clase para inicias sesion y validar usuarios
// esta clase esta vinculada con registro.php y verificar_sesion.php

session_start();

// Si ya hay sesión activa, redirigir
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'Profesional') {
        header('Location: /ProyectoTimeNest/html/profesional.html'); //header es para redirigir a otra pagina
    } else {
        header('Location: /ProyectoTimeNest/html/cliente.html');
    }
    exit();
}

// Procesar el formulario de login
if ($_SERVER['REQUEST_METHOD'] == 'POST') { //aqui verifica si el metodo de envio es post
    $email = $_POST['email'];                   //POST es para obtener los datos del formulario
    $password = $_POST['password'];

    // Conexión a la base de datos
    $host = 'localhost';
    $dbname = 'timenest';
    $username = 'root';
    $db_password = '';

    try { //try catch para manejar errores
        //pdo es una clase de php para manejar bases de dato
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $db_password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        //aqui se prepara la consulta para buscar el usuario
        //la estructura es Select columna From tabla Where condicion, el ? significa que es un parametro que se va a pasar despues
        $stmt = $pdo->prepare("SELECT IDUsuario, Email, Contraseña, Rol, IdPerfil, Activo FROM usuario WHERE Email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);//fetch para obtener los datos
        //fetch es una funcion que obtiene los datos de la consulta y los guarda en un array asociativo


        // Verificar contraseña
        if ($usuario && password_verify($password, $usuario['Contraseña'])) {
            if ($usuario['Activo'] != 1) {//verifica si la cuenta esta activa
                $error = "Tu no esta actuva";
            } else {
                // Crear sesión
                $_SESSION['user_id'] = $usuario['IDUsuario']; //aqui se crean las variables de sesion
                $_SESSION['email'] = $usuario['Email'];
                $_SESSION['role'] = $usuario['Rol'];
                $_SESSION['profile_id'] = $usuario['IdPerfil'];

                //aqui se actualiza la fecha del ultimo login
                //la estructura es Update tabla Set columna = valor Where condicion
                $stmt = $pdo->prepare("UPDATE usuario SET UltimoLogin = NOW() WHERE IDUsuario = ?");
                $stmt->execute([$usuario['IDUsuario']]);

               //en esta parte se redirige al usuario segun su rol
                if ($usuario['Rol'] == 'Profesional') {
                    header('Location: /ProyectoTimeNest/html/profesionalClientes.html'); //header redirige
                } else {
                    header('Location: /ProyectoTimeNest/html/cliente.html');
                }
                exit();
            }
        } else {
            $error = "Correo o contraseña incorrectos";
        }
    } catch (PDOException $e) {
        $error = "Error de conexión";
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="/ProyectoTimeNest/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {/* Estilos para el fondo y centrado del formulario */
            background: linear-gradient(135deg, #2f50e2ff 0%, #9b50e7ff 100%); /* Degradado de colores */
            min-height: 100vh; /* Altura mínima de la ventana */
            display: flex;/*display flex para centrar*/
            align-items: center;
            justify-content: center; /* Centrado horizontal y vertical */
        }

        .login-card { /* Estilos para la tarjeta del formulario */
            /*la tarjeta es el contenedor del formulario*/
            max-width: 400px;
            width: 100%;
            background: white;
            border-radius: 10px; /* Bordes redondeados */
            padding: 30px;/* Espaciado interno */
            box-shadow: 0 10px 30px rgba(25, 229, 10, 0.91); /* Sombra para dar profundidad */
        }

        .login-card h2 { /* Estilos para el título */
            color: #667eea;
            margin-bottom: 30px;
        }

    </style>
</head>

<body>
    <div class="login-card">
        <h2 class="text-center">Iniciar Sesión</h2> <!-- Titulo -->

        <?php if (isset($error)): ?> <!-- isset verifica si la variable error esta definida -->
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?> <!-- endif cierra el if -->

        <!-- aqui va el formulario de login -->
        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Correo Electrónico</label>
                <input type="email" class="form-control" name="email" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Contraseña</label>
                <input type="password" class="form-control" name="password" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Entrar</button>
        </form>

        <div class="text-center mt-3">
            <a href="/ProyectoTimeNest/php/registro.php">No tienes cuenta? Regístrate</a>
        </div>
    </div>
</body>

</html>