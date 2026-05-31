<?php
/**
 * Verifica contraseña contra hash guardado (bcrypt, argon2 o legado md5/texto plano).
 */
function cafetinVerificarContrasena(string $plano, string $hashGuardado): bool {
    $hashGuardado = trim($hashGuardado);
    if ($hashGuardado === '') {
        return false;
    }

    if (password_verify($plano, $hashGuardado)) {
        return true;
    }

    $info = password_get_info($hashGuardado);
    if ($info['algo'] !== 0) {
        return false;
    }

    if (strlen($hashGuardado) === 32 && ctype_xdigit($hashGuardado)) {
        return hash_equals(strtolower($hashGuardado), md5($plano));
    }

    return hash_equals($hashGuardado, $plano);
}
