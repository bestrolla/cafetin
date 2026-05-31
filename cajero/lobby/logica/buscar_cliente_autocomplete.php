<?php
require_once __DIR__ . '/../../../BBDD/BBDD.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $campo = $_GET['campo'] ?? '';
    $valor = $_GET['valor'] ?? '';
    
    if (empty($valor)) {
        echo json_encode([
            'success' => false,
            'message' => 'Valor de búsqueda requerido'
        ]);
        exit;
    }
    
    try {
        $sql = "";
        $params = [];
        
        // Determinar el tipo de búsqueda según el campo
        switch ($campo) {
            case 'cedula':
                $sql = "SELECT 
                            c.id_cliente,
                            p.nombre,
                            p.apellido, 
                            p.cedula,
                            p.telefono,
                            c.alias
                        FROM cliente c
                        JOIN usuario u ON c.id_usuario = u.id_usuario
                        JOIN persona p ON u.id_persona = p.id_persona
                        WHERE p.cedula LIKE ?";
                $params = ["%$valor%"];
                break;
                
            case 'telefono':
                $sql = "SELECT 
                            c.id_cliente,
                            p.nombre,
                            p.apellido, 
                            p.cedula,
                            p.telefono,
                            c.alias
                        FROM cliente c
                        JOIN usuario u ON c.id_usuario = u.id_usuario
                        JOIN persona p ON u.id_persona = p.id_persona
                        WHERE p.telefono LIKE ?";
                $params = ["%$valor%"];
                break;
                
            case 'nombre':
                $sql = "SELECT 
                            c.id_cliente,
                            p.nombre,
                            p.apellido, 
                            p.cedula,
                            p.telefono,
                            c.alias
                        FROM cliente c
                        JOIN usuario u ON c.id_usuario = u.id_usuario
                        JOIN persona p ON u.id_persona = p.id_persona
                        WHERE p.nombre LIKE ?";
                $params = ["%$valor%"];
                break;
                
            case 'apellido':
                $sql = "SELECT 
                            c.id_cliente,
                            p.nombre,
                            p.apellido, 
                            p.cedula,
                            p.telefono,
                            c.alias
                        FROM cliente c
                        JOIN usuario u ON c.id_usuario = u.id_usuario
                        JOIN persona p ON u.id_persona = p.id_persona
                        WHERE p.apellido LIKE ?";
                $params = ["%$valor%"];
                break;
                
            case 'alias':
                $sql = "SELECT 
                            c.id_cliente,
                            p.nombre,
                            p.apellido, 
                            p.cedula,
                            p.telefono,
                            c.alias
                        FROM cliente c
                        JOIN usuario u ON c.id_usuario = u.id_usuario
                        JOIN persona p ON u.id_persona = p.id_persona
                        WHERE c.alias LIKE ?";
                $params = ["%$valor%"];
                break;
                
            default:
                // Búsqueda general en todos los campos
                $sql = "SELECT 
                            c.id_cliente,
                            p.nombre,
                            p.apellido, 
                            p.cedula,
                            p.telefono,
                            c.alias
                        FROM cliente c
                        JOIN usuario u ON c.id_usuario = u.id_usuario
                        JOIN persona p ON u.id_persona = p.id_persona
                        WHERE p.cedula LIKE ? 
                           OR p.telefono LIKE ? 
                           OR p.nombre LIKE ? 
                           OR p.apellido LIKE ? 
                           OR c.alias LIKE ?";
                $params = ["%$valor%", "%$valor%", "%$valor%", "%$valor%", "%$valor%"];
                break;
        }
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute($params);
        $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($clientes)) {
            echo json_encode([
                'success' => true,
                'clientes' => $clientes,
                'total' => count($clientes)
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No se encontraron clientes',
                'clientes' => [],
                'total' => 0
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