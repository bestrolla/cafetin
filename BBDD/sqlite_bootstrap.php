<?php
/**
 * Usa BBDD/cafetin.sql como base SQLite (copia a /tmp en Vercel).
 */

function cafetinRutaSemillaSqlite(): string {
    return __DIR__ . '/cafetin.sql';
}

function cafetinEsArchivoSqlite(string $ruta): bool {
    if (!is_readable($ruta)) {
        return false;
    }
    $fh = @fopen($ruta, 'rb');
    if (!$fh) {
        return false;
    }
    $magic = fread($fh, 15);
    fclose($fh);
    return $magic === 'SQLite format 3';
}

function cafetinSqliteTablaExiste(PDO $conexion, string $tabla): bool {
    $stmt = $conexion->prepare(
        "SELECT 1 FROM sqlite_master WHERE type = 'table' AND name = ? LIMIT 1"
    );
    $stmt->execute([$tabla]);
    return (bool) $stmt->fetchColumn();
}

/** Copia cafetin.sql al destino (p. ej. /tmp/cafetin.db en Vercel). */
function cafetinCopiarCafetinSql(string $destino): bool {
    $semilla = cafetinRutaSemillaSqlite();
    if (!cafetinEsArchivoSqlite($semilla)) {
        return false;
    }
    $dir = dirname($destino);
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    return @copy($semilla, $destino);
}

/** Si el destino no existe o está incompleto, copia de nuevo cafetin.sql. */
function cafetinPrepararSqliteEnRuta(string $rutaDestino): void {
    if ($rutaDestino === '') {
        return;
    }

    $semilla = cafetinRutaSemillaSqlite();
    if (!cafetinEsArchivoSqlite($semilla)) {
        return;
    }

    $necesitaCopia = !file_exists($rutaDestino);
    if (!$necesitaCopia && cafetinEsArchivoSqlite($rutaDestino)) {
        try {
            $pdo = new PDO('sqlite:' . $rutaDestino);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $usuarios = (int) $pdo->query('SELECT COUNT(*) FROM usuario')->fetchColumn();
            $necesitaCopia = !cafetinSqliteTablaExiste($pdo, 'ventas') || $usuarios === 0;
        } catch (Exception $e) {
            $necesitaCopia = true;
        }
    } elseif (!$necesitaCopia) {
        $necesitaCopia = true;
    }

    if ($necesitaCopia) {
        cafetinCopiarCafetinSql($rutaDestino);
    }
}

function cafetinResolverRutaSqlite(): string {
    if (isVercelRuntime()) {
        return $_ENV['CAFETIN_SQLITE_PATH'] ?? '/tmp/cafetin.db';
    }
    if (!empty($_ENV['CAFETIN_SQLITE_PATH'])) {
        return $_ENV['CAFETIN_SQLITE_PATH'];
    }
    return cafetinRutaSemillaSqlite();
}
