
<?php
//esta clase es para cerrar sesion
session_start();

// aqui se maneja el cierre de sesion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_destroy();
    header('Location: /ProyectoTimeNest/php/login.php');
    exit();
}


?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cerrar Sesión</title>
    <link href="/ProyectoTimeNest/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .logout-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            text-align: center;
            max-width: 400px;
        }
    </style>
</head>
<body>
    <div class="logout-card">
        <i class="fas fa-sign-out-alt fa-4x text-danger mb-3"></i>
        <h3>Cerrar Sesión?</h3>
        <p class="text-muted">Estás seguro de que deseas cerrar tu sesión?</p>
        
        <form method="POST">
            <button type="submit" class="btn btn-danger btn-lg w-100 mb-2">
                Sí
            </button>
        </form>
        
        <button onclick="history.back()" class="btn btn-secondary w-100">
            Cancelar
        </button>
    </div>
    
    <script src="/ProyectoTimeNest/fontawesome/js/all.min.js"></script>
</body>
</html>