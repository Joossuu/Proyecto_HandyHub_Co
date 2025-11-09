// assets/dashboard.js
// Dashboard full frontend: charts, fetch, users search local, sidebar toggle + tooltip
(() => {
  // charts globales
  window.lineChart = null;
  window.donutChart = null;

  // Cache de datos recibidos
  let cachedDashboard = { users: [], alerts: [], chart: null, counts: {} };

  // ---------------- Chart creation ----------------
  function createCharts() {
    if (typeof Chart === 'undefined') return;
    try {
      const lctx = document.getElementById('lineChart')?.getContext('2d');
      if (lctx && !window.lineChart) {
        window.lineChart = new Chart(lctx, {
          type: 'line',
          data: { labels: [], datasets: [{ label:'Préstamos/día', data:[], fill:true, backgroundColor:'rgba(99,102,241,0.06)', borderColor:'rgba(99,102,241,1)', tension:0.35, pointRadius:3 }] },
          options: { maintainAspectRatio:false, plugins:{ legend:{ display:true } }, scales:{ x:{ grid:{ color:'#f1f5f9' } }, y:{ beginAtZero:true, ticks:{ stepSize:1 }, grid:{ color:'#f1f5f9' } } } }
        });
      }

      const dctx = document.getElementById('donutChart')?.getContext('2d');
      if (dctx && !window.donutChart) {
        window.donutChart = new Chart(dctx, {
          type: 'doughnut',
          data: { labels:['Disponible','No disponible'], datasets:[{ data:[1,0], backgroundColor:['#2dd4bf','#dae6dd'] }] },
          options: { maintainAspectRatio:false, cutout:'70%', plugins:{ legend:{ display:false } } }
        });
      }
    } catch (e) {
      console.warn('createCharts error', e);
    }
  }

  // ---------------- Fetch dashboard data ----------------
  async function fetchDashboard() {
    const endpoint = '/Proyecto_HandyHub/controllers/dashboard_data.php';
    try {
      const res = await fetch(endpoint, { credentials:'same-origin' });
      if (!res.ok) throw new Error('HTTP ' + res.status);
      const data = await res.json();
      // data esperado: { counts: {...}, recent: [...], users: [...], chart: {...}, tools: {...}, alerts: [...], nextDue: [...] }
      cachedDashboard = Object.assign({}, cachedDashboard, data || {});
      renderDashboard();
    } catch (err) {
      console.error('dashboard fetch error', err);
      // deja visibles indicadores con guiones y mensaje en users
      document.querySelectorAll('[data-key]').forEach(el => el.textContent = '-');
      document.getElementById('usersEmpty').textContent = 'Error al cargar datos (revisa consola).';
    }
  }

  // ---------------- Render functions ----------------
  function renderDashboard() {
    // counts
    document.querySelectorAll('[data-key]').forEach(el => {
      const key = el.getAttribute('data-key');
      el.textContent = (cachedDashboard.counts && (cachedDashboard.counts[key] !== undefined)) ? cachedDashboard.counts[key] : '-';
    });

    // recent table
    const tbody = document.getElementById('recentTableBody');
    if (tbody) {
      tbody.innerHTML = '';
      const rows = Array.isArray(cachedDashboard.recent) ? cachedDashboard.recent : [];
      if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="5" style="color:#94a3b8;padding:14px;">No hay registros recientes.</td></tr>';
      } else {
        rows.forEach(r => {
          const code = r.Codigo_Prestamo ?? r.Codigo ?? '';
          const tool = r.Herramienta ?? '';
          const user = r.Usuario_Login ?? r.Usuario ?? '';
          const fecha = r.Fecha_Prestamo ?? r.Fecha ?? '';
          const estado = r.Estado ?? '';
          const tr = document.createElement('tr');
          tr.innerHTML = `<td>${escapeHtml(code)}</td><td>${escapeHtml(tool)}</td><td>${escapeHtml(user)}</td><td>${escapeHtml(fecha)}</td><td>${escapeHtml(estado)}</td>`;
          tbody.appendChild(tr);
        });
      }
    }

    // charts
    if (window.lineChart && cachedDashboard.chart) {
      window.lineChart.data.labels = cachedDashboard.chart.labels || [];
      window.lineChart.data.datasets[0].data = cachedDashboard.chart.values || [];
      try { window.lineChart.update(); } catch(e) { console.warn(e); }
    }
    if (window.donutChart && cachedDashboard.tools) {
      const d = cachedDashboard.tools.Disponible ?? cachedDashboard.tools.disponible ?? cachedDashboard.tools.available ?? 0;
      const o = cachedDashboard.tools.Otros ?? cachedDashboard.tools.otros ?? cachedDashboard.tools.unavailable ?? 0;
      window.donutChart.data.datasets[0].data = [d || 0, o || 0];
      try { window.donutChart.update(); } catch(e) { console.warn(e); }
    }

    // alerts
    renderAlerts();

    // users (almacena y renderiza)
    cachedDashboard.users = Array.isArray(cachedDashboard.users) ? cachedDashboard.users : [];
    renderUsers(cachedDashboard.users);
  }

  function renderAlerts() {
    const alertsList = document.getElementById('alertsList');
    if (!alertsList) return;
    alertsList.innerHTML = '';
    const arr = Array.isArray(cachedDashboard.alerts) ? cachedDashboard.alerts : [];
    if (!arr.length) {
      alertsList.innerHTML = '<div style="color:#94a3b8;font-size:13px;">No hay alertas</div>';
      return;
    }
    arr.forEach(a => {
      // buscar days en posibles campos
      const candidates = [a.daysLate, a.dayslate, a.days_until, a.daysUntil, a.days, a.days_diff];
      let days = null;
      for (const c of candidates) {
        if (typeof c === 'number') { days = c; break; }
        if (typeof c === 'string' && c.match(/^-?\d+$/)) { days = parseInt(c,10); break; }
      }
      const code = a.Codigo_Prestamo ?? a.Codigo ?? '';
      const tool = a.Herramienta ?? '';
      const user = a.Usuario_Login ?? a.Usuario ?? '';
      const wrapper = document.createElement('div');
      wrapper.className = 'alert-item';
      const daysHtml = (days !== null && days !== undefined) ? `<span class="badge-days">${Math.abs(days)}d</span>` : '';
      wrapper.innerHTML = `<div style="display:flex;justify-content:space-between;align-items:flex-start;">
                             <div>
                               <strong>${escapeHtml(code)}</strong>
                               <div style="font-size:13px;color:#6b7280;margin-top:6px;">${escapeHtml(tool)} — <em style="color:#94a3b8">${escapeHtml(user)}</em></div>
                             </div>
                             <div>${daysHtml}</div>
                           </div>`;
      alertsList.appendChild(wrapper);
    });
  }

  // ---------------- Users render + search (client-side filter) ----------------
  function renderUsers(users) {
    const usersRow = document.getElementById('usersRow');
    const usersEmpty = document.getElementById('usersEmpty');
    if (!usersRow) return;
    usersRow.innerHTML = '';
    usersEmpty.textContent = '';

    if (!users || !users.length) {
      usersEmpty.textContent = 'No se encontraron usuarios.';
      return;
    }

    // por defecto mostramos tarjetas pequeñas
    users.forEach(u => {
      const login = u.Usuario_Login ?? u.Codigo_Usuario ?? 'usuario';
      const id = u.ID_Usuario ?? '';
      const estado = u.Estado ?? '';
      const card = document.createElement('div');
      card.className = 'user-mini';
      card.innerHTML = `<strong style="font-size:15px;">${escapeHtml(login)}</strong>
                        <div style="font-size:12px;color:#6b7280;margin-top:4px;">USR-${escapeHtml(id)}</div>
                        <div style="font-size:13px;color:#94a3b8;margin-top:8px;">${escapeHtml(estado)}</div>`;
      usersRow.appendChild(card);
    });
  }

  // filtro local instantáneo
  function initUserSearch() {
    const input = document.getElementById('dashboardSearchUsers');
    const refreshBtn = document.getElementById('refreshUsersBtn');
    if (!input) return;
    input.addEventListener('input', (e) => {
      const q = (e.target.value || '').trim().toLowerCase();
      if (!q) {
        renderUsers(cachedDashboard.users);
        return;
      }
      // filtrar por Usuario_Login o Codigo_Usuario o ID_Usuario
      const filtered = (cachedDashboard.users || []).filter(u => {
        const login = (u.Usuario_Login || '').toString().toLowerCase();
        const code = (u.Codigo_Usuario || '').toString().toLowerCase();
        const id = (u.ID_Usuario || '').toString().toLowerCase();
        return login.includes(q) || code.includes(q) || id.includes(q);
      });
      renderUsers(filtered);
    });

    if (refreshBtn) {
      refreshBtn.addEventListener('click', () => {
        // limpia búsqueda y recarga del servidor
        document.getElementById('dashboardSearchUsers').value = '';
        fetchDashboard();
      });
    }
  }

  // ---------------- Sidebar toggle + tooltip (robusto) ----------------
  function initSidebar() {
    const toggle = document.getElementById('toggleSidebar') || document.querySelector('.toggle-btn');
    const sidebar = document.querySelector('.sidebar');
    const sidebarStateKey = 'hh_sidebar_collapsed';

    // restaurar estado guardado
    try {
      const saved = localStorage.getItem(sidebarStateKey);
      if (saved === '1') {
        sidebar && sidebar.classList.add('collapsed');
        document.body.classList.add('sidebar-collapsed');
      } else {
        sidebar && sidebar.classList.remove('collapsed');
        document.body.classList.remove('sidebar-collapsed');
      }
    } catch (e){}

    // toggle handler
    if (toggle && sidebar) {
      toggle.addEventListener('click', () => {
        const collapsing = !sidebar.classList.contains('collapsed');
        if (collapsing) {
          sidebar.classList.add('collapsed');
          document.body.classList.add('sidebar-collapsed');
          try { localStorage.setItem(sidebarStateKey, '1'); } catch(e){}
        } else {
          sidebar.classList.remove('collapsed');
          document.body.classList.remove('sidebar-collapsed');
          try { localStorage.setItem(sidebarStateKey, '0'); } catch(e){}
        }
        // redimensionar charts
        setTimeout(()=>{ try { window.lineChart && window.lineChart.resize(); window.donutChart && window.donutChart.resize(); } catch(e){} }, 220);
      });
    }

    // tooltip: usa elemento único creado dinámicamente (no pseudo::after)
    let tooltip = document.getElementById('sbTooltip');
    if (!tooltip) {
      tooltip = document.createElement('div');
      tooltip.id = 'sbTooltip';
      tooltip.style.position = 'fixed';
      tooltip.style.display = 'none';
      tooltip.style.opacity = '0';
      tooltip.style.transition = 'opacity .12s ease';
      document.body.appendChild(tooltip);
    }

    const navLinks = document.querySelectorAll('.sidebar-nav .nav-link');
    navLinks.forEach(link => {
      link.addEventListener('mouseenter', (ev) => {
        if (!sidebar.classList.contains('collapsed')) return;
        const title = link.getAttribute('data-title') || link.textContent.trim();
        if (!title) return;
        tooltip.textContent = title;
        tooltip.style.display = 'block';
        tooltip.style.opacity = '1';
        const rect = link.getBoundingClientRect();
        const left = rect.right + 10;
        const top = rect.top + (rect.height / 2) - (tooltip.offsetHeight / 2);
        const minTop = 8;
        const maxTop = window.innerHeight - tooltip.offsetHeight - 8;
        tooltip.style.left = left + 'px';
        tooltip.style.top = Math.max(minTop, Math.min(top, maxTop)) + 'px';
      });
      link.addEventListener('mouseleave', () => {
        tooltip.style.opacity = '0';
        setTimeout(()=> tooltip.style.display = 'none', 120);
      });
    });
  }

  // ---------------- Util ----------------
  function escapeHtml(s) {
    if (s === null || s === undefined) return '';
    return String(s).replace(/[&<>"']/g, (m) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
  }

  // ---------------- Init DOMContentLoaded ----------------
  document.addEventListener('DOMContentLoaded', () => {
    createCharts();
    initSidebar();
    initUserSearch();

    // refresh controls
    document.getElementById('refreshWidgetBtn')?.addEventListener('click', fetchDashboard);
    // auto-refresh dropdown
    let timer = null;
    document.getElementById('autoRefreshSelect')?.addEventListener('change', (e) => {
      if (timer) { clearInterval(timer); timer = null; }
      const v = e.target.value;
      if (v !== 'off') timer = setInterval(fetchDashboard, parseInt(v,10)*1000);
    });

    // initial fetch
    setTimeout(fetchDashboard, 120);
    // expose for debug
    window.hhFetchDashboard = fetchDashboard;
  });

})();
