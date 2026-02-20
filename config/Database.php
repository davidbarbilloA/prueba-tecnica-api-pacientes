<?php

class Database {

    private $host = "127.0.0.1";
    private $db_name = "sistema_pacientes";
    private $username = "root";
    private $password = "r2W64su_t~-~";
    private $conn;

    public function connect() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");

        } catch(PDOException $e) {
            echo json_encode(["error" => $e->getMessage()]);
        }

        return $this->conn;
    }
}