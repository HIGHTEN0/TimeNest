<?php
//esta clase es para editar citas
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
    
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    $cita_id = $data['id'];
    $fecha = $data['fecha'];
    $hora_inicio = $data['hora_inicio'];
    $hora_fin = $data['hora_fin'];
    $consultorio_id = !empty($data['consultorio_id']) ? $data['consultorio_id'] : null;
    $profesional_id = $_SESSION['profile_id'];
    
    // Validaciones básicas
    if (empty($fecha) || empty($hora_inicio) || empty($hora_fin)) {
        echo json_encode([
            'success' => false,
            'message' => 'Todos los campos son obligatorios'
        ]);
        exit();
    }
    
    // Verificar que la hora de fin sea posterior a la hora de inicio
    if ($hora_inicio >= $hora_fin) {
        echo json_encode([
            'success' => false,
            'message' => 'La hora de fin debe ser posterior a la hora de inicio'
        ]);
        exit();
    }
    
    // Obtener el IDCliente de la cita que se está editando
    $stmt = $pdo->prepare("SELECT IDCliente FROM cita WHERE IDCita = ?");
    $stmt->execute([$cita_id]);
    $cliente_id = $stmt->fetchColumn();
    
       // primera validacion: Verificar que el cliente no tenga otra cita con el mismo profesional
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM cita 
                          WHERE IDProfesional = ? 
                          AND DiaCita = ? 
                          AND Estado = 'Programado'
                          AND IDCita != ?
                          AND (
                              (HoraInicial < ? AND HoraFinal > ?) OR
                              (HoraInicial < ? AND HoraFinal > ?) OR
                              (HoraInicial >= ? AND HoraFinal <= ?)
                          )");
    $stmt->execute([
        $profesional_id, 
        $fecha, 
        $cita_id,
        $hora_fin, $hora_inicio,
        $hora_fin, $hora_fin,
        $hora_inicio, $hora_fin
    ]);
    
    if ($stmt->fetchColumn() > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Ya tienes una cita programada en ese horario'
        ]);
        exit();
    }
    
        // segunda validacion: Verificar que el CLIENTE no tenga otra cita con otro profesional
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM cita 
                          WHERE IDCliente = ? 
                          AND DiaCita = ? 
                          AND Estado = 'Programado'
                          AND IDCita != ?
                          AND (
                              (HoraInicial < ? AND HoraFinal > ?) OR
                              (HoraInicial < ? AND HoraFinal > ?) OR
                              (HoraInicial >= ? AND HoraFinal <= ?)
                          )");
    $stmt->execute([
        $cliente_id, 
        $fecha, 
        $cita_id,
        $hora_fin, $hora_inicio,
        $hora_fin, $hora_fin,
        $hora_inicio, $hora_fin
    ]);
    
    if ($stmt->fetchColumn() > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'El cliente ya tiene una cita con otro profesional en ese horarario'
        ]);
        exit();
    }
    
    // Actualizar la cita
    $stmt = $pdo->prepare("UPDATE cita 
                          SET DiaCita = ?, HoraInicial = ?, HoraFinal = ?, IDConsultorio = ?
                          WHERE IDCita = ? AND IDProfesional = ?");
    
    $result = $stmt->execute([$fecha, $hora_inicio, $hora_fin, $consultorio_id, $cita_id, $profesional_id]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Cita actualizada exitosamente'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al actualizar la cita'
        ]);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error de base de datos: ' . $e->getMessage()
    ]);
}
?>