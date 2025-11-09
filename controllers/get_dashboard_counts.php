<?php
// controllers/get_dashboard_counts.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db_config.php';

try {
    // counts
    $out = [];

    $q = $conn->query("SELECT COUNT(*) as c FROM Prestamo WHERE Estado = 'Activo'");
    $out['prestamos_activos'] = (int)$q->fetch_assoc()['c'];

    $q = $conn->query("SELECT COUNT(*) as c FROM Prestamo WHERE Fecha_Limite < NOW() AND Fecha_Devolucion IS NULL");
    $out['prestamos_retrasados'] = (int)$q->fetch_assoc()['c'];

    $q = $conn->query("SELECT COUNT(*) as c FROM Herramienta WHERE Estado = 'Mantenimiento'");
    $out['en_mantenimiento'] = (int)$q->fetch_assoc()['c'];

    $q = $conn->query("SELECT COUNT(*) as c FROM Usuario WHERE Estado = 'Activo'");
    $out['usuarios_activos'] = (int)$q->fetch_assoc()['c'];

    // recent prestamos (limit 6)
    $rs = $conn->query("SELECT p.Codigo_Prestamo, h.Nombre as herramienta, u.Usuario_Login as usuario, p.Fecha_Prestamo, p.Estado
                        FROM Prestamo p
                        LEFT JOIN Herramienta h ON p.ID_Herramienta = h.ID_Herramienta
                        LEFT JOIN Usuario u ON p.ID_Usuario = u.ID_Usuario
                        ORDER BY p.Fecha_Prestamo DESC LIMIT 6");
    $out['recent'] = $rs->fetch_all(MYSQLI_ASSOC);

    // alertas vencidos
    $rs = $conn->query("SELECT p.Codigo_Prestamo, h.Nombre as herramienta, u.Usuario_Login as usuario, DATEDIFF(NOW(), p.Fecha_Limite) as dias_vencido
                        FROM Prestamo p
                        LEFT JOIN Herramienta h ON p.ID_Herramienta = h.ID_Herramienta
                        LEFT JOIN Usuario u ON p.ID_Usuario = u.ID_Usuario
                        WHERE p.Fecha_Limite < NOW() AND p.Fecha_Devolucion IS NULL
                        ORDER BY p.Fecha_Limite ASC LIMIT 6");
    $out['alertas'] = $rs->fetch_all(MYSQLI_ASSOC);

    echo json_encode(['ok'=>true,'data'=>$out], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
}
