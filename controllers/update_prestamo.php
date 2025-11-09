<?php
require_once __DIR__ . '/../config/db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = intval($_POST['id']);
  $id_usuario = intval($_POST['id_usuario']);
  $id_herramienta = intval($_POST['id_herramienta']);
  $fecha_prestamo = $_POST['fecha_prestamo'];
  $fecha_entrega = $_POST['fecha_entrega'] !== '' ? $_POST['fecha_entrega'] : null;
  $estado = trim($_POST['estado']);
  $observaciones = trim($_POST['observaciones']);

  if ($id && $id_usuario && $id_herramienta && $fecha_prestamo && $estado) {
    $stmt = $mysqli->prepare("
      UPDATE Prestamo
      SET ID_Usuario = ?, ID_Herramienta = ?, Fecha_Prestamo = ?, Fecha_Entrega = ?, Estado = ?, Observaciones = ?
      WHERE ID_Prestamo = ?
    ");
    $stmt->bind_param("iissssi", $id_usuario, $id_herramienta, $fecha_prestamo, $fecha_entrega, $estado, $observaciones, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: ../views/prestamos.php?actualizado=1");
    exit;
  } else {
    header("Location: ../views/prestamos.php?error=1");
    exit;
  }
}
?>
