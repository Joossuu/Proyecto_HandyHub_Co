<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../config/session_check.php';
if (!isset($_SESSION['id_usuario'])) { http_response_code(401); echo json_encode(['error'=>'No autorizado']); exit; }

$labels=[];$values=[];
for($i=6;$i>=0;$i--){
  $day=date('Y-m-d', strtotime("-{$i} days"));
  $labels[] = date('d/m', strtotime($day));
  $stmt = $mysqli->prepare("SELECT COUNT(*) as c FROM Prestamo WHERE DATE(Fecha_Prestamo)=?");
  $stmt->bind_param("s",$day); $stmt->execute(); $res=$stmt->get_result()->fetch_assoc();
  $values[] = (int)$res['c'];
}

// herramientas by estado
$labels2=[];$values2=[];
$res2 = $mysqli->query("SELECT Estado,COUNT(*) c FROM Herramienta GROUP BY Estado");
if ($res2) {
  while($r=$res2->fetch_assoc()){ $labels2[]=$r['Estado']; $values2[]=(int)$r['c']; }
}

echo json_encode(['prestamos_last_days'=>['labels'=>$labels,'values'=>$values],'herramientas_by_estado'=>['labels'=>$labels2,'values'=>$values2]], JSON_UNESCAPED_UNICODE);
