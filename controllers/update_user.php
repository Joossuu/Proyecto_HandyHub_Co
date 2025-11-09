<?php
// controllers/update_user.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db_config.php';
try {
    if (!isset($conn) || !($conn instanceof mysqli)) {
        throw new Exception('No hay conexión válida a la BD.');
    }

    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $usuario = $_POST['usuario'] ?? '';
    $codigo = $_POST['codigo'] ?? '';
    $email = $_POST['email'] ?? null;
    $rol = $_POST['rol'] ?? null;
    $estado = $_POST['estado'] ?? 'Activo';

    if ($id <= 0) throw new Exception('ID inválido.');
    if (trim($usuario) === '') throw new Exception('Usuario requerido.');
    if (trim($codigo) === '') throw new Exception('Código requerido.');

    // verificar unicidad (excepto este id)
    $stmt = $conn->prepare("SELECT ID_Usuario FROM Usuario WHERE (Usuario_Login = ? OR Codigo_Usuario = ?) AND ID_Usuario != ? LIMIT 1");
    $stmt->bind_param('ssi', $usuario, $codigo, $id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception('Otro registro ya usa ese login o código.');
    }

    $sql = "UPDATE Usuario SET Codigo_Usuario = ?, Usuario_Login = ?, Email = ?, ID_Rol = ?, Estado = ? WHERE ID_Usuario = ?";
    $stmt2 = $conn->prepare($sql);
    $stmt2->bind_param('sssisi', $codigo, $usuario, $email, $rol, $estado, $id);
    $stmt2->execute();

    echo json_encode(['success' => true, 'message' => 'Usuario actualizado.']);
} catch (Exception $ex) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $ex->getMessage()]);
}
