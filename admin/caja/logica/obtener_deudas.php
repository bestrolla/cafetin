<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../../../BBDD/BBDD.php';

try {
    // Obtener parámetros de fecha si se proporcionan
    $fecha_inicio = $_GET['fecha_inicio'] ?? null;
    $fecha_fin = $_GET['fecha_fin'] ?? null;
    
    // Parámetros de búsqueda por cliente
    $buscar_nombre = $_GET['buscar_nombre'] ?? null;
    $buscar_apellido = $_GET['buscar_apellido'] ?? null;
    $buscar_cedula = $_GET['buscar_cedula'] ?? null;

    // Consulta para obtener las facturas agrupadas por cliente y fecha (igual que el módulo de cuentas)
    $sql = "
        SELECT 
            MIN(c.id_credito) as id_credito,
            CONCAT(p.nombre, ' ', p.apellido) as cliente,
            p.nombre AS nombre,
            p.apellido AS apellido,
            p.cedula AS cedula,
            c.id_cliente,
            DATE(c.fecha_cre) as fecha_factura,
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

    // Filtros de texto por cliente
    if ($buscar_nombre) {
        $sql .= " AND p.nombre LIKE :buscar_nombre";
    }
    if ($buscar_apellido) {
        $sql .= " AND p.apellido LIKE :buscar_apellido";
    }
    if ($buscar_cedula) {
        $sql .= " AND p.cedula LIKE :buscar_cedula";
    }
    
    $sql .= " GROUP BY c.id_cliente, fecha_factura, p.nombre, p.apellido, p.cedula
              HAVING saldo_pendiente > 0
              ORDER BY fecha_factura DESC";
    
    $stmt = $conexion->prepare($sql);
    
    // Vincular parámetros si existen
    if ($fecha_inicio && $fecha_fin) {
        $stmt->bindParam(':fecha_inicio', $fecha_inicio);
        $stmt->bindParam(':fecha_fin', $fecha_fin);
    }
    if ($buscar_nombre) {
        $likeNombre = "%" . $buscar_nombre . "%";
        $stmt->bindParam(':buscar_nombre', $likeNombre);
    }
    if ($buscar_apellido) {
        $likeApellido = "%" . $buscar_apellido . "%";
        $stmt->bindParam(':buscar_apellido', $likeApellido);
    }
    if ($buscar_cedula) {
        $likeCedula = "%" . $buscar_cedula . "%";
        $stmt->bindParam(':buscar_cedula', $likeCedula);
    }
    
    $stmt->execute();
    $deudas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($deudas);
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al obtener deudas: ' . $e->getMessage()]);
}
?>