<?php
/**
 * Sistema de Control de Acceso
 * Verifica si el usuario tiene una sesión válida y los permisos necesarios
 */

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Función para verificar si el usuario está autenticado
 */
function verificarAutenticacion() {
    // Verificar si existe la sesión del usuario
    if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_nombre']) || !isset($_SESSION['usuario_rol'])) {
        return false;
    }
    
    // Verificar si la sesión no ha expirado (opcional: 2 horas)
    if (isset($_SESSION['ultimo_acceso'])) {
        $tiempoInactivo = time() - $_SESSION['ultimo_acceso'];
        $tiempoMaximo = 2 * 60 * 60; // 2 horas en segundos
        
        if ($tiempoInactivo > $tiempoMaximo) {
            // Sesión expirada
            destruirSesion();
            return false;
        }
    }
    
    // Actualizar último acceso
    $_SESSION['ultimo_acceso'] = time();
    
    return true;
}

/**
 * Función para verificar permisos de rol
 */
function verificarRol($rolesPermitidos = []) {
    if (!verificarAutenticacion()) {
        return false;
    }
    
    // Si no se especifican roles, solo verificar autenticación
    if (empty($rolesPermitidos)) {
        return true;
    }
    
    // Verificar si el rol del usuario está en los roles permitidos
    $rolUsuario = $_SESSION['usuario_rol'];
    return in_array($rolUsuario, $rolesPermitidos);
}

/**
 * Función para destruir la sesión
 */
function destruirSesion() {
    // Limpiar todas las variables de sesión
    $_SESSION = array();
    
    // Destruir la cookie de sesión si existe
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destruir la sesión
    session_destroy();
}

/**
 * Función para redirigir al login
 */
function redirigirLogin($mensaje = '') {
    $loginUrl = '/cafetin/login/inicio/vista/inicio.php';
    
    if (!empty($mensaje)) {
        $loginUrl .= '?error=' . urlencode($mensaje);
    }
    
    header("Location: $loginUrl");
    exit();
}

/**
 * Función principal para proteger páginas
 * $rolesPermitidos: array de roles que pueden acceder (ej: ['admin', 'cajero'])
 */
function protegerPagina($rolesPermitidos = []) {
    if (!verificarAutenticacion()) {
        redirigirLogin('Debe iniciar sesión para acceder a esta página');
        return;
    }
    
    if (!empty($rolesPermitidos) && !verificarRol($rolesPermitidos)) {
        redirigirLogin('No tiene permisos para acceder a esta página');
        return;
    }
}

/**
 * Función para obtener información del usuario actual
 */
function obtenerUsuarioActual() {
    if (!verificarAutenticacion()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['usuario_id'],
        'nombre' => $_SESSION['usuario_nombre'],
        'rol' => $_SESSION['usuario_rol']
    ];
}

/**
 * Función para verificar si es admin
 */
function esAdmin() {
    return verificarRol(['admin']);
}

/**
 * Función para verificar si es cajero
 */
function esCajero() {
    return verificarRol(['cajero']);
}

/**
 * Función para verificar si es admin o cajero
 */
function esAdminOCajero() {
    return verificarRol(['admin', 'cajero']);
}
?>