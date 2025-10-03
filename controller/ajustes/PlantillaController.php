<?php
require_once __DIR__ . "/../../models/ajustes/PlantillaModel.php";
require_once __DIR__ . "/../../config/database.php";

header('Content-Type: application/json; charset=utf-8');

$response = ["success" => false, "message" => "Acción no válida"];

try {
    $pdo = Database::getConnection();
    $plantillaModel = new PlantillaModel($pdo);

    if (isset($_GET['action'])) {
        switch ($_GET['action']) {

            case 'listar':
                $data = $plantillaModel->obtenerPlantillas();
                $response = $data;
                break;

            case 'ver':
                if (!isset($_GET['idplantilla'])) throw new Exception("ID de plantilla requerido");
                $data = $plantillaModel->obtenerPlantilla($_GET['idplantilla']);
                $response = $data ?: ["success" => false, "message" => "Plantilla no encontrada"];
                break;

            case 'crear':
                $data = $_POST;
                if (empty($data['nombre']) || empty($data['contenido'])) {
                    throw new Exception("Nombre y contenido son obligatorios");
                }
                $id = $plantillaModel->crearPlantilla($data);
                $response = [
                    "success" => true,
                    "message" => "Plantilla creada correctamente",
                    "idplantilla" => $id
                ];
                break;

            case 'actualizar':
                if (!isset($_POST['idplantilla'])) throw new Exception("ID de plantilla requerido");
                $data = $_POST;
                $plantillaModel->editarPlantilla($_POST['idplantilla'], $data);
                $response = [
                    "success" => true,
                    "message" => "Plantilla actualizada correctamente"
                ];
                break;

            case 'eliminar':
                if (!isset($_POST['idplantilla'])) throw new Exception("ID de plantilla requerido");
                $plantillaModel->eliminarPlantilla($_POST['idplantilla']);
                $response = [
                    "success" => true,
                    "message" => "Plantilla eliminada correctamente"
                ];
                break;
        }
    }
} catch (Exception $e) {
    $response = ["success" => false, "message" => $e->getMessage()];
}

echo json_encode($response);
