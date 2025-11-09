<?php
require_once __DIR__ . '/../config/db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = intval($_POST['id']);
  $nombre = trim($_POST['nombre']);
  $descripcion = trim($_POST['descripcion']);
  $estado = trim($_POST['estado']);
  $ubicacion = trim($_POST['ubicacion']);

  if ($id && $nombre && $estado) {
    $stmt = $mysqli->prepare("UPDATE Herramienta SET Nombre = ?, Descripcion = ?, Estado = ?, Ubicacion = ? WHERE ID_Herramienta = ?");
    $stmt->bind_param("ssssi", $nombre, $descripcion, $estado, $ubicacion, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: ../views/herramientas.php?actualizado=1");
    exit;
  } else {
    header("Location: ../views/herramientas.php?error=1");
    exit;
  }
}
?>
