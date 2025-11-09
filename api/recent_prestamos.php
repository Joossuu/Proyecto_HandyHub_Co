<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../config/session_check.php';
if (!isset($_SESSION['id_usuario'])) { http_response_code(401); echo json_encode(['error'=>'No autorizado']); exit; }
$allowed=['Administrador','Supervisor'];
if (!in_array($_SESSION['rol'] ?? '', $allowed)) { echo json_encode([]); exit; }

$sql = "SELECT p.ID_Prestamo,p.Codigo_Prestamo,p.Fecha_Prestamo,p.Estado,u.Usuario_Login,h.Nombre AS Herramienta
        FROM Prestamo p JOIN Usuario u ON p.ID_Usuario=u.ID_Usuario JOIN Herramienta h ON p.ID_Herramienta=h.ID_Herramienta
        ORDER BY p.Fecha_Prestamo DESC LIMIT 10";
$res = $mysqli->query($sql);
$out=[];
if ($res) while($r=$res->fetch_assoc()) $out[]=['id'=>(int)$r['ID_Prestamo'],'codigo'=>$r['Codigo_Prestamo'],'fecha_prestamo'=>$r['Fecha_Prestamo'],'estado'=>$r['Estado'],'usuario'=>$r['Usuario_Login'],'herramienta'=>$r['Herramienta']];
echo json_encode($out, JSON_UNESCAPED_UNICODE);
