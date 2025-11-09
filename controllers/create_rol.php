<?php
require_once __DIR__ . '/../config/db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nombre = trim($_POST['nombre']);
  $descripcion = trim($_POST['descripcion']);

  if ($nombre && $descripcion) {
    // Verificar si ya existe el rol
    $check = $mysqli->prepare("SELECT ID_Rol FROM Rol WHERE Nombre_Rol = ?");
    $check->bind_param("s", $nombre);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
      $check->close();
      header("Location: ../views/roles.php?error1=1");
      exit;
    }
    $check->close();

    // Insertar nuevo rol (sin Estado)
    $stmt = $mysqli->prepare("INSERT INTO Rol (Nombre_Rol, Descripcion) VALUES (?, ?)");
    $stmt->bind_param("ss", $nombre, $descripcion);
    $stmt->execute();
    $stmt->close();

    header("Location: ../views/roles.php?exito=1");
    exit;
  } else {
    header("Location: ../views/roles.php?error=1");
    exit;
  }
}
?>
