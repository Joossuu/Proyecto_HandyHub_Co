<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../config/session_check.php';
if ($_SERVER['REQUEST_METHOD']!=='POST') { http_response_code(405); echo json_encode(['error'=>'Method not allowed']); exit; }
if (!isset($_SESSION['id_usuario'])) { http_response_code(401); echo json_encode(['error'=>'No autorizado']); exit; }
if (!in_array($_SESSION['rol'] ?? '', ['Administrador'])) { http_response_code(403); echo json_encode(['error'=>'No permitido']); exit; }

$raw = file_get_contents('php://input'); $data = json_decode($raw,true);
if (!isset($data['id']) || !is_numeric($data['id'])) { http_response_code(400); echo json_encode(['error'=>'Bad request']); exit; }
$id=(int)$data['id'];

$stmt = $mysqli->prepare("SELECT Estado FROM Usuario WHERE ID_Usuario=?"); $stmt->bind_param("i",$id); $stmt->execute(); $res=$stmt->get_result();
if (!$res || $res->num_rows===0) { http_response_code(404); echo json_encode(['error'=>'Usuario no encontrado']); exit; }
$current = $res->fetch_assoc()['Estado']; $new = ($current==='Activo') ? 'Inactivo' : 'Activo';
$upd = $mysqli->prepare("UPDATE Usuario SET Estado=? WHERE ID_Usuario=?"); $upd->bind_param("si",$new,$id); $ok = $upd->execute();

// bitacora
$accion = ($ok ? "Cambio estado usuario ID {$id} a {$new}" : "Error al cambiar estado usuario ID {$id}");
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$stmtlog = $mysqli->prepare("INSERT INTO Bitacora (ID_Usuario,Accion,IP_Origen) VALUES (?,?,?)");
$stmtlog->bind_param("iss", $_SESSION['id_usuario'], $accion, $ip); $stmtlog->execute();

echo json_encode(['ok'=>(bool)$ok,'estado'=>$new]);
