<?php
require_once __DIR__ . '/../config/db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = intval($_POST['id']);
  $nombre = trim($_POST['nombre']);
  $descripcion = trim($_POST['descripcion']);

  if ($id && $nombre && $descripcion) {
    $stmt = $mysqli->prepare("UPDATE Rol SET Nombre_Rol = ?, Descripcion = ? WHERE ID_Rol = ?");
    $stmt->bind_param("ssi", $nombre, $descripcion, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: ../views/roles.php?actualizado=1");
    exit;
  } else {
    header("Location: ../views/roles.php?error=1");
    exit;
  }
}
?>
