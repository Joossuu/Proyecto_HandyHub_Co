<div id="editModal" class="modal" style="display:none;">
  <div class="modal-content">
    <span class="close" onclick="closeModal()">&times;</span>
    <h3>Editar Usuario</h3>
    <form id="editUserForm" method="POST" action="../controllers/update_user.php">
      <input type="hidden" name="ID_Usuario" id="editID">

      <label>Nombre de usuario:</label>
      <input type="text" name="Usuario_Login" id="editLogin" required>

      <label>Rol:</label>
      <select name="ID_Rol" id="editRol">
        <option value="1">Administrador</option>
        <option value="2">Supervisor</option>
        <option value="3">Técnico</option>
        <option value="4">Usuario</option>
      </select>

      <label>Departamento:</label>
      <select name="ID_Departamento" id="editDept">
        <option value="">—</option>
        <option value="1">Almacén</option>
        <option value="2">Mantenimiento</option>
      </select>

      <button type="submit">Guardar cambios</button>
    </form>
  </div>
</div>
