<?php
/**
 * Copia la base MySQL local (WAMP) a BBDD/cafetin.sql para desplegar en Vercel.
 * Ejecutar una vez: php BBDD/sincronizar_mysql_a_sqlite.php
 */
declare(strict_types=1);

$mysqlHost = getenv('MYSQL_HOST') ?: '127.0.0.1';
$mysqlDb = getenv('MYSQL_DATABASE') ?: 'cafetin';
$mysqlUser = getenv('MYSQL_USER') ?: 'root';
$mysqlPass = getenv('MYSQL_PASSWORD') ?: '';

$destino = __DIR__ . '/cafetin.sql';
$temp = $destino . '.tmp';

function mysqlTipoASqlite(string $tipo): string {
    $t = strtolower($tipo);
    if (preg_match('/\b(int|bigint|smallint|tinyint|mediumint)\b/', $t)) {
        return 'INTEGER';
    }
    if (preg_match('/\b(float|double|decimal|real|numeric)\b/', $t)) {
        return 'REAL';
    }
    if (preg_match('/\b(bool|boolean)\b/', $t)) {
        return 'INTEGER';
    }
    return 'TEXT';
}

try {
    $mysql = new PDO(
        "mysql:host={$mysqlHost};dbname={$mysqlDb};charset=utf8mb4",
        $mysqlUser,
        $mysqlPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    if (file_exists($temp)) {
        unlink($temp);
    }

    $sqlite = new PDO('sqlite:' . $temp);
    $sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sqlite->exec('PRAGMA foreign_keys = OFF');

    $tablas = $mysql->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    echo 'Tablas en MySQL: ' . count($tablas) . PHP_EOL;

    foreach ($tablas as $tabla) {
        $cols = $mysql->query("DESCRIBE `{$tabla}`")->fetchAll(PDO::FETCH_ASSOC);
        $definiciones = [];
        foreach ($cols as $col) {
            $nombre = $col['Field'];
            $extra = strtolower((string) $col['Extra']);
            if ($col['Key'] === 'PRI' && str_contains($extra, 'auto_increment')) {
                $definiciones[] = "`{$nombre}` INTEGER PRIMARY KEY AUTOINCREMENT";
            } else {
                $definiciones[] = '`' . $nombre . '` ' . mysqlTipoASqlite((string) $col['Type']);
            }
        }

        $sqlite->exec('DROP TABLE IF EXISTS `' . $tabla . '`');
        $sqlite->exec('CREATE TABLE `' . $tabla . '` (' . implode(', ', $definiciones) . ')');

        $filas = $mysql->query("SELECT * FROM `{$tabla}`")->fetchAll(PDO::FETCH_ASSOC);
        if (count($filas) === 0) {
            echo "  - {$tabla}: vacía\n";
            continue;
        }

        $nombres = array_keys($filas[0]);
        $listaCols = implode(',', array_map(static fn ($c) => '`' . $c . '`', $nombres));
        $placeholders = implode(',', array_fill(0, count($nombres), '?'));
        $insert = $sqlite->prepare("INSERT INTO `{$tabla}` ({$listaCols}) VALUES ({$placeholders})");

        $sqlite->beginTransaction();
        foreach ($filas as $fila) {
            $insert->execute(array_values($fila));
        }
        $sqlite->commit();

        echo "  - {$tabla}: " . count($filas) . " filas\n";
    }

    $sqlite = null;
    unset($sqlite);
    gc_collect_cycles();

    if (!@copy($temp, $destino)) {
        $alterno = __DIR__ . '/cafetin_exportado.sql';
        copy($temp, $alterno);
        echo PHP_EOL . "No se pudo sobrescribir cafetin.sql (ciérralo en el editor)." . PHP_EOL;
        echo "Se guardó copia en: {$alterno}" . PHP_EOL;
        echo "Renómbrala manualmente a cafetin.sql y vuelve a desplegar." . PHP_EOL;
        @unlink($temp);
        exit(0);
    }
    @unlink($temp);

    $usuarios = (int) (new PDO('sqlite:' . $destino))->query('SELECT COUNT(*) FROM usuario')->fetchColumn();
    echo PHP_EOL . "Listo: {$destino}" . PHP_EOL;
    echo "Usuarios en cafetin.sql: {$usuarios}" . PHP_EOL;
    echo "Sube este archivo a Git y vuelve a desplegar en Vercel." . PHP_EOL;
} catch (Throwable $e) {
    if (file_exists($temp)) {
        unlink($temp);
    }
    fwrite(STDERR, 'Error: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
