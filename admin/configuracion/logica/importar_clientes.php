<?php
require_once __DIR__ . '/../../../acces/auth_check.php';
require_once __DIR__ . '/../../../BBDD/BBDD.php';

header('Content-Type: application/json');

if (!esAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

if (!isset($_FILES['archivo']) || !is_uploaded_file($_FILES['archivo']['tmp_name'])) {
    echo json_encode(['success' => false, 'message' => 'Archivo no recibido']);
    exit;
}

$nombreArchivo = $_FILES['archivo']['name'] ?? '';
$tmpFile = $_FILES['archivo']['tmp_name'];
$ext = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));

if ($ext !== 'csv') {
    echo json_encode(['success' => false, 'message' => 'Formato no soportado. Use CSV']);
    exit;
}

$handle = fopen($tmpFile, 'r');
if (!$handle) {
    echo json_encode(['success' => false, 'message' => 'No se pudo leer el archivo']);
    exit;
}

$firstLine = fgets($handle);
rewind($handle);
$delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';

$headers = fgetcsv($handle, 0, $delimiter);
if (!$headers || !count($headers)) {
    echo json_encode(['success' => false, 'message' => 'Encabezados inválidos']);
    fclose($handle);
    exit;
}

$map = [];
foreach ($headers as $idx => $h) {
    $k = strtolower(trim($h));
    $k = strtr($k, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ñ'=>'n']);
    $k = str_replace([' ', '-'], '_', $k);
    if ($k === 'cedula' || $k === 'cedula_') $k = 'cedula';
    if ($k === 'deuda_en_dolares' || $k === 'deuda_dolares' || $k === 'deuda_usd') $k = 'deuda_dolares';
    if ($k === 'deuda_en_bolivares' || $k === 'deuda_bolivares' || $k === 'deuda_bs') $k = 'deuda_bolivares';
    if ($k === 'deuda_total' || $k === 'total_deuda') $k = 'deuda_total';
    $map[$idx] = $k;
}

// Requisitos: cédula, nombre y apellido
$required = ['cedula','nombre','apellido'];
foreach ($required as $req) {
    if (!in_array($req, $map, true)) {
        echo json_encode(['success' => false, 'message' => 'Faltan columnas requeridas: cedula, nombre y apellido']);
        fclose($handle);
        exit;
    }
}

$insertados = 0;
$actualizados = 0;
$omitidos = 0;
$errores = 0;
$creditos = 0;
$detalles = [];

try {
    $pdo = $conexion;
    $pdo->beginTransaction();

    $stmtTasa = $pdo->prepare("SELECT valor FROM configuraciones WHERE clave = 'tasa_dolar' AND activo = 1");
    $stmtTasa->execute();
    $tasaRow = $stmtTasa->fetch(PDO::FETCH_ASSOC);
    $tasaCambio = $tasaRow ? floatval($tasaRow['valor']) : 36.0;
    if ($tasaCambio <= 0) { $tasaCambio = 36.0; }

    $stmtRol = $pdo->prepare("SELECT id_rol FROM rol WHERE nombre_rol = 'cliente' LIMIT 1");
    $stmtRol->execute();
    $rolRow = $stmtRol->fetch(PDO::FETCH_ASSOC);
    $idRolCliente = $rolRow ? (int)$rolRow['id_rol'] : null;
    if (!$idRolCliente) {
        $pdo->exec("INSERT INTO rol (nombre_rol) VALUES ('cliente')");
        $idRolCliente = (int)$pdo->lastInsertId();
    }

    $rowNum = 1;
    while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
        $rowNum++;
        if (!is_array($row) || !count($row)) {
            $omitidos++;
            $detalles[] = ['fila' => $rowNum, 'estado' => 'omitido', 'mensaje' => 'Fila vacía'];
            continue;
        }
        $data = [];
        foreach ($row as $i => $val) {
            $key = $map[$i] ?? ('col'.$i);
            $data[$key] = is_string($val) ? trim($val) : $val;
        }

        $cedula = $data['cedula'] ?? '';
        $nombre = normalizarTextoNombre($data['nombre'] ?? '');
        $apellido = normalizarTextoNombre($data['apellido'] ?? '');
        $telefono = $data['telefono'] ?? '';
        $alias = normalizarTextoNombre($data['alias'] ?? '');
        $deuda_dolares_raw = $data['deuda_dolares'] ?? '';
        $deuda_bolivares_raw = $data['deuda_bolivares'] ?? '';
        $deuda_total_raw = $data['deuda_total'] ?? '';

        // Validación requerida: cédula, nombre, apellido
        if ($cedula === '' || $nombre === '' || $apellido === '') {
            $omitidos++;
            $detalles[] = ['fila' => $rowNum, 'estado' => 'omitido', 'mensaje' => 'Faltan campos requeridos'];
            continue;
        }

        // Normalizar cédula y validar > 0
        $cedula = is_string($cedula) ? preg_replace('/[^0-9]/', '', $cedula) : $cedula;
        $cedulaInt = ($cedula !== '' && $cedula !== null) ? (int)$cedula : 0;
        if ($cedulaInt <= 0) {
            $omitidos++;
            $detalles[] = ['fila' => $rowNum, 'estado' => 'omitido', 'mensaje' => 'Cédula inválida'];
            continue;
        }

        try {
            // Buscar por cédula
            $stmtCheckPersona = $pdo->prepare("SELECT id_persona FROM persona WHERE cedula = ? LIMIT 1");
            $stmtCheckPersona->execute([$cedulaInt]);
            $personaRow = $stmtCheckPersona->fetch(PDO::FETCH_ASSOC);

            if ($personaRow) {
                $idPersona = (int)$personaRow['id_persona'];
                $stmtUpdPersona = $pdo->prepare("UPDATE persona SET nombre = ?, apellido = ?, telefono = ? WHERE id_persona = ?");
                $stmtUpdPersona->execute([$nombre, $apellido, $telefono, $idPersona]);

                $stmtUsuarioByPersona = $pdo->prepare("SELECT id_usuario FROM usuario WHERE id_persona = ? LIMIT 1");
                $stmtUsuarioByPersona->execute([$idPersona]);
                $usuarioRow = $stmtUsuarioByPersona->fetch(PDO::FETCH_ASSOC);
                if ($usuarioRow) {
                    $idUsuario = (int)$usuarioRow['id_usuario'];
                } else {
                    $username = strtolower(preg_replace('/\s+/', '', $nombre.$apellido));
                    $passwordHash = password_hash($cedulaInt, PASSWORD_DEFAULT);
                    $stmtInsUsuario = $pdo->prepare("INSERT INTO usuario (id_persona, usuario, contrasena, id_rol) VALUES (?, ?, ?, ?)");
                    $stmtInsUsuario->execute([$idPersona, $username, $passwordHash, $idRolCliente]);
                    $idUsuario = (int)$pdo->lastInsertId();
                }

                $stmtClienteByUsuario = $pdo->prepare("SELECT id_cliente FROM cliente WHERE id_usuario = ? LIMIT 1");
                $stmtClienteByUsuario->execute([$idUsuario]);
                $clienteRow = $stmtClienteByUsuario->fetch(PDO::FETCH_ASSOC);
                $descripcion = "Cliente: $nombre $apellido - Tel: $telefono";
                if ($clienteRow) {
                    $stmtUpdCliente = $pdo->prepare("UPDATE cliente SET alias = ?, descripcion = ? WHERE id_cliente = ?");
                    $stmtUpdCliente->execute([$alias, $descripcion, (int)$clienteRow['id_cliente']]);
                    $idCliente = (int)$clienteRow['id_cliente'];
                    $actualizados++;
                    $detalles[] = ['fila' => $rowNum, 'estado' => 'actualizado'];
                } else {
                    $stmtInsCliente = $pdo->prepare("INSERT INTO cliente (alias, descripcion, id_usuario) VALUES (?, ?, ?)");
                    $stmtInsCliente->execute([$alias, $descripcion, $idUsuario]);
                    $idCliente = (int)$pdo->lastInsertId();
                    $insertados++;
                    $detalles[] = ['fila' => $rowNum, 'estado' => 'insertado'];
                }
                $valUSD = 0.0;
                $usd = is_string($deuda_dolares_raw) ? str_replace(['$', 'bs', 'Bs', 'BS', ' ', ','], ['', '', '', '', '', '.'], $deuda_dolares_raw) : $deuda_dolares_raw;
                $bs = is_string($deuda_bolivares_raw) ? str_replace(['$', 'bs', 'Bs', 'BS', ' ', ','], ['', '', '', '', '', '.'], $deuda_bolivares_raw) : $deuda_bolivares_raw;
                $tot = is_string($deuda_total_raw) ? str_replace(['$', 'bs', 'Bs', 'BS', ' ', ','], ['', '', '', '', '', '.'], $deuda_total_raw) : $deuda_total_raw;
                $usdF = is_numeric($usd) ? floatval($usd) : 0.0;
                $bsF = is_numeric($bs) ? floatval($bs) : 0.0;
                $totF = is_numeric($tot) ? floatval($tot) : 0.0;
                if ($usdF > 0) { $valUSD = round($usdF, 2); }
                elseif ($bsF > 0 && $tasaCambio > 0) { $valUSD = round($bsF / $tasaCambio, 2); }
                elseif ($totF > 0) { $valUSD = round($totF, 2); }
                if ($valUSD > 0 && isset($idCliente)) {
                    $stmtInsCredito = $pdo->prepare("INSERT INTO credito (id_cajero, id_cliente, id_producto, cantidad, total, estado) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmtInsCredito->execute([null, $idCliente, null, 1, $valUSD, 'pendiente']);
                    $creditos++;
                }
            } else {
                // Insertar con cédula requerida
                $stmtInsPersona = $pdo->prepare("INSERT INTO persona (cedula, nombre, apellido, telefono) VALUES (?, ?, ?, ?)");
                $stmtInsPersona->execute([$cedulaInt, $nombre, $apellido, $telefono]);
                $idPersona = (int)$pdo->lastInsertId();

                $username = strtolower(preg_replace('/\s+/', '', $nombre.$apellido));
                $passwordHash = password_hash($cedulaInt, PASSWORD_DEFAULT);
                $stmtInsUsuario = $pdo->prepare("INSERT INTO usuario (id_persona, usuario, contrasena, id_rol) VALUES (?, ?, ?, ?)");
                $stmtInsUsuario->execute([$idPersona, $username, $passwordHash, $idRolCliente]);
                $idUsuario = (int)$pdo->lastInsertId();

                $descripcion = "Cliente: $nombre $apellido - Tel: $telefono";
                $stmtInsCliente = $pdo->prepare("INSERT INTO cliente (alias, descripcion, id_usuario) VALUES (?, ?, ?)");
                $stmtInsCliente->execute([$alias, $descripcion, $idUsuario]);
                $idCliente = (int)$pdo->lastInsertId();
                $insertados++;
                $detalles[] = ['fila' => $rowNum, 'estado' => 'insertado'];
                $valUSD = 0.0;
                $usd = is_string($deuda_dolares_raw) ? str_replace(['$', 'bs', 'Bs', 'BS', ' ', ','], ['', '', '', '', '', '.'], $deuda_dolares_raw) : $deuda_dolares_raw;
                $bs = is_string($deuda_bolivares_raw) ? str_replace(['$', 'bs', 'Bs', 'BS', ' ', ','], ['', '', '', '', '', '.'], $deuda_bolivares_raw) : $deuda_bolivares_raw;
                $tot = is_string($deuda_total_raw) ? str_replace(['$', 'bs', 'Bs', 'BS', ' ', ','], ['', '', '', '', '', '.'], $deuda_total_raw) : $deuda_total_raw;
                $usdF = is_numeric($usd) ? floatval($usd) : 0.0;
                $bsF = is_numeric($bs) ? floatval($bs) : 0.0;
                $totF = is_numeric($tot) ? floatval($tot) : 0.0;
                if ($usdF > 0) { $valUSD = round($usdF, 2); }
                elseif ($bsF > 0 && $tasaCambio > 0) { $valUSD = round($bsF / $tasaCambio, 2); }
                elseif ($totF > 0) { $valUSD = round($totF, 2); }
                if ($valUSD > 0 && isset($idCliente)) {
                    $stmtInsCredito = $pdo->prepare("INSERT INTO credito (id_cajero, id_cliente, id_producto, cantidad, total, estado) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmtInsCredito->execute([null, $idCliente, null, 1, $valUSD, 'pendiente']);
                    $creditos++;
                }
            }
        } catch (Exception $e) {
            $errores++;
            $detalles[] = ['fila' => $rowNum, 'estado' => 'error', 'mensaje' => 'Error al procesar fila'];
        }
    }

    fclose($handle);
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'totales' => [
            'insertados' => $insertados,
            'actualizados' => $actualizados,
            'omitidos' => $omitidos,
            'errores' => $errores,
            'creditos' => $creditos
        ],
        'detalles' => $detalles
    ]);
} catch (Exception $e) {
    fclose($handle);
    if ($conexion) {
        try { $conexion->rollBack(); } catch (Exception $ignored) {}
    }
    echo json_encode(['success' => false, 'message' => 'Error en la importación']);
}
?>