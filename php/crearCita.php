<?php
// en esta clase se crea una cita

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

try {//aqui se conecta a la base de datos
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    $profesional_id = $_SESSION['profile_id'];
    $fecha = $data['fecha'];
    $hora_inicio = $data['hora_inicio'];
    $hora_fin = $data['hora_fin'];
    $cliente_id = $data['cliente_id'];
    $consultorio_id = !empty($data['consultorio_id']) ? $data['consultorio_id'] : null;
    $ubicacion = !empty($data['ubicacion']) ? $data['ubicacion'] : null;
    
    // Validaciones básicas
    if (empty($fecha) || empty($hora_inicio) || empty($hora_fin) || empty($cliente_id)) {
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
    
    // primera validacion: Verificar que el cliente no tenga otra cita con el mismo profesional
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM cita 
                          WHERE IDProfesional = ? 
                          AND DiaCita = ? 
                          AND Estado = 'Programado'
                          AND (
                              (HoraInicial < ? AND HoraFinal > ?) OR
                              (HoraInicial < ? AND HoraFinal > ?) OR
                              (HoraInicial >= ? AND HoraFinal <= ?)
                          )");
    $stmt->execute([
        $profesional_id, 
        $fecha, 
        $hora_fin, $hora_inicio,
        $hora_fin, $hora_fin,
        $hora_inicio, $hora_fin
    ]);
    //aqui se verifica si ya existe una cita en ese horario
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
                      AND (
                          (HoraInicial <= ? AND HoraFinal > ?) OR
                          (HoraInicial < ? AND HoraFinal >= ?) OR
                          (HoraInicial >= ? AND HoraFinal <= ?)
                      )");
$stmt->execute([
    $cliente_id, 
    $fecha, 
    $hora_inicio, $hora_inicio,
    $hora_fin, $hora_fin,
    $hora_inicio, $hora_fin
]);

if ($stmt->fetchColumn() > 0) {//fetchColumn obtiene el valor de la primera columna de la primera fila del resultado
    echo json_encode([
        'success' => false,
        'message' => 'El cliente ya tiene una cita con otro profesional en ese horario'
    ]);
    exit();
}

    // Insertar la cita
    $stmt = $pdo->prepare("INSERT INTO cita 
                          (IDProfesional, IDCliente, IDConsultorio, DiaCita, HoraInicial, HoraFinal, Descripcion, Estado) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, 'Programado')");
    
    $result = $stmt->execute([
        $profesional_id,
        $cliente_id,
        $consultorio_id,
        $fecha,
        $hora_inicio,
        $hora_fin,
        $ubicacion
    ]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Cita creada exitosamente',
            'cita_id' => $pdo->lastInsertId()
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al crear la cita'
        ]);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error de base de datos: ' . $e->getMessage()
    ]);
}
?>