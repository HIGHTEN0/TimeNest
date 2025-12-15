<?php

//esta clase obtiene las citas de un profesional
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Profesional') {
    echo json_encode([ //json_encode convierte un array a formato json
        //un array es una estructura de datos que almacena varios valores
        'success' => false,
        'message' => 'No hay sesión activa'
    ]);
    exit();
}

$host = 'localhost';
$dbname = 'timenest';
$username = 'root';
$password = '';

try {//conexion a la base de datos
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $profesional_id = $_SESSION['profile_id'];
    
    // Citas en curso (Programado)
    $query_curso = "SELECT c.*, 
                    cp.NombreCliente, 
                    cp.ApellidoCliente, 
                    CONCAT(cp.NombreCliente, ' ', IFNULL(cp.ApellidoCliente, '')) as NombreCompleto,
                    con.NombreConsultorio
                    FROM cita c
                    INNER JOIN cliente_profesional cp ON c.IDCliente = cp.IDClienteProfesional
                    LEFT JOIN consultorio con ON c.IDConsultorio = con.IDConsultorio
                    WHERE c.IDProfesional = ? AND c.Estado = 'Programado'
                    ORDER BY c.DiaCita ASC, c.HoraInicial ASC";
    //cp es el alias de la tabla cliente_profesional 
    //c es el alias de la tabla cita
    //con es el alias de la tabla consultorio
    //CONCAT es una funcion de mysql que concatena dos o mas cadenas de texto
    //IFNULL es una funcion de mysql que devuelve el primer valor si no es null, sino devuelve el segundo valor
    $stmt_curso = $pdo->prepare($query_curso);
    $stmt_curso->execute([$profesional_id]);
    $citas_curso = $stmt_curso->fetchAll(PDO::FETCH_ASSOC);
    
    // Citas finalizadas (Hecho)
    $query_finalizadas = "SELECT c.*, 
                          cp.NombreCliente, 
                          cp.ApellidoCliente,
                          CONCAT(cp.NombreCliente, ' ', IFNULL(cp.ApellidoCliente, '')) as NombreCompleto,
                          con.NombreConsultorio
                          FROM cita c
                          INNER JOIN cliente_profesional cp ON c.IDCliente = cp.IDClienteProfesional
                          LEFT JOIN consultorio con ON c.IDConsultorio = con.IDConsultorio
                          WHERE c.IDProfesional = ? AND c.Estado = 'Hecho'
                          ORDER BY c.DiaCita DESC, c.HoraInicial DESC";
    //LEFT JOIN es una operacion de mysql que une dos tablas y devuelve todos los registros de la tabla izquierda y los registros coincidentes de la tabla derecha
    $stmt_finalizadas = $pdo->prepare($query_finalizadas);
    $stmt_finalizadas->execute([$profesional_id]);
    $citas_finalizadas = $stmt_finalizadas->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'citas_curso' => $citas_curso,
        'citas_finalizadas' => $citas_finalizadas
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error de base de datos: ' . $e->getMessage()
    ]);
}
?>