<?php
// controllers/usuarios_data.php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db_config.php'; // debe definir $conn (mysqli)

try {
    if (!isset($conn) || !$conn instanceof mysqli) {
        throw new Exception('No hay conexión mysqli disponible en db_config.php');
    }

    $search = isset($_GET['search']) ? trim($_GET['search']) : '';

    if ($search !== '') {
        $q = "%{$search}%";
        $sql = "SELECT u.ID_Usuario, u.Codigo_Usuario, u.Usuario_Login, COALESCE(u.Email,'') AS Email, u.Fecha_Creacion, u.Estado, r.Nombre_Rol AS Rol_Nombre
                FROM Usuario u
                LEFT JOIN Rol r ON u.ID_Rol = r.ID_Rol
                WHERE u.Usuario_Login LIKE ? OR u.Codigo_Usuario LIKE ?
                ORDER BY u.Usuario_Login
                LIMIT 50";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $q, $q);
        $stmt->execute();
        $res = $stmt->get_result();
        $users = [];
        while ($row = $res->fetch_assoc()) $users[] = $row;
        echo json_encode(['success' => true, 'data' => $users], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // sin search -> devolver todos
    $sql = "SELECT u.ID_Usuario, u.Codigo_Usuario, u.Usuario_Login, COALESCE(u.Email,'') AS Email, u.Fecha_Creacion, u.Estado, r.Nombre_Rol AS Rol_Nombre
            FROM Usuario u
            LEFT JOIN Rol r ON u.ID_Rol = r.ID_Rol
            ORDER BY u.ID_Usuario";
    $res = $conn->query($sql);
    $users = [];
    while ($row = $res->fetch_assoc()) $users[] = $row;

    echo json_encode(['success' => true, 'data' => $users], JSON_UNESCAPED_UNICODE);

} catch (Exception $ex) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $ex->getMessage()]);
}
