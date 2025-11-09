// assets/herramientas.js
(() => {
  const module = 'herramientas';
  const base = '/Proyecto_HandyHub/controllers/' + module + '_data.php';
  function $ (s) { return document.querySelector(s); }

  async function openCreate() {
    const form = document.createElement('div');
    form.innerHTML = `
      <div style="margin-bottom:8px"><label>Código</label><input id="h_code" class="input" /></div>
      <div style="margin-bottom:8px"><label>Nombre</label><input id="h_name" class="input" /></div>
      <div style="margin-bottom:8px"><label>Ubicación</label><input id="h_loc" class="input" /></div>
      <div style="margin-bottom:8px"><label>Estado</label>
        <select id="h_state" class="input"><option>Disponible</option><option>Prestado</option><option>Mantenimiento</option><option>Dañado</option></select>
      </div>
      <div style="text-align:right"><button id="cancel" class="btn small outline">Cancelar</button> <button id="save" class="btn small">Guardar</button></div>
    `;
    const modal = window.hhOpenModal ? window.hhOpenModal('Nueva herramienta', form) : null;
    if (!modal) alert('Implementa openModal');
    form.querySelector('#cancel').addEventListener('click', ()=>modal && modal.remove());
    form.querySelector('#save').addEventListener('click', async () => {
      const payload = { codigo: form.querySelector('#h_code').value.trim(), nombre: form.querySelector('#h_name').value.trim(), ubicacion: form.querySelector('#h_loc').value.trim(), estado: form.querySelector('#h_state').value.trim() };
      try {
        const res = await fetch(base, { method:'POST', credentials:'same-origin', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload)});
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
