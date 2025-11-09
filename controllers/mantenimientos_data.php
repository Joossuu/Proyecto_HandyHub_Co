<?php
// controllers/mantenimientos_data.php
session_start();
header('Content-Type: application/json; charset=utf-8');

// --- Ajusta si tus credenciales están en otro archivo ---
$dbHost = '127.0.0.1';
$dbName = 'handyhubdb';
$dbUser = 'root';
$dbPass = ''; // si tienes contraseña, ponerla aquí

try {
    $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (Exception $e) {
    http_response_code(500);
    // No imprimir HTML ni warnings, sólo JSON
    echo json_encode(['success' => false, 'message' => 'DB connection error: ' . $e->getMessage()]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $q = isset($_GET['q']) ? trim($_GET['q']) : '';
    try {
        if ($q === '') {
            $stmt = $pdo->prepare("SELECT m.ID_Mantenimiento, m.Tipo_Mantenimiento, m.Fecha_Inicio, m.Estado,
                                          h.ID_Herramienta, h.Codigo_Herramienta, h.Nombre AS Herramienta,
                                          u.ID_Usuario AS ID_Tecnico, u.Usuario_Login AS Tecnico_Nombre
                                   FROM Mantenimiento m
                                   LEFT JOIN Herramienta h ON h.ID_Herramienta = m.ID_Herramienta
                                   LEFT JOIN Usuario u ON u.ID_Usuario = m.ID_Tecnico
                                   ORDER BY m.Fecha_Inicio DESC
                                   LIMIT 200");
            $stmt->execute();
        } else {
            if (ctype_digit($q)) {
                $stmt = $pdo->prepare("SELECT m.ID_Mantenimiento, m.Tipo_Mantenimiento, m.Fecha_Inicio, m.Estado,
                                              h.ID_Herramienta, h.Codigo_Herramienta, h.Nombre AS Herramienta,
                                              u.ID_Usuario AS ID_Tecnico, u.Usuario_Login AS Tecnico_Nombre
                                       FROM Mantenimiento m
                                       LEFT JOIN Herramienta h ON h.ID_Herramienta = m.ID_Herramienta
                                       LEFT JOIN Usuario u ON u.ID_Usuario = m.ID_Tecnico
                                       WHERE m.ID_Mantenimiento = :id
                                       LIMIT 200");
                $stmt->execute([':id' => (int)$q]);
            } else {
                $stmt = $pdo->prepare("SELECT m.ID_Mantenimiento, m.Tipo_Mantenimiento, m.Fecha_Inicio, m.Estado,
                                             h.ID_Herramienta, h.Codigo_Herramienta, h.Nombre AS Herramienta,
                                             u.ID_Usuario AS ID_Tecnico, u.Usuario_Login AS Tecnico_Nombre
                                      FROM Mantenimiento m
                                      LEFT JOIN Herramienta h ON h.ID_Herramienta = m.ID_Herramienta
                                      LEFT JOIN Usuario u ON u.ID_Usuario = m.ID_Tecnico
                                      WHERE h.Nombre LIKE :q OR h.Codigo_Herramienta LIKE :q
                                      ORDER BY m.Fecha_Inicio DESC
                                      LIMIT 200");
                $stmt->execute([':q' => "%{$q}%"]);
            }
        }
        $rows = $stmt->fetchAll();
        echo json_encode(['success' => true, 'data' => $rows]);
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

if ($method === 'POST') {
    // crear mantenimiento
    $id_herr = $_POST['id_herramienta'] ?? null;
    $id_tecnico = $_POST['id_tecnico'] ?? null;
    $tipo = $_POST['tipo'] ?? null;
    $estado = $_POST['estado'] ?? 'Programado';
    $obs = $_POST['observaciones'] ?? null;

    if (!$id_herr || !$tipo) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
        exit;
    }

    try {
        if (!ctype_digit((string)$id_herr)) {
            $stmt = $pdo->prepare("SELECT ID_Herramienta FROM Herramienta WHERE Codigo_Herramienta = :c LIMIT 1");
            $stmt->execute([':c' => $id_herr]);
            $found = $stmt->fetch();
            if ($found) $id_herr = $found['ID_Herramienta'];
        }

        $now = date('Y-m-d H:i:s');
        $ins = $pdo->prepare("INSERT INTO Mantenimiento (ID_Herramienta, ID_Tecnico, Tipo_Mantenimiento, Fecha_Inicio, Estado, Observaciones)
                              VALUES (:idh, :idtec, :tipo, :fini, :estado, :obs)");
        $ins->execute([
            ':idh' => $id_herr,
            ':idtec' => $id_tecnico ?: null,
            ':tipo' => $tipo,
            ':fini' => $now,
            ':estado' => $estado,
            ':obs' => $obs
        ]);

        // actualizar estado herramienta (opcional)
        $u = $pdo->prepare("UPDATE Herramienta SET Estado = 'Mantenimiento' WHERE ID_Herramienta = :idh");
        $u->execute([':idh' => $id_herr]);

        echo json_encode(['success' => true, 'message' => 'Mantenimiento creado']);
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method not allowed']);
exit;
