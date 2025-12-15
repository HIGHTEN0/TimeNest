<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Profesional') {
    echo json_encode([
        'success' => false,
        'message' => 'No hay sesión activa'
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
    
    $profesional_id = $_SESSION['profile_id'];
    
    $stmt = $pdo->prepare("SELECT * FROM profesional WHERE IDProfesional = ?");
    $stmt->execute([$profesional_id]);
    $profesional = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($profesional) {
        echo json_encode([
            'success' => true,
            'id' => $profesional['IDProfesional'],
            'nombre' => $profesional['NombreProfesional'],
            'apellido' => $profesional['ApellidoProfesional'],
            'profesion' => $profesional['Profesion'],
            'telefono' => $profesional['NumTelefono'],
            'nombre_consultorio' => $profesional['NombreConsultorio'],
            'foto_perfil' => $profesional['FotoPerfil'] ?? null,
            'logo' => $profesional['Logo'] ?? null
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Profesional no encontrado'
        ]);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error de base de datos: ' . $e->getMessage()
    ]);
}
?>