<?php
// api/dashboard_counts.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/session_check.php';
require_once __DIR__ . '/../config/db_config.php'; // debe definir $conn (mysqli)

try {
    // prestamos activos: estado = 'Activo' y sin Fecha_Devolucion
    $q1 = "SELECT COUNT(*) AS cnt FROM Prestamo WHERE Estado = 'Activo' AND (Fecha_Devolucion IS NULL OR Fecha_Devolucion = '')";
    $r1 = $conn->query($q1);
    $prestamos_activos = $r1->fetch_assoc()['cnt'] ?? 0;

    // prestamos retrasados: Fecha_Limite < NOW() and no devolucion
    $q2 = "SELECT COUNT(*) AS cnt FROM Prestamo WHERE (Fecha_Devolucion IS NULL OR Fecha_Devolucion = '') AND Fecha_Limite < NOW()";
    $r2 = $conn->query($q2);
    $prestamos_retrasados = $r2->fetch_assoc()['cnt'] ?? 0;

    // mantenimientos en proceso
    $q3 = "SELECT COUNT(*) AS cnt FROM Mantenimiento WHERE Estado = 'En Proceso'";
    $r3 = $conn->query($q3);
    $mantenimientos_en_proceso = $r3->fetch_assoc()['cnt'] ?? 0;

    // usuarios activos
    $q4 = "SELECT COUNT(*) AS cnt FROM Usuario WHERE Estado = 'Activo'";
    $r4 = $conn->query($q4);
    $usuarios_activos = $r4->fetch_assoc()['cnt'] ?? 0;

    echo json_encode([
        'prestamos_activos' => (int)$prestamos_activos,
        'prestamos_retrasados' => (int)$prestamos_retrasados,
        'mantenimientos_en_proceso' => (int)$mantenimientos_en_proceso,
        'usuarios_activos' => (int)$usuarios_activos
    ]);
} catch (Exception $ex) {
    http_response_code(500);
    echo json_encode(['error' => $ex->getMessage()]);
}
