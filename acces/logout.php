<?php
/**
 * Archivo para cerrar sesión de forma segura
 */

// Incluir sistema de control de acceso
require_once 'auth_check.php';

// Destruir la sesión
destruirSesion();

// Redirigir al login con mensaje
$url = appUrl('/login/inicio/vista/inicio.php');
$url .= '?mensaje=' . urlencode('Sesión cerrada correctamente');
header("Location: $url");
exit();
?>