<?php
if (ob_get_level() === 0) { ob_start(); }
if (!headers_sent()) {
header('X-Powered-By: cafetin');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
$self = "'self'";
header("Content-Security-Policy: default-src $self; script-src $self 'unsafe-inline'; style-src $self 'unsafe-inline' https://fonts.googleapis.com; font-src $self https://fonts.gstatic.com; img-src $self data:; connect-src $self; frame-ancestors 'self'; form-action $self");
}

