// Global Inventory Notifications
(function() {
  const BASE = (typeof window !== 'undefined' && window.__APP_BASE) ? window.__APP_BASE : '';
  const API_URL = `${BASE}/admin/configuracion/logica/obtener_notificaciones_inventario.php`;
  let notifTimer = null;
  let lastNotifCount = 0;
  let lastItemsHash = '';

  function init() {
    const btn = document.getElementById('btn-notificaciones');
    if (btn) btn.addEventListener('click', () => togglePanel());
    // Initial fetch and polling
    fetchNotifs(true);
    notifTimer = setInterval(() => fetchNotifs(false), 30000);
    window.__globalNotifInitialized = true;
  }

  async function fetchNotifs(isFirstLoad) {
    try {
      const resp = await fetch(API_URL);
      const data = await resp.json();
      if (!data.success) return;
      updateUI(data);
      const hash = JSON.stringify(data.items);
      const hasNew = data.count > lastNotifCount || (hash !== lastItemsHash && data.count > 0);
      if (!isFirstLoad && hasNew) beep();
      lastNotifCount = data.count;
      lastItemsHash = hash;
    } catch (e) {
      // Silent failure
      console.warn('Inventario notifs error:', e);
    }
  }

  function updateUI(data) {
    const dot = document.getElementById('notif-dot');
    const list = document.getElementById('notif-list');
    const summary = document.getElementById('notif-summary');
    if (!dot || !list || !summary) return;
    if (data.count > 0) {
      dot.hidden = false;
      const zeros = Array.isArray(data.items) ? data.items.reduce((acc, it) => {
        const qty = parseInt(it.cantidad_total ?? 0);
        return acc + (Number.isFinite(qty) && qty <= 0 ? 1 : 0);
      }, 0) : 0;
      const low = data.count - zeros;
      summary.textContent = zeros > 0
        ? `${zeros} producto(s) Vacío${low > 0 ? `, ${low} bajo stock ( ${data.threshold})` : ''}`
        : `${data.count} producto(s) bajo stock ( ${data.threshold})`;
      list.innerHTML = '';
      data.items.forEach(item => {
        const row = document.createElement('div');
        row.className = 'notif-item';
        const qty = parseInt(item.cantidad_total ?? 0);
        const qtyLabel = (Number.isFinite(qty) && qty <= 0) ? 'Vacío' : qty;
        const badge = (Number.isFinite(qty) && qty <= 0) ? 'Vacío' : 'Bajo';
        row.innerHTML = `
          <span class="name">${item.nombre_produc}</span>
          <span class="qty">Stock: ${qtyLabel}</span>
          <span class="badge">${badge}</span>
        `;
        if (data.canNavigate) {
          row.classList.add('clickable');
          row.title = 'Abrir Inventario (Admin)';
          row.addEventListener('click', () => {
            const url = `${BASE}/admin/inventario/vista/inventario.php`;
            window.location.href = url;
          });
        }
        list.appendChild(row);
      });
    } else {
      dot.hidden = true;
      summary.textContent = 'Sin alertas';
      list.innerHTML = `<div class="notif-empty">No hay productos con stock bajo.</div>`;
    }
  }

  function togglePanel(force) {
    const panel = document.getElementById('notif-panel');
    if (!panel) return;
    const open = force === undefined ? !panel.classList.contains('open') : !!force;
    panel.classList.toggle('open', open);
    panel.setAttribute('aria-hidden', open ? 'false' : 'true');
  }

  function beep() {
    try {
      const ctx = new (window.AudioContext || window.webkitAudioContext)();
      const o = ctx.createOscillator();
      const g = ctx.createGain();
      o.type = 'sine';
      o.frequency.value = 880;
      g.gain.setValueAtTime(0.001, ctx.currentTime);
      g.gain.exponentialRampToValueAtTime(0.1, ctx.currentTime + 0.01);
      g.gain.exponentialRampToValueAtTime(0.00001, ctx.currentTime + 0.25);
      o.connect(g);
      g.connect(ctx.destination);
      o.start();
      o.stop(ctx.currentTime + 0.25);
    } catch (e) {}
  }

  // Initialize after DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();