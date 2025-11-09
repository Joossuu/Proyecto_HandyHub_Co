<?php
// controllers/herramientas_data.php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db_config.php';
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (Exception $e) {
    http_response_code(500); echo json_encode(['success'=>false,'message'=>$e->getMessage()]); exit;
}
$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'GET') {
    $q = $_GET['q'] ?? '';
    if ($q === '') {
        $stmt = $pdo->query("SELECT ID_Herramienta, Codigo_Herramienta, Nombre, Estado, Ubicacion FROM Herramienta ORDER BY Nombre LIMIT 500");
        echo json_encode(['success'=>true,'data'=>$stmt->fetchAll()]);
    } else {
        $stmt = $pdo->prepare("SELECT ID_Herramienta, Codigo_Herramienta, Nombre, Estado, Ubicacion FROM Herramienta WHERE Nombre LIKE :q OR Codigo_Herramienta LIKE :q LIMIT 200");
        $stmt->execute([':q'=>"%$q%"]);
        echo json_encode(['success'=>true,'data'=>$stmt->fetchAll()]);
    }
    exit;
}
if ($method === 'POST') {
    $payload = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $codigo = $payload['codigo'] ?? null;
    $nombre = $payload['nombre'] ?? null;
    $estado = $payload['estado'] ?? 'Disponible';
    if (!$nombre) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'nombre requerido']); exit; }
    try {
        $stmt = $pdo->prepare("INSERT INTO Herramienta (Codigo_Herramienta, Nombre, Estado, Ubicacion) VALUES (:c, :n, :e, :u)");
        $stmt->execute([':c'=>$codigo,':n'=>$nombre,':e'=>$estado,':u'=>$payload['ubicacion'] ?? null]);
        echo json_encode(['success'=>true,'id'=>$pdo->lastInsertId()]);
    } catch (Exception $e) {
        http_response_code(500); echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
    }
    exit;
}
if ($method === 'DELETE') {
    $payload = json_decode(file_get_contents('php://input'), true);
    $id = $payload['id'] ?? null;
    if (!$id) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'id requerido']); exit; }
    try {
        $stmt = $pdo->prepare("DELETE FROM Herramienta WHERE ID_Herramienta = :id");
        $stmt->execute([':id'=>$id]);
        echo json_encode(['success'=>true]);
    } catch (Exception $e) {
        http_response_code(500); echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
    }
    exit;
}
http_response_code(405); echo json_encode(['success'=>false,'message'=>'Method not allowed']);
