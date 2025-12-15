<?php
//en esta clase se verifica si el usuario tiene una sesion activa

session_start();
header('Content-Type: application/json'); //content-type json para que el front entienda la respuesta
//se usa este header porque la respuesta sera en formato json porque es mas facil de manejar en el front

// Verificar si hay sesión activa
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    echo json_encode([
        'conectado' => false,
        'message' => 'No hay sesión activa'
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
    
    // Verificar que el usuario todavía existe y está activo
    $stmt = $pdo->prepare("SELECT IDUsuario, Rol, Activo FROM usuario WHERE IDUsuario = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario || $usuario['Activo'] != 1) {
        // Usuario no existe, cerrar sesión
        session_destroy();
        echo json_encode([
            'conectado' => false, //conectado es false porque la sesion no es valida
            'message' => 'Sesión inválida'
        ]);
        exit();
    }
    
    // Sesión válida
    echo json_encode([
        'conectado' => true,
        'role' => $_SESSION['role'],
        'user_id' => $_SESSION['user_id'],
        'profile_id' => $_SESSION['profile_id']
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'conectado' => false,
        'message' => 'Error de base de datos'
    ]);
}
?>