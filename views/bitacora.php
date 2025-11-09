<?php
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../config/session_check.php';

$desde = $_GET['desde'] ?? null;
$hasta = $_GET['hasta'] ?? null;

$where = '';
if ($desde && $hasta) {
  $where = "WHERE b.Fecha BETWEEN '$desde 00:00:00' AND '$hasta 23:59:59'";
}

$sql = "SELECT b.ID_Bitacora, u.Usuario_Login, b.Accion, b.Fecha
        FROM Bitacora b
        LEFT JOIN Usuario u ON b.ID_Usuario = u.ID_Usuario
        $where
        ORDER BY b.Fecha DESC";
$result = $mysqli->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Bitácora - HandyHub</title>
  <link rel="stylesheet" href="../assets/styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
  <?php include '../partials/sidebar.php'; ?>

  <div class="main-content">
    <div class="usuarios-header">
      <h2>📋 Bitácora del Sistema</h2>
      <form method="GET" style="display:flex; gap:10px; align-items:center;">
        <label>Desde:</label>
        <input type="date" name="desde" value="<?= htmlspecialchars($desde) ?>">
        <label>Hasta:</label>
        <input type="date" name="hasta" value="<?= htmlspecialchars($hasta) ?>">
        <button type="submit" class="save-btn">Filtrar</button>
        <button type="button" class="crear-btn" onclick="descargarCSV()">📥 Descargar CSV</button>
      </form>
    </div>

    <div class="usuarios-busqueda">
      <input type="text" id="buscador" placeholder="Buscar por usuario o acción...">
    </div>

    <table class="usuarios-tabla" id="tablaBitacora">
      <thead>
        <tr>
          <th>Usuario</th>
          <th>Acción</th>
          <th>Fecha</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['Usuario_Login'] ?? 'Sistema') ?></td>
          <td><?= htmlspecialchars($row['Accion']) ?></td>
          <td><?= htmlspecialchars($row['Fecha']) ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <?php include '../partials/footer.php'; ?>

  <script>
    document.getElementById('buscador').addEventListener('input', function () {
      const filtro = this.value.toLowerCase();
      const filas = document.querySelectorAll('#tablaBitacora tbody tr');
      filas.forEach(fila => {
        const texto = fila.textContent.toLowerCase();
        fila.style.display = texto.includes(filtro) ? '' : 'none';
      });
    });

    function descargarCSV() {
      const filas = document.querySelectorAll('#tablaBitacora tbody tr');
      let csv = "Usuario,Acción,Fecha\n";

      filas.forEach(fila => {
        if (fila.style.display !== 'none') {
          const columnas = fila.querySelectorAll('td');
          const filaCSV = Array.from(columnas).map(td => `"${td.textContent.trim()}"`).join(',');
          csv += filaCSV + "\n";
        }
      });

      const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
      const link = document.createElement('a');
      link.href = URL.createObjectURL(blob);
      link.download = "bitacora.csv";
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    }

    setTimeout(() => {
      const alerta = document.querySelector('.alert');
      if (alerta) {
        alerta.classList.add('ocultar');
        setTimeout(() => alerta.style.display = 'none', 500);
      }
    }, 4000);
  </script>
</body>
</html>
