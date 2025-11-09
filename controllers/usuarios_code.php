<?php
// controllers/usuarios_code.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db_config.php';

try {
    if (!isset($conn) || !$conn instanceof mysqli) {
        throw new Exception('No hay conexión mysqli disponible en db_config.php');
    }
    // Buscar el mayor código existente
    $sql = "SELECT Codigo_Usuario FROM Usuario WHERE Codigo_Usuario LIKE 'USR-%' ORDER BY ID_Usuario DESC LIMIT 1";
    $res = $conn->query($sql);
    $last = $res->fetch_assoc();
    $next = 'USR-0001';
    if ($last && !empty($last['Codigo_Usuario'])) {
        if (preg_match('/USR-(\d+)/', $last['Codigo_Usuario'], $m)) {
            $n = intval($m[1]) + 1;
            $next = 'USR-' . str_pad($n, 4, '0', STR_PAD_LEFT);
        }
    }
    echo json_encode(['success' => true, 'code' => $next]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
