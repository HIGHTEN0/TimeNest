<?php
session_start();

// Verificar si hay sesión activa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Cliente') {
    header('Location: /ProyectoTimeNest/php/login.php');
    exit();
}

// Conexión
$host = 'localhost';
$dbname = 'timenest';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $profile_id = $_SESSION['profile_id'];
    
    // Procesar actualización
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        
        // Actualizar foto de perfil
        if (isset($_POST['actualizar_foto']) && $_POST['actualizar_foto'] == '1') {
            $foto = $_POST['foto_base64'];
            $stmt = $pdo->prepare("UPDATE cliente SET FotoPerfil = ? WHERE IDCliente = ?");
            $stmt->execute([$foto, $profile_id]);
            
            $mensaje = "Foto de perfil actualizada";
        }
        // Actualizar campos de texto
        elseif (isset($_POST['campo']) && isset($_POST['valor'])) {
            $campo = $_POST['campo'];
            $valor = $_POST['valor'];
            
            // Campos permitidos para actualizar en tabla cliente
            $campos_permitidos = [
                'NombreCliente', 'ApellidoCliente', 'NumTelefono'
            ];
            
            if (in_array($campo, $campos_permitidos)) {
                $stmt = $pdo->prepare("UPDATE cliente SET $campo = ? WHERE IDCliente = ?");
                $stmt->execute([$valor, $profile_id]);
                
                $mensaje = "Campo actualizado correctamente";
            }
        }
    }
    
    // Obtener datos del cliente
    $stmt = $pdo->prepare("SELECT * FROM cliente WHERE IDCliente = ?");
    $stmt->execute([$profile_id]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Obtener email del usuario
    $stmt = $pdo->prepare("SELECT Email FROM usuario WHERE IdPerfil = ? AND Rol = 'Cliente'");
    $stmt->execute([$profile_id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = "Error de base de datos: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil - Cliente</title>
    <link href="/ProyectoTimeNest/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="/ProyectoTimeNest/fontawesome/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f5f5;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .table td {
            vertical-align: middle;
        }
        .btn-editar {
            background: #ffc107;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn-editar:hover {
            background: #ffca28;
        }
        .foto-container {
            text-align: center;
            margin: 20px 0;
        }
        .foto-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #107dea;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center mb-4">Editar Perfil Cliente</h2>
        
        <?php if (isset($mensaje)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $mensaje; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Foto de perfil -->
        <div class="foto-container">
            <h5>Foto de Perfil</h5>
            <img id="previewFoto" class="foto-preview" 
                 src="<?php echo !empty($cliente['FotoPerfil']) ? $cliente['FotoPerfil'] : '/imagenes/usuario.png'; ?>">
            <input type="file" id="inputFoto" accept="image/*" class="form-control mt-2" style="max-width: 300px; margin: 0 auto;">
            <button class="btn btn-primary mt-2" onclick="guardarFoto()">Guardar Foto</button>
        </div>
        
        <h4 class="mt-4 mb-3">Información Personal</h4>
        
        <!-- Datos -->
        <table class="table table-hover">
            <tr>
                <td><strong>Nombre:</strong></td>
                <td id="valor-NombreCliente"><?php echo $cliente['NombreCliente']; ?></td>
                <td><button class="btn-editar" onclick="editarCampo('NombreCliente', 'Nombre')">
                    <i class="fas fa-pencil-alt"></i></button></td>
            </tr>
            <tr>
                <td><strong>Apellido:</strong></td>
                <td id="valor-ApellidoCliente"><?php echo $cliente['ApellidoCliente'] ?: 'No especificado'; ?></td>
                <td><button class="btn-editar" onclick="editarCampo('ApellidoCliente', 'Apellido')">
                    <i class="fas fa-pencil-alt"></i></button></td>
            </tr>
            <tr>
                <td><strong>Correo Electrónico:</strong></td>
                <td><?php echo $usuario['Email']; ?></td>
                <td><i class="fas fa-lock text-muted" title="No editable"></i></td>
            </tr>
            <tr>
                <td><strong>Teléfono:</strong></td>
                <td id="valor-NumTelefono"><?php echo $cliente['NumTelefono'] ?: 'No especificado'; ?></td>
                <td><button class="btn-editar" onclick="editarCampo('NumTelefono', 'Teléfono')">
                    <i class="fas fa-pencil-alt"></i></button></td>
            </tr>
        </table>
        
        <div class="text-center mt-4">
            <a href="/ProyectoTimeNest/html/cliente.html" class="btn btn-secondary">Volver</a>
        </div>
    </div>
    
    <!-- Form oculto para foto -->
    <form id="formFoto" method="POST" style="display:none;">
        <input type="hidden" name="actualizar_foto" value="1">
        <input type="hidden" id="foto_base64" name="foto_base64">
    </form>
    
    <script src="/ProyectoTimeNest/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        // Preview y guardar foto
        let fotoTemporal = null;
        document.getElementById('inputFoto').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (file.size > 2*1024*1024) {
                    alert('La imagen debe ser menor a 2MB');
                    this.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    fotoTemporal = e.target.result;
                    document.getElementById('previewFoto').src = fotoTemporal;
                };
                reader.readAsDataURL(file);
            }
        });
        
        function guardarFoto() {
            if (!fotoTemporal) {
                alert('Primero selecciona una foto');
                return;
            }
            document.getElementById('foto_base64').value = fotoTemporal;
            document.getElementById('formFoto').submit();
        }
        
        // Editar campos
        function editarCampo(campo, nombre) {
            const valor = prompt('Nuevo ' + nombre + ':');
            if (valor !== null && valor.trim() !== '') {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="campo" value="${campo}">
                    <input type="hidden" name="valor" value="${valor}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>