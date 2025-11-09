<?php
// api/users_list.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/session_check.php';
require_once __DIR__ . '/../config/db_config.php';

try {
    $sql = "SELECT u.ID_Usuario, u.Codigo_Usuario, u.Usuario_Login, u.Estado,
                   r.Nombre_Rol AS rol, d.Nombre AS departamento
            FROM Usuario u
            LEFT JOIN Rol r ON u.ID_Rol = r.ID_Rol
            LEFT JOIN Departamento d ON u.ID_Departamento = d.ID_Departamento
            ORDER BY u.Usuario_Login ASC";
    $res = $conn->query($sql);
    $rows = [];
    while ($r = $res->fetch_assoc()) {
        $rows[] = [
            'id_usuario' => (int)$r['ID_Usuario'],
            'codigo_usuario' => $r['Codigo_Usuario'],
            'usuario_login' => $r['Usuario_Login'],
            'estado' => $r['Estado'],
            'rol' => $r['rol'] ?? '',
            'departamento' => $r['departamento'] ?? ''
        ];
    }
    echo json_encode($rows);
} catch (Exception $ex) {
    http_response_code(500);
    echo json_encode(['error' => $ex->getMessage()]);
}
