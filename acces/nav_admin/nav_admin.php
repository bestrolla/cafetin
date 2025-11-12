<?php require_once __DIR__ . '/../auth_check.php'; ?>
<style>
section header {
    background: rgba(0, 0, 0, 0.85);
    color: #fff;
    padding-left: 25px; 
    box-shadow: 0 4px 16px rgba(0,0,0,0.3);
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    z-index: 1000;
    transition: transform 0.3s;
}
.header-hidden {
    transform: translateY(-100%);
}
#show-header-tab {
    display: block; /* Mostrar por defecto */
    position: fixed;
    top: 0px; /* Debajo del logo */
    right: 0px; /* Lado derecho */
    background: #ffffffff;
    color: #000;
    padding: 6px 10px;
    border-radius: 0 0 0px 12px;
    font-size: 14px;
    cursor: pointer;
    z-index: 1100;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    border: none;
}

section header h2 {
    font-size: 2rem;
    font-weight: bold;
    margin-right: 32px;
    letter-spacing: 2px;
}

section header ul {
    list-style: none;
    display: flex;
    gap: 25px;
    margin: 0;
    padding: 0;
}

section header ul li a {
    color: #fff;
    text-decoration: none;
    font-size: 1.1rem;
    padding: 8px 18px;
    border-radius: 6px;
    transition: background 0.2s;
}

section header ul li a:hover {
    background: #007bff;
    color: #fff;
}

section header .logo img {
    border-radius: 15px 0px 0px 15px;
    border: 2px solid #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    margin-left: 40px;
    width: 80px;
    height: 80px;
    object-fit: cover;
}
</style>

<section>
  <header id="header-cajero" class="header-hidden">
    
    <nav>
      <ul>
        <li><a href="../../inventario/vista/inventario.php">Inventario</a></li>
        <li><a href="../../caja/vista/caja.php">Caja</a></li>
        <li><a href="../../cuentas/vista/cuentas.php">Cuentas</a></li>
        <li><a href="../../agregar_cajero/vista/agregar_cajero.php">Agregar Cajero</a></li>
        <li><a href="../../configuracion/vista/configuracion.php">Configuracion</a></li>
        <li><a href="../../../acces/logout.php">Cerrar Sesión</a></li>
      </ul>
    </nav>
    <div class="logo">
      <img id="logo-cajero" src="../../../acces/img/logo.jpg" alt="Logo" width="100px" height="100px">
    </div>
  </header>
  <button id="show-header-tab">Mostrar menú</button>
</section>

<!-- Notificaciones de Inventario (global) -->
<button id="btn-notificaciones" class="notif-button" aria-label="Notificaciones" onclick="togglePanel()">
  <span class="notif-icon">🔔</span>
  <span id="notif-dot" class="notif-dot" hidden></span>
  
</button>
<aside id="notif-panel" class="notif-panel" aria-hidden="true">
  <div class="notif-header">
    <h3>Alertas de Inventario</h3>
    <button class="close" aria-label="Cerrar" onclick="document.getElementById('notif-panel').classList.remove('open');document.getElementById('notif-panel').setAttribute('aria-hidden','true');">×</button>
  </div>
  <div class="notif-subheader">
    <span id="notif-summary">Cargando...</span>
  </div>
  <div class="notif-body" id="notif-list"></div>
  <div class="notif-footer"></div>
  <script>
    function togglePanel(){
      const panel = document.getElementById('notif-panel');
      const overlay = document.getElementById('notif-overlay');
      if (!panel) return;
      const open = !panel.classList.contains('open');
      panel.classList.toggle('open', open);
      panel.setAttribute('aria-hidden', open ? 'false' : 'true');
      if (overlay) overlay.style.display = open ? 'block' : 'none';
    }
    function closePanel(){
      const panel = document.getElementById('notif-panel');
      const overlay = document.getElementById('notif-overlay');
      if (panel) {
        panel.classList.remove('open');
        panel.setAttribute('aria-hidden','true');
      }
      if (overlay) overlay.style.display = 'none';
    }
    const btnNotif = document.getElementById('btn-notificaciones');
    if (btnNotif) btnNotif.addEventListener('click', togglePanel);
    // Cerrar al hacer clic fuera del panel
    document.addEventListener('click', function(e){
      const panel = document.getElementById('notif-panel');
      if (!panel || !panel.classList.contains('open')) return;
      const isInsidePanel = panel.contains(e.target);
      const isButton = btnNotif && (btnNotif === e.target || btnNotif.contains(e.target));
      if (!isInsidePanel && !isButton) closePanel();
    });
    // Evitar cierre al interactuar dentro del panel
    const panelEl = document.getElementById('notif-panel');
    if (panelEl) panelEl.addEventListener('click', function(e){ e.stopPropagation(); });
  </script>
</aside>

<div id="notif-overlay" class="notif-overlay" onclick="closePanel()"></div>

<script>
const header = document.getElementById('header-cajero');
const logo = document.getElementById('logo-cajero');
const tab = document.getElementById('show-header-tab');

// Mostrar header al hacer clic en la pestaña
tab.addEventListener('click', function(e) {
    header.classList.remove('header-hidden');
    tab.style.display = 'none';
    e.stopPropagation();
});

// Ocultar header al hacer clic en el logo
logo.addEventListener('click', function(e) {
    header.classList.add('header-hidden');
    tab.style.display = 'block';
    e.stopPropagation();
});

// Ocultar header al hacer clic fuera del header
document.addEventListener('click', function(e) {
    if (!header.classList.contains('header-hidden')) {
        if (!header.contains(e.target)) {
            header.classList.add('header-hidden');
            tab.style.display = 'block';
        }
    }
});
</script>
<script>window.__APP_BASE = "<?php echo appBasePath(); ?>";</script>
<script src="<?php echo appUrl('/acces/js/notifications.js'); ?>"></script>