<?php include '../partials/navbar.php'; ?>
<div style="margin-top:50px;"></div> <!-- Espacio para que no tape el contenido -->


<?php
require_once __DIR__ . '/../config/session_check.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Reportes - HandyHub</title>
  <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>
  <?php include '../partials/sidebar.php'; ?>

  <div class="main-content">
    <h2>Generación de Reportes</h2>
    <p>Aquí podrás generar reportes de inventario, préstamos, mantenimientos, etc.</p>

    <ul>
      <li><a href="../controllers/export_inventario.php">Exportar Inventario</a></li>
      <li><a href="../controllers/export_prestamos.php">Exportar Préstamos</a></li>
      <li><a href="../controllers/export_mantenimientos.php">Exportar Mantenimientos</a></li>
    </ul>
  </div>
  <?php include '../partials/footer.php'; ?>

</body>
</html>
