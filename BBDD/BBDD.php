<?php
// ===============================================
// 📦 CONEXIÓN A LA BASE DE DATOS - cafetin
// Autor: Ángel
// ===============================================

class Conexion {
    private $driver = 'mysql';
    private $host = 'localhost';
    private $dbname = 'cafetin';
    private $username = 'root';
    private $password = '';
    private $sqlite_path;
    private $conexion;

    public function __construct() {
        $this->sqlite_path = __DIR__ . '/cafetin.db';
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

// Mantener compatibilidad con código existente que usa $conexion directamente
try {
    $conexionObj = new Conexion();
    if (isset($_ENV['CAFETIN_DB_DRIVER'])) {
        if ($_ENV['CAFETIN_DB_DRIVER'] === 'sqlite') {
            $ruta = isset($_ENV['CAFETIN_SQLITE_PATH']) ? $_ENV['CAFETIN_SQLITE_PATH'] : null;
            $conexionObj->usarSqlite($ruta);
        }
    }
    try {
        $conexion = $conexionObj->conectar();
    } catch (Exception $e) {
        if (extension_loaded('pdo_sqlite')) {
            $ruta = (isset($_ENV['CAFETIN_SQLITE_PATH']) && $_ENV['CAFETIN_SQLITE_PATH']) ? $_ENV['CAFETIN_SQLITE_PATH'] : (__DIR__ . '/cafetin.db');
            $conexionObj->usarSqlite($ruta);
            $conexion = $conexionObj->conectar();
        } else {
            throw $e;
        }
    }
    $driverActual = $conexion->getAttribute(PDO::ATTR_DRIVER_NAME);
    if ($driverActual === 'sqlite') {
        $dump = __DIR__ . '/cafetin.sql';
        if (file_exists($dump)) {
            $t = $conexion->query("SELECT 1 FROM sqlite_master WHERE type='table' AND name='usuario'")->fetchColumn();
            $c = 0;
            if ($t) { $c = (int)$conexion->query("SELECT COUNT(1) FROM usuario")->fetchColumn(); }
            if (!$t || $c === 0) { sqliteImportFromMysqlDump($conexion, $dump); }
        }
        $t = $conexion->query("SELECT 1 FROM sqlite_master WHERE type='table' AND name='usuario'")->fetchColumn();
        if (!$t) {
            $conexion->exec("CREATE TABLE IF NOT EXISTS rol (id_rol INTEGER PRIMARY KEY AUTOINCREMENT, nombre_rol TEXT NOT NULL UNIQUE)");
            $conexion->exec("CREATE TABLE IF NOT EXISTS persona (id_persona INTEGER PRIMARY KEY AUTOINCREMENT, nombre TEXT NOT NULL, apellido TEXT NOT NULL, cedula TEXT, telefono TEXT)");
            $conexion->exec("CREATE TABLE IF NOT EXISTS usuario (id_usuario INTEGER PRIMARY KEY AUTOINCREMENT, id_persona INTEGER NOT NULL, id_rol INTEGER NOT NULL, usuario TEXT NOT NULL UNIQUE, contrasena TEXT NOT NULL, estado INTEGER NOT NULL DEFAULT 1, FOREIGN KEY(id_persona) REFERENCES persona(id_persona), FOREIGN KEY(id_rol) REFERENCES rol(id_rol))");
            $conexion->exec("INSERT OR IGNORE INTO rol (nombre_rol) VALUES ('admin'), ('cajero'), ('cliente')");
        }
        $stmtAdmin = $conexion->prepare("SELECT id_usuario FROM usuario WHERE usuario = :u LIMIT 1");
        $stmtAdmin->execute([':u' => 'admin']);
        $rowAdmin = $stmtAdmin->fetch(PDO::FETCH_ASSOC);
        if ($rowAdmin && isset($rowAdmin['id_usuario'])) {
            $hash = password_hash('Admin123$', PASSWORD_DEFAULT);
            $upd = $conexion->prepare("UPDATE usuario SET contrasena = :h WHERE id_usuario = :id");
            $upd->execute([':h' => $hash, ':id' => (int)$rowAdmin['id_usuario']]);
        } else {
            $idRolAdmin = (int)$conexion->query("SELECT id_rol FROM rol WHERE nombre_rol = 'admin' LIMIT 1")->fetchColumn();
            if ($idRolAdmin > 0) {
                $conexion->beginTransaction();
                $stmtP = $conexion->prepare("INSERT INTO persona (nombre, apellido, cedula, telefono) VALUES (:n,:a,:c,:t)");
                $stmtP->execute([':n' => 'Administrador', ':a' => 'Sistema', ':c' => '0', ':t' => '0000000000']);
                $idPersona = (int)$conexion->lastInsertId();
                $hash = password_hash('Admin123$', PASSWORD_DEFAULT);
                $stmtU = $conexion->prepare("INSERT INTO usuario (id_persona, usuario, contrasena, id_rol) VALUES (:idp, :u, :h, :r)");
                $stmtU->execute([':idp' => $idPersona, ':u' => 'admin', ':h' => $hash, ':r' => $idRolAdmin]);
                $idUsuario = (int)$conexion->lastInsertId();
                $tAdmin = $conexion->query("SELECT 1 FROM sqlite_master WHERE type='table' AND name='admin'")->fetchColumn();
                if (!$tAdmin) {
                    $conexion->exec("CREATE TABLE IF NOT EXISTS admin (id_admin INTEGER PRIMARY KEY AUTOINCREMENT, id_usuario INTEGER NOT NULL)");
                }
                $stmtA = $conexion->prepare("INSERT INTO admin (id_usuario) VALUES (:id)");
                $stmtA->execute([':id' => $idUsuario]);
                $conexion->commit();
            }
        }
    } else {
        $stmtAdmin = $conexion->prepare("SELECT id_usuario FROM usuario WHERE usuario = :u LIMIT 1");
        $stmtAdmin->execute([':u' => 'admin']);
        $rowAdmin = $stmtAdmin->fetch(PDO::FETCH_ASSOC);
        if ($rowAdmin && isset($rowAdmin['id_usuario'])) {
            $hash = password_hash('Admin123$', PASSWORD_DEFAULT);
            $upd = $conexion->prepare("UPDATE usuario SET contrasena = :h WHERE id_usuario = :id");
            $upd->execute([':h' => $hash, ':id' => (int)$rowAdmin['id_usuario']]);
        }
    }
} catch (Exception $e) {
    echo $e->getMessage();
    exit;
}
?>
