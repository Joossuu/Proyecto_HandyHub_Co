<?php
// api/dashboard_alerts.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/session_check.php';
require_once __DIR__ . '/../config/db_config.php';

try {
    // prestamos vencidos (sin devolver)
    $sqlV = "SELECT p.ID_Prestamo, p.Codigo_Prestamo, p.Fecha_Prestamo, p.Fecha_Limite, h.Nombre AS nombre_herramienta, u.Usuario_Login AS usuario_login,
              DATEDIFF(NOW(), p.Fecha_Limite) AS days_overdue
             FROM Prestamo p
             JOIN Herramienta h ON p.ID_Herramienta = h.ID_Herramienta
             JOIN Usuario u ON p.ID_Usuario = u.ID_Usuario
             WHERE (p.Fecha_Devolucion IS NULL OR p.Fecha_Devolucion = '') AND p.Fecha_Limite < NOW()
             ORDER BY days_overdue DESC";
    $resV = $conn->query($sqlV);
    $vencidos = [];
    while ($row = $resV->fetch_assoc()) {
        $vencidos[] = [
            'id' => (int)$row['ID_Prestamo'],
            'codigo_prestamo' => $row['Codigo_Prestamo'],
            'fecha_prestamo' => $row['Fecha_Prestamo'],
            'fecha_limite' => $row['Fecha_Limite'],
            'nombre_herramienta' => $row['nombre_herramienta'],
            'usuario_login' => $row['usuario_login'],
            'days_overdue' => (int)$row['days_overdue']
        ];
    }

    // proximos vencimientos (próximos 7 días) - sin devolver
    $sqlP = "SELECT p.ID_Prestamo, p.Codigo_Prestamo, p.Fecha_Prestamo, p.Fecha_Limite, h.Nombre AS nombre_herramienta, u.Usuario_Login AS usuario_login,
              DATEDIFF(p.Fecha_Limite, NOW()) AS days_to
             FROM Prestamo p
             JOIN Herramienta h ON p.ID_Herramienta = h.ID_Herramienta
             JOIN Usuario u ON p.ID_Usuario = u.ID_Usuario
             WHERE (p.Fecha_Devolucion IS NULL OR p.Fecha_Devolucion = '') AND p.Fecha_Limite >= NOW() AND p.Fecha_Limite <= DATE_ADD(NOW(), INTERVAL 7 DAY)
             ORDER BY p.Fecha_Limite ASC";
    $resP = $conn->query($sqlP);
    $proximos = [];
    while ($row = $resP->fetch_assoc()) {
        $proximos[] = [
            'id' => (int)$row['ID_Prestamo'],
            'codigo_prestamo' => $row['Codigo_Prestamo'],
            'fecha_prestamo' => $row['Fecha_Prestamo'],
            'fecha_limite' => $row['Fecha_Limite'],
            'nombre_herramienta' => $row['nombre_herramienta'],
            'usuario_login' => $row['usuario_login'],
            'days_to' => (int)$row['days_to']
        ];
    }

    // Chart: prestamos últimos 7 días (labels + counts)
    $labels = [];
    $values = [];
    for ($i = 6; $i >= 0; $i--) {
        $day = date('Y-m-d', strtotime("-$i days"));
        $labels[] = date('d/m', strtotime($day));
        $sqlc = "SELECT COUNT(*) as cnt FROM Prestamo WHERE DATE(Fecha_Prestamo) = '{$day}'";
        $rc = $conn->query($sqlc);
        $values[] = (int)$rc->fetch_assoc()['cnt'];
    }

    // Herramientas: disponible vs total
    $sqlTotal = "SELECT COUNT(*) as cnt FROM Herramienta";
    $sqlDisp  = "SELECT COUNT(*) as cnt FROM Herramienta WHERE Estado = 'Disponible'";
    $total = (int)$conn->query($sqlTotal)->fetch_assoc()['cnt'];
    $disp  = (int)$conn->query($sqlDisp)->fetch_assoc()['cnt'];

    echo json_encode([
        'vencidos' => $vencidos,
        'proximos' => $proximos,
        'chart' => ['labels' => $labels, 'values' => $values],
        'herramientas' => ['total' => $total, 'disponible' => $disp]
    ]);
} catch (Exception $ex) {
    http_response_code(500);
    echo json_encode(['error' => $ex->getMessage()]);
}
