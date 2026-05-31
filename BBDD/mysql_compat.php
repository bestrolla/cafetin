<?php
/**
 * Consultas compatibles con el esquema MySQL de cafetin.
 */

function cafetinSqlConcatNombre(string $aliasPersona = 'p'): string {
    return "CONCAT({$aliasPersona}.nombre, ' ', {$aliasPersona}.apellido)";
}

function cafetinSqlUsuarioIgual(string $columna = 'u.usuario'): string {
    return "LOWER({$columna}) = LOWER(:usuario)";
}
