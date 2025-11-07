<?php
// Utilidades CSRF

require_once __DIR__ . '/auth_check.php';

function csrfEnsureToken() {
    initSessionIfNeeded();
    if (empty($_SESSION['csrf_token'])) {
        // Token aleatorio seguro
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfGetToken() {
    initSessionIfNeeded();
    return $_SESSION['csrf_token'] ?? csrfEnsureToken();
}

function csrfVerifyFromPost($fieldName = 'csrf_token') {
    initSessionIfNeeded();
    if (!isset($_POST[$fieldName], $_SESSION['csrf_token'])) {
        return false;
    }
    $isValid = hash_equals($_SESSION['csrf_token'], $_POST[$fieldName]);
    return $isValid;
}

function csrfVerifyFromHeader($headerName = 'X-CSRF-Token') {
    initSessionIfNeeded();
    $headers = getallheaders();
    $token = $headers[$headerName] ?? $headers[strtolower($headerName)] ?? null;
    if (!$token || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

?>

