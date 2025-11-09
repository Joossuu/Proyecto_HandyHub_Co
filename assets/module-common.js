// assets/module-common.js
// helper general (evita 404 cuando otras páginas importen este archivo)
window.hh = window.hh || {};
hh.utils = {
  el: (s) => document.querySelector(s),
  elAll: (s) => Array.from(document.querySelectorAll(s)),
  formatDate: (iso) => {
    try {
      const d = new Date(iso); if (isNaN(d)) return iso;
      return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')} ${String(d.getHours()).padStart(2,'0')}:${String(d.getMinutes()).padStart(2,'0')}:00`;
    } catch (e) { return iso; }
  }
};
