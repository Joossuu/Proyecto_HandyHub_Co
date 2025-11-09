<?php
// controllers/create_user.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db_config.php';
try {
    if (!isset($conn) || !($conn instanceof mysqli)) {
        throw new Exception('No hay conexión válida a la BD.');
    }

    // esperar JSON POST o form POST
    $usuario = $_POST['usuario'] ?? '';
    $codigo = $_POST['codigo'] ?? '';
    $email = $_POST['email'] ?? null;
    $rol = $_POST['rol'] ?? null;
    $estado = $_POST['estado'] ?? 'Activo';

    if (trim($usuario) === '') throw new Exception('Usuario requerido.');
    if (trim($codigo) === '') throw new Exception('Código requerido.');

    // validar unicidad
    $stmt = $conn->prepare("SELECT ID_Usuario FROM Usuario WHERE Usuario_Login = ? OR Codigo_Usuario = ? LIMIT 1");
    $stmt->bind_param('ss', $usuario, $codigo);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception('Ya existe un usuario con ese login o código.');
    }

    $sql = "INSERT INTO Usuario (Codigo_Usuario, Usuario_Login, Email, ID_Rol, Estado) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssiss', $codigo, $usuario, $email, $rol, $estado);
    $stmt->execute();
    $newId = $conn->insert_id;

    // crear credencial básica con contraseña temporal (obliga a cambiar fuera de scope)
    $tempHash = password_hash('ChangeMe123!', PASSWORD_BCRYPT);
    $stmt2 = $conn->prepare("INSERT INTO Credencial (ID_Usuario, Password_Hash) VALUES (?, ?)");
    $stmt2->bind_param('is', $newId, $tempHash);
    $stmt2->execute();

    echo json_encode(['success' => true, 'message' => 'Usuario creado.', 'id' => $newId]);
} catch (Exception $ex) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $ex->getMessage()]);
}
