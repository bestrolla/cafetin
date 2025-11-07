<?php
// Incluir sistema de control de acceso
require_once '../../../acces/auth_check.php';

// Verificar que el usuario sea cajero
if (!esCajero()) {
    echo json_encode([
        'error' => 'Acceso denegado. Solo cajeros pueden ver los detalles de facturas.'
    ]);
    exit;
}

header('Content-Type: application/json');

// Configuración de la base de datos
$host = 'localhost';
$dbname = 'cafetin';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Obtener parámetros
    $id_cliente = $_GET['id_cliente'] ?? null;
    $fecha_factura = $_GET['fecha_factura'] ?? null;

    if (!$id_cliente || !$fecha_factura) {
        echo json_encode(['error' => 'Parámetros requeridos: id_cliente y fecha_factura']);
        exit;
    }

    // Consulta para obtener los productos de una factura específica
    $sql = "
        SELECT 
            c.id_credito,
            i.nombre_produc as producto,
            c.cantidad,
            c.total as subtotal,
            DATE(c.fecha_cre) as fecha_compra,
            TIME(c.fecha_cre) as hora_compra,
            c.fecha_cre,
            CONCAT(p.nombre, ' ', p.apellido) as cliente
        FROM credito c
        INNER JOIN cliente cl ON c.id_cliente = cl.id_cliente
        INNER JOIN usuario u ON cl.id_usuario = u.id_usuario
        INNER JOIN persona p ON u.id_persona = p.id_persona
        INNER JOIN inventario i ON c.id_producto = i.id_producto
        WHERE c.id_cliente = :id_cliente 
        AND DATE(c.fecha_cre) = :fecha_factura
        AND c.estado IN ('pendiente', 'parcial')
        ORDER BY c.fecha_cre ASC, TIME(c.fecha_cre) ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
    $stmt->bindParam(':fecha_factura', $fecha_factura, PDO::PARAM_STR);
    $stmt->execute();
    $productos_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Agrupar productos por fecha de compra
    $productos_agrupados = [];
    foreach ($productos_raw as $producto) {
        $fecha = $producto['fecha_compra'];
        if (!isset($productos_agrupados[$fecha])) {
            $productos_agrupados[$fecha] = [
                'fecha' => $fecha,
                'productos' => [],
                'total_fecha' => 0
            ];
        }
        $productos_agrupados[$fecha]['productos'][] = $producto;
        $productos_agrupados[$fecha]['total_fecha'] += $producto['subtotal'];
    }

    // Convertir a array indexado para mantener compatibilidad
    $productos = array_values($productos_agrupados);

    // Obtener información de abonos para esta factura
    $sqlAbonos = "
        SELECT 
            a.monto,
            a.metodo_pago,
            a.observaciones,
            a.fecha_abono
        FROM abonos a
        INNER JOIN credito c ON a.id_credito = c.id_credito
        WHERE c.id_cliente = :id_cliente 
        AND DATE(c.fecha_cre) = :fecha_factura
        ORDER BY a.fecha_abono DESC
    ";

    $stmtAbonos = $pdo->prepare($sqlAbonos);
    $stmtAbonos->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
    $stmtAbonos->bindParam(':fecha_factura', $fecha_factura, PDO::PARAM_STR);
    $stmtAbonos->execute();
    $abonos = $stmtAbonos->fetchAll(PDO::FETCH_ASSOC);

    // Calcular totales
    $total_factura = 0;
    foreach ($productos as $grupo_fecha) {
        $total_factura += $grupo_fecha['total_fecha'];
    }
    $total_abonado = array_sum(array_column($abonos, 'monto'));
    $saldo_pendiente = $total_factura - $total_abonado;

    $response = [
        'productos' => $productos,
        'abonos' => $abonos,
        'resumen' => [
            'total_factura' => $total_factura,
            'total_abonado' => $total_abonado,
            'saldo_pendiente' => $saldo_pendiente,
            'cliente' => $productos[0]['productos'][0]['cliente'] ?? '',
            'fecha_factura' => $fecha_factura
        ]
    ];

    echo json_encode($response);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Error de conexión: ' . $e->getMessage()]);
}
?>