<?php
require_once __DIR__ . '/../../../acces/auth_check.php';
require_once __DIR__ . '/../../../BBDD/BBDD.php';

header('Content-Type: application/json');

// Admin y Cajero pueden consultar
if (!esAdminOCajero()) {
    echo json_encode([
        'success' => false,
        'message' => 'Acceso denegado'
    ]);
    exit();
}

// Umbral predeterminado
$threshold = 50;

// Permitir override por querystring o body JSON
if (isset($_GET['threshold'])) {
    $threshold = max(0, intval($_GET['threshold']));
} else {
    $input = json_decode(file_get_contents('php://input'), true);
    if (is_array($input) && isset($input['threshold'])) {
        $threshold = max(0, intval($input['threshold']));
    }
}

try {
    $conexion = new Conexion();
    $pdo = $conexion->conectar();

    // Intentar leer configuración si existe (inventario_umbral_bajo)
    try {
        $stmtCfg = $pdo->prepare("SELECT valor FROM configuraciones WHERE clave = 'inventario_umbral_bajo' AND activo = 1 LIMIT 1");
        $stmtCfg->execute();
        $cfgVal = $stmtCfg->fetchColumn();
        if ($cfgVal !== false) {
            $cfgNum = intval($cfgVal);
            if ($cfgNum >= 0) {
                $threshold = $cfgNum;
            }
        }
    } catch (Exception $e) {
        // Ignorar si no existe la tabla o clave; usar umbral por defecto
    }

    // Consultar productos con stock bajo
    $stmt = $pdo->prepare(
        "SELECT id_producto, nombre_produc, cantidad_total, unidades_sueltas, activo
         FROM inventario
         WHERE activo = 1 AND cantidad_total <= :threshold
         ORDER BY cantidad_total ASC, nombre_produc ASC"
    );
    $stmt->bindValue(':threshold', $threshold, PDO::PARAM_INT);
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'threshold' => $threshold,
        'count' => count($items),
        'items' => $items,
        'canNavigate' => esAdmin()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener notificaciones de inventario: ' . $e->getMessage()
    ]);
}
?>