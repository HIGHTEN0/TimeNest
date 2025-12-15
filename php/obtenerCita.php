<?php
//esta clase solo obtiene una cita esspecifica
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
    
    $cita_id = $_GET['id'];//obtener el id de la cita desde la url
    $profesional_id = $_SESSION['profile_id'];
    
    $stmt = $pdo->prepare("SELECT * FROM cita 
                          WHERE IDCita = ? AND IDProfesional = ?");
    $stmt->execute([$cita_id, $profesional_id]);
    $cita = $stmt->fetch(PDO::FETCH_ASSOC); //FETCH_ASSOC obtiene los datos en un array asociativo  
    //aqui se verifica si se encontro la cita
    if ($cita) {
        echo json_encode([
            'success' => true,
            'cita' => $cita
        ]);
    } else { //si no se encontro la cita muestra este mensaje
        echo json_encode([
            'success' => false,
            'message' => 'Cita no encontrada'
        ]);
    }
    
} catch (PDOException $e) { //este error es por si hay un problema con la base de datos
    echo json_encode([
        'success' => false,
        'message' => 'Error de base de datos: ' . $e->getMessage()
    ]);
}

?>