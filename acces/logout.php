<?php
/**
 * Archivo para cerrar sesión de forma segura
 */

// Incluir sistema de control de acceso
require_once 'auth_check.php';

// Destruir la sesión
destruirSesion();

// Redirigir al login con mensaje
header("Location: /cafetin/login/inicio/vista/inicio.php?mensaje=" . urlencode("Sesión cerrada correctamente"));
exit();
?>