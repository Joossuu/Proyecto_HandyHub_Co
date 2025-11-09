<?php include '../partials/navbar.php'; ?>
<div style="margin-top:50px;"></div> <!-- Espacio para que no tape el contenido -->


<?php
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../config/session_check.php';

$sql = "SELECT h.ID_Herramienta, h.Nombre, h.Estado, c.Nombre AS Categoria, h.Ubicacion, h.Fecha_Creacion
        FROM Herramienta h
        LEFT JOIN Categoria c ON h.ID_Categoria = c.ID_Categoria";
$result = $mysqli->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Inventario - HandyHub</title>
  <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>
  <?php include '../partials/sidebar.php'; ?>

  <div class="main-content">
    <h2>Inventario</h2>
    <table>
      <thead>
        <tr>
          <th>Nombre</th>
          <th>Estado</th>
          <th>Categoría</th>
          <th>Ubicación</th>
          <th>Creado</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['Nombre']) ?></td>
          <td><?= htmlspecialchars($row['Estado']) ?></td>
          <td><?= htmlspecialchars($row['Categoria']) ?></td>
          <td><?= htmlspecialchars($row['Ubicacion']) ?></td>
          <td><?= htmlspecialchars($row['Fecha_Creacion']) ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <?php include '../partials/footer.php'; ?>

</body>
</html>
