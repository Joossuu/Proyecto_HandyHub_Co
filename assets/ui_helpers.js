// llama esto antes de los demás JS
window.hhOpenModal = function(title, contentEl) {
  // crea backdrop/modal simple (mejor que alert)
  const backdrop = document.createElement('div');
  backdrop.className = 'modal-backdrop';
  const panel = document.createElement('div');
  panel.className = 'modal-panel';
  const close = document.createElement('button');
  close.className = 'modal-close';
  close.type = 'button';
  close.textContent = '✕';
  const h = document.createElement('h3');
  h.textContent = title;
  panel.appendChild(close);
  panel.appendChild(h);
  panel.appendChild(contentEl);
  backdrop.appendChild(panel);
  document.body.appendChild(backdrop);
  close.addEventListener('click', () => backdrop.remove());
  backdrop.addEventListener('click', (ev) => { if (ev.target === backdrop) backdrop.remove(); });
  return backdrop;
};

window.hh = window.hh || {};
window.hh.reloadModule = function(module) {
  const search = document.getElementById(module + 'Search');
  if (search && search.value.trim()) {
    search.dispatchEvent(new KeyboardEvent('keyup', { key: 'Enter' }));
  } else {
    // fallback: re-trigger module-common loader by focusing wrapper (simple)
    const wrapper = document.querySelector(`[data-module="${module}"]`);
    if (wrapper) {
      const evt = new Event('hh.reload');
      wrapper.dispatchEvent(evt);
      // module-common listens? if no, reload page fallback:
      setTimeout(()=> {
        const body = document.querySelector(`[data-module="${module}"]`);
        if (body) {
          // call load via fetch again: we can't access loadModule from common, so reload page
          location.reload();
        }
      }, 400);
    }
  }
};
