<?php

require_once __DIR__ . '/../models/Paciente.php';

class UserController {

    private $model;

    public function __construct($db = null) {
        if ($db) {
            $this->model = new Paciente($db);
        }
    }

    public function getById($id) {
        if (empty($id) || !is_numeric($id)) {
            http_response_code(400);
            echo json_encode(["error" => "ID inválido"]);
            return;
        }

        $result = $this->model->getById($id);

        if (!$result) {
            http_response_code(404);
            echo json_encode(["error" => "Paciente no encontrado"]);
            return;
        }

        echo json_encode($result);
    }

    private function validate($data) {
        $requiredFields = [
            'tipo_documento_id', 'numero_documento', 'nombre1', 
            'apellido1', 'genero_id', 'departamento_id', 'municipio_id'
        ];

        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return "El campo $field es obligatorio";
            }
        }

        if (!empty($data['correo']) && !filter_var($data['correo'], FILTER_VALIDATE_EMAIL)) {
            return "Email inválido";
        }

        return null;
    }

    public function create() {
        $data = json_decode(file_get_contents("php://input"), true);

        $error = $this->validate($data);
        if ($error) {
            http_response_code(400);
            echo json_encode(["error" => $error]);
            return;
        }

        try {
            $result = $this->model->create($data);
            echo json_encode(["success" => $result]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error al crear paciente: " . $e->getMessage()]);
        }
    }

    public function getAll() {
        echo json_encode($this->model->getAll());
    }

    public function update($id) {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (empty($data)) {
             http_response_code(400);
             echo json_encode(["error" => "No se enviaron datos para actualizar"]);
             return;
        }

        $result = $this->model->update($id, $data);
        echo json_encode(["success" => $result]);
    }

    public function delete($id) {
        echo json_encode(["success" => $this->model->delete($id)]);
    }
}
