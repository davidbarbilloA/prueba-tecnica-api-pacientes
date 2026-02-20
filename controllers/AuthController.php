<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/JWT.php';

class AuthController {
    private $db;
    private $user;

    public function __construct($db) {
        $this->db = $db;
        $this->user = new User($db);
    }

    public function login() {
        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->email) && !empty($data->password)) {
            $user = $this->user->login($data->email, $data->password);

            if ($user) {
                $payload = [
                    "iss" => "localhost",
                    "aud" => "localhost",
                    "iat" => time(),
                    "exp" => time() + (60 * 60),
                    "data" => [
                        "id" => $user['id'],
                        "name" => $user['name'],
                        "email" => $user['email']
                    ]
                ];

                $jwt = JWT::encode($payload);

                echo json_encode([
                    "message" => "Login exitoso",
                    "token" => $jwt,
                    "user" => $user['name']
                ]);
            } else {
                http_response_code(401);
                echo json_encode(["message" => "Credenciales inválidas."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Datos incompletos."]);
        }
    }
}
