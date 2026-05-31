<?php
/**
 * Raíz del proyecto y rutas absolutas (Vercel / WAMP).
 */
if (!defined('CAFETIN_ROOT')) {
    define('CAFETIN_ROOT', dirname(__DIR__));
}

function cafetin_path(string $relative): string {
    return CAFETIN_ROOT . '/' . ltrim(str_replace('\\', '/', $relative), '/');
}

function cafetin_require(string $relative): void {
    require_once cafetin_path($relative);
}
