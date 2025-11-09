<?php
require_once __DIR__ . '/../config/session_check.php';
require_once __DIR__ . '/../config/db_config.php';

$prestamos = $mysqli->query("
  SELECT P.ID_Prestamo, P.ID_Usuario, P.ID_Herramienta,
         H.Nombre AS Herramienta, U.Usuario_Login AS Usuario,
         P.Fecha_Prestamo, P.Fecha_Entrega, P.Estado, P.Observaciones
  FROM Prestamo P
  JOIN Herramienta H ON P.ID_Herramienta = H.ID_Herramienta
  JOIN Usuario U ON P.ID_Usuario = U.ID_Usuario
  ORDER BY P.ID_Prestamo DESC
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión de Préstamos</title>
  <link rel="stylesheet" href="../assets/styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
  <?php include '../partials/sidebar.php'; ?>

  <div class="main-content">
    <div class="usuarios-header">
      <h2>📦 Gestión de Préstamos</h2>
      <button class="crear-btn" onclick="abrirModal('modalCrear')">+ Registrar préstamo</button>
    </div>

    <?php if (isset($_GET['exito'])): ?>
      <div class="alert success">✅ Préstamo registrado correctamente</div>
    <?php elseif (isset($_GET['actualizado'])): ?>
      <div class="alert success">✅ Préstamo actualizado</div>
    <?php elseif (isset($_GET['eliminado'])): ?>
      <div class="alert success">🗑️ Préstamo eliminado</div>
    <?php elseif (isset($_GET['error'])): ?>
      <div class="alert error">❌ Faltan datos o hubo un error</div>
    <?php endif; ?>

    <div class="usuarios-busqueda">
      <input type="text" id="buscador" placeholder="Buscar por usuario, herramienta o estado...">
    </div>

    <table class="usuarios-tabla" id="tablaPrestamos">
      <thead>
        <tr>
          <th>Usuario</th>
          <th>Herramienta</th>
          <th>Fecha préstamo</th>
          <th>Fecha entrega</th>
          <th>Estado</th>
          <th>Observaciones</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $prestamos->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['Usuario']) ?></td>
            <td><?= htmlspecialchars($row['Herramienta']) ?></td>
            <td><?= htmlspecialchars($row['Fecha_Prestamo']) ?></td>
            <td><?= htmlspecialchars($row['Fecha_Entrega'] ?? '-') ?></td>
            <td><?= htmlspecialchars($row['Estado']) ?></td>
            <td><?= htmlspecialchars($row['Observaciones']) ?></td>
            <td class="acciones">
              <button title="Editar" onclick="abrirModalEditar(
                <?= $row['ID_Prestamo'] ?>,
                <?= $row['ID_Usuario'] ?>,
                <?= $row['ID_Herramienta'] ?>,
                '<?= htmlspecialchars($row['Fecha_Prestamo'], ENT_QUOTES) ?>',
                '<?= htmlspecialchars($row['Fecha_Entrega'], ENT_QUOTES) ?>',
                '<?= htmlspecialchars($row['Estado'], ENT_QUOTES) ?>',
                '<?= htmlspecialchars($row['Observaciones'], ENT_QUOTES) ?>'
              )"><i class="fas fa-edit"></i></button>

              <button title="Eliminar" onclick="abrirModalEliminar(<?= $row['ID_Prestamo'] ?>)">
                <i class="fas fa-trash-alt"></i>
              </button>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <?php include '../partials/footer.php'; ?>

  <!-- Modal Crear -->
  <div class="modal-overlay" id="modalCrear">
    <div class="modal">
      <h3>Registrar préstamo</h3>
      <form action="../controllers/create_prestamo.php" method="POST">
        <select name="id_usuario" required>
          <option value="">Seleccionar usuario</option>
          <?php
          $usuarios = $mysqli->query("SELECT ID_Usuario, Usuario_Login FROM Usuario");
          while ($u = $usuarios->fetch_assoc()) {
            echo "<option value='{$u['ID_Usuario']}'>{$u['Usuario_Login']}</option>";
          }
          ?>
        </select>

        <select name="id_herramienta" required>
          <option value="">Seleccionar herramienta</option>
          <?php
          $herramientas = $mysqli->query("SELECT ID_Herramienta, Nombre FROM Herramienta");
          while ($h = $herramientas->fetch_assoc()) {
            echo "<option value='{$h['ID_Herramienta']}'>{$h['Nombre']}</option>";
          }
          ?>
        </select>

        <input type="datetime-local" name="fecha_prestamo" required>
        <textarea name="observaciones" placeholder="Observaciones"></textarea>

        <div class="modal-actions">
          <button type="button" class="cancel-btn" onclick="cerrarModal('modalCrear')">Cancelar</button>
          <button type="submit" class="save-btn">Guardar</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal Editar -->
  <div class="modal-overlay" id="modalEditar">
    <div class="modal">
      <h3>Editar préstamo</h3>
      <form action="../controllers/update_prestamo.php" method="POST">
        <input type="hidden" name="id" id="editId">

        <label>Usuario</label>
        <select name="id_usuario" id="editUsuario">
          <?php
          $usuarios = $mysqli->query("SELECT ID_Usuario, Usuario_Login FROM Usuario");
          while ($u = $usuarios->fetch_assoc()) {
            echo "<option value='{$u['ID_Usuario']}'>{$u['Usuario_Login']}</option>";
          }
          ?>
        </select>

        <label>Herramienta</label>
        <select name="id_herramienta" id="editHerramienta">
          <?php
          $herramientas = $mysqli->query("SELECT ID_Herramienta, Nombre FROM Herramienta");
          while ($h = $herramientas->fetch_assoc()) {
            echo "<option value='{$h['ID_Herramienta']}'>{$h['Nombre']}</option>";
          }
          ?>
        </select>

        <label>Fecha préstamo</label>
        <input type="datetime-local" name="fecha_prestamo" id="editPrestamo" required>

        <label>Fecha entrega</label>
        <input type="datetime-local" name="fecha_entrega" id="editEntrega">

        <label>Estado</label>
        <select name="estado" id="editEstado">
          <option value="Activo">Activo</option>
          <option value="Finalizado">Finalizado</option>
          <option value="Retrasado">Retrasado</option>
        </select>

        <label>Observaciones</label>
        <textarea name="observaciones" id="editObs"></textarea>

        <div class="modal-actions">
          <button type="button" class="cancel-btn" onclick="cerrarModal('modalEditar')">Cancelar</button>
          <button type="submit" class="save-btn">Actualizar</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal Eliminar -->
  <div class="modal-overlay" id="modalEliminar">
    <div class="modal">
      <form action="../controllers/delete_prestamo.php" method="POST">
        <input type="hidden" name="id" id="deleteId">
        <h3>¿Eliminar este préstamo?</h3>
        <div class="modal-actions">
          <button type="button" class="cancel-btn" onclick="cerrarModal('modalEliminar')">Cancelar</button>
          <button type="submit" class="delete-btn">Eliminar</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    document.getElementById('buscador').addEventListener('input', function () {
      const filtro = this.value.toLowerCase();
      const filas = document.querySelectorAll('#tablaPrestamos tbody tr');
      filas.forEach(fila => {
        const texto = fila.textContent.toLowerCase();
        fila.style.display = texto.includes(filtro) ? '' : 'none';
      });
    });

    function abrirModal(id) {
      document.getElementById(id).style.display = 'flex';
    }

    function cerrarModal(id) {
      document.getElementById(id).style
            document.getElementById(id).style.display = 'none';
    }

    function abrirModalEditar(id, usuarioId, herramientaId, prestamo, entrega, estado, obs) {
      document.getElementById('editId').value = id;
      document.getElementById('editUsuario').value = usuarioId;
      document.getElementById('editHerramienta').value = herramientaId;
      document.getElementById('editPrestamo').value = prestamo.replace(' ', 'T');
      document.getElementById('editEntrega').value = entrega ? entrega.replace(' ', 'T') : '';
      document.getElementById('editEstado').value = estado;
      document.getElementById('editObs').value = obs;
      abrirModal('modalEditar');
    }

    function abrirModalEliminar(id) {
      document.getElementById('deleteId').value = id;
      abrirModal('modalEliminar');
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
