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
    <h2>Bienvenido Cajero</h2>
    <nav>
      <ul>
        <li><a href="/cafetin/cajero/lobby/vista/lobby.php">Lobby</a></li>
        <li><a href="/cafetin/cajero/cuentas/vista/cuentas.php">Cuentas</a></li>
        <li><a href="/cafetin/cajero/configuracion/vista/configuracion.php">Configuración</a></li>
        <li><a href="/cafetin/login/inicio/vista/inicio.php">Cerrar Sesión</a></li>
      </ul>
    </nav>
    <div class="logo">
      <img id="logo-cajero" src="../../../acces/img/logo.jpg" alt="Logo" width="100px" height="100px">
    </div>
  </header>
  <button id="show-header-tab">Mostrar menú</button>
</section>
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