<?php
require_once '../../../BBDD/BBDD.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'data' => null];

try {
    // Validar método
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }

    // Obtener y validar parámetros
    $id = isset($_POST['id_producto']) ? (int)$_POST['id_producto'] : 0;
    $cajasAgregar = isset($_POST['cajas_agregar']) ? (int)$_POST['cajas_agregar'] : 0;
    $sueltasAgregar = isset($_POST['unidades_sueltas_agregar']) ? (int)$_POST['unidades_sueltas_agregar'] : 0;
    $precioCostoUnitarioNuevo = isset($_POST['precio_costo_unitario_nuevo']) ? (float)$_POST['precio_costo_unitario_nuevo'] : null;
    $precioVentaNuevo = isset($_POST['precio_venta_nuevo']) ? (float)$_POST['precio_venta_nuevo'] : null;
    $observacion = isset($_POST['observacion']) ? trim($_POST['observacion']) : null;

    if ($id <= 0) throw new Exception('ID de producto inválido');
    if ($cajasAgregar < 0 || $sueltasAgregar < 0) throw new Exception('Cantidades no pueden ser negativas');

    // Crear tabla historial si no existe (fuera de transacción para evitar commits implícitos)
    $conexion->exec("CREATE TABLE IF NOT EXISTS historial_producto (
        id_historial INT AUTO_INCREMENT PRIMARY KEY,
        id_producto INT NOT NULL,
        fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        cajas_agregar INT NOT NULL,
        unidades_por_caja INT NOT NULL,
        unidades_sueltas_agregar INT NOT NULL,
        unidades_agregadas_total INT NOT NULL,
        precio_venta_usd DECIMAL(10,2) NOT NULL,
        precio_venta_bs DECIMAL(12,2) NOT NULL,
        tasa_dolar DECIMAL(10,2) NOT NULL,
        observacion VARCHAR(255) NULL,
        INDEX (id_producto)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

    // Iniciar transacción
    $conexion->beginTransaction();

    // Bloquear fila de inventario
    $stmt = $conexion->prepare("SELECT id_producto, nombre_produc, cantidad_total, cantidad_caja, caja_produc, precio_venta FROM inventario WHERE id_producto = :id FOR UPDATE");
    $stmt->execute([':id' => $id]);
    $prod = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$prod) {
        throw new Exception('Producto no encontrado');
    }

    $unidPorCaja = (int)$prod['cantidad_caja'];
    if ($unidPorCaja <= 0) {
        throw new Exception('Configuración inválida: unidades por caja debe ser mayor a cero');
    }

    // Calcular unidades agregadas
    $unidadesAgregadasTotal = ($cajasAgregar * $unidPorCaja) + $sueltasAgregar;

    // Actualizar inventario
    $nuevoTotal = (int)$prod['cantidad_total'] + $unidadesAgregadasTotal;
    $nuevasCajas = intdiv($nuevoTotal, $unidPorCaja);

    $sqlUp = "UPDATE inventario SET cantidad_total = :total, caja_produc = :cajas";
    $paramsUp = [
        ':total' => $nuevoTotal,
        ':cajas' => $nuevasCajas,
        ':id' => $id
    ];

    // Actualizar precios si se enviaron
    $precioVentaHistorial = (float)$prod['precio_venta'];
    
    if ($precioCostoUnitarioNuevo !== null && $precioVentaNuevo !== null) {
        // Calcular precio caja basado en el unitario
        $precioCajaNuevo = $precioCostoUnitarioNuevo * $unidPorCaja;
        
        $sqlUp .= ", precio_caja = :precio_caja, precio_venta = :precio_venta, precio_produc = :precio_produc";
        $paramsUp[':precio_caja'] = $precioCajaNuevo;
        $paramsUp[':precio_venta'] = $precioVentaNuevo;
        $paramsUp[':precio_produc'] = $precioCostoUnitarioNuevo;
        
        $precioVentaHistorial = $precioVentaNuevo;
    }

    $sqlUp .= " WHERE id_producto = :id";
    $stmtUp = $conexion->prepare($sqlUp);
    $stmtUp->execute($paramsUp);

    // Obtener tasa de cambio actual
    $tasa = 36.00; // valor por defecto
    try {
        $stmtT = $conexion->query("SELECT valor FROM configuraciones WHERE clave = 'tasa_dolar' LIMIT 1");
        $filaT = $stmtT->fetch(PDO::FETCH_ASSOC);
        if ($filaT && isset($filaT['valor'])) {
            $tasa = (float)$filaT['valor'];
        }
    } catch (Exception $e) {
        // Si falla la lectura, usamos el valor por defecto
    }

    // (Tabla ya asegurada antes de la transacción)

    // Registrar historial con snapshot de precios
    $precioVentaUSD = $precioVentaHistorial;
    $precioVentaBS = $precioVentaUSD * $tasa;

    $stmtH = $conexion->prepare("INSERT INTO historial_producto 
        (id_producto, cajas_agregar, unidades_por_caja, unidades_sueltas_agregar, unidades_agregadas_total, precio_venta_usd, precio_venta_bs, tasa_dolar, observacion)
        VALUES (:id_producto, :cajas_agregar, :unidades_por_caja, :unidades_sueltas_agregar, :unidades_agregadas_total, :precio_venta_usd, :precio_venta_bs, :tasa_dolar, :observacion)");
    $stmtH->execute([
        ':id_producto' => $id,
        ':cajas_agregar' => $cajasAgregar,
        ':unidades_por_caja' => $unidPorCaja,
        ':unidades_sueltas_agregar' => $sueltasAgregar,
        ':unidades_agregadas_total' => $unidadesAgregadasTotal,
        ':precio_venta_usd' => $precioVentaUSD,
        ':precio_venta_bs' => $precioVentaBS,
        ':tasa_dolar' => $tasa,
        ':observacion' => $observacion
    ]);

    // Confirmar (protegido)
    if ($conexion->inTransaction()) {
        $conexion->commit();
    }

    $response['success'] = true;
    $response['data'] = [
        'id_producto' => $id,
        'cantidad_total' => $nuevoTotal,
        'caja_produc' => $nuevasCajas
    ];

} catch (Exception $e) {
    if ($conexion && $conexion->inTransaction()) {
        $conexion->rollBack();
    }
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>