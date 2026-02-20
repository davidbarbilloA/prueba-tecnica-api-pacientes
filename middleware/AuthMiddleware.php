<?php
require_once __DIR__ . '/../helpers/JWT.php';

class AuthMiddleware {
    public static function validateToken() {
        $headers = null;
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
        }
        
        $authHeader = null;
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        }

        $token = null;
        if ($authHeader) {
            $matches = [];
            if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                $token = $matches[1];
            }
        }

        if (!$token) {
            http_response_code(401);
            echo json_encode(["message" => "Acceso no autorizado. Token no proporcionado."]);
            exit();
        }

        $decoded = JWT::decode($token);
        if (!$decoded) {
            http_response_code(401);
            echo json_encode(["message" => "Token inválido o expirado."]);
            exit();
        }

        return $decoded;
    }
}
