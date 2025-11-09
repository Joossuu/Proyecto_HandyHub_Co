<?php
// controllers/usuarios_action.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db_config.php';

try {
    $mysqli = get_mysqli();
    // recogemos POST (ajax)
    $id = isset($_POST['ID_Usuario']) && $_POST['ID_Usuario'] !== '' ? intval($_POST['ID_Usuario']) : null;
    $usuario = isset($_POST['Usuario_Login']) ? trim($_POST['Usuario_Login']) : '';
    $email = isset($_POST['Email']) ? trim($_POST['Email']) : '';
    $id_rol = isset($_POST['ID_Rol']) && $_POST['ID_Rol'] !== '' ? intval($_POST['ID_Rol']) : null;
    $estado = isset($_POST['Estado']) ? trim($_POST['Estado']) : 'Activo';
    // Para código si el cliente intenta enviarlo, lo ignoramos en creación y lo auto generamos.
    $codigo = isset($_POST['Codigo_Usuario']) ? trim($_POST['Codigo_Usuario']) : '';

    if ($usuario === '') {
        throw new Exception('El campo Usuario es obligatorio.');
    }

    if ($id) {
        // UPDATE existente
        $sql = "UPDATE Usuario SET Usuario_Login = ?, Email = ?, ID_Rol = ?, Estado = ? WHERE ID_Usuario = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('ssisi', $usuario, $email, $id_rol, $estado, $id);
        $stmt->execute();
        echo json_encode(['success' => true, 'message' => 'Usuario actualizado.']);
        exit;
    } else {
        // GENERAR Codigo_Usuario automático: buscar max(ID_Usuario) o max(Codigo_Usuario)
        // mejor: tomar el MAX de ID_Usuario y usar +1 para el sufijo
        $res = $mysqli->query("SELECT MAX(ID_Usuario) AS mx FROM Usuario");
        $row = $res->fetch_assoc();
        $next = intval($row['mx']) + 1;
        $codigoGen = sprintf('USR-%04d', $next);

        // Insert
        $sql = "INSERT INTO Usuario (Codigo_Usuario, Usuario_Login, Email, ID_Rol, Estado) VALUES (?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('sssds', $codigoGen, $usuario, $email, $id_rol, $estado);
        // note: 'd' for integer works too; if bind_param complains change to 'i' for ID_Rol
        // correction: use 'siiss' types properly:
        $stmt = $mysqli->prepare("INSERT INTO Usuario (Codigo_Usuario, Usuario_Login, Email, ID_Rol, Estado) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('sssis', $codigoGen, $usuario, $email, $id_rol, $estado);
        $stmt->execute();
        echo json_encode(['success' => true, 'message' => 'Usuario creado.', 'Codigo_Usuario' => $codigoGen]);
        exit;
    }

} catch (Exception $ex) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $ex->getMessage()]);
}
