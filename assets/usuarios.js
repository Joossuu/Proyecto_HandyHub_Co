// assets/usuarios.js
(() => {
  const apiList = '/Proyecto_HandyHub/controllers/usuarios_data.php';
  const apiRoles = '/Proyecto_HandyHub/controllers/roles_data.php';
  const apiCode = '/Proyecto_HandyHub/controllers/usuarios_code.php';
  const form = document.getElementById('userForm');
  const modal = document.getElementById('userModal');
  const tbody = document.getElementById('usersTbody');
  const searchBox = document.getElementById('searchBox');
  const btnClear = document.getElementById('btnClear');
  const btnNewUser = document.getElementById('btnNewUser');
  const closeModal = document.getElementById('closeModal');
  const cancelBtn = document.getElementById('cancelBtn');

  function fetchJSON(url, opts) {
    return fetch(url, opts).then(r => r.json());
  }

  function renderTable(data) {
    if (!data || data.length === 0) {
      tbody.innerHTML = '<tr><td colspan="7">No hay usuarios.</td></tr>';
      return;
    }
    tbody.innerHTML = data.map(u => {
      return `<tr>
        <td>${u.ID_Usuario}</td>
        <td>${u.Codigo_Usuario}</td>
        <td>${u.Usuario_Login}</td>
        <td>${u.Email || ''}</td>
        <td>${u.Rol_Nombre || ''}</td>
        <td>${(u.Fecha_Creacion||'').split(' ')[0]}</td>
        <td>
          <button class="btn btn-small edit-btn" data-id="${u.ID_Usuario}"><i class="fa fa-pen"></i></button>
          <button class="btn btn-small delete-btn" data-id="${u.ID_Usuario}"><i class="fa fa-trash"></i></button>
        </td>
      </tr>`;
    }).join('');
    // attach listeners (edición borrado)
    document.querySelectorAll('.edit-btn').forEach(b => b.addEventListener('click', onEdit));
    document.querySelectorAll('.delete-btn').forEach(b => b.addEventListener('click', onDelete));
  }

  function loadTable(q = '') {
    const url = q ? apiList + '?search=' + encodeURIComponent(q) : apiList;
    fetchJSON(url).then(res => {
      if (res.success) renderTable(res.data);
      else tbody.innerHTML = '<tr><td colspan="7">Error cargando usuarios.</td></tr>';
    }).catch(e => {
      tbody.innerHTML = '<tr><td colspan="7">Error de red.</td></tr>';
      console.error(e);
    });
  }

  function loadRoles() {
    fetchJSON(apiRoles).then(res => {
      const sel = document.getElementById('f_rol');
      if (!res || !res.data) return;
      sel.innerHTML = '<option value="">-- Seleccionar rol --</option>';
      res.data.forEach(r => {
        // asumo {ID_Rol, Nombre_Rol}
        sel.innerHTML += `<option value="${r.ID_Rol}">${r.Nombre_Rol}</option>`;
      });
    }).catch(e => console.error(e));
  }

  function openModal() {
    // pedir codigo nuevo
    fetchJSON(apiCode).then(res => {
      if (res && res.code) {
        document.getElementById('f_codigo').value = res.code;
      }
    }).catch(()=>{});
    modal.style.display = 'flex';
  }

  function closeModalFn() {
    modal.style.display = 'none';
    form.reset();
  }

  // handlers
  btnNewUser.addEventListener('click', () => {
    openModal();
  });
  closeModal.addEventListener('click', closeModalFn);
  cancelBtn.addEventListener('click', closeModalFn);

  form.addEventListener('submit', e => {
    e.preventDefault();
    const formData = new FormData(form);
    // POST a tu endpoint create_user (ajusta ruta si tu archivo se llama distinto)
    fetch('/Proyecto_HandyHub/controllers/create_user.php', {
      method: 'POST',
      body: formData
    }).then(r => r.json()).then(res => {
      if (res.success) {
        loadTable();
        closeModalFn();
        alert('Usuario creado correctamente');
      } else {
        alert('Error: ' + (res.message || 'ver consola'));
      }
    }).catch(err => {
      console.error(err);
      alert('Error de red al crear usuario.');
    });
  });

  function onEdit(e) {
    const id = e.currentTarget.dataset.id;
    // llamar a endpoint para traer usuario por id (crea controller si no existe)
    fetchJSON(`/Proyecto_HandyHub/controllers/usuarios_get.php?id=${id}`).then(res=>{
      if (res.success && res.data) {
        const d = res.data;
        document.getElementById('f_usuario').value = d.Usuario_Login;
        document.getElementById('f_codigo').value = d.Codigo_Usuario;
        document.getElementById('f_email').value = d.Email || '';
        document.getElementById('f_rol').value = d.ID_Rol || '';
        document.getElementById('f_estado').value = d.Estado || 'Activo';
        openModal();
      } else alert('No se pudo cargar usuario');
    });
  }

  function onDelete(e) {
    const id = e.currentTarget.dataset.id;
    if (!confirm('¿Eliminar usuario?')) return;
    fetch('/Proyecto_HandyHub/controllers/delete_user.php', {
      method:'POST', body: new URLSearchParams({id})
    }).then(r=>r.json()).then(res=>{
      if (res.success) loadTable(); else alert('Error: '+res.message);
    }).catch(err=>{console.error(err); alert('Error red');});
  }

  // search
  searchBox.addEventListener('input', () => {
    const q = searchBox.value.trim();
    loadTable(q);
  });
  btnClear.addEventListener('click', ()=>{ searchBox.value=''; loadTable(); });

  // init
  loadTable();
  loadRoles();
})();
