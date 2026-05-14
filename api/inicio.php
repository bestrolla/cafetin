<?php
// Esto soluciona TODAS las rutas relativas de una vez
chdir(__DIR__ . '/..');

// Ahora incluye tu archivo original sin modificar nada
require 'login/inicio/vista/inicio.php';