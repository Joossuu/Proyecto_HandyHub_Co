<?php
// controllers/delete_mantenimiento.php
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
if (!$id) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'Falta id']); exit; }

try {
  $d = $pdo->prepare("DELETE FROM Mantenimiento WHERE ID_Mantenimiento = :id");
  $d->execute([':id' => $id]);
  echo json_encode(['success'=>true,'message'=>'Mantenimiento eliminado']);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
