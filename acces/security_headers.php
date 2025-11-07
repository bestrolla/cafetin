<?php
// Encabezados de seguridad comunes
// Importante: incluir este archivo ANTES de cualquier salida HTML

// Evitar disclosure de versión del servidor (cuando sea posible)
header('X-Powered-By: cafetin');

// Clickjacking
header('X-Frame-Options: SAMEORIGIN');

// XSS protection (para navegadores antiguos)
header('X-XSS-Protection: 1; mode=block');

// Evitar sniffing de tipos
header('X-Content-Type-Options: nosniff');

// Política básica de CSP; ajustar según recursos reales si es necesario
// Nota: Permite inline styles del proyecto actual si existen, se recomienda migrar a archivos CSS
$self = "'self'";
header("Content-Security-Policy: default-src $self; script-src $self 'unsafe-inline'; style-src $self 'unsafe-inline' https://fonts.googleapis.com; font-src $self https://fonts.gstatic.com; img-src $self data:; connect-src $self; frame-ancestors 'self'; form-action $self");

?>

