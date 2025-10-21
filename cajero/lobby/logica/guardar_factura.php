<?php
require_once '../../../BBDD/BBDD.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $tipo = $data['tipo'] ?? ''; // 'contado' o 'credito'
    $cliente_id = $data['cliente_id'] ?? null;
    $productos = $data['productos'] ?? [];
    $total_dolares = $data['total_dolares'] ?? 0;
    $total_bolivares = $data['total_bolivares'] ?? 0;
    $cajero_id = 1; // Por defecto, deberías obtenerlo de la sesión
    
    try {
        $conexion->beginTransaction();
        
        if ($tipo === 'contado') {
            // Guardar venta al contado
            foreach ($productos as $producto) {
                $sqlVenta = "INSERT INTO ventas (id_cliente, id_cajero, id_producto, cantidad, total) 
                            VALUES (?, ?, ?, ?, ?)";
                $stmtVenta = $conexion->prepare($sqlVenta);
                $stmtVenta->execute([
                    $cliente_id,
                    $cajero_id,
                    $producto['id'],
                    $producto['cantidad'],
                    $producto['total']
                ]);
            }
            
            $conexion->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Venta registrada exitosamente',
                'numero_factura' => $conexion->lastInsertId(),
                'tipo' => 'contado'
            ]);
            
        } elseif ($tipo === 'credito') {
            // Guardar crédito
            foreach ($productos as $producto) {
                $sqlCredito = "INSERT INTO credito (id_cliente, id_cajero, id_producto, cantidad, total, estado) 
                              VALUES (?, ?, ?, ?, ?, 'pendiente')";
                $stmtCredito = $conexion->prepare($sqlCredito);
                $stmtCredito->execute([
                    $cliente_id,
                    $cajero_id,
                    $producto['id'],
                    $producto['cantidad'],
                    $producto['total']
                ]);
            }
            
            $conexion->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Crédito registrado exitosamente',
                'numero_factura' => $conexion->lastInsertId(),
                'tipo' => 'credito'
            ]);
        } else {
            throw new Exception('Tipo de factura no válido');
        }
        
    } catch (Exception $e) {
        $conexion->rollBack();
        echo json_encode([
            'success' => false,
            'message' => 'Error al guardar factura: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
}
?>