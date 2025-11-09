<?php
// controllers/mantenimientos_api.php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Ajusta la ruta si tu config está en otro lugar
require_once __DIR__ . '/../config/db.php'; // debe exponer $pdo (PDO)

function json($v){ echo json_encode($v, JSON_UNESCAPED_UNICODE); exit; }
function addBitacora($pdo, $usuarioId, $accion) {
    try {
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $st = $pdo->prepare("INSERT INTO Bitacora (ID_Usuario, Accion, IP_Origen) VALUES (?, ?, ?)");
        $st->execute([$usuarioId, $accion, $ip]);
    } catch(Exception $e){ /* no fatal */ }
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_REQUEST['action'] ?? '';

try {
  if ($method === 'GET') {
    if ($action === 'list') {
      // lista recientes
      $sql = "SELECT m.*, h.Nombre AS Herramienta_Nombre, u.Usuario_Login AS Tecnico_Login
              FROM Mantenimiento m
              LEFT JOIN Herramienta h ON h.ID_Herramienta = m.ID_Herramienta
              LEFT JOIN Usuario u ON u.ID_Usuario = m.ID_Tecnico
              ORDER BY m.Fecha_Inicio DESC LIMIT 200";
      $stmt = $pdo->prepare($sql);
      $stmt->execute();
      $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
      json(['ok'=>true,'data'=>$rows]);
    }

    if ($action === 'get') {
      $id = intval($_GET['id'] ?? 0);
      if (!$id) json(['ok'=>false,'msg'=>'id requerido']);
      $stmt = $pdo->prepare("SELECT * FROM Mantenimiento WHERE ID_Mantenimiento = ?");
      $stmt->execute([$id]);
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      json(['ok'=>true,'data'=>$row]);
    }

    json(['ok'=>false,'msg'=>'action GET desconocida']);
  }

  if ($method === 'POST') {
    // leer payload (json preferido)
    $input = $_POST;
    if (empty($input)) {
      $raw = file_get_contents('php://input');
      $input = json_decode($raw, true) ?? [];
    }

    // CREATE
    if ($action === 'create') {
      $id_h = intval($input['ID_Herramienta'] ?? 0);
      $id_tec = isset($input['ID_Tecnico']) && $input['ID_Tecnico'] !== '' ? intval($input['ID_Tecnico']) : null;
      $tipo = $input['Tipo_Mantenimiento'] ?? 'Preventivo';
      $fecha_inicio = $input['Fecha_Inicio'] ?? date('Y-m-d H:i:s');
      $estado = $input['Estado'] ?? 'Programado';
      $obs = $input['Observaciones'] ?? null;

      $pdo->beginTransaction();
      $stmt = $pdo->prepare("INSERT INTO Mantenimiento (ID_Herramienta, ID_Tecnico, Tipo_Mantenimiento, Fecha_Inicio, Estado, Observaciones) VALUES (?,?,?,?,?,?)");
      $stmt->execute([$id_h, $id_tec, $tipo, $fecha_inicio, $estado, $obs]);
      $mid = $pdo->lastInsertId();

      // actualizar estado de herramienta a 'Mantenimiento'
      if ($id_h) {
        $u = $pdo->prepare("UPDATE Herramienta SET Estado = 'Mantenimiento' WHERE ID_Herramienta = ?");
        $u->execute([$id_h]);
      }

      // bitacora
      $user = $_SESSION['ID_Usuario'] ?? null;
      addBitacora($pdo, $user, "Creó mantenimiento ID {$mid} para herramienta ID {$id_h}");

      $pdo->commit();
      json(['ok'=>true,'msg'=>'Mantenimiento creado','id'=>$mid]);
    }

    // UPDATE
    if ($action === 'update') {
      $id = intval($input['ID_Mantenimiento'] ?? $input['id'] ?? 0);
      if (!$id) json(['ok'=>false,'msg'=>'id requerido']);

      $allowed = ['ID_Tecnico','Fecha_Finalizacion','Fecha_Proximo_Mantenimiento','Estado','Resultado','Observaciones','Tipo_Mantenimiento'];
      $sets = [];
      $vals = [];
      foreach ($allowed as $f) {
        if (array_key_exists($f, $input)) {
          $sets[] = "$f = ?";
          $vals[] = $input[$f];
        }
      }
      if (empty($sets)) json(['ok'=>false,'msg'=>'Nada para actualizar']);
      $vals[] = $id;
      $sql = "UPDATE Mantenimiento SET " . implode(',', $sets) . " WHERE ID_Mantenimiento = ?";
      $stmt = $pdo->prepare($sql);
      $stmt->execute($vals);

      // si se marcó completado -> set herramienta disponible
      if (isset($input['Estado']) && $input['Estado'] === 'Completado') {
        $g = $pdo->prepare("SELECT ID_Herramienta FROM Mantenimiento WHERE ID_Mantenimiento = ?");
        $g->execute([$id]);
        $hid = $g->fetchColumn();
        if ($hid) {
          $u2 = $pdo->prepare("UPDATE Herramienta SET Estado = 'Disponible' WHERE ID_Herramienta = ?");
          $u2->execute([$hid]);
        }
      }

      $user = $_SESSION['ID_Usuario'] ?? null;
      addBitacora($pdo, $user, "Actualizó mantenimiento ID {$id}");

      json(['ok'=>true,'msg'=>'Actualizado']);
    }

    // DELETE
    if ($action === 'delete') {
      $id = intval($input['ID_Mantenimiento'] ?? $input['id'] ?? 0);
      if (!$id) json(['ok'=>false,'msg'=>'id requerido']);
      // opcional: antes obtener herramienta y/o estado para revertir algo
      $stmt = $pdo->prepare("DELETE FROM Mantenimiento WHERE ID_Mantenimiento = ?");
      $stmt->execute([$id]);
      $user = $_SESSION['ID_Usuario'] ?? null;
      addBitacora($pdo, $user, "Eliminó mantenimiento ID {$id}");
      json(['ok'=>true,'msg'=>'Eliminado']);
    }

    json(['ok'=>false,'msg'=>'action POST desconocida']);
  }

  json(['ok'=>false,'msg'=>'Método no soportado']);
} catch (Exception $e) {
  if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
  json(['ok'=>false,'msg'=>'Error: '.$e->getMessage()]);
}
