<?php
header('Content-Type: application/json');

require_once '../../../BBDD/BBDD.php';

try {
    // Verificar si la tabla abonos existe, si no, crearla
    $checkTable = "SHOW TABLES LIKE 'abonos'";
    $result = $conexion->query($checkTable);
    
    if ($result->rowCount() == 0) {
        $createTable = "
            CREATE TABLE abonos (
                id_abono INT AUTO_INCREMENT PRIMARY KEY,
                id_credito INT NOT NULL,
                monto DECIMAL(10,2) NOT NULL,
                metodo_pago VARCHAR(50) DEFAULT 'efectivo',
                observaciones TEXT,
                fecha_abono TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (id_credito) REFERENCES credito(id_credito)
            )
        ";
        $conexion->exec($createTable);
    }

    // Obtener parámetros de fecha si se proporcionan
    $fecha_inicio = $_GET['fecha_inicio'] ?? null;
    $fecha_fin = $_GET['fecha_fin'] ?? null;
    
    // Consulta para obtener las facturas agrupadas por cliente y fecha (igual que el módulo de cuentas)
    $sql = "
        SELECT 
            MIN(c.id_credito) as id_credito,
            CONCAT(p.nombre, ' ', p.apellido) as cliente,
            c.id_cliente,
            DATE(c.fecha_cre) as fecha_factura,
            c.fecha_cre,
            COUNT(c.id_credito) as total_productos,
            SUM(c.total) as total_factura,
            COALESCE(SUM(a.monto), 0) as total_abonado,
            (SUM(c.total) - COALESCE(SUM(a.monto), 0)) as saldo_pendiente,
            CASE 
                WHEN (SUM(c.total) - COALESCE(SUM(a.monto), 0)) <= 0 THEN 'pagado'
                WHEN COALESCE(SUM(a.monto), 0) > 0 THEN 'parcial'
                ELSE 'pendiente'
            END as estado
        FROM credito c
        INNER JOIN cliente cl ON c.id_cliente = cl.id_cliente
        INNER JOIN usuario u ON cl.id_usuario = u.id_usuario
        INNER JOIN persona p ON u.id_persona = p.id_persona
        LEFT JOIN abonos a ON c.id_credito = a.id_credito
        WHERE c.estado IN ('pendiente', 'parcial')";
    
    // Agregar filtros de fecha si se proporcionan
    if ($fecha_inicio && $fecha_fin) {
        $sql .= " AND DATE(c.fecha_cre) BETWEEN :fecha_inicio AND :fecha_fin";
    }
    
    $sql .= " GROUP BY c.id_cliente, DATE(c.fecha_cre), p.nombre, p.apellido
              HAVING saldo_pendiente > 0
              ORDER BY c.fecha_cre DESC";
    
    $stmt = $conexion->prepare($sql);
    
    // Vincular parámetros si existen
    if ($fecha_inicio && $fecha_fin) {
        $stmt->bindParam(':fecha_inicio', $fecha_inicio);
        $stmt->bindParam(':fecha_fin', $fecha_fin);
    }
    
    $stmt->execute();
    $deudas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($deudas);
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al obtener deudas: ' . $e->getMessage()]);
}
?>