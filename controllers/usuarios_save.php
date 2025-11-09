<?php
// controllers/usuarios_save.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db_config.php';

try {
    $pdo = get_db_connection();

    $action = $_POST['action'] ?? '';
    $usuario = trim($_POST['usuario'] ?? '');
    $codigo = trim($_POST['codigo'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $id_rol = intval($_POST['id_rol'] ?? 0);
    $estado = $_POST['estado'] ?? 'Activo';
    $id_usuario = intval($_POST['id_usuario'] ?? 0);

    if ($action === 'create') {
        if ($usuario === '' || $codigo === '') {
            throw new Exception('Usuario y Código son obligatorios.');
        }
        // Inserción
        $sql = "INSERT INTO Usuario (Codigo_Usuario, Usuario_Login, Email, ID_Rol, Estado, Fecha_Creacion) VALUES
                (:codigo, :usuario, :email, :id_rol, :estado, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':codigo'=>$codigo,
            ':usuario'=>$usuario,
            ':email'=>$email,
            ':id_rol'=>$id_rol ? $id_rol : null,
            ':estado'=>$estado
        ]);
        echo json_encode(['success'=>true,'message'=>'Usuario creado.']);
        exit;
    } elseif ($action === 'update') {
        if ($id_usuario <= 0) throw new Exception('ID de usuario inválido.');
        $sql = "UPDATE Usuario SET Usuario_Login=:usuario, Email=:email, ID_Rol=:id_rol, Estado=:estado WHERE ID_Usuario=:id_usuario";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':usuario'=>$usuario,
            ':email'=>$email,
            ':id_rol'=>$id_rol? $id_rol : null,
            ':estado'=>$estado,
            ':id_usuario'=>$id_usuario
        ]);
        echo json_encode(['success'=>true,'message'=>'Usuario actualizado.']);
        exit;
    } else {
        throw new Exception('Acción inválida.');
    }
} catch (Exception $ex) {
    echo json_encode(['success'=>false,'message'=>$ex->getMessage()]);
}
