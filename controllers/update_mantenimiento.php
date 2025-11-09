<?php
// controllers/update_mantenimiento.php
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

$raw = file_get_contents('php://input');
$payload = $_POST;
if (!$payload && $raw) {
  $json = json_decode($raw, true);
  if (is_array($json)) $payload = $json;
}

$id = $payload['id_mantenimiento'] ?? null;
$id_herr = $payload['id_herramienta'] ?? null;
$id_tec = $payload['id_tecnico'] ?? null;
$tipo = $payload['tipo'] ?? null;
$estado = $payload['estado'] ?? null;
$obs = $payload['observaciones'] ?? null;

if (!$id || !$id_herr) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'Faltan id o id_herramienta']); exit; }

try {
  // resolve code -> id
  if (!ctype_digit((string)$id_herr)) {
    $s = $pdo->prepare("SELECT ID_Herramienta FROM Herramienta WHERE Codigo_Herramienta = :c LIMIT 1");
    $s->execute([':c'=>$id_herr]);
    $f = $s->fetch();
    if ($f) $id_herr = $f['ID_Herramienta'];
  }

  $upd = $pdo->prepare("UPDATE Mantenimiento SET ID_Herramienta = :idh, ID_Tecnico = :idtec, Tipo_Mantenimiento = :tipo, Estado = :estado, Observaciones = :obs WHERE ID_Mantenimiento = :id");
  $upd->execute([
    ':idh' => $id_herr,
    ':idtec' => $id_tec ?: null,
    ':tipo' => $tipo,
    ':estado' => $estado,
    ':obs' => $obs,
    ':id' => $id
  ]);

  echo json_encode(['success'=>true,'message'=>'Mantenimiento actualizado']);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
