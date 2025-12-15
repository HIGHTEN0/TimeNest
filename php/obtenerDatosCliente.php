<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Cliente') {
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
    
    $cliente_id = $_SESSION['profile_id'];
    
    $stmt = $pdo->prepare("SELECT * FROM cliente WHERE IDCliente = ?");
    $stmt->execute([$cliente_id]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($cliente) {
        echo json_encode([
            'success' => true,
            'id' => $cliente['IDCliente'],
            'nombre' => $cliente['NombreCliente'],
            'apellido' => $cliente['ApellidoCliente'],
            'telefono' => $cliente['NumTelefono'],
            'foto_perfil' => $cliente['FotoPerfil'] ?? null
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Cliente no encontrado'
        ]);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error de base de datos: ' . $e->getMessage()
    ]);
}
?>