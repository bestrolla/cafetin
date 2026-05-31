<?php
// ===============================================
// 📦 CONEXIÓN A LA BASE DE DATOS - cafetin
// Autor: Ángel
// ===============================================

class Conexion {
    private $driver = 'mysql';
    private $host = 'sql103.infinityfree.com';
    private $dbname = 'if0_41909456_cafetin';
    private $username = 'if0_41909456';
    private $password = 'udWAvVG9sN';
    private $sqlite_path;
    private $conexion;

    public function __construct() {
        $this->sqlite_path = __DIR__ . '/cafetin.sql';
    }

    public function conectar() {
        try {
            if ($this->driver === 'sqlite') {
                $this->conexion = new PDO('sqlite:' . $this->sqlite_path);
                $this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->conexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                $this->conexion->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                $this->conexion->exec('PRAGMA foreign_keys = ON');
                return $this->conexion;
            }

            $this->conexion = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset=utf8",
                $this->username,
                $this->password
            );
            $this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->conexion->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            return $this->conexion;

        } catch (PDOException $e) {
            throw new Exception("❌ Error de conexión: " . $e->getMessage());
        }
    }

    public function usarSqlite($rutaDb = null) {
        $this->driver = 'sqlite';
        if ($rutaDb) {
            $this->sqlite_path = $rutaDb;
        }
    }

    public function desconectar() {
        $this->conexion = null;
    }

    public function getSqlitePath(): string {
        return $this->sqlite_path;
    }
}

function normalizarTextoNombre($texto) {
    $texto = trim((string)$texto);
    if ($texto === '') {
        return $texto;
    }

    // Unificar espacios internos
    $texto = preg_replace('/\s+/u', ' ', $texto);

    if (function_exists('mb_convert_case')) {
        return mb_convert_case($texto, MB_CASE_TITLE, 'UTF-8');
    }

    return ucwords(strtolower($texto));
}

function sqliteTransformMysqlDump($sql) {
    $sql = str_replace("\r", "\n", $sql);
    $sql = preg_replace('/\/\*![\s\S]*?\*\//i', '', $sql);
    $sql = preg_replace('/\/\*[\s\S]*?\*\//', '', $sql);
    $lines = explode("\n", $sql);
    $out = [];
    $inCreate = false;
    $buffer = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || preg_match('/^--/',$line)) { continue; }
        if (preg_match('/^(SET|START TRANSACTION|COMMIT|LOCK TABLES|UNLOCK TABLES|DELIMITER)\b/i',$line)) { continue; }
        if (!$inCreate && preg_match('/^CREATE\s+TABLE/i', $line)) {
            $inCreate = true;
            $buffer = [$line];
            continue;
        }
        if ($inCreate) {
            $buffer[] = $line;
            if (preg_match('/\)\s*;?$/', $line)) {
                $out[] = transformCreateBlock(implode("\n", $buffer));
                $inCreate = false;
                $buffer = [];
            }
            continue;
        }
        $line = str_replace('`','',$line);
        $line = preg_replace('/\bINSERT\s+INTO\s+"?/i','INSERT INTO ',$line);
        $out[] = $line;
    }
    $sqlOut = implode("\n", $out);
    $sqlOut = preg_replace('/\)\s*ENGINE\s*=.*?;/', ');', $sqlOut);
    $sqlOut = preg_replace('/DEFAULT\s+CHARSET\s*=\s*\w+/i','',$sqlOut);
    $sqlOut = preg_replace('/COLLATE\s*=\s*\w+/i','',$sqlOut);
    return $sqlOut;
}

function transformCreateBlock($block) {
    $block = str_replace('`','',$block);
    $block = preg_replace('/\)\s*ENGINE\s*=.*?;/', ');', $block);
    $block = preg_replace('/DEFAULT\s+CHARSET\s*=\s*\w+/i','',$block);
    $block = preg_replace('/COLLATE\s*=\s*\w+/i','',$block);
    $parts = explode("\n", $block);
    $header = array_shift($parts);
    $footer = array_pop($parts);
    $header = preg_replace('/^CREATE\s+TABLE\s+/i','CREATE TABLE IF NOT EXISTS ', $header);
    $cols = [];
    $pkDone = false;
    foreach ($parts as $p) {
        $p = trim($p);
        if ($p === '') { continue; }
        $p = rtrim($p, ',');
        if (preg_match('/^(PRIMARY\s+KEY|UNIQUE\s+KEY|KEY)\b/i',$p)) { continue; }
        $p = preg_replace('/\bint\(\d+\)\b/i','INTEGER',$p);
        $p = preg_replace('/\bbigint\(\d+\)\b/i','INTEGER',$p);
        $p = preg_replace('/\bbigint\b/i','INTEGER',$p);
        $p = preg_replace('/\btinyint\(\d+\)\b/i','INTEGER',$p);
        $p = preg_replace('/\btinyint\b/i','INTEGER',$p);
        $p = preg_replace('/\bdecimal\([^)]*\)\b/i','REAL',$p);
        $p = preg_replace('/\bdouble\b/i','REAL',$p);
        $p = preg_replace('/\bfloat\b/i','REAL',$p);
        $p = preg_replace('/\bvarchar\([^)]*\)\b/i','TEXT',$p);
        $p = preg_replace('/\bchar\([^)]*\)\b/i','TEXT',$p);
        $p = preg_replace('/\bdatetime\b/i','TEXT',$p);
        $p = preg_replace('/\btimestamp\b/i','TEXT',$p);
        $p = preg_replace('/\bdate\b/i','TEXT',$p);
        $p = preg_replace('/\btime\b/i','TEXT',$p);
        $p = preg_replace('/\bunsigned\b/i','',$p);
        $p = preg_replace('/ON\s+UPDATE\s+CURRENT_TIMESTAMP/i','',$p);
        if (preg_match('/\benum\([^)]*\)/i',$p)) {
            $p = preg_replace('/\benum\([^)]*\)/i','TEXT',$p);
        }
        if (preg_match('/\bAUTO_INCREMENT\b/i',$p)) {
            $p = preg_replace('/^([A-Za-z0-9_"\']+)\s+\w+.*$/','$1 INTEGER PRIMARY KEY AUTOINCREMENT',$p);
            $pkDone = true;
        }
        $cols[] = $p;
    }
    $colsStr = implode(",\n", $cols);
    return $header . "\n(" . "\n" . $colsStr . "\n" . ")" . ";";
}

function sqliteImportFromMysqlDump($conexion, $dumpPath) {
    if (!file_exists($dumpPath)) { return false; }
    $sql = file_get_contents($dumpPath);
    if ($sql === false || $sql === '') { return false; }
    $sqliteSql = sqliteTransformMysqlDump($sql);
    try {
        $conexion->beginTransaction();
        $conexion->exec($sqliteSql);
        $conexion->commit();
        return true;
    } catch (Exception $e) {
        if ($conexion->inTransaction()) { $conexion->rollBack(); }
        return false;
    }
}

require_once __DIR__ . '/sqlite_bootstrap.php';

// Mantener compatibilidad con código existente que usa $conexion directamente
try {
    require_once __DIR__ . '/../acces/vercel_env.php';
    $conexionObj = new Conexion();

    $usarSqlite = (isset($_ENV['CAFETIN_DB_DRIVER']) && $_ENV['CAFETIN_DB_DRIVER'] === 'sqlite')
        || isVercelRuntime();

    if ($usarSqlite) {
        $sqliteRuta = cafetinResolverRutaSqlite();
        if ($sqliteRuta !== cafetinRutaSemillaSqlite()) {
            cafetinPrepararSqliteEnRuta($sqliteRuta);
        }
        $conexionObj->usarSqlite($sqliteRuta);
    }

    try {
        $conexion = $conexionObj->conectar();
    } catch (Exception $e) {
        if (extension_loaded('pdo_sqlite') && $usarSqlite) {
            $sqliteRuta = cafetinResolverRutaSqlite();
            cafetinPrepararSqliteEnRuta($sqliteRuta);
            $conexionObj->usarSqlite($sqliteRuta);
            $conexion = $conexionObj->conectar();
        } else {
            throw $e;
        }
    }

} catch (Exception $e) {
    echo $e->getMessage();
    exit;
}
?>
