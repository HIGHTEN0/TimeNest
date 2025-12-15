<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Cliente') {
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
    
    $cliente_id = $_SESSION['profile_id'];
    
    // Obtener datos del cliente registrado
    $stmt = $pdo->prepare("SELECT NombreCliente, ApellidoCliente, NumTelefono FROM cliente WHERE IDCliente = ?");
    $stmt->execute([$cliente_id]);
    $cliente_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$cliente_data) {
        echo json_encode(['success' => false, 'message' => 'Cliente no encontrado']);
        exit();
    }
    
    $nombre = $cliente_data['NombreCliente'];
    $apellido = $cliente_data['ApellidoCliente'];
    $telefono = $cliente_data['NumTelefono'];
    
    // Buscar en cliente_profesional por nombre, apellido o teléfono
    // Obtener IDs de cliente_profesional que coincidan
    $query_ids = "SELECT IDClienteProfesional FROM cliente_profesional 
                  WHERE NombreCliente = ?";
    
    $params = [$nombre];
    
    if (!empty($telefono)) {
        $query_ids .= " OR TelefonoCliente = ?";
        $params[] = $telefono;
    }
    
    $stmt_ids = $pdo->prepare($query_ids);
    $stmt_ids->execute($params);
    $cliente_prof_ids = $stmt_ids->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($cliente_prof_ids)) {
        echo json_encode([
            'success' => true,
            'citas_proximas' => [],
            'citas_finalizadas' => []
        ]);
        exit();
    }
    
    // Crear placeholders para IN
    $placeholders = str_repeat('?,', count($cliente_prof_ids) - 1) . '?';
    
    // Obtener citas próximas (Programado)
    $query_proximas = "SELECT c.*, 
                       p.NombreProfesional,
                       p.ApellidoProfesional,
                       p.Profesion,
                       con.NombreConsultorio,
                       con.Direccion as UbicacionConsultorio
                       FROM cita c
                       INNER JOIN profesional p ON c.IDProfesional = p.IDProfesional
                       LEFT JOIN consultorio con ON c.IDConsultorio = con.IDConsultorio
                       WHERE c.IDCliente IN ($placeholders) AND c.Estado = 'Programado'
                       ORDER BY c.DiaCita ASC, c.HoraInicial ASC";
    
    $stmt_proximas = $pdo->prepare($query_proximas);
    $stmt_proximas->execute($cliente_prof_ids);
    $citas_proximas = $stmt_proximas->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener citas finalizadas (Hecho)
    $query_finalizadas = "SELECT c.*, 
                          p.NombreProfesional,
                          p.ApellidoProfesional,
                          p.Profesion,
                          con.NombreConsultorio,
                          con.Direccion as UbicacionConsultorio
                          FROM cita c
                          INNER JOIN profesional p ON c.IDProfesional = p.IDProfesional
                          LEFT JOIN consultorio con ON c.IDConsultorio = con.IDConsultorio
                          WHERE c.IDCliente IN ($placeholders) AND c.Estado = 'Hecho'
                          ORDER BY c.DiaCita DESC, c.HoraInicial DESC";
    
    $stmt_finalizadas = $pdo->prepare($query_finalizadas);
    $stmt_finalizadas->execute($cliente_prof_ids);
    $citas_finalizadas = $stmt_finalizadas->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'citas_proximas' => $citas_proximas,
        'citas_finalizadas' => $citas_finalizadas
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error de base de datos: ' . $e->getMessage()
    ]);
}
?>