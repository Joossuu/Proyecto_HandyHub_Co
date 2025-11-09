<?php
require_once __DIR__ . '/../config/db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = intval($_POST['id']);

  if ($id) {
    // Eliminar físicamente el rol
    $stmt = $mysqli->prepare("DELETE FROM Rol WHERE ID_Rol = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: ../views/roles.php?eliminado=1");
    exit;
  } else {
    header("Location: ../views/roles.php?error=1");
    exit;
  }
}
?>
