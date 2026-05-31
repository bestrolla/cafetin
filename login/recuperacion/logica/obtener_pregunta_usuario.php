<?php
require_once __DIR__ . '/../../../acces/security_headers.php';
require_once __DIR__ . '/../../../acces/csrf.php';
require_once __DIR__ . '/../../../BBDD/BBDD.php';

header('Content-Type: application/json');

// Solo aceptar POST con CSRF, para evitar enumeración de usuarios vía GET
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

if (!csrfVerifyFromHeader('X-CSRF-Token')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'CSRF inválido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$usuario = isset($input['usuario']) ? trim($input['usuario']) : '';

if ($usuario === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Usuario requerido']);
    exit;
}

try {
    $conexion = (new Conexion())->conectar();
    // Obtener hasta 3 preguntas asociadas al usuario, si existen
    $stmt = $conexion->prepare('SELECT r.id_pregunta, p.pregunta
                                FROM usuario u
                                JOIN respuestas r ON r.id_usuario = u.id_usuario
                                JOIN preguntas p ON p.id_pregunta = r.id_pregunta
                                WHERE LOWER(TRIM(u.usuario)) = LOWER(TRIM(:usuario))
                                ORDER BY r.id_pregunta ASC
                                LIMIT 3');
    $stmt->execute([':usuario' => $usuario]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!$rows || count($rows) === 0) {
        echo json_encode(['success' => true, 'has_question' => false]);
        exit;
    }
    echo json_encode(['success' => true, 'has_question' => true, 'preguntas' => $rows]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error del servidor']);
}
?>