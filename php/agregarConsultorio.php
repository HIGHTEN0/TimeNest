<?php
//esta clase es para agregar consultorios
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
    
    $profesional_id = $_SESSION['profile_id'];
    $nombre = trim($data['nombre']);
    $direccion = trim($data['direccion']);
    
    if (empty($nombre) || empty($direccion)) {
        echo json_encode(['success' => false, 'message' => 'Nombre y dirección son obligatorios']);
        exit();
    }
    
    $stmt = $pdo->prepare("INSERT INTO consultorio (IDProfesional, NombreConsultorio, Direccion) VALUES (?, ?, ?)");
    $result = $stmt->execute([$profesional_id, $nombre, $direccion]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Consultorio agregado exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al agregar consultorio']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error de base de datos']);
}
?>