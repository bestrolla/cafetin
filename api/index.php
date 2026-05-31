<?php
/**
 * Punto de entrada en Vercel: enruta cada petición al PHP correspondiente del proyecto.
 */
declare(strict_types=1);

$root = dirname(__DIR__);
chdir($root);

require_once $root . '/acces/vercel_env.php';

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uri = $uri === false || $uri === '' ? '/' : rawurldecode($uri);

if ($uri === '/' || $uri === '/index.html') {
    $uri = '/login/inicio/vista/inicio.php';
}

$target = $root . $uri;

if (is_dir($target)) {
    $indexPhp = rtrim($target, '/\\') . '/index.php';
    if (is_file($indexPhp)) {
        $target = $indexPhp;
    }
}

$realRoot = realpath($root);
$realTarget = realpath($target);

if ($realRoot === false || $realTarget === false || strpos($realTarget, $realRoot) !== 0) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Página no encontrada';
    exit;
}

if (!is_file($realTarget)) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Página no encontrada';
    exit;
}

$ext = strtolower(pathinfo($realTarget, PATHINFO_EXTENSION));
if ($ext !== 'php') {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Página no encontrada';
    exit;
}

require $realTarget;
