<?php
require_once __DIR__ . '/../config/db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id_usuario = intval($_POST['id_usuario']);
  $id_herramienta = intval($_POST['id_herramienta']);
  $fecha_prestamo = $_POST['fecha_prestamo'];
  $observaciones = trim($_POST['observaciones']);

  if ($id_usuario && $id_herramienta && $fecha_prestamo) {
    $stmt = $mysqli->prepare("
      INSERT INTO Prestamo (ID_Usuario, ID_Herramienta, Fecha_Prestamo, Observaciones)
      VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("iiss", $id_usuario, $id_herramienta, $fecha_prestamo, $observaciones);
    $stmt->execute();
    $stmt->close();

    header("Location: ../views/prestamos.php?exito=1");
    exit;
  } else {
    header("Location: ../views/prestamos.php?error=1");
    exit;
  }
}
?>
