<?php
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../config/session_check.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $herramienta = $_POST['ID_Herramienta'];
  $usuario = $_POST['ID_Usuario'];
  $fecha_prestamo = $_POST['Fecha_Prestamo'];
  $fecha_entrega = $_POST['Fecha_Entrega'] ?: null;
  $estado = $_POST['Estado'];

  $stmt = $mysqli->prepare("INSERT INTO Prestamo (ID_Herramienta, ID_Usuario, Fecha_Prestamo, Fecha_Entrega, Estado) VALUES (?, ?, ?, ?, ?)");
  $stmt->bind_param("iisss", $herramienta, $usuario, $fecha_prestamo, $fecha_entrega, $estado);
  $stmt->execute();

  header("Location: ../views/prestamos.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Nuevo Préstamo - HandyHub</title>
  <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>
  <?php include '../partials/sidebar.php'; ?>

  <div class="main-content">
    <h2>Registrar Préstamo</h2>
    <form method="POST">
      <label>Herramienta:</label>
      <select name="ID_Herramienta">
        <option value="1">Taladro</option>
        <option value="2">Multímetro</option>
      </select>

      <label>Usuario:</label>
      <select name="ID_Usuario">
        <option value="2">test</option>
        <option value="3">test2</option>
      </select>

      <label>Fecha de Préstamo:</label>
      <input type="date" name="Fecha_Prestamo" required>

      <label>Fecha de Entrega:</label>
      <input type="date" name="Fecha_Entrega">

      <label>Estado:</label>
      <select name="Estado">
        <option value="Activo">Activo</option>
        <option value="Entregado">Entregado</option>
      </select>

      <button type="submit">Registrar</button>
    </form>
  </div>
</body>
</html>
