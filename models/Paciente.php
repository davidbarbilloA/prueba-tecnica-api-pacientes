<?php

class Paciente {

    private $conn;
    private $table = "paciente";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {

        $query = "INSERT INTO {$this->table}
        (tipo_documento_id, numero_documento, nombre1, nombre2, 
         apellido1, apellido2, genero_id, departamento_id, 
         municipio_id, correo, foto)
        VALUES
        (:tipo_documento_id, :numero_documento, :nombre1, :nombre2,
         :apellido1, :apellido2, :genero_id, :departamento_id,
         :municipio_id, :correo, :foto)";

        $stmt = $this->conn->prepare($query);

        return $stmt->execute([
            ":tipo_documento_id" => $data["tipo_documento_id"],
            ":numero_documento" => $data["numero_documento"],
            ":nombre1" => $data["nombre1"],
            ":nombre2" => $data["nombre2"] ?? null,
            ":apellido1" => $data["apellido1"],
            ":apellido2" => $data["apellido2"] ?? null,
            ":genero_id" => $data["genero_id"],
            ":departamento_id" => $data["departamento_id"],
            ":municipio_id" => $data["municipio_id"],
            ":correo" => $data["correo"] ?? null,
            ":foto" => $data["foto"] ?? null
        ]);
    }

    public function getAll() {
        $query = "SELECT * FROM {$this->table}";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {

        $query = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([":id" => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id, $data) {

        $query = "UPDATE {$this->table}
        SET nombre1=:nombre1,
            nombre2=:nombre2,
            apellido1=:apellido1,
            apellido2=:apellido2,
            correo=:correo
        WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        return $stmt->execute([
            ":id" => $id,
            ":nombre1" => $data["nombre1"],
            ":nombre2" => $data["nombre2"] ?? null,
            ":apellido1" => $data["apellido1"],
            ":apellido2" => $data["apellido2"] ?? null,
            ":correo" => $data["correo"] ?? null
        ]);
    }

    public function delete($id) {

        $query = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        return $stmt->execute([":id" => $id]);
    }
}
