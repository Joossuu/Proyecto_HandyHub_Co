<?php
// views/mantenimientos.php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: /Proyecto_HandyHub/index.php');
    exit;
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <title>Mantenimientos - HandyHub</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="/Proyecto_HandyHub/assets/styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<?php require_once __DIR__ . '/../partials/sidebar.php'; ?>

<main class="main-wrapper">
  <div class="header-row">
    <div>
      <h1 class="page-title">Mantenimientos</h1>
      <div class="small-muted">Registra y controla mantenimientos</div>
    </div>

    <div class="header-actions">
      <button id="newMaintBtn" class="btn btn-primary">Nuevo mantenimiento</button>
    </div>
  </div>

  <section class="card recent-table">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
      <div style="font-weight:700;">Mantenimientos recientes</div>
      <div style="display:flex;gap:8px;align-items:center;">
        <input id="maintSearch" placeholder="Buscar por ID o nombre de herramienta..." style="padding:8px;border-radius:8px;border:1px solid #e6e9ef;" />
        <button id="maintClear" class="btn small outline">Limpiar</button>
      </div>
    </div>

    <div id="maintMsg" style="color:#b91c1c;margin-bottom:10px;"></div>

    <table class="table-min" id="maintTable">
      <thead>
        <tr>
          <th style="width:70px;">ID</th>
          <th>Herramienta</th>
          <th>Técnico</th>
          <th>Tipo</th>
          <th>Fecha inicio</th>
          <th>Estado</th>
          <th style="width:120px;">Acciones</th>
        </tr>
      </thead>
      <tbody id="maintTableBody">
        <tr><td colspan="7" style="color:#94a3b8;padding:14px;">Cargando...</td></tr>
      </tbody>
    </table>
  </section>

  <footer class="app-footer">© <?= date('Y') ?> HandyHub. Todos los derechos reservados.</footer>
</main>

<!-- Modal (simple) -->
<div id="maintModal" class="modal" style="display:none;">
  <div class="modal-backdrop"></div>
  <div class="modal-panel modal-card" role="dialog" aria-modal="true">
    <button class="modal-close" id="maintCloseBtn">✕</button>
    <h3 style="margin-top:0;">Nuevo mantenimiento</h3>

    <form id="maintForm">
      <div style="display:flex;gap:16px;margin-bottom:12px;">
        <div style="flex:1">
          <label>Herramienta (ID o código)</label>
          <input id="fm_id_herr" name="id_herramienta" class="input" placeholder="ID o código" />
        </div>
        <div style="width:180px">
          <label>Técnico (ID)</label>
          <input id="fm_id_tecnico" name="id_tecnico" class="input" placeholder="ID técnico" />
        </div>
      </div>

      <div style="display:flex;gap:16px;margin-bottom:12px;">
        <div style="flex:1">
          <label>Tipo</label>
          <select id="fm_tipo" name="tipo" class="input">
            <option>Preventivo</option>
            <option>Correctivo</option>
            <option>Calibración</option>
          </select>
        </div>
        <div style="width:180px">
          <label>Estado</label>
          <select id="fm_estado" name="estado" class="input">
            <option>Programado</option>
            <option>En Proceso</option>
            <option>Completado</option>
            <option>Cancelado</option>
          </select>
        </div>
      </div>

      <div style="margin-bottom:12px;">
        <label>Observaciones</label>
        <textarea id="fm_obs" name="observaciones" rows="4" class="input" placeholder="Observaciones..."></textarea>
      </div>

      <div style="display:flex;justify-content:flex-end;gap:8px;">
        <button type="button" id="maintCancel" class="btn small outline">Cancelar</button>
        <button type="submit" class="btn small">Guardar</button>
      </div>

      <div id="maintFormMsg" style="margin-top:8px;color:#b91c1c;"></div>
    </form>
  </div>
</div>

<script src="/Proyecto_HandyHub/assets/mantenimientos.js"></script>
</body>
</html>
