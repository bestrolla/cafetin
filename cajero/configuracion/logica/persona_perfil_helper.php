<?php
/**
 * Asegura que la tabla persona tenga columna email (perfil cajero).
 */
function cafetin_persona_ensure_email_column(PDO $pdo): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    try {
        if ($driver === 'mysql') {
            $db = $pdo->query('SELECT DATABASE()')->fetchColumn();
            if ($db) {
                $q = $pdo->prepare(
                    'SELECT COUNT(*) FROM information_schema.COLUMNS
                     WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?'
                );
                $q->execute([$db, 'persona', 'email']);
                if ((int) $q->fetchColumn() === 0) {
                    $pdo->exec('ALTER TABLE persona ADD COLUMN email VARCHAR(255) NULL DEFAULT NULL');
                }
            }
        }
    } catch (Throwable $e) {
        // Columna ya existe u operación no aplicable
    }
    $done = true;
}
