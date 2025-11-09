<?php
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../config/session_check.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nombre = $_POST['Nombre'];
  $estado = $_POST['Estado'];
  $categoria = $_POST['ID_Categoria'];
  $ubicacion = $_POST['Ubicacion'];

  $stmt = $mysqli->prepare("INSERT INTO Herramienta (Nombre, Estado, ID_Categoria, Ubicacion, Fecha_Creacion) VALUES (?, ?, ?, ?, NOW())");
  $stmt->bind_param("ssis", $nombre, $estado, $categoria, $ubicacion);
  $stmt->execute();

  header("Location: ../views/inventario.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Nueva Herramienta - HandyHub</title>
  <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>
  <?php include '../partials/sidebar.php'; ?>

  <div class="main-content">
    <h2>Agregar Herramienta</h2>
    <form method="POST">
      <label>Nombre:</label>
      <input type="text" name="Nombre" required>

      <label>Estado:</label>
      <select name="Estado">
        <option value="Disponible">Disponible</option>
        <option value="Prestada">Prestada</option>
        <option value="En mantenimiento">En mantenimiento</option>
      </select>

      <label>Categoría:</label>
      <select name="ID_Categoria">
        <option value="1">Manual</option>
        <option value="2">Eléctrica</option>
      </select>

      <label>Ubicación:</label>
      <input type="text" name="Ubicacion" required>

      <button type="submit">Agregar</button>
    </form>
  </div>
</body>
</html>
