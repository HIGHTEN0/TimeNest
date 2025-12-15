<?php
//esta clase es para generar el pdf de las citas
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Profesional') {
    die('No hay sesión activa');
}

define('FPDF_FONTPATH', __DIR__ . '/../fpdf186/font/');//ruta de las fuentes
require('../fpdf186/fpdf.php');

//aqui se crea la clase PDF para generar el pdf con tildes
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
    $where_clause = "WHERE c.IDProfesional = ?";
    $params = [$profesional_id];
    $filtro_texto = "";
    $tipo_citas = "";

    if ($filtro === 'dia-finalizadas') {
        $fecha = $_GET['fecha'];
        $where_clause .= " AND c.DiaCita = ? AND c.Estado = 'Finalizado'";
        $params[] = $fecha;
        $filtro_texto = "Finalizadas - Día: " . date('d/m/Y', strtotime($fecha));
        $tipo_citas = "Finalizadas";
    } elseif ($filtro === 'dia-realizar') {
        $fecha = $_GET['fecha'];
        $where_clause .= " AND c.DiaCita = ? AND c.Estado = 'Programado'";
        $params[] = $fecha;
        $filtro_texto = "A realizar - Día: " . date('d/m/Y', strtotime($fecha));
        $tipo_citas = "A realizar";
    } elseif ($filtro === 'ultimos7-finalizadas') {
        $where_clause .= " AND c.DiaCita >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND c.Estado = 'Finalizado'";
        $filtro_texto = "Finalizadas en los últimos 7 días";
        $tipo_citas = "Finalizadas";
    } elseif ($filtro === 'proximos7-realizar') {
        $where_clause .= " AND c.DiaCita BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY) AND c.Estado = 'Programado'";
        $filtro_texto = "A realizar en los próximos 7 días";
        $tipo_citas = "A realizar";
    } elseif ($filtro === 'mes-finalizadas') {
        $mes = $_GET['mes'];
        $anio = $_GET['anio'];
        $where_clause .= " AND MONTH(c.DiaCita) = ? AND YEAR(c.DiaCita) = ? AND c.Estado = 'Finalizado'";
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
        $filtro_texto = "Finalizadas - Mes: " . $meses[intval($mes)] . " " . $anio;
        $tipo_citas = "Finalizadas";
    } elseif ($filtro === 'mes-realizar') {
        $mes = $_GET['mes'];
        $anio = $_GET['anio'];
        $where_clause .= " AND MONTH(c.DiaCita) = ? AND YEAR(c.DiaCita) = ? AND c.Estado = 'Programado'";
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
        $filtro_texto = "A realizar - Mes: " . $meses[intval($mes)] . " " . $anio;
        $tipo_citas = "A realizar";
    } elseif ($filtro === 'cliente-finalizadas') {
        $cliente_id = $_GET['cliente'];
        $where_clause .= " AND c.IDCliente = ? AND c.Estado = 'Finalizado'";
        $params[] = $cliente_id;

        $stmt = $pdo->prepare("SELECT NombreCliente, ApellidoCliente FROM cliente_profesional WHERE IDClienteProfesional = ?");
        $stmt->execute([$cliente_id]);
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
        $filtro_texto = "Finalizadas del Cliente: " . $cliente['NombreCliente'] . " " . ($cliente['ApellidoCliente'] ?: '');
        $tipo_citas = "Finalizadas";
    } elseif ($filtro === 'cliente-realizar') {
        $cliente_id = $_GET['cliente'];
        $where_clause .= " AND c.IDCliente = ? AND c.Estado = 'Programado'";
        $params[] = $cliente_id;

        $stmt = $pdo->prepare("SELECT NombreCliente, ApellidoCliente FROM cliente_profesional WHERE IDClienteProfesional = ?");
        $stmt->execute([$cliente_id]);
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
        $filtro_texto = "A realizar para el Cliente: " . $cliente['NombreCliente'] . " " . ($cliente['ApellidoCliente'] ?: '');
        $tipo_citas = "A realizar";
    } elseif ($filtro === 'consultorio-finalizadas') {
        $consultorio_id = $_GET['consultorio'];
        $where_clause .= " AND c.IDConsultorio = ? AND c.Estado = 'Finalizado'";
        $params[] = $consultorio_id;

        $stmt = $pdo->prepare("SELECT NombreConsultorio FROM consultorio WHERE IDConsultorio = ?");
        $stmt->execute([$consultorio_id]);
        $consultorio = $stmt->fetch(PDO::FETCH_ASSOC);
        $filtro_texto = "Finalizadas en el consultorio: " . $consultorio['NombreConsultorio'];
        $tipo_citas = "Finalizadas";
    } elseif ($filtro === 'consultorio-realizar') {
        $consultorio_id = $_GET['consultorio'];
        $where_clause .= " AND c.IDConsultorio = ? AND c.Estado = 'Programado'";
        $params[] = $consultorio_id;

        $stmt = $pdo->prepare("SELECT NombreConsultorio FROM consultorio WHERE IDConsultorio = ?");
        $stmt->execute([$consultorio_id]);
        $consultorio = $stmt->fetch(PDO::FETCH_ASSOC);
        $filtro_texto = "A realizar en el consultorio: " . $consultorio['NombreConsultorio'];
        $tipo_citas = "A realizar";
    }

    // Obtener citas
    $query = "SELECT c.*, 
              cp.NombreCliente, cp.ApellidoCliente,
              con.NombreConsultorio, con.Direccion as UbicacionConsultorio
              FROM cita c
              LEFT JOIN cliente_profesional cp ON c.IDCliente = cp.IDClienteProfesional
              LEFT JOIN consultorio con ON c.IDConsultorio = con.IDConsultorio
              $where_clause 
              ORDER BY c.DiaCita DESC, c.HoraInicial DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calcular indicadores
    $total_citas = count($citas);
    $finalizadas = count(array_filter($citas, fn($c) => $c['Estado'] === 'Finalizado'));
    $por_realizar = count(array_filter($citas, fn($c) => $c['Estado'] === 'Programado'));

    // Período incluido
    if (!empty($citas)) {
        $fechas = array_map(fn($c) => $c['DiaCita'], $citas);
        $fecha_min = min($fechas);
        $fecha_max = max($fechas);
    } else {
        $fecha_min = $fecha_max = date('Y-m-d');
    }

    // Consultorios relevantes
    $consultorios_unicos = array_unique(array_filter(array_column($citas, 'NombreConsultorio')));
    $consultorios_texto = !empty($consultorios_unicos) ? implode(', ', $consultorios_unicos) : 'Todos';

    // Crear PDF
    $pdf = new PDF();
    $pdf->AddPage();

    // Encabezado
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'INFORME: LISTADO DE CITAS (' . $tipo_citas . ')', 0, 1, 'C');
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
    $pdf->Cell(0, 6, "  Total de citas listadas: $total_citas", 0, 1);

    $pdf->Cell(5, 6, '-', 0, 0);
    $pdf->Cell(0, 6, "  Citas finalizadas: $finalizadas   Citas por realizar: $por_realizar", 0, 1);

    $pdf->Cell(5, 6, '-', 0, 0);
    $pdf->Cell(0, 6, "  Período incluido: " . date('d/m/Y', strtotime($fecha_min)) . " a " . date('d/m/Y', strtotime($fecha_max)), 0, 1);

    $pdf->Cell(5, 6, '-', 0, 0);
    $pdf->Cell(0, 6, "  Consultorio(s) relevantes: " . substr($consultorios_texto, 0, 50), 0, 1);
    $pdf->Ln(5);

    // Listado
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 6, 'LISTADO:', 0, 1);
    $pdf->Ln(2);

    // Tabla
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(15, 7, 'ID Cita', 1, 0, 'C');
    $pdf->Cell(30, 7, 'Cliente', 1, 0, 'C');
    $pdf->Cell(22, 7, 'Fecha', 1, 0, 'C');
    $pdf->Cell(20, 7, 'Hora Inicio', 1, 0, 'C');
    $pdf->Cell(20, 7, 'Hora Fin', 1, 0, 'C');
    $pdf->Cell(40, 7, 'Profesional', 1, 0, 'C');
    $pdf->Cell(43, 7, 'Consultorio', 1, 1, 'C');

    $pdf->SetFont('Arial', '', 7);
    foreach ($citas as $cita) {
        $cliente_nombre = $cita['NombreCliente'] . ' ' . ($cita['ApellidoCliente'] ?: '');
        $profesional_nombre = $profesional['NombreProfesional'] . ' ' . ($profesional['ApellidoProfesional'] ?: '');

        $pdf->Cell(15, 6, $cita['IDCita'], 1, 0, 'C');
        $pdf->Cell(30, 6, substr($cliente_nombre, 0, 20), 1, 0);
        $pdf->Cell(22, 6, date('d/m/Y', strtotime($cita['DiaCita'])), 1, 0, 'C');
        $pdf->Cell(20, 6, $cita['HoraInicial'], 1, 0, 'C');
        $pdf->Cell(20, 6, $cita['HoraFinal'], 1, 0, 'C');
        $pdf->Cell(40, 6, substr($profesional_nombre, 0, 25), 1, 0);
        $pdf->Cell(43, 6, substr($cita['NombreConsultorio'] ?: 'N/A', 0, 28), 1, 1);
    }

    $pdf->Output('I', 'Listado_Citas.pdf');
} catch (PDOException $e) {
    die('Error: ' . $e->getMessage());
}
