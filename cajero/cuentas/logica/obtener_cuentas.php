<?php
header('Content-Type: application/json');

// Configuración de la base de datos
$host = 'localhost';
$dbname = 'cafetin';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verificar si la tabla abonos existe, si no, crearla
    $checkTable = "SHOW TABLES LIKE 'abonos'";
    $result = $pdo->query($checkTable);
    
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
        $pdo->exec($createTable);
    }

    // Consulta para obtener las facturas agrupadas por cliente y fecha
    $sql = "
        SELECT 
            MIN(c.id_credito) as id_factura,
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
            END as estado_factura
        FROM credito c
        INNER JOIN cliente cl ON c.id_cliente = cl.id_cliente
        INNER JOIN usuario u ON cl.id_usuario = u.id_usuario
        INNER JOIN persona p ON u.id_persona = p.id_persona
        LEFT JOIN abonos a ON c.id_credito = a.id_credito
        WHERE c.estado IN ('pendiente', 'parcial')
        GROUP BY c.id_cliente, DATE(c.fecha_cre), p.nombre, p.apellido
        HAVING saldo_pendiente > 0
        ORDER BY c.fecha_cre DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($facturas);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Error de conexión: ' . $e->getMessage()]);
}
?>