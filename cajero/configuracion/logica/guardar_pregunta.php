<?php
require_once '../../../acces/auth_check.php';
require_once '../../../BBDD/BBDD.php';

// Solo cajero
protegerPagina(['cajero']);

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Sin datos']);
        exit;
    }

    $usuario = obtenerUsuarioActual();
    if (!$usuario) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'No autenticado']);
        exit;
    }

    $conexion = (new Conexion())->conectar();
    $conexion->beginTransaction();

    // Múltiples preguntas
    if (isset($input['respuestas']) && is_array($input['respuestas'])) {
        $respuestas = $input['respuestas'];
        if (count($respuestas) !== 3) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Debe enviar 3 preguntas y respuestas']);
            exit;
        }
        $ids = array_map(function($r){ return (int)($r['id_pregunta'] ?? 0); }, $respuestas);
        $texts = array_map(function($r){ return trim($r['respuesta'] ?? ''); }, $respuestas);
        // Validaciones
        if (in_array(0, $ids, true) || in_array('', $texts, true)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Preguntas/respuestas inválidas']);
            exit;
        }
        if (count(array_unique($ids)) !== 3) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'No se permiten preguntas repetidas']);
            exit;
        }
        // Limpiar existentes
        $stmtDel = $conexion->prepare('DELETE FROM respuestas WHERE id_usuario = :id');
        $stmtDel->execute([':id' => $usuario['id']]);
        // Insertar nuevas
        $stmtIns = $conexion->prepare('INSERT INTO respuestas (id_usuario, id_pregunta, respuesta) VALUES (:id_usuario, :id_pregunta, :respuesta)');
        foreach ($respuestas as $r) {
            $hash = password_hash(trim($r['respuesta']), PASSWORD_DEFAULT);
            $stmtIns->execute([':id_usuario' => $usuario['id'], ':id_pregunta' => (int)$r['id_pregunta'], ':respuesta' => $hash]);
        }
    } else {
        // Modo anterior: una sola pregunta
        if (!isset($input['id_pregunta']) || !isset($input['respuesta'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            exit;
        }
        $idPregunta = (int)$input['id_pregunta'];
        $respuesta = trim($input['respuesta']);
        if ($idPregunta <= 0 || strlen($respuesta) < 2) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
            exit;
        }
        $hash = password_hash($respuesta, PASSWORD_DEFAULT);
        // Upsert simple
        $stmtChk = $conexion->prepare('SELECT 1 FROM respuestas WHERE id_usuario = :id LIMIT 1');
        $stmtChk->execute([':id' => $usuario['id']]);
        if ($stmtChk->fetch(PDO::FETCH_ASSOC)) {
            $stmtUpd = $conexion->prepare('UPDATE respuestas SET id_pregunta = :id_pregunta, respuesta = :respuesta WHERE id_usuario = :id');
            $stmtUpd->execute([':id_pregunta' => $idPregunta, ':respuesta' => $hash, ':id' => $usuario['id']]);
        } else {
            $stmtIns = $conexion->prepare('INSERT INTO respuestas (id_usuario, id_pregunta, respuesta) VALUES (:id_usuario, :id_pregunta, :respuesta)');
            $stmtIns->execute([':id_usuario' => $usuario['id'], ':id_pregunta' => $idPregunta, ':respuesta' => $hash]);
        }
    }

    $conexion->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    if (isset($conexion) && $conexion->inTransaction()) {
        $conexion->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al guardar', 'error' => $e->getMessage()]);
}
?>