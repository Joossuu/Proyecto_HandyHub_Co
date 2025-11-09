<?php
// controllers/roles_data.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db_config.php';
try {
    if (!isset($conn) || !($conn instanceof mysqli)) {
        throw new Exception('No hay conexión válida a la BD.');
    }
    $sql = "SELECT ID_Rol, Nombre_Rol FROM Rol WHERE Estado = 'Activo' ORDER BY Nombre_Rol";
    $res = $conn->query($sql);
    $roles = $res->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['success' => true, 'data' => $roles]);
} catch (Exception $ex) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $ex->getMessage()]);
}
