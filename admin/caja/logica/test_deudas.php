<?php
require_once '../../../BBDD/BBDD.php';

echo "<h2>Verificación de datos en la base de datos</h2>";

try {
    // Verificar si existe la tabla credito
    $sql = "SHOW TABLES LIKE 'credito'";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $table_exists = $stmt->fetch();
    
    if ($table_exists) {
        echo "<p>✅ La tabla 'credito' existe</p>";
        
        // Contar registros en la tabla credito
        $sql = "SELECT COUNT(*) as total FROM credito";
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        $count = $stmt->fetch();
        echo "<p>📊 Total de registros en credito: " . $count['total'] . "</p>";
        
        if ($count['total'] > 0) {
            // Mostrar algunos registros
            $sql = "SELECT * FROM credito LIMIT 5";
            $stmt = $conexion->prepare($sql);
            $stmt->execute();
            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<h3>Primeros 5 registros:</h3>";
            echo "<pre>";
            print_r($registros);
            echo "</pre>";
        }
    } else {
        echo "<p>❌ La tabla 'credito' NO existe</p>";
        
        // Mostrar todas las tablas disponibles
        $sql = "SHOW TABLES";
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<h3>Tablas disponibles:</h3>";
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>" . $table . "</li>";
        }
        echo "</ul>";
    }
    
} catch (PDOException $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>