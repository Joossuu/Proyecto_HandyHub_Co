<?php
// controllers/dashboard_data.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db_config.php';
session_start();

// (opcional) para debug local: comentar en producción
// ini_set('display_errors', 1); error_reporting(E_ALL);

if (!isset($_SESSION['usuario'])) {
    // permitir modo no auth si lo deseas (temporal para debug), o devolver 401
    // http_response_code(401); echo json_encode(['error'=>'Unauthorized']); exit;
    // para desarrollo, permitimos retornar datos de prueba si no hay sesión:
    $noauth_mode = true;
} else {
    $noauth_mode = false;
}

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

$counts = [
    'active_loans' => 0,
    'late_loans' => 0,
    'in_maint' => 0,
    'active_users' => 0
];

try {
    // 1) counts
    $res = $conn->query("SELECT COUNT(*) as c FROM Prestamo WHERE Fecha_Devolucion IS NULL");
    $counts['active_loans'] = intval($res->fetch_assoc()['c'] ?? 0);

    $res = $conn->query("SELECT COUNT(*) as c FROM Prestamo WHERE Fecha_Devolucion IS NULL AND Fecha_Limite < NOW()");
    $counts['late_loans'] = intval($res->fetch_assoc()['c'] ?? 0);

    $res = $conn->query("SELECT COUNT(*) as c FROM Herramienta WHERE Estado = 'Mantenimiento'");
    $counts['in_maint'] = intval($res->fetch_assoc()['c'] ?? 0);

    $res = $conn->query("SELECT COUNT(*) as c FROM Usuario WHERE Estado = 'Activo'");
    $counts['active_users'] = intval($res->fetch_assoc()['c'] ?? 0);

    // 2) alerts - prestamos vencidos (limit 5)
    $alerts = [];
    $stmt = $conn->prepare("SELECT P.Codigo_Prestamo, H.Nombre AS Herramienta, U.Usuario_Login, DATEDIFF(NOW(), P.Fecha_Limite) AS daysLate
                             FROM Prestamo P
                             JOIN Herramienta H ON P.ID_Herramienta = H.ID_Herramienta
                             JOIN Usuario U ON P.ID_Usuario = U.ID_Usuario
                             WHERE P.Fecha_Devolucion IS NULL AND P.Fecha_Limite < NOW()
                             ORDER BY P.Fecha_Limite ASC
                             LIMIT 5");
    $stmt->execute();
    $r = $stmt->get_result();
    while ($row = $r->fetch_assoc()) $alerts[] = $row;
    $stmt->close();

    // 3) next due (7 dias)
    $nextDue = [];
    $stmt2 = $conn->prepare("SELECT P.Codigo_Prestamo, H.Nombre AS Herramienta, U.Usuario_Login, DATEDIFF(P.Fecha_Limite, NOW()) AS daysUntil
                              FROM Prestamo P
                              JOIN Herramienta H ON P.ID_Herramienta = H.ID_Herramienta
                              JOIN Usuario U ON P.ID_Usuario = U.ID_Usuario
                              WHERE P.Fecha_Devolucion IS NULL AND P.Fecha_Limite >= NOW() AND P.Fecha_Limite <= DATE_ADD(NOW(), INTERVAL 7 DAY)
                              ORDER BY P.Fecha_Limite ASC
                              LIMIT 5");
    $stmt2->execute();
    $r2 = $stmt2->get_result();
    while ($row = $r2->fetch_assoc()) $nextDue[] = $row;
    $stmt2->close();

    // 4) recent loans
    $recent = [];
    $res = $conn->query("SELECT P.Codigo_Prestamo, H.Nombre AS Herramienta, U.Usuario_Login, P.Fecha_Prestamo, P.Estado
                         FROM Prestamo P
                         JOIN Herramienta H ON P.ID_Herramienta = H.ID_Herramienta
                         JOIN Usuario U ON P.ID_Usuario = U.ID_Usuario
                         ORDER BY P.Fecha_Prestamo DESC
                         LIMIT 5");
    while ($row = $res->fetch_assoc()) $recent[] = $row;

    // 5) users (filtrado por q)
    $users = [];
    if ($q === '') {
        $res = $conn->query("SELECT ID_Usuario, Codigo_Usuario, Usuario_Login, Estado FROM Usuario ORDER BY Usuario_Login LIMIT 50");
        while ($row = $res->fetch_assoc()) $users[] = $row;
    } else {
        $like = '%' . $conn->real_escape_string($q) . '%';
        $res = $conn->query("SELECT ID_Usuario, Codigo_Usuario, Usuario_Login, Estado FROM Usuario WHERE Usuario_Login LIKE '{$like}' OR Codigo_Usuario LIKE '{$like}' ORDER BY Usuario_Login LIMIT 50");
        while ($row = $res->fetch_assoc()) $users[] = $row;
    }

    // 6) chart (ultimos 7 dias)
    $chart = ['labels'=>[], 'values'=>[]];
    for ($i = 6; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-{$i} days"));
        $chart['labels'][] = date('d/m', strtotime($d));
        $r = $conn->query("SELECT COUNT(*) as c FROM Prestamo WHERE DATE(Fecha_Prestamo) = '{$d}'");
        $chart['values'][] = intval($r->fetch_assoc()['c'] ?? 0);
    }

    // 7) tools donut
    $res = $conn->query("SELECT Estado, COUNT(*) as c FROM Herramienta GROUP BY Estado");
    $tools = ['Disponible' => 0, 'Otros' => 0];
    while ($row = $res->fetch_assoc()) {
        if ($row['Estado'] === 'Disponible') $tools['Disponible'] += intval($row['c']);
        else $tools['Otros'] += intval($row['c']);
    }

    $payload = [
        'counts' => $counts,
        'alerts' => $alerts,
        'nextDue' => $nextDue,
        'recent' => $recent,
        'users' => $users,
        'chart' => $chart,
        'tools' => $tools,
        'time' => date(DATE_ATOM)
    ];

    echo json_encode($payload);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
