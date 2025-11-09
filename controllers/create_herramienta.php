<?php
require_once __DIR__ . '/../config/db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nombre = trim($_POST['nombre']);
  $descripcion = trim($_POST['descripcion']);
  $estado = trim($_POST['estado']);
  $ubicacion = trim($_POST['ubicacion']);

  if ($nombre && $estado) {
    // Verificar si ya existe una herramienta con ese nombre
    $check = $mysqli->prepare("SELECT ID_Herramienta FROM Herramienta WHERE Nombre = ?");
    $check->bind_param("s", $nombre);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
      $check->close();
      header("Location: ../views/herramientas.php?error1=1");
      exit;
    }
    $check->close();

    // Insertar herramienta
    $stmt = $mysqli->prepare("INSERT INTO Herramienta (Nombre, Descripcion, Estado, Ubicacion) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nombre, $descripcion, $estado, $ubicacion);
    $stmt->execute();
    $stmt->close();

    header("Location: ../views/herramientas.php?exito=1");
    exit;
  } else {
    header("Location: ../views/herramientas.php?error=1");
    exit;
  }
}
?>
