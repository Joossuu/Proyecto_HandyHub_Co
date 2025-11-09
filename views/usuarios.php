<?php
// views/usuarios.php
// Vista principal de Usuarios
// Ajusta permisos/session checks según tu app
require_once __DIR__ . '/../config/session_check.php'; // si tienes
// incluir partials (sidebar, navbar) con ruta segura
$root = dirname(__DIR__); // carpeta proyecto (contiene forms, assets, controllers...)
$formsPartials = $root . '/forms/partials';

?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <title>Usuarios - HandyHub</title>
  <link rel="stylesheet" href="/Proyecto_HandyHub/assets/styles.css">
  <link rel="stylesheet" href="/Proyecto_HandyHub/assets/vendor/all.min.css">
</head>
<body class="">
<?php
// Sidebar: si existe el archivo lo incluimos, si no mostramos una nota (evita warnings)
$sidebarFile = $formsPartials . '/sidebar.php';
if (file_exists($sidebarFile)) {
    include $sidebarFile;
} else {
    // no romper la vista si falta el partial
    // echo "<!-- sidebar no encontrado: $sidebarFile -->";
}
?>
<div class="main-wrapper">
  <div class="header-row">
    <div>
      <h1 class="page-title">Usuarios</h1>
      <div class="muted">Gestiona cuentas y roles</div>
    </div>
    <div>
      <button id="btnNewUser" class="btn btn-primary">Nuevo usuario</button>
    </div>
  </div>

  <div class="recent-table">
    <div class="card">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
        <strong>Usuarios recientes</strong>
        <div>
          <input id="searchBox" placeholder="Buscar por usuario o código" style="padding:8px;border-radius:6px;border:1px solid #e6e9ef;">
          <button id="btnClear" class="btn">Limpiar</button>
        </div>
      </div>

      <table id="usersTable" class="table-min" style="width:100%;">
        <thead>
          <tr>
            <th>ID</th>
            <th>Código</th>
            <th>Usuario</th>
            <th>Email</th>
            <th>Rol</th>
            <th>Fecha creación</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody id="usersTbody">
          <tr><td colspan="7">Cargando...</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal (nuevo/editar) -->
<div id="userModal" class="modal" style="display:none;">
  <div class="modal-backdrop"></div>
  <div class="modal-panel">
    <button class="modal-close" id="closeModal">✕</button>
    <h3>Nuevo usuario</h3>
    <form id="userForm">
      <div style="display:flex;gap:14px;">
        <div style="flex:1;">
          <label>Usuario</label><br>
          <input id="f_usuario" name="usuario" placeholder="Nombre de usuario" style="width:100%;padding:8px;">
          <label style="margin-top:8px;display:block;">Código</label>
          <input id="f_codigo" name="codigo" placeholder="Código usuario" style="width:100%;padding:8px;">
          <small>Se genera automáticamente al crear (puedes editar si lo deseas).</small>
        </div>
        <div style="flex:1;">
          <label>Rol</label><br>
          <select id="f_rol" name="rol" style="width:100%;padding:8px;">
            <option value="">-- Seleccionar rol --</option>
          </select>
          <label style="margin-top:8px;display:block;">Email</label>
          <input id="f_email" name="email" placeholder="correo@ejemplo.com" style="width:100%;padding:8px;">
          <label style="margin-top:8px;display:block;">Estado</label>
          <select id="f_estado" name="estado" style="padding:8px;"><option>Activo</option><option>Inactivo</option></select>
        </div>
      </div>
      <div style="margin-top:12px; text-align:right;">
        <button type="button" id="cancelBtn" class="btn">Cancelar</button>
        <button type="submit" class="btn btn-primary">Guardar</button>
      </div>
    </form>
  </div>
</div>

<script src="/Proyecto_HandyHub/assets/usuarios.js"></script>
</body>
</html>
