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
    
    $profesional_id = $_SESSION['profile_id'];
    
    // Obtener consultorios del profesional
    $stmt = $pdo->prepare("SELECT IDConsultorio, NombreConsultorio, Direccion 
                          FROM consultorio 
                          WHERE IDProfesional = ? 
                          ORDER BY NombreConsultorio ASC");
    $stmt->execute([$profesional_id]);
    $consultorios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'consultorios' => $consultorios
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error de base de datos: ' . $e->getMessage()
    ]);
}
?>