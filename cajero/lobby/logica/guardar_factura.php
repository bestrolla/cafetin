<?php
require_once __DIR__ . '/../../../BBDD/BBDD.php';
$conexion = Conexion::getConnection();

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
        
        // Verificar y descontar inventario por cada producto
        foreach ($productos as $producto) {
            $idProd = (int)($producto['id'] ?? 0);
            $cantSolicitada = (int)($producto['cantidad'] ?? 0);
            if ($idProd <= 0 || $cantSolicitada <= 0) {
                throw new Exception('Producto o cantidad inválidos');
            }
            // Obtener stock actual (en cajas y unidades por caja)
            $stmtInv = $conexion->prepare("SELECT caja_produc, cantidad_caja, COALESCE(cantidad_total,0) AS cantidad_total FROM inventario WHERE id_producto = ? FOR UPDATE");
            $stmtInv->execute([$idProd]);
            $inv = $stmtInv->fetch(PDO::FETCH_ASSOC);
            if (!$inv) {
                throw new Exception('Producto no existe en inventario');
            }
            $cajas = (int)($inv['caja_produc'] ?? 0);
            $unidadesPorCaja = (int)($inv['cantidad_caja'] ?? 0);
            $stockUnidades = (int)$inv['cantidad_total'];
            if ($cantSolicitada > $stockUnidades) {
                throw new Exception('Stock insuficiente para el producto ID ' . $idProd);
            }
            $nuevoStockUnidades = $stockUnidades - $cantSolicitada;
            $nuevasCajas = $unidadesPorCaja > 0 ? intdiv($nuevoStockUnidades, $unidadesPorCaja) : $nuevoStockUnidades;
            // Activar/inactivar según stock
            $activo = $nuevoStockUnidades > 0 ? 1 : 0;
            $stmtUpd = $conexion->prepare("UPDATE inventario SET caja_produc = ?, cantidad_total = ?, activo = ? WHERE id_producto = ?");
            $stmtUpd->execute([$nuevasCajas, $nuevoStockUnidades, $activo, $idProd]);
        }
        
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
            
            if ($conexion->inTransaction()) {
                $conexion->commit();
            }
            
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
            
            if ($conexion->inTransaction()) {
                $conexion->commit();
            }
            
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
        if ($conexion && $conexion->inTransaction()) {
            $conexion->rollBack();
        }
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