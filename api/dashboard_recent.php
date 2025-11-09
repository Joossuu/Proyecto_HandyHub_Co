<?php
// api/dashboard_recent.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/session_check.php';
require_once __DIR__ . '/../config/db_config.php';

try {
    $sql = "SELECT p.Codigo_Prestamo, h.Nombre AS nombre_herramienta, u.Usuario_Login AS usuario_login, p.Fecha_Prestamo, p.Estado
            FROM Prestamo p
            JOIN Herramienta h ON p.ID_Herramienta = h.ID_Herramienta
            JOIN Usuario u ON p.ID_Usuario = u.ID_Usuario
            ORDER BY p.Fecha_Prestamo DESC
            LIMIT 8";
    $res = $conn->query($sql);
    $rows = [];
    while ($r = $res->fetch_assoc()) {
        $rows[] = [
            'codigo_prestamo' => $r['Codigo_Prestamo'],
            'nombre_herramienta' => $r['nombre_herramienta'],
            'usuario_login' => $r['usuario_login'],
            'fecha_prestamo' => $r['Fecha_Prestamo'],
            'estado' => $r['Estado']
        ];
    }
    echo json_encode($rows);
} catch (Exception $ex) {
    http_response_code(500);
    echo json_encode(['error' => $ex->getMessage()]);
}
