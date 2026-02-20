<?php

require_once __DIR__ . '/../models/Paciente.php';

class PacienteController {

    private $model;

    public function __construct($db) {
        $this->model = new Paciente($db);
    }

    private function validate($data) {

        $required = [
            "tipo_documento_id",
            "numero_documento",
            "nombre1",
            "apellido1",
            "genero_id",
            "departamento_id",
            "municipio_id"
        ];

        foreach ($required as $field) {
            if (empty($data[$field])) {
                return "El campo $field es obligatorio";
            }
        }

        if (!empty($data["correo"]) && 
            !filter_var($data["correo"], FILTER_VALIDATE_EMAIL)) {
            return "Correo inválido";
        }

        if (!preg_match('/^[0-9]+$/', $data["numero_documento"])) {
            return "Número de documento inválido";
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
            $this->model->create($data);
            echo json_encode(["message" => "Paciente creado correctamente"]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    public function getAll() {
        echo json_encode($this->model->getAll());
    }

    public function getById($id) {
        echo json_encode($this->model->getById($id));
    }

    public function delete($id) {
        echo json_encode(["success" => $this->model->delete($id)]);
    }
}
