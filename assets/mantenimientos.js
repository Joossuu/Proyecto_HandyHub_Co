// assets/mantenimientos.js (versión corregida - fix botón nuevo mantenimiento)
(() => {
  const endpointList = '/Proyecto_HandyHub/controllers/mantenimientos_data.php';
  const endpointCreate = '/Proyecto_HandyHub/controllers/create_mantenimiento.php';
  const endpointUpdate = '/Proyecto_HandyHub/controllers/update_mantenimiento.php';
  const endpointDelete = '/Proyecto_HandyHub/controllers/delete_mantenimiento.php';

  const $ = sel => document.querySelector(sel);
  const $$ = sel => Array.from(document.querySelectorAll(sel));
  function el(tag, attrs = {}, html = '') {
    const e = document.createElement(tag);
    Object.keys(attrs).forEach(k => {
      if (k === 'style') e.style.cssText = attrs[k];
      else e.setAttribute(k, attrs[k]);
    });
    if (html) e.innerHTML = html;
    return e;
  }

  function showMsg(text, danger = false) {
    let container = $('#maintMsg');
    if (!container) {
      const wrapper = $('#maintListWrapper') || document.querySelector('.recent-table') || document.body;
      container = el('div', { id: 'maintMsg' });
      container.style.margin = '8px 0';
      if (wrapper && wrapper.firstChild) wrapper.insertBefore(container, wrapper.firstChild);
      else wrapper.appendChild(container);
    }
    container.style.color = danger ? '#b91c1c' : '#065f46';
    container.textContent = text;
    clearTimeout(container._t);
    container._t = setTimeout(() => { if (container) container.textContent = ''; }, 5000);
  }

  function openModal(title, contentEl) {
    const existing = document.getElementById('maintModal');
    if (existing) existing.remove();

    const backdrop = el('div', { class: 'modal-backdrop', id: 'maintModal', style: 'z-index:1200; display:flex; align-items:center; justify-content:center;' });
    const panel = el('div', { class: 'modal-panel', style: 'position:relative; z-index:1201;' });
    const close = el('button', { class: 'modal-close', type: 'button' }, '✕');
    const h = el('h3', {}, title);
    panel.appendChild(close);
    panel.appendChild(h);
    panel.appendChild(contentEl);
    backdrop.appendChild(panel);
    document.body.appendChild(backdrop);

    close.addEventListener('click', () => backdrop.remove());
    backdrop.addEventListener('click', (ev) => { if (ev.target === backdrop) backdrop.remove(); });

    // comprobar que modal se muestra (debug)
    console.debug('Modal abierto:', title, 'backdrop z-index:', backdrop.style.zIndex);
    return backdrop;
  }

  function formatDateTime(dt) {
    if (!dt) return '';
    const d = new Date(dt);
    if (isNaN(d)) return dt;
    const y = d.getFullYear();
    const m = String(d.getMonth()+1).padStart(2,'0');
    const day = String(d.getDate()).padStart(2,'0');
    const hh = String(d.getHours()).padStart(2,'0');
    const mm = String(d.getMinutes()).padStart(2,'0');
    return `${y}-${m}-${day} ${hh}:${mm}:00`;
  }

  function ensureMarkup() {
    // wrapper & table
    if (!$('#maintListWrapper')) {
      const section = el('section', { id: 'maintListWrapper', class: 'recent-table' });
      section.innerHTML = `
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
          <div style="font-weight:700;">Mantenimientos recientes</div>
          <div style="display:flex;gap:8px;align-items:center;">
            <input id="maintSearch" placeholder="Buscar por ID o nombre de herramienta..." style="padding:8px;border-radius:8px;border:1px solid #e6e9ef;" />
            <button id="maintClear" class="btn small outline" style="margin-left:6px;">Limpiar</button>
          </div>
        </div>
        <div class="card" style="padding:12px;">
          <div id="maintMsg" style="margin-bottom:8px;"></div>
          <table class="table-min" aria-describedby="Mantenimientos recientes">
            <thead>
              <tr>
                <th style="width:60px">ID</th>
                <th>Herramienta</th>
                <th style="width:140px">Técnico</th>
                <th style="width:120px">Tipo</th>
                <th style="width:160px">Fecha inicio</th>
                <th style="width:120px">Estado</th>
                <th style="width:120px;text-align:right">Acciones</th>
              </tr>
            </thead>
            <tbody id="maintTableBody">
              <tr><td colspan="7" style="color:#94a3b8;padding:14px">Cargando...</td></tr>
            </tbody>
          </table>
        </div>
      `;
      const target = document.querySelector('.grid-charts') || document.querySelector('.header-row')?.parentNode || document.querySelector('.main-wrapper') || document.body;
      if (target && target.parentNode) target.parentNode.insertBefore(section, target.nextSibling);
      else document.body.appendChild(section);
    }

    // localizar botón nuevo mantenimiento (no forzamos id)
    let foundBtn = null;
    const knownSelectors = ['#btnNewMaint', '#btnNewMaintenance', '.btn-new', '[data-new-maint]', '.new-maint-btn', '.btn.btn-primary'];
    for (const s of knownSelectors) {
      const qq = document.querySelector(s);
      if (qq) { foundBtn = qq; break; }
    }

    if (!foundBtn) {
      const buttons = Array.from(document.querySelectorAll('button, a'));
      foundBtn = buttons.find(n => n.textContent && n.textContent.trim() === 'Nuevo mantenimiento');
    }

    if (!foundBtn) {
      const btn = el('button', { id: 'btnNewMaint', class: 'btn btn-primary', 'data-maint-button': '1' }, 'Nuevo mantenimiento');
      btn.style.cssText = 'display:inline-block; margin-left:10px;';
      const headerActions = document.querySelector('.header-actions') || document.querySelector('.page-title')?.parentNode;
      if (headerActions) headerActions.appendChild(btn);
      else {
        const wrapper = $('#maintListWrapper');
        if (wrapper) wrapper.insertBefore(btn, wrapper.firstChild);
        else document.body.appendChild(btn);
      }
      foundBtn = btn;
    } else {
      // marcar para que init lo encuentre sin cambiar el id real
      foundBtn.setAttribute('data-maint-button', '1');
    }

    // debug
    console.debug('ensureMarkup: boton encontrado?', !!foundBtn, foundBtn);
  }

  function renderTable(rows = []) {
    const tbody = $('#maintTableBody');
    if (!tbody) return;
    tbody.innerHTML = '';
    if (!rows.length) {
      const tr = el('tr');
      tr.innerHTML = `<td colspan="7" style="color:#94a3b8;padding:14px">No se encontraron mantenimientos.</td>`;
      tbody.appendChild(tr);
      return;
    }

    rows.forEach(r => {
      const tr = el('tr');
      const toolText = r.Codigo_Herramienta ? `${r.Codigo_Herramienta} — ${r.Herramienta}` : (r.Herramienta || '');
      tr.innerHTML = `
        <td>${r.ID_Mantenimiento}</td>
        <td>${escapeHtml(toolText)}</td>
        <td>${escapeHtml(r.Tecnico_Nombre || '')}</td>
        <td>${escapeHtml(r.Tipo_Mantenimiento || r.Tipo || '')}</td>
        <td>${escapeHtml(formatDateTime(r.Fecha_Inicio))}</td>
        <td>${escapeHtml(r.Estado || '')}</td>
        <td style="text-align:right"></td>
      `;
      const actionsTd = tr.querySelector('td:last-child');

      const btnEdit = el('button', { class: 'action-btn', title: 'Editar' });
      btnEdit.innerHTML = '<i class="fa fa-edit"></i>';
      btnEdit.addEventListener('click', () => openEdit(r));

      const btnDelete = el('button', { class: 'action-btn', title: 'Borrar' });
      btnDelete.innerHTML = '<i class="fa fa-trash"></i>';
      btnDelete.addEventListener('click', () => doDelete(r.ID_Mantenimiento));

      actionsTd.appendChild(btnEdit);
      actionsTd.appendChild(btnDelete);

      tbody.appendChild(tr);
    });
  }

  function escapeHtml(s) {
    if (s == null) return '';
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
             .replace(/"/g,'&quot;').replace(/'/g,'&#039;');
  }

  async function loadMaints(q = '') {
    const tbody = $('#maintTableBody');
    if (tbody) tbody.innerHTML = `<tr><td colspan="7" style="color:#94a3b8;padding:14px">Cargando...</td></tr>`;
    try {
      const url = q ? `${endpointList}?q=${encodeURIComponent(q)}` : endpointList;
      const res = await fetch(url, { credentials: 'same-origin' });
      if (!res.ok) throw new Error('HTTP ' + res.status);
      const data = await res.json();
      if (!data || typeof data !== 'object') throw new Error('Respuesta no JSON');
      if (data.success === false) throw new Error(data.message || 'Error en respuesta');

      let rows = [];
      if (Array.isArray(data.data)) rows = data.data;
      else if (Array.isArray(data.mantenimientos)) rows = data.mantenimientos;
      else if (Array.isArray(data)) rows = data;
      renderTable(rows);
      console.debug('loadMaints: rows cargadas', rows.length);
    } catch (err) {
      console.error('Error fetching mantenimientos:', err);
      if (tbody) tbody.innerHTML = `<tr><td colspan="7" style="color:#b91c1c;padding:14px">Error cargando mantenimientos.</td></tr>`;
      showMsg('Error fetching mantenimientos. Revisa consola.', true);
    }
  }

  function openCreate() {
    const form = buildForm();
    const modal = openModal('Nuevo mantenimiento', form.formEl);
    form.onSubmit = async (payload) => {
      try {
        const res = await fetch(endpointCreate, {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.message || 'Error creando');
        modal.remove();
        showMsg('Mantenimiento creado');
        loadMaints($('#maintSearch')?.value.trim() || '');
      } catch (err) {
        console.error(err);
        showMsg('Error creando mantenimiento: ' + err.message, true);
      }
    };
  }

  function openEdit(record) {
    const form = buildForm(record);
    const modal = openModal(`Editar mantenimiento ${record.ID_Mantenimiento}`, form.formEl);
    form.onSubmit = async (payload) => {
      payload.id_mantenimiento = record.ID_Mantenimiento;
      try {
        const res = await fetch(endpointUpdate, {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.message || 'Error actualizando');
        modal.remove();
        showMsg('Mantenimiento actualizado');
        loadMaints($('#maintSearch')?.value.trim() || '');
      } catch (err) {
        console.error(err);
        showMsg('Error actualizando: ' + err.message, true);
      }
    };
  }

  async function doDelete(id) {
    if (!confirm('¿Borrar mantenimiento #' + id + '?')) return;
    try {
      const res = await fetch(endpointDelete, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_mantenimiento: id })
      });
      const data = await res.json();
      if (!data.success) throw new Error(data.message || 'Error borrando');
      showMsg('Mantenimiento borrado');
      loadMaints($('#maintSearch')?.value.trim() || '');
    } catch (err) {
      console.error(err);
      showMsg('Error borrando: ' + err.message, true);
    }
  }

  function buildForm(initial = {}) {
    const formEl = el('div');
    formEl.style.padding = '8px 0';
    formEl.innerHTML = `
      <div style="display:flex;gap:12px;margin-bottom:8px;">
        <div style="flex:1">
          <label style="font-size:13px;color:#334155">Herramienta (ID o código)</label>
          <input id="f_id_herr" class="input" type="text" value="${escapeHtml(initial.ID_Herramienta || initial.Codigo_Herramienta || '')}" placeholder="ID o código" />
        </div>
        <div style="width:160px">
          <label style="font-size:13px;color:#334155">Técnico (ID)</label>
          <input id="f_id_tec" class="input" type="text" value="${escapeHtml(initial.ID_Tecnico || '')}" placeholder="ID técnico" />
        </div>
      </div>
      <div style="display:flex;gap:12px;margin-bottom:8px;">
        <div style="flex:1">
          <label style="font-size:13px;color:#334155">Tipo</label>
          <select id="f_tipo" class="input">
            <option ${ (initial.Tipo_Mantenimiento||'').toLowerCase() === 'preventivo' ? 'selected':'' }>Preventivo</option>
            <option ${ (initial.Tipo_Mantenimiento||'').toLowerCase() === 'correctivo' ? 'selected':'' }>Correctivo</option>
            <option ${ (initial.Tipo_Mantenimiento||'').toLowerCase() === 'calibración' || (initial.Tipo_Mantenimiento||'').toLowerCase()==='calibracion' ? 'selected':'' }>Calibración</option>
          </select>
        </div>
        <div style="width:200px">
          <label style="font-size:13px;color:#334155">Estado</label>
          <select id="f_estado" class="input">
            <option ${ (initial.Estado||'').toLowerCase()==='programado' ? 'selected':'' }>Programado</option>
            <option ${ (initial.Estado||'').toLowerCase()==='en proceso' || (initial.Estado||'').toLowerCase()==='en_proceso' ? 'selected':'' }>En Proceso</option>
            <option ${ (initial.Estado||'').toLowerCase()==='completado' ? 'selected':'' }>Completado</option>
            <option ${ (initial.Estado||'').toLowerCase()==='cancelado' ? 'selected':'' }>Cancelado</option>
          </select>
        </div>
      </div>
      <div style="margin-bottom:8px;">
        <label style="font-size:13px;color:#334155">Observaciones</label>
        <textarea id="f_obs" class="input" rows="4" placeholder="Observaciones...">${escapeHtml(initial.Observaciones || '')}</textarea>
      </div>
      <div style="text-align:right;margin-top:6px;">
        <button id="btnCancel" class="btn small outline">Cancelar</button>
        <button id="btnSave" class="btn small">Guardar</button>
      </div>
    `;

    const btnCancel = formEl.querySelector('#btnCancel');
    const btnSave = formEl.querySelector('#btnSave');

    btnCancel.addEventListener('click', () => {
      const modal = document.getElementById('maintModal');
      if (modal) modal.remove();
    });

    const form = { formEl, onSubmit: null };
    btnSave.addEventListener('click', () => {
      const payload = {
        id_herramienta: formEl.querySelector('#f_id_herr').value.trim(),
        id_tecnico: formEl.querySelector('#f_id_tec').value.trim(),
        tipo: formEl.querySelector('#f_tipo').value.trim(),
        estado: formEl.querySelector('#f_estado').value.trim(),
        observaciones: formEl.querySelector('#f_obs').value.trim()
      };
      if (typeof form.onSubmit === 'function') form.onSubmit(payload);
    });

    return form;
  }

  function init() {
    ensureMarkup();

    // localiza el botón (marca data-maint-button="1" en ensureMarkup)
    const newBtn = document.querySelector('[data-maint-button="1"]');
    if (newBtn) {
      console.debug('init: registrando listener en boton nuevo mantenimiento', newBtn);
      newBtn.addEventListener('click', (e) => {
        e.preventDefault();
        console.debug('Nuevo mantenimiento: click detectado');
        openCreate();
      });
    } else {
      console.warn('init: no se encontró botón nuevo mantenimiento (data-maint-button).');
    }

    const search = document.getElementById('maintSearch');
    const clearBtn = document.getElementById('maintClear');
    if (search) {
      search.addEventListener('keyup', (e) => {
        if (e.key === 'Enter') loadMaints(search.value.trim());
      });
    }
    if (clearBtn) clearBtn.addEventListener('click', () => { if (search) { search.value=''; loadMaints(); } });

    loadMaints();
  }

  document.addEventListener('DOMContentLoaded', init);
  window.hh = window.hh || {};
  window.hh.reloadMaints = loadMaints;

})();
