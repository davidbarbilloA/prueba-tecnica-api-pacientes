<?php
error_reporting(E_ALL);
ini_set('display_errors', 0); 


header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

require_once 'config/Database.php';
require_once 'controllers/UserController.php';
require_once "models/Paciente.php";


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $database = new Database();
    $db = $database->connect();

    $controller = new UserController($db);

    $request = $_SERVER['REQUEST_METHOD'];
    $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri = explode('/', trim($requestUri, '/'));

    $baseFolder = "Prueba_tecnica";
    $index = array_search($baseFolder, $uri);

    $resource = $uri[$index + 1] ?? null;
    $subResource = $uri[$index + 2] ?? null;
    $id = $uri[$index + 3] ?? null;

    if ($resource === "login" && $request === 'POST') {
        require_once 'controllers/AuthController.php';
        $auth = new AuthController($db);
        $auth->login();
    } elseif ($resource === "pacientes") {
        require_once 'middleware/AuthMiddleware.php';
        AuthMiddleware::validateToken();

        $id = $subResource; 
        
        switch ($request) {
            case 'GET':
                if ($id) $controller->getById($id);
                else $controller->getAll();
                break;
            case 'POST':
                $controller->create();
                break;
            case 'PUT': 
                 if ($id) $controller->update($id);
                 break;
            case 'DELETE':
                if ($id) $controller->delete($id);
                break;
        }
    } elseif ($resource === "catalogs") {
        require_once 'controllers/CatalogController.php';
        $catalogController = new CatalogController($db);

        switch ($subResource) {
            case 'tipos_documento':
                $catalogController->getTiposDocumento();
                break;
            case 'generos':
                $catalogController->getGeneros();
                break;
            case 'departamentos':
                $catalogController->getDepartamentos();
                break;
            case 'municipios':
                $depId = $id; 
                if ($depId) $catalogController->getMunicipios($depId);
                else echo json_encode([]);
                break;
            default:
                http_response_code(404);
                echo json_encode(["error" => "Catálogo no encontrado"]);
        }
    } else {
        http_response_code(404);
        echo json_encode(["error" => "Endpoint no encontrado"]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["message" => "Error interno", "error" => $e->getMessage()]);
} catch (Error $e) {
    http_response_code(500);
    echo json_encode(["message" => "Error fatal", "error" => $e->getMessage()]);
}
