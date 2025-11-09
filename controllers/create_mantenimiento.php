<?php
// controllers/create_mantenimiento.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json; charset=utf-8');
session_start();

$config = __DIR__ . '/../config/db_config.php';
if (!file_exists($config)) { http_response_code(500); echo json_encode(['success'=>false,'message'=>'Missing config']); exit; }
require_once $config;

try {
  $pdo = new PDO("mysql:host=".DB_HOST.";port=".DB_PORT.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS, [
    PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC
  ]);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['success'=>false,'message'=>'DB connection error: '.$e->getMessage()]);
  exit;
}

// accept JSON or form
$raw = file_get_contents('php://input');
$payload = $_POST;
if (!$payload && $raw) {
  $json = json_decode($raw, true);
  if (is_array($json)) $payload = $json;
}

$id_herr = $payload['id_herramienta'] ?? null;
$tipo = $payload['tipo'] ?? null;
$id_tec = $payload['id_tecnico'] ?? null;
$estado = $payload['estado'] ?? 'Programado';
$obs = $payload['observaciones'] ?? null;

if (empty($id_herr) || empty($tipo)) {
  http_response_code(400);
  echo json_encode(['success'=>false,'message'=>'Faltan campos requeridos']);
  exit;
}

// try resolve code -> id
try {
  if (!ctype_digit((string)$id_herr)) {
    $s = $pdo->prepare("SELECT ID_Herramienta FROM Herramienta WHERE Codigo_Herramienta = :c LIMIT 1");
    $s->execute([':c'=>$id_herr]);
    $f = $s->fetch();
    if ($f) $id_herr = $f['ID_Herramienta'];
  }
  $now = date('Y-m-d H:i:s');
  $ins = $pdo->prepare("INSERT INTO Mantenimiento (ID_Herramienta, ID_Tecnico, Tipo_Mantenimiento, Fecha_Inicio, Estado, Observaciones)
                        VALUES (:idh, :idtec, :tipo, :fini, :estado, :obs)");
  $ins->execute([
    ':idh' => $id_herr,
    ':idtec' => $id_tec ?: null,
    ':tipo' => $tipo,
    ':fini' => $now,
    ':estado' => $estado,
    ':obs' => $obs
  ]);
  // update tool state
  $u = $pdo->prepare("UPDATE Herramienta SET Estado = 'Mantenimiento' WHERE ID_Herramienta = :idh");
  $u->execute([':idh' => $id_herr]);

  echo json_encode(['success'=>true,'message'=>'Mantenimiento creado']);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
