<!DOCTYPE html>
<html>
<head>
    <title>Debug Deudas</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <h1>Debug - Datos de Deudas</h1>
    
    <?php
    require_once '../../../BBDD/BBDD.php';
    
    try {
        // Verificar si existe la tabla credito
        $sql = "SHOW TABLES LIKE 'credito'";
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        $table_exists = $stmt->fetch();
        
        if ($table_exists) {
            echo "<p class='success'>✅ La tabla 'credito' existe</p>";
            
            // Contar registros en la tabla credito
            $sql = "SELECT COUNT(*) as total FROM credito";
            $stmt = $conexion->prepare($sql);
            $stmt->execute();
            $count = $stmt->fetch();
            echo "<p>📊 Total de registros en credito: " . $count['total'] . "</p>";
            
            if ($count['total'] > 0) {
                // Mostrar algunos registros con JOIN
                $sql = "SELECT 
                    c.id_credito,
                    c.id_cliente,
                    CONCAT(p.nombre, ' ', p.apellido) as cliente,
                    i.nombre_produc as producto,
                    c.cantidad,
                    c.total,
                    c.estado,
                    c.fecha_cre
                FROM credito c
                LEFT JOIN cliente cl ON c.id_cliente = cl.id_cliente
                LEFT JOIN usuario u ON cl.id_usuario = u.id_usuario
                LEFT JOIN persona p ON u.id_persona = p.id_persona
                LEFT JOIN inventario i ON c.id_producto = i.id_producto
                LIMIT 10";
                
                $stmt = $conexion->prepare($sql);
                $stmt->execute();
                $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo "<h3>Registros de crédito:</h3>";
                if (count($registros) > 0) {
                    echo "<table>";
                    echo "<tr>";
                    foreach (array_keys($registros[0]) as $column) {
                        echo "<th>" . htmlspecialchars($column) . "</th>";
                    }
                    echo "</tr>";
                    
                    foreach ($registros as $registro) {
                        echo "<tr>";
                        foreach ($registro as $value) {
                            echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                        }
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p class='error'>No hay registros en la tabla credito</p>";
                }
            } else {
                echo "<p class='error'>La tabla credito está vacía</p>";
            }
        } else {
            echo "<p class='error'>❌ La tabla 'credito' NO existe</p>";
        }
        
    } catch (Exception $e) {
        echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
    }
    ?>
</body>
</html>