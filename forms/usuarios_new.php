<?php
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../config/session_check.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $login = $_POST['Usuario_Login'];
  $rol = $_POST['ID_Rol'];
  $dept = $_POST['ID_Departamento'] ?: null;

  $stmt = $mysqli->prepare("INSERT INTO Usuario (Usuario_Login, ID_Rol, ID_Departamento, Fecha_Creacion) VALUES (?, ?, ?, NOW())");
  $stmt->bind_param("sii", $login, $rol, $dept);
  $stmt->execute();

  header("Location: ../views/usuarios.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Nuevo Usuario - HandyHub</title>
  <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>
  <?php include '../partials/sidebar.php'; ?>

  <div class="main-content">
    <h2>Crear Nuevo Usuario</h2>
    <form method="POST">
      <label>Nombre de usuario:</label>
      <input type="text" name="Usuario_Login" required>

      <label>Rol:</label>
      <select name="ID_Rol">
        <option value="1">Administrador</option>
        <option value="2">Supervisor</option>
        <option value="3">Técnico</option>
        <option value="4">Usuario</option>
      </select>

      <label>Departamento:</label>
      <select name="ID_Departamento">
        <option value="">—</option>
        <option value="1">Almacén</option>
        <option value="2">Mantenimiento</option>
      </select>

      <button type="submit">Crear Usuario</button>
    </form>
  </div>
</body>
</html>
