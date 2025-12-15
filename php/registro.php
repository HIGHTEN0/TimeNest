<?php
//en esta clase se registra un nuevo usuario
//esta vinculada con la base de datos

session_start();

// FUNCIÓN DE VALIDACIÓN DE CORREO
function validarCorreo($email) {
    // Dominios permitidos
    $dominios_permitidos = [
        'gmail.com', 
        'hotmail.com', 
        'tecnm.mx'
    ];
    
    // Validar formato básico
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { //filter_var es una funcion de php para validar datos
        return false;          //FILTER_VALIDATE_EMAIL es una constante que indica que se quiere validar un correo electronico
    }
    
    // Validar dominio
    $partes = explode('@', $email); //explode es una funcion que divide una cadena en un array segun un delimitador
    if (count($partes) != 2) { //verifica que haya solo una @
        return false;
    }
    
    $dominio = strtolower($partes[1]); //strtolower convierte una cadena a minusculas
    
    return in_array($dominio, $dominios_permitidos); //in_array verifica si un valor existe en un array
}

// Si ya hay sesión activa, redirigir
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'Profesional') {
        header('Location: /ProyectoTimeNest/html/profesional.html'); //header redirige
    } else {
        header('Location: /ProyectoTimeNest/html/cliente.html');
    }
    exit();
}

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {//REQUEST_METHOD es una variable superglobal que contiene el metodo de envio del formulario
    $nombre = trim($_POST['nombre']); //trim quita espacios en blanco al inicio y al final
    $apellido = trim($_POST['apellido']);//_POST es para obtener los datos del formulario
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];
    $telefono = trim($_POST['telefono']);
    $profesion = isset($_POST['profesion']) ? trim($_POST['profesion']) : null; //isset verifica si una variable esta definida

    // Validaciones
      if (!validarCorreo($email)) {
        $error = "Use un correo valido";
    }
    // validacion telefono
    elseif (!empty($telefono) && !preg_match('/^[0-9]{10}$/', $telefono)) {//preg_match verifica si una cadena coincide con una expresion regular
        //empty verifica si una variable esta vacia
        ///^[0-9]{10}$/ es una expresion regular que verifica que la cadena tenga exactamente 10 digitos
        $error = "El teléfono debe tener 10 dígitos";
    }
    if (empty($nombre) || empty($email) || empty($password) || empty($role)) {
        $error = "Completa todos los campos obligatorios";
    } elseif ($role == 'Profesional' && empty($profesion)) {
        $error = "La profesión es obligatoria";
    } elseif ($password !== $confirm_password) {
        $error = "Las contraseñas no coinciden";
    } elseif (strlen($password) < 6) {//strlen obtiene la longitud de una cadena
        $error = "La contraseña debe tener al menos 6 caracteres";
    } else {// Conexión a la base de datos
        $host = 'localhost';
        $dbname = 'timenest';
        $username = 'root';
        $db_password = '';

        try {//try catch para manejar errores
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $db_password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Verificar si el email existe
            $stmt = $pdo->prepare("SELECT IDUsuario FROM usuario WHERE Email = ?");
            $stmt->execute([$email]);

            if ($stmt->fetch()) {//fetch obtiene los datos de la consulta
                $error = "Este correo ya está registrado";
            } else {
                $pdo->beginTransaction();//beginTransaction inicia una transaccion
                //una transaccion es un conjunto de operaciones que se ejecutan como una sola unidad

                try {
                    if ($role == 'Profesional') {
                        $stmt = $pdo->prepare("INSERT INTO profesional (NombreProfesional, ApellidoProfesional, Profesion, NumTelefono) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$nombre, $apellido, $profesion, $telefono]);
                        $profile_id = $pdo->lastInsertId();//lastInsertId obtiene el id del ultimo registro insertado
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO cliente (NombreCliente, ApellidoCliente, NumTelefono) VALUES (?, ?, ?)");
                        $stmt->execute([$nombre, $apellido, $telefono]);
                        $profile_id = $pdo->lastInsertId();//pdo es una clase de php para manejar bases de datos
                    }
                    //hash es una funcion para encriptar contraseñas
                    $contraseña_hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO usuario (Email, Contraseña, Rol, IdPerfil) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$email, $contraseña_hash, $role, $profile_id]);//execute ejecuta la consulta preparada con los parametros dados

                    $pdo->commit();//commit confirma la transaccion

                    $success = "Registro exitoso. Redirigiendo al login...";
                    header("refresh:2;url=/ProyectoTimeNest/php/login.php");
                } catch (Exception $e) {
                    $pdo->rollBack();//rollBack deshace la transaccion en caso de error
                    $error = "Error al registrar";
                }
            }
        } catch (PDOException $e) {
            $error = "Error de conexión";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- width=device-width hace que el ancho de la pagina sea igual al ancho de la pantalla del dispositivo -->
    <title>Registro</title>
    <link href="/ProyectoTimeNest/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
    body { /* Estilos para el fondo y centrado del formulario */
        background: linear-gradient(135deg, #3657eaff 0%, #9e4bf0ff 100%);/* Degradado de colores */
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .registro-card { /* Estilos para la tarjeta del formulario */
        max-width: 500px;
        width: 100%;
        background: white;
        border-radius: 10px;
        padding: 30px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }

    .registro-card h2 {
        color: #667eea;
        margin-bottom: 30px;
    }

    .campo-profesional {
        display: none;
    }
    </style>
</head>

<body>
    <div class="registro-card">
        <h2 class="text-center">Registro</h2>

        <?php if (isset($error)): ?> <!-- isset verifica si la variable error esta definida -->
        <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (isset($success)): ?><!-- isset verifica si la variable success esta definida -->
        <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST" action="" id="registroForm">
            <div class="mb-3">
                <label class="form-label">Tipo de Usuario *</label>
                <select class="form-control" name="role" id="role" required onchange="cambiarFormulario()">
                    <option value="">Seleccionar...</option>
                    <option value="Profesional">Profesional</option>
                    <option value="Cliente">Cliente</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Nombre *</label>
                <input type="text" class="form-control" name="nombre" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Apellido</label>
                <input type="text" class="form-control" name="apellido">
            </div>

            <div class="mb-3 campo-profesional" id="campoProfesion">
                <label class="form-label">Profesión *</label>
                <input type="text" class="form-control" name="profesion" id="profesion" placeholder="Ej. Odontólogo">
            </div>

            <div class="mb-3">
                <label class="form-label">Correo Electrónico *</label>
                <input type="email" class="form-control" name="email" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Teléfono (10 digitos)</label>
                <input type="tel" class="form-control" id="telefono" name="telefono" pattern="[0-9]{10}" maxlength="10">
                <!-- pattern es una expresion regular que valida que el telefono tenga 10 digitos -->
            </div>

            <div class="mb-3">
                <label class="form-label">Contraseña * (mínimo 6 caracteres)</label>
                <input type="password" class="form-control" name="password" id="password" minlength="6" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Confirmar Contraseña *</label>
                <input type="password" class="form-control" name="confirm_password" minlength="6" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Registrarse</button>
            <!-- w-100 hace que el boton ocupe todo el ancho del contenedor -->
        </form>

        <div class="text-center mt-3">
            <a href="/ProyectoTimeNest/php/login.php">Ya tienes cuenta? Inicia sesión</a>
        </div>
    </div>

    <script>
    function cambiarFormulario() { //funcion para mostrar u ocultar el campo profesion
        const role = document.getElementById('role').value;
        const campoProfesion = document.getElementById('campoProfesion'); //aqui se obtiene el div del campo profesion
        //div es un contenedor que agrupa elementos html
        const profesionInput = document.getElementById('profesion');//getElementById obtiene un elemento por su id

        if (role === 'Profesional') {
            campoProfesion.style.display = 'block'; //style.display cambia la propiedad de visualizacion del elemento
            profesionInput.required = true; //required hace que el campo sea obligatorio
        } else {
            campoProfesion.style.display = 'none'; //none oculta el elemento y block lo muestra como un bloque
            profesionInput.required = false;
            profesionInput.value = '';
        }
    }

    document.getElementById('registroForm').addEventListener('submit', function(e) { //aqui se valida que las contraseñas coincidan antes de enviar el formulario
        const password = document.getElementById('password').value;
        const confirmPassword = document.querySelector('input[name="confirm_password"]').value;

        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Las contraseñas no coinciden');
        }
    });
    </script>
</body>

</html>