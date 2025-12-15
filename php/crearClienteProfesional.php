<?php
//en esta clase se da de alta a un cliente

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Profesional') {
    echo json_encode([
        'success' => false,
        'message' => 'No hay sesión activa'
    ]);
    exit();
}

// Función para validar correo
function validarCorreo($email) {
    $dominios_permitidos = [
        'gmail.com', 
        'hotmail.com', 
        'outlook.com', 
        'yahoo.com'
    ];
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    
    $partes = explode('@', $email);
    if (count($partes) != 2) {
        return false;
    }
    
    $dominio = strtolower($partes[1]);
    return in_array($dominio, $dominios_permitidos);
}

$host = 'localhost';
$dbname = 'timenest';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    $profesional_id = $_SESSION['profile_id'];
    $nombre = trim($data['nombre']);
    $apellido = !empty($data['apellido']) ? trim($data['apellido']) : null;
    $genero = $data['genero'];
    $edad = !empty($data['edad']) ? intval($data['edad']) : null;
    $telefono = !empty($data['telefono']) ? trim($data['telefono']) : null;
    $correo = !empty($data['correo']) ? trim($data['correo']) : null;
    $domicilio = !empty($data['domicilio']) ? trim($data['domicilio']) : null;
    $metodoContacto = $data['metodoContacto'];
    
    // Validación: nombre y género obligatorios
    if (empty($nombre) || empty($genero)) {
        echo json_encode([
            'success' => false,
            'message' => 'Nombre y género son obligatorios'
        ]);
        exit();
    }
    
    // Validación según método de contacto
    if ($metodoContacto === 'telefono' && empty($telefono)) {
        echo json_encode([
            'success' => false,
            'message' => 'Debes proporcionar un teléfono'
        ]);
        exit();
    }
    
    if ($metodoContacto === 'correo' && empty($correo)) {
        echo json_encode([
            'success' => false,
            'message' => 'Debes proporcionar un correo'
        ]);
        exit();
    }
    
    if ($metodoContacto === 'ambos' && (empty($telefono) || empty($correo))) {
        echo json_encode([
            'success' => false,
            'message' => 'Debes proporcionar teléfono y correo'
        ]);
        exit();
    }
    
    // Validar formato de teléfono si se proporciona
    if (!empty($telefono) && !preg_match('/^[0-9]{10}$/', $telefono)) {
        echo json_encode([
            'success' => false,
            'message' => 'El teléfono debe tener exactamente 10 dígitos'
        ]);
        exit();
    }
    
    // Validar formato de correo si se proporciona
    if (!empty($correo) && !validarCorreo($correo)) {
        echo json_encode([
            'success' => false,
            'message' => 'Por favor usa un correo válido'
        ]);
        exit();
    }
    
    // Insertar el cliente
    $stmt = $pdo->prepare("INSERT INTO cliente_profesional 
                          (IDProfesional, NombreCliente, ApellidoCliente, EdadCliente, GeneroCliente, 
                           TelefonoCliente, CorreoCliente, DomicilioCliente) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    $result = $stmt->execute([
        $profesional_id,
        $nombre,
        $apellido,
        $edad,
        $genero,
        $telefono,
        $correo,
        $domicilio
    ]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Cliente dado de alta exitosamente',
            'cliente_id' => $pdo->lastInsertId()
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al dar de alta al cliente'
        ]);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error de base de datos: ' . $e->getMessage()
    ]);
}
?>