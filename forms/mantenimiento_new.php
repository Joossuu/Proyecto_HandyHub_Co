<?php
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../config/session_check.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $herramienta = $_POST['ID_Herramienta'];
  $tecnico = $_POST['ID_Tecnico'];
  $tipo = $_POST['Tipo_Mantenimiento'];
  $inicio = $_POST['Fecha_Inicio'];
  $fin = $_POST['Fecha_Finalizacion'] ?: null;

  $stmt = $mysqli->prepare("INSERT INTO Mantenimiento (ID_Herramienta, ID_Tecnico, Tipo_Mantenimiento, Fecha_Inicio, Fecha_Finalizacion) VALUES (?, ?, ?, ?, ?)");
  $stmt->bind_param("iisss", $herramienta, $tecnico, $tipo, $inicio, $fin);
  $stmt->execute();

  header("Location: ../views/mantenimiento.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Nuevo Mantenimiento - HandyHub</title>
  <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>
  <?php include '../partials/sidebar.php'; ?>

  <div class="main-content">
    <h2>Registrar Mantenimiento</h2>
    <form method="POST">
      <label>Herramienta:</label>
      <select name="ID_Herramienta">
        <option value="1">Taladro</option>
        <option value="2">Multímetro</option>
      </select>

      <label>Técnico:</label>
      <select name="ID_Tecnico">
        <option value="2">test</option>
        <option value="3">test2</option>
      </select>

      <label>Tipo de Mantenimiento:</label>
      <input type="text" name="Tipo_Mantenimiento" required>

      <label>Fecha de Inicio:</label>
      <input type="date" name="Fecha_Inicio" required>

      <label>Fecha de Finalización:</label>
      <input type="date" name="Fecha_Finalizacion">

      <button type="submit">Registrar</button>
    </form>
  </div>
</body>
</html>
