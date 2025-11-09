<?php
session_start();
require_once __DIR__ . '/db_config.php';

$id_usuario = $_SESSION['id_usuario'] ?? null;

if ($id_usuario) {
  $accion = "Cierre de sesión manual";
  $fecha = date('Y-m-d H:i:s');

  $stmt = $mysqli->prepare("INSERT INTO Bitacora (ID_Usuario, Accion, Fecha) VALUES (?, ?, ?)");
  $stmt->bind_param("iss", $id_usuario, $accion, $fecha);
  $stmt->execute();
  $stmt->close();
}

session_destroy();
header("Location: /Proyecto_HandyHub/index.php");
exit;
?>
