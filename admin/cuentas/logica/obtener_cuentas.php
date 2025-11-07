<?php
// Incluir sistema de control de acceso
require_once '../../../acces/auth_check.php';

// Verificar que el usuario sea administrador
if (!esAdmin()) {
    echo json_encode([
        'error' => 'Acceso denegado. Solo administradores pueden ver las cuentas.'
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

    // Consulta corregida para evitar duplicar SUM(c.total) por múltiples filas en abonos
    // Se agrega una subconsulta que suma abonos por crédito y se une por id_credito
    $sql = "
        SELECT 
            CONCAT(p.nombre, ' ', p.apellido) AS cliente,
            c.id_cliente,
            COUNT(c.id_credito) AS total_productos,
            SUM(c.total) AS total_factura,
            COALESCE(SUM(a.total_abonado), 0) AS total_abonado,
            (SUM(c.total) - COALESCE(SUM(a.total_abonado), 0)) AS saldo_pendiente,
            CASE 
                WHEN (SUM(c.total) - COALESCE(SUM(a.total_abonado), 0)) <= 0 THEN 'pagado'
                WHEN COALESCE(SUM(a.total_abonado), 0) > 0 THEN 'parcial'
                ELSE 'pendiente'
            END AS estado_factura
        FROM credito c
        INNER JOIN cliente cl ON c.id_cliente = cl.id_cliente
        INNER JOIN usuario u ON cl.id_usuario = u.id_usuario
        INNER JOIN persona p ON u.id_persona = p.id_persona
        LEFT JOIN (
            SELECT id_credito, SUM(monto) AS total_abonado
            FROM abonos
            GROUP BY id_credito
        ) a ON a.id_credito = c.id_credito
        WHERE c.estado IN ('pendiente', 'parcial')
        GROUP BY c.id_cliente, p.nombre, p.apellido
        HAVING saldo_pendiente > 0
        ORDER BY p.nombre ASC, p.apellido ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($facturas);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Error de conexión: ' . $e->getMessage()]);
}
?>