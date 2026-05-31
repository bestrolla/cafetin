<?php
// Utilidades CSRF (compatible con Vercel/serverless: tokens firmados sin depender de sesión)

require_once __DIR__ . '/auth_check.php';

function csrfSecret(): string {
    static $secret = null;
    if ($secret !== null) {
        return $secret;
    }

    $fromEnv = getenv('CAFETIN_CSRF_SECRET');
    if ($fromEnv === false || $fromEnv === '') {
        $fromEnv = $_ENV['CAFETIN_CSRF_SECRET'] ?? '';
    }
    if ($fromEnv !== '') {
        $secret = $fromEnv;
        return $secret;
    }

    $root = defined('CAFETIN_ROOT') ? CAFETIN_ROOT : dirname(__DIR__);
    $host = getenv('VERCEL_URL') ?: ($_SERVER['HTTP_HOST'] ?? 'localhost');
    $secret = hash('sha256', $root . '|' . $host . '|cafetin-csrf-v1');
    return $secret;
}

function csrfCreateSignedToken(): string {
    $payload = bin2hex(random_bytes(16));
    $signature = hash_hmac('sha256', $payload, csrfSecret());
    return $payload . '.' . $signature;
}

function csrfVerifySignedToken(string $token): bool {
    $token = trim($token);
    $dot = strpos($token, '.');
    if ($dot === false) {
        return false;
    }
    $payload = substr($token, 0, $dot);
    $signature = substr($token, $dot + 1);
    if ($payload === '' || $signature === '' || !ctype_xdigit($payload)) {
        return false;
    }
    $expected = hash_hmac('sha256', $payload, csrfSecret());
    return hash_equals($expected, $signature);
}

function csrfIsSignedToken(string $token): bool {
    return strpos(trim($token), '.') !== false;
}

function csrfEnsureToken(): string {
    if (isVercelRuntime()) {
        return csrfCreateSignedToken();
    }

    initSessionIfNeeded();
    if (!empty($_SESSION['csrf_token']) && csrfIsSignedToken($_SESSION['csrf_token'])) {
        return $_SESSION['csrf_token'];
    }
    if (!empty($_SESSION['csrf_token']) && !csrfIsSignedToken($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = csrfCreateSignedToken();
        return $_SESSION['csrf_token'];
    }

    $_SESSION['csrf_token'] = csrfCreateSignedToken();
    return $_SESSION['csrf_token'];
}

function csrfGetToken(): string {
    return csrfEnsureToken();
}

function csrfVerifyTokenValue(string $token): bool {
    if ($token === '') {
        return false;
    }
    if (csrfIsSignedToken($token)) {
        return csrfVerifySignedToken($token);
    }

    initSessionIfNeeded();
    if (!empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
        return true;
    }
    return false;
}

function csrfVerifyFromPost(string $fieldName = 'csrf_token'): bool {
    if (!isset($_POST[$fieldName])) {
        return false;
    }
    return csrfVerifyTokenValue((string) $_POST[$fieldName]);
}

function csrfVerifyFromHeader(string $headerName = 'X-CSRF-Token'): bool {
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    $token = $headers[$headerName] ?? $headers[strtolower($headerName)] ?? null;
    if (!$token) {
        return false;
    }
    return csrfVerifyTokenValue((string) $token);
}

?>
