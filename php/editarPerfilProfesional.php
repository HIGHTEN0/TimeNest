<?php
session_start();

// Verificar si hay sesión activa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Profesional') {
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
  
        
        // Procesar actualización
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Actualizar foto de perfil
    if (isset($_POST['actualizar_foto']) && $_POST['actualizar_foto'] == '1') {
        $foto = $_POST['foto_base64'];
        $stmt = $pdo->prepare("UPDATE profesional SET FotoPerfil = ? WHERE IDProfesional = ?");
        $stmt->execute([$foto, $profile_id]);
        
        $mensaje = "Foto de perfil actualizada";
    }
    // Actualizar logo
    elseif (isset($_POST['actualizar_logo']) && $_POST['actualizar_logo'] == '1') {
        $logo = $_POST['logo_base64'];
        $stmt = $pdo->prepare("UPDATE profesional SET Logo = ? WHERE IDProfesional = ?");
        $stmt->execute([$logo, $profile_id]);
        
        $mensaje = "Logo actualizado";
    }
    // Actualizar campos de texto
    elseif (isset($_POST['campo']) && isset($_POST['valor'])) {
        $campo = $_POST['campo'];
        $valor = $_POST['valor'];
        
        // Campos permitidos para actualizar en tabla profesional
        $campos_permitidos = [
            'NombreProfesional', 'ApellidoProfesional', 'Profesion', 'NumTelefono'
        ];
        
        if (in_array($campo, $campos_permitidos)) {
            $stmt = $pdo->prepare("UPDATE profesional SET $campo = ? WHERE IDProfesional = ?");
            $stmt->execute([$valor, $profile_id]);
            
            $mensaje = "Campo actualizado correctamente";
        }
    }
}
    // Obtener datos del profesional
    $stmt = $pdo->prepare("SELECT * FROM profesional WHERE IDProfesional = ?");
    $stmt->execute([$profile_id]);
    $profesional = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Obtener email del usuario
    $stmt = $pdo->prepare("SELECT Email FROM usuario WHERE IdPerfil = ? AND Rol = 'Profesional'");
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
    <title>Editar Perfil - Profesional</title>
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
        .consultorio-item {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .consultorio-info {
            flex: 1;
        }
        .consultorio-info h6 {
            margin: 0;
            color: #1976d2;
        }
        .consultorio-info small {
            color: #666;
        }
        .consultorio-actions {
            display: flex;
            gap: 10px;
        }
        .btn-sm {
            padding: 5px 10px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center mb-4">Editar Perfil Profesional</h2>
        
        <?php if (isset($mensaje)): ?>
            <div class="alert alert-success"><?php echo $mensaje; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <!-- Fotos -->
        <div class="row">
            <div class="col-md-6">
                <div class="foto-container">
                    <h5>Foto de Perfil</h5>
                    <img id="previewFoto" class="foto-preview" 
                         src="<?php echo $profesional['FotoPerfil'] ?: '/imagenes/usuario.png'; ?>">
                    <input type="file" id="inputFoto" accept="image/*" class="form-control mt-2">
                    <button class="btn btn-primary mt-2" onclick="guardarFoto()">Guardar Foto</button>
                </div>
            </div>
            <div class="col-md-6">
                <div class="foto-container">
                    <h5>Logo Consultorio</h5>
                    <img id="previewLogo" class="foto-preview" 
                         src="<?php echo $profesional['Logo'] ?: '/imagenes/usuario.png'; ?>">
                    <input type="file" id="inputLogo" accept="image/*" class="form-control mt-2">
                    <button class="btn btn-primary mt-2" onclick="guardarLogo()">Guardar Logo</button>
                </div>
            </div>
        </div>
        
        <!-- Datos -->
        <table class="table table-hover mt-4">
            <tr>
                <td><strong>Nombre:</strong></td>
                <td id="valor-NombreProfesional"><?php echo $profesional['NombreProfesional']; ?></td>
                <td><button class="btn-editar" onclick="editarCampo('NombreProfesional', 'Nombre')">
                    <i class="fas fa-pencil-alt"></i></button></td>
            </tr>
            <tr>
                <td><strong>Apellido:</strong></td>
                <td id="valor-ApellidoProfesional"><?php echo $profesional['ApellidoProfesional']; ?></td>
                <td><button class="btn-editar" onclick="editarCampo('ApellidoProfesional', 'Apellido')">
                    <i class="fas fa-pencil-alt"></i></button></td>
            </tr>
            <tr>
                <td><strong>Profesión:</strong></td>
                <td id="valor-Profesion"><?php echo $profesional['Profesion']; ?></td>
                <td><button class="btn-editar" onclick="editarCampo('Profesion', 'Profesión')">
                    <i class="fas fa-pencil-alt"></i></button></td>
            </tr>
            <tr>
                <td><strong>Teléfono:</strong></td>
                <td id="valor-NumTelefono"><?php echo $profesional['NumTelefono']; ?></td>
                <td><button class="btn-editar" onclick="editarCampo('NumTelefono', 'Teléfono')">
                    <i class="fas fa-pencil-alt"></i></button></td>
            </tr>
            <tr>
                <td><strong>Correo Electrónico:</strong></td>
                <td><?php echo $usuario['Email']; ?></td>
                <td><i class="fas fa-lock text-muted"></i></td>
            </tr>
        </table>
        
        <!-- Gestión de Consultorios -->
        <div class="mt-5">
            <h4><i class="fas fa-building"></i> Mis Consultorios</h4>
            <div id="listaConsultorios"></div>
            <button class="btn btn-success mt-3" onclick="mostrarFormularioConsultorio()">
                <i class="fas fa-plus"></i> Agregar Consultorio
            </button>
        </div>

        <!-- Formulario agregar consultorio -->
        <div id="formNuevoConsultorio" style="display:none; margin-top: 20px;">
            <div class="card">
                <div class="card-body">
                    <h5>Nuevo Consultorio</h5>
                    <div class="mb-3">
                        <label class="form-label">Nombre del Consultorio:</label>
                        <input type="text" class="form-control" id="nuevoNombreConsultorio">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Dirección:</label>
                        <textarea class="form-control" id="nuevaDireccionConsultorio" rows="2"></textarea>
                    </div>
                    <button class="btn btn-primary" onclick="guardarNuevoConsultorio()">Guardar</button>
                    <button class="btn btn-secondary" onclick="cancelarNuevoConsultorio()">Cancelar</button>
                </div>
            </div>
        </div>

        <!-- Modal Editar Consultorio -->
        <div class="modal fade" id="modalEditarConsultorio" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Editar Consultorio</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="editConsultorioId">
                        <div class="mb-3">
                            <label class="form-label">Nombre:</label>
                            <input type="text" class="form-control" id="editConsultorioNombre">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Dirección:</label>
                            <textarea class="form-control" id="editConsultorioDireccion" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" onclick="guardarEdicionConsultorio()">Guardar</button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <a href="/ProyectoTimeNest/html/profesionalClientes.html" class="btn btn-secondary">Volver</a>
        </div>
    </div>
    
    <!-- Forms ocultos -->
    <form id="formFoto" method="POST" style="display:none;">
        <input type="hidden" name="actualizar_foto" value="1">
        <input type="hidden" id="foto_base64" name="foto_base64">
    </form>
    
    <form id="formLogo" method="POST" style="display:none;">
        <input type="hidden" name="actualizar_logo" value="1">
        <input type="hidden" id="logo_base64" name="logo_base64">
    </form>
    
    <script src="/ProyectoTimeNest/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        // Preview y guardar foto
        let fotoTemporal = null;
        document.getElementById('inputFoto').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file && file.size <= 2*1024*1024) {
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
        
        // Preview y guardar logo
        let logoTemporal = null;
        document.getElementById('inputLogo').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file && file.size <= 2*1024*1024) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    logoTemporal = e.target.result;
                    document.getElementById('previewLogo').src = logoTemporal;
                };
                reader.readAsDataURL(file);
            }
        });
        
        function guardarLogo() {
            if (!logoTemporal) {
                alert('Primero selecciona un logo');
                return;
            }
            document.getElementById('logo_base64').value = logoTemporal;
            document.getElementById('formLogo').submit();
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

        // CONSULTORIOS
        cargarConsultorios();

        function cargarConsultorios() {
            fetch('/ProyectoTimeNest/php/obtenerConsultorios.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let html = '';
                        if (data.consultorios && data.consultorios.length > 0) {
                            data.consultorios.forEach(c => {
                                html += `
                                    <div class="consultorio-item">
                                        <div class="consultorio-info">
                                            <h6>${c.NombreConsultorio}</h6>
                                            <small>${c.Direccion || 'Sin dirección'}</small>
                                        </div>
                                        <div class="consultorio-actions">
                                            <button class="btn btn-warning btn-sm" onclick="editarConsultorio(${c.IDConsultorio}, '${c.NombreConsultorio}', '${c.Direccion || ''}')">
                                                <i class="fas fa-edit"></i> Editar
                                            </button>
                                            <button class="btn btn-danger btn-sm" onclick="eliminarConsultorio(${c.IDConsultorio})">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </button>
                                        </div>
                                    </div>
                                `;
                            });
                        } else {
                            html = '<p class="text-muted">No hay consultorios registrados</p>';
                        }
                        document.getElementById('listaConsultorios').innerHTML = html;
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        function mostrarFormularioConsultorio() {
            document.getElementById('formNuevoConsultorio').style.display = 'block';
        }

        function cancelarNuevoConsultorio() {
            document.getElementById('formNuevoConsultorio').style.display = 'none';
            document.getElementById('nuevoNombreConsultorio').value = '';
            document.getElementById('nuevaDireccionConsultorio').value = '';
        }

        function guardarNuevoConsultorio() {
            const nombre = document.getElementById('nuevoNombreConsultorio').value.trim();
            const direccion = document.getElementById('nuevaDireccionConsultorio').value.trim();
            
            if (!nombre || !direccion) {
                alert('Por favor completa nombre y dirección');
                return;
            }
            
            fetch('/ProyectoTimeNest/php/agregarConsultorio.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({nombre: nombre, direccion: direccion})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Consultorio agregado exitosamente');
                    cancelarNuevoConsultorio();
                    cargarConsultorios();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al agregar consultorio');
            });
        }

        function editarConsultorio(id, nombre, direccion) {
            document.getElementById('editConsultorioId').value = id;
            document.getElementById('editConsultorioNombre').value = nombre;
            document.getElementById('editConsultorioDireccion').value = direccion;
            
            const modal = new bootstrap.Modal(document.getElementById('modalEditarConsultorio'));
            modal.show();
        }

        function guardarEdicionConsultorio() {
            const id = document.getElementById('editConsultorioId').value;
            const nombre = document.getElementById('editConsultorioNombre').value.trim();
            const direccion = document.getElementById('editConsultorioDireccion').value.trim();
            
            if (!nombre || !direccion) {
                alert('Por favor completa todos los campos');
                return;
            }
            
            fetch('/ProyectoTimeNest/php/editarConsultorio.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({id: id, nombre: nombre, direccion: direccion})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Consultorio actualizado exitosamente');
                    bootstrap.Modal.getInstance(document.getElementById('modalEditarConsultorio')).hide();
                    cargarConsultorios();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al actualizar consultorio');
            });
        }

        function eliminarConsultorio(id) {
            if (!confirm('¿Estás seguro de eliminar este consultorio?')) {
                return;
            }
            
            fetch('/ProyectoTimeNest/php/eliminarConsultorio.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({id: id})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Consultorio eliminado exitosamente');
                    cargarConsultorios();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al eliminar consultorio');
            });
        }
    </script>
</body>
</html>