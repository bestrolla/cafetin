<?php
require_once __DIR__ . '/../../../acces/auth_check.php';
require_once __DIR__ . '/../../../BBDD/BBDD.php';
$conexion = Conexion::getConnection();

if (!esAdmin()) {
    echo json_encode(['error' => 'Acceso denegado']);
    exit;
}

header('Content-Type: application/json');

try {

    $id_cliente = $_GET['id_cliente'] ?? null;
    if (!$id_cliente) {
        echo json_encode(['error' => 'Parámetro requerido: id_cliente']);
        exit;
    }

    $sql = "
        SELECT 
            c.id_credito,
            DATE(c.fecha_cre) AS fecha,
            c.total AS total_credito,
            COALESCE(SUM(a.monto), 0) AS total_abonado
        FROM credito c
        LEFT JOIN abonos a ON a.id_credito = c.id_credito
        WHERE c.id_cliente = :id_cliente
          AND c.estado IN ('pendiente','parcial')
        GROUP BY c.id_credito
        ORDER BY c.fecha_cre DESC
    ";
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $creditos = array_map(function($r){
        $total = (float)$r['total_credito'];
        $abonado = (float)$r['total_abonado'];
        return [
            'id_credito' => (int)$r['id_credito'],
            'fecha' => $r['fecha'],
            'total' => $total,
            'abonado' => $abonado,
            'saldo' => $total - $abonado
        ];
    }, $rows);

    echo json_encode($creditos);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Error de conexión: ' . $e->getMessage()]);
}
?>