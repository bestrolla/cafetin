<?php
require_once '../../../acces/auth_check.php';
require_once '../../../BBDD/BBDD.php';

// Solo admin
protegerPagina(['admin']);

header('Content-Type: application/json');

try {
    $conexion = (new Conexion())->conectar();

    // Obtener preguntas predeterminadas globales (id_usuario = 0)
    $stmt = $conexion->prepare('SELECT id_pregunta, pregunta FROM preguntas WHERE id_usuario = 0 ORDER BY id_pregunta ASC');
    $stmt->execute();
    $preguntas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Sembrar preguntas predeterminadas globales si no existen
    if (!$preguntas || count($preguntas) === 0) {
        $defaults = [
            '¿Cuál es el nombre de tu primera mascota?',
            '¿En qué ciudad naciste?',
            '¿Cuál es tu comida favorita?'
        ];
        $stmtIns = $conexion->prepare('INSERT INTO preguntas (id_usuario, pregunta) VALUES (0, :pregunta)');
        foreach ($defaults as $q) {
            $stmtIns->execute([':pregunta' => $q]);
        }
        // Volver a cargar
        $stmt->execute();
        $preguntas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener selección actual del usuario para marcar sus preguntas elegidas
    $usuario = obtenerUsuarioActual();
    $selecciones = [];
    if ($usuario && isset($usuario['id'])) {
        $stmtSel = $conexion->prepare('SELECT id_pregunta FROM respuestas WHERE id_usuario = :id_usuario ORDER BY id_pregunta ASC LIMIT 3');
        $stmtSel->execute([':id_usuario' => $usuario['id']]);
        $rows = $stmtSel->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $r) {
            $selecciones[] = ['id_pregunta' => $r['id_pregunta']];
        }
    }

    echo json_encode(['success' => true, 'preguntas' => $preguntas, 'selecciones' => $selecciones]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al obtener preguntas', 'error' => $e->getMessage()]);
}
?>