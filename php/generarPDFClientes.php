<?php
//esta clase es para generar el pdf de los clientes
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Profesional') {
    die('No hay sesión activa');
}

define('FPDF_FONTPATH', __DIR__ . '/../fpdf186/font/');
require('../fpdf186/fpdf.php');

class PDF extends FPDF
{
    function Cell($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = false, $link = '')
    {
        parent::Cell($w, $h, iconv('UTF-8', 'windows-1252', $txt), $border, $ln, $align, $fill, $link);
    }

    function MultiCell($w, $h, $txt, $border = 0, $align = 'J', $fill = false)
    {
        parent::MultiCell($w, $h, iconv('UTF-8', 'windows-1252', $txt), $border, $align, $fill);
    }
}

$host = 'localhost';
$dbname = 'timenest';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $profesional_id = $_SESSION['profile_id'];

    // Obtener datos del profesional
    $stmt = $pdo->prepare("SELECT * FROM profesional WHERE IDProfesional = ?");
    $stmt->execute([$profesional_id]);
    $profesional = $stmt->fetch(PDO::FETCH_ASSOC);

    // Procesar filtros
    $filtro = $_GET['filtro'];
    $where_clause = "WHERE IDProfesional = ?";
    $params = [$profesional_id];
    $filtro_texto = "";

    if ($filtro === 'dia') {
        $fecha = $_GET['fecha'];
        $where_clause .= " AND DATE(Creado_en) = ?";
        $params[] = $fecha;
        $filtro_texto = "Día: " . date('d/m/Y', strtotime($fecha));
    } elseif ($filtro === 'ultimos7') {
        $where_clause .= " AND Creado_en >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        $filtro_texto = "Últimos 7 días";
    } elseif ($filtro === 'mes') {
        $mes = $_GET['mes'];
        $anio = $_GET['anio'];
        $where_clause .= " AND MONTH(Creado_en) = ? AND YEAR(Creado_en) = ?";
        $params[] = $mes;
        $params[] = $anio;
        $meses = [
            '',
            'Enero',
            'Febrero',
            'Marzo',
            'Abril',
            'Mayo',
            'Junio',
            'Julio',
            'Agosto',
            'Septiembre',
            'Octubre',
            'Noviembre',
            'Diciembre'
        ];
        $filtro_texto = "Mes: " . $meses[intval($mes)] . " " . $anio;
    } elseif ($filtro === 'genero') {
        $genero = $_GET['genero'];
        $where_clause .= " AND GeneroCliente = ?";
        $params[] = $genero;
        $filtro_texto = "Género: " . $genero;
    } elseif ($filtro === 'edad') {
        $edad = $_GET['edad'];
        $where_clause .= " AND EdadCliente = ?";
        $params[] = $edad;
        $filtro_texto = "Edad: " . $edad . " años";
    }

    // Obtener clientes
    $query = "SELECT * FROM cliente_profesional $where_clause ORDER BY Creado_en DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calcular indicadores
    $total_clientes = count($clientes);
    $edades = array_filter(array_column($clientes, 'EdadCliente'));
    $edad_promedio = !empty($edades) ? round(array_sum($edades) / count($edades), 1) : 0;

    $masculino = count(array_filter($clientes, fn($c) => $c['GeneroCliente'] === 'Masculino'));
    $femenino = count(array_filter($clientes, fn($c) => $c['GeneroCliente'] === 'Femenino'));
    $porc_m = $total_clientes > 0 ? round(($masculino / $total_clientes) * 100, 1) : 0;
    $porc_f = $total_clientes > 0 ? round(($femenino / $total_clientes) * 100, 1) : 0;

    // Rango de fechas
    if (!empty($clientes)) {
        $fechas = array_map(fn($c) => $c['Creado_en'], $clientes);
        $fecha_min = min($fechas);
        $fecha_max = max($fechas);
    } else {
        $fecha_min = $fecha_max = date('Y-m-d');
    }

    // Crear PDF
    $pdf = new PDF();
    $pdf->AddPage();//addpage agrega una nueva pagina

    // Encabezado
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'INFORME: LISTADO DE CLIENTES DADOS DE ALTA', 0, 1, 'C');
    $pdf->Ln(5);

    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 6, $profesional['NombreConsultorio'] ?: 'Consultorio', 0, 1, 'C');
    $pdf->Cell(0, 6, 'Fecha de generación: ' . date('Y-m-d'), 0, 1, 'C');
    $pdf->Cell(0, 6, 'Responsable: ' . $profesional['NombreProfesional'] . ' ' . $profesional['ApellidoProfesional'], 0, 1, 'C');
    $pdf->Cell(0, 6, 'Filtro aplicado: ' . $filtro_texto, 0, 1, 'C');
    $pdf->Ln(5);

    $pdf->Cell(0, 0, '', 'T', 1);
    $pdf->Ln(5);

    // Resumen ejecutivo
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 6, 'RESUMEN EJECUTIVO: INDICADORES CLAVE', 0, 1);
    $pdf->Ln(2);

    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(5, 6, '-', 0, 0);
    $pdf->Cell(0, 6, "  Total de clientes: $total_clientes", 0, 1);

    $pdf->Cell(5, 6, '-', 0, 0);
    $pdf->Cell(0, 6, "  Edad promedio: $edad_promedio años", 0, 1);

    $pdf->Cell(5, 6, '-', 0, 0);
    $pdf->Cell(0, 6, "  Distribución por género: F: $femenino ($porc_f%), M: $masculino ($porc_m%)", 0, 1);

    $pdf->Cell(5, 6, '-', 0, 0);
    $pdf->Cell(0, 6, "  Rango de fechas: " . date('Y-m-d', strtotime($fecha_min)) . " a " . date('Y-m-d', strtotime($fecha_max)), 0, 1);
    $pdf->Ln(5);

    // Listado
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 6, 'LISTADO:', 0, 1);
    $pdf->Ln(2);

    // Tabla
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(10, 7, 'ID', 1, 0, 'C');
    $pdf->Cell(35, 7, 'Nombre', 1, 0, 'C');
    $pdf->Cell(35, 7, 'Apellido', 1, 0, 'C');
    $pdf->Cell(15, 7, 'Edad', 1, 0, 'C');
    $pdf->Cell(20, 7, 'Género', 1, 0, 'C');
    $pdf->Cell(30, 7, 'Teléfono', 1, 0, 'C');
    $pdf->Cell(45, 7, 'Correo', 1, 1, 'C');

    $pdf->SetFont('Arial', '', 8);
    foreach ($clientes as $cliente) {
        $pdf->Cell(10, 6, $cliente['IDClienteProfesional'], 1, 0, 'C');
        $pdf->Cell(35, 6, substr($cliente['NombreCliente'], 0, 18), 1, 0);
        $pdf->Cell(35, 6, substr($cliente['ApellidoCliente'] ?: 'N/A', 0, 18), 1, 0);
        $pdf->Cell(15, 6, $cliente['EdadCliente'] ?: 'N/A', 1, 0, 'C');
        $pdf->Cell(20, 6, substr($cliente['GeneroCliente'], 0, 1), 1, 0, 'C');
        $pdf->Cell(30, 6, $cliente['TelefonoCliente'] ?: 'N/A', 1, 0);
        $pdf->Cell(45, 6, substr($cliente['CorreoCliente'] ?: 'N/A', 0, 25), 1, 1);
    }

    $pdf->Output('I', 'Listado_Clientes.pdf');
} catch (PDOException $e) {
    die('Error: ' . $e->getMessage());
}
