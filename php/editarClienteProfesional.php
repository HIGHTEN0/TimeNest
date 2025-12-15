<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Profesional') {
    echo json_encode(['success' => false, 'message' => 'No hay sesión activa']);
    exit();
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
    
    $id = $data['id'];
    $nombre = trim($data['nombre']);
    $apellido = !empty($data['apellido']) ? trim($data['apellido']) : null;
    $genero = $data['genero'];
    $edad = !empty($data['edad']) ? intval($data['edad']) : null;
    $telefono = !empty($data['telefono']) ? trim($data['telefono']) : null;
    $correo = !empty($data['correo']) ? trim($data['correo']) : null;
    $domicilio = !empty($data['domicilio']) ? trim($data['domicilio']) : null;
    
    // Validar que tenga al menos teléfono o correo
    if (empty($telefono) && empty($correo)) {
        echo json_encode(['success' => false, 'message' => 'Debe tener al menos teléfono o correo']);
        exit();
    }
    
    $stmt = $pdo->prepare("UPDATE cliente_profesional 
                          SET NombreCliente = ?, 
                              ApellidoCliente = ?, 
                              GeneroCliente = ?,
                              EdadCliente = ?,
                              TelefonoCliente = ?, 
                              CorreoCliente = ?,
                              DomicilioCliente = ?
                          WHERE IDClienteProfesional = ?");
    
    $result = $stmt->execute([$nombre, $apellido, $genero, $edad, $telefono, $correo, $domicilio, $id]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Cliente actualizado']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error de base de datos']);
}
?>