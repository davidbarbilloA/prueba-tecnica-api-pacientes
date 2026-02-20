<?php
require_once 'config/Database.php';

$database = new Database();
$db = $database->connect();

function seed($db, $table, $column, $value) {
    try {
        $check = $db->prepare("SELECT id FROM $table LIMIT 1");
        $check->execute();
        if ($check->rowCount() == 0) {
            $stmt = $db->prepare("INSERT INTO $table ($column) VALUES (:val)");
            $stmt->execute([':val' => $value]);
            echo "Inserted '$value' into $table. ID: " . $db->lastInsertId() . "<br>";
        } else {
            echo "Table $table already has data.<br>";
        }
    } catch (PDOException $e) {
        echo "Error seeding $table: " . $e->getMessage() . "<br>";
    }
}

function seedMuni($db, $depId) {
    try {
        $check = $db->prepare("SELECT id FROM municipios LIMIT 1");
        $check->execute();
        if ($check->rowCount() == 0) {
            $stmt = $db->prepare("INSERT INTO municipios (nombre, departamento_id) VALUES ('Municipio Test', :depId)");
            $stmt->execute([':depId' => $depId]);
            echo "Inserted 'Municipio Test' into municipios.<br>";
        } else {
             echo "Table municipios already has data.<br>";
        }
    } catch (PDOException $e) {
        echo "Error seeding municipios: " . $e->getMessage() . "<br>";
    }
}

echo "<h2>Seeding Database...</h2>";

seed($db, "tipos_documento", "nombre", "Cedula");
seed($db, "genero", "nombre", "Masculino");
seed($db, "departamentos", "nombre", "Antioquia"); 
seedMuni($db, 1); 

echo "<h3>Done. IDs to use:</h3>";
echo "<ul>
    <li>Tipo Doc ID: 1</li>
    <li>Genero ID: 1</li>
    <li>Depto ID: 1</li>
    <li>Municipio ID: 1</li>
</ul>";
