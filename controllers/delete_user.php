<?php
// controllers/delete_user.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db_config.php';

try {
    if (!isset($conn) || !$conn) throw new Exception('No hay conexión db.');

    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) throw new Exception('ID inválido.');

    $stmt = $conn->prepare("DELETE FROM Usuario WHERE ID_Usuario=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();

    echo json_encode(['success' => true, 'message' => 'Usuario eliminado.']);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

