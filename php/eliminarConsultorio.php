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
    $profesional_id = $_SESSION['profile_id'];
    
    // Verificar que el consultorio pertenece al profesional
    $stmt = $pdo->prepare("DELETE FROM consultorio 
                          WHERE IDConsultorio = ? AND IDProfesional = ?");
    $result = $stmt->execute([$id, $profesional_id]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Consultorio eliminado']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error de base de datos']);
}
?>