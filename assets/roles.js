// assets/roles.js
(() => {
  const module = 'roles';
  const base = '/Proyecto_HandyHub/controllers/' + module + '_data.php';
  function $ (s) { return document.querySelector(s); }
  async function openCreate() {
    const form = document.createElement('div');
    form.innerHTML = `
      <div style="margin-bottom:8px"><label>Nombre rol</label><input id="r_name" class="input" /></div>
      <div style="margin-bottom:8px"><label>Descripción</label><textarea id="r_desc" class="input"></textarea></div>
      <div style="text-align:right"><button id="cancel" class="btn small outline">Cancelar</button> <button id="save" class="btn small">Guardar</button></div>
    `;
    const modal = window.hhOpenModal ? window.hhOpenModal('Nuevo rol', form) : null;
    if (!modal) alert('Implementa openModal');
    form.querySelector('#cancel').addEventListener('click', ()=> modal && modal.remove());
    form.querySelector('#save').addEventListener('click', async () => {
      const payload = { nombre: form.querySelector('#r_name').value.trim(), descripcion: form.querySelector('#r_desc').value.trim() };
      try {
        const res = await fetch(base, { method:'POST', credentials:'same-origin', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload) });
        const data = await res.json();
        if (!data.success) throw new Error(data.message||'error');
        modal && modal.remove();
        window.hh && window.hh.reloadModule && window.hh.reloadModule(module);
      } catch (e) { console.error(e); alert('Error: '+e.message); }
    });
  }
  document.addEventListener('DOMContentLoaded', () => {
    const b = document.querySelector(`[data-new-btn="${module}"]`);
    if (b) b.addEventListener('click', openCreate);
  });
})();
