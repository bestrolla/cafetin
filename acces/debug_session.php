<?php
// Endpoint temporal para depuración de sesión y BD
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../BBDD/BBDD.php';

initSessionIfNeeded();

header('Content-Type: text/plain; charset=utf-8');

echo "== SESSION INFO ==\n";
echo "session_status: " . session_status() . "\n";
echo "session_id: " . session_id() . "\n";
echo "session_name: " . session_name() . "\n";
echo "Cookie header: " . ($_SERVER['HTTP_COOKIE'] ?? '(none)') . "\n";
echo "\n";

$params = session_get_cookie_params();
echo "== COOKIE PARAMS ==\n";
foreach ($params as $k => $v) {
    echo "$k: $v\n";
}
echo "\n";

echo "== SERVER INFO ==\n";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? '') . "\n";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? '') . "\n";
echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? '') . "\n";
echo "APP_BASE: " . appBasePath() . "\n";
echo "\n";

echo "== SESSION PAYLOAD ==\n";
foreach ($_SESSION as $k => $v) {
    echo "$k => ";
    if (is_scalar($v)) echo $v . "\n";
    else echo print_r($v, true) . "\n";
}
echo "\n";

echo "== DB TEST ==\n";
try {
    $pdo = Conexion::getConnection();
    $tables = ['usuario', 'producto'];
    foreach ($tables as $t) {
        try {
            $st = $pdo->query("SELECT COUNT(*) AS c FROM `" . $t . "`");
            $row = $st->fetch(PDO::FETCH_ASSOC);
            echo "$t count: " . ($row['c'] ?? '(n/a)') . "\n";
        } catch (Exception $e) {
            echo "$t: error -> " . $e->getMessage() . "\n";
        }
    }
} catch (Exception $e) {
    echo "DB connect error: " . $e->getMessage() . "\n";
}

echo "\n== END ==\n";

// EOF
