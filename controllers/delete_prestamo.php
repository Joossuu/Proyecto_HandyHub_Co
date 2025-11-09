<?php
require_once __DIR__ . '/../config/db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = intval($_POST['id']);

  if ($id) {
    $stmt = $mysqli->prepare("DELETE FROM Prestamo WHERE ID_Prestamo = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: ../views/prestamos.php?eliminado=1");
    exit;
  } else {
    header("Location: ../views/prestamos.php?error=1");
    exit;
  }
}
?>
