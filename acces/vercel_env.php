<?php
/**
 * Ajustes de entorno para Vercel (serverless PHP).
 */
if (!function_exists('isVercelRuntime')) {
    function isVercelRuntime(): bool {
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }
        $cached = getenv('VERCEL') === '1'
            || getenv('VERCEL') === 'true'
            || !empty($_ENV['VERCEL'])
            || !empty($_SERVER['VERCEL']);
        return $cached;
    }
}

if (isVercelRuntime()) {
    if (is_dir('/tmp') && is_writable('/tmp')) {
        ini_set('session.save_path', '/tmp');
    }
}
