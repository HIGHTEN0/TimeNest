<?php
//esta clase es para cancelar citas
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Profesional') {
    echo json_encode([
        'success' => false,
        'message' => 'No hay sesión activa o no tienes permisos'
    ]);
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
    
    $cita_id = $data['id'];
    $profesional_id = $_SESSION['profile_id'];
    
    $stmt = $pdo->prepare("UPDATE cita 
                          SET Estado = 'Cancelado' 
                          WHERE IDCita = ? AND IDProfesional = ?");
    
    $result = $stmt->execute([$cita_id, $profesional_id]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Cita cancelada exitosamente'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al cancelar la cita'
        ]);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error de base de datos: ' . $e->getMessage()
    ]);
}
?>