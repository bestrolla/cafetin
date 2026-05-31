<?php
require_once __DIR__ . '/../../../BBDD/BBDD.php';
$conexion = Conexion::getConnection();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $cedula = $_GET['cedula'] ?? '';
    
    try {
        // Buscar cliente por teléfono (usando teléfono como cédula)
        $sql = "SELECT 
                    c.id_cliente,
                    p.nombre,
                    p.apellido, 
                    p.telefono as cedula,
                    p.telefono,
                    c.alias
                FROM cliente c
                JOIN usuario u ON c.id_usuario = u.id_usuario
                JOIN persona p ON u.id_persona = p.id_persona
                WHERE p.telefono = ?";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$cedula]);
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($cliente) {
            echo json_encode([
                'success' => true,
                'cliente' => $cliente
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Cliente no encontrado'
            ]);
        }
        
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error en la búsqueda: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
}
?>