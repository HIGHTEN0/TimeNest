
<?php
session_start();
header('Content-Type: application/json');

// Verificar si hay sesión activa y es profesional
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Profesional') {
    echo json_encode([
        'success' => false,
        'message' => 'No hay sesión activa o no tienes permisos'
    ]);
    exit();
}

// Conexión a la base de datos
$host = 'localhost';
$dbname = 'timenest';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Obtener datos JSON
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    $profesional_id = $_SESSION['profile_id'];
    $cliente_id = $data['id'];
    
    // Verificar si el cliente tiene citas pendientes
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM cita 
                          WHERE IDCliente = ? AND Estado = 'scheduled'");
    $stmt->execute([$cliente_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['total'] > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'No se puede eliminar el cliente porque tiene citas pendientes'
        ]);
        exit();
    }
    
    // Eliminar el cliente
    $stmt = $pdo->prepare("DELETE FROM cliente_profesional 
                          WHERE IDClienteProfesional = ? AND IDProfesional = ?");
    
    $result = $stmt->execute([$cliente_id, $profesional_id]);
    
    if ($result && $stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Cliente eliminado exitosamente'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al eliminar el cliente'
        ]);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error de base de datos: ' . $e->getMessage()
    ]);
}
?>