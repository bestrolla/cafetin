<?php
require_once '../../../acces/auth_check.php';

if (!esCajero()) {
    echo json_encode(['error' => 'Acceso denegado']);
    exit;
}

header('Content-Type: application/json');

$host = 'localhost';
$dbname = 'cafetin';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $id_cliente = $_GET['id_cliente'] ?? null;
    if (!$id_cliente) {
        echo json_encode(['error' => 'Parámetro requerido: id_cliente']);
        exit;
    }

    $sqlTotales = "
        SELECT 
            COALESCE(SUM(c.total), 0) AS total_factura,
            COALESCE(SUM(a.monto), 0) AS total_abonado
        FROM credito c
        LEFT JOIN abonos a ON c.id_credito = a.id_credito
        WHERE c.id_cliente = :id_cliente
          AND c.estado IN ('pendiente','parcial')
    ";
    $stmt = $pdo->prepare($sqlTotales);
    $stmt->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
    $stmt->execute();
    $totales = $stmt->fetch(PDO::FETCH_ASSOC);

    $total_factura = (float)($totales['total_factura'] ?? 0);
    $total_abonado = (float)($totales['total_abonado'] ?? 0);
    $saldo_pendiente = $total_factura - $total_abonado;

    $sqlFechasAbono = "
        SELECT DATE(a.fecha_abono) AS fecha, SUM(a.monto) AS monto
        FROM abonos a
        INNER JOIN credito c ON a.id_credito = c.id_credito
        WHERE c.id_cliente = :id_cliente
        GROUP BY DATE(a.fecha_abono)
        ORDER BY DATE(a.fecha_abono) DESC
    ";
    $stmtFechas = $pdo->prepare($sqlFechasAbono);
    $stmtFechas->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
    $stmtFechas->execute();
    $fechas_abono = $stmtFechas->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'total_factura' => $total_factura,
        'total_abonado' => $total_abonado,
        'saldo_pendiente' => $saldo_pendiente,
        'fechas_abono' => array_map(function($r){
            return [
                'fecha' => $r['fecha'],
                'monto' => (float)$r['monto']
            ];
        }, $fechas_abono)
    ]);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Error de conexión: ' . $e->getMessage()]);
}
?>