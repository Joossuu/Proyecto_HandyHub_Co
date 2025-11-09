<?php
// views/dashboard.php
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
  <title>Dashboard - HandyHub</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <link rel="stylesheet" href="/Proyecto_HandyHub/assets/styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<?php require_once __DIR__ . '/../partials/sidebar.php'; ?>

<main class="main-wrapper">
  <div class="header-row">
    <div class="header-left">
      <h1 class="page-title">Dashboard</h1>
      <div class="small-muted">Bienvenido, <strong><?= htmlspecialchars($_SESSION['usuario']) ?></strong> — Rol: <em><?= htmlspecialchars($_SESSION['rol'] ?? '') ?></em></div>
    </div>

    <div class="header-actions">
      <select id="autoRefreshSelect" title="Auto refresh">
        <option value="off">Auto: Off</option>
        <option value="5">Auto: 5s</option>
        <option value="10">Auto: 10s</option>
        <option value="30">Auto: 30s</option>
      </select>
      <button id="refreshWidgetBtn" class="btn">Refrescar</button>
    </div>
  </div>

  <section class="cards-row" aria-label="Indicadores">
    <div class="card"><div class="label">Préstamos activos</div><div data-key="active_loans" class="big-num">-</div></div>
    <div class="card"><div class="label">Préstamos retrasados</div><div data-key="late_loans" class="big-num">-</div></div>
    <div class="card"><div class="label">En mantenimiento</div><div data-key="in_maint" class="big-num">-</div></div>
    <div class="card"><div class="label">Usuarios activos</div><div data-key="active_users" class="big-num">-</div></div>
  </section>

  <section class="grid-charts">
    <div class="chart-box" aria-hidden="false">
      <canvas id="lineChart" aria-label="Gráfico de préstamos"></canvas>
    </div>

    <aside class="right-column" aria-label="Panel derecho">
      <div class="card">
        <div class="label">Estado de herramientas</div>
        <div class="donut-wrap">
          <canvas id="donutChart"></canvas>
        </div>
        <div class="legend-row">
          <div><span class="legend-dot available"></span> Disponible</div>
          <div><span class="legend-dot not-available"></span> No disponible</div>
        </div>
      </div>

      <div class="card">
        <div class="label">Alertas — Préstamos vencidos</div>
        <div id="alertsList" style="margin-top:8px;"></div>
        <div id="nextDue" style="margin-top:10px;color:#6b7280;font-size:13px;"></div>
      </div>
    </aside>
  </section>

  <section>
    <div class="recent-table">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
        <div style="font-weight:700;">Resumen reciente — Préstamos</div>
      </div>
      <table>
        <thead>
          <tr><th>Código</th><th>Herramienta</th><th>Usuario</th><th>Fecha</th><th>Estado</th></tr>
        </thead>
        <tbody id="recentTableBody"></tbody>
      </table>
    </div>

    <div class="card" style="padding:18px; margin-top:12px;">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
        <div style="font-weight:700;">Gestión de usuarios</div>

        <div style="display:flex; gap:8px; align-items:center;">
          <!-- input de búsqueda que usa el JS -->
          <input id="dashboardSearchUsers" placeholder="Buscar usuario por login..." style="padding:8px;border-radius:8px;border:1px solid #e6e9ef;" />
          <button id="refreshUsersBtn" class="btn">Refrescar</button>
        </div>
      </div>

      <div id="usersRow" class="users-row" aria-live="polite"></div>
      <div id="usersEmpty" style="margin-top:14px;color:#94a3b8;"></div>
    </div>
  </section>

  <footer class="app-footer">© <?= date('Y') ?> HandyHub. Todos los derechos reservados.</footer>
</main>

<script src="/Proyecto_HandyHub/assets/dashboard.js"></script>
</body>
</html>
