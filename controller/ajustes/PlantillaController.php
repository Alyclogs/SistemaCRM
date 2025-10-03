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
                if (isset($_FILES['contenido_html']) && $_FILES['contenido_html']['error'] === UPLOAD_ERR_OK) {
                    $tmpName = $_FILES['contenido_html']['tmp_name'];
                    $contenido = file_get_contents($tmpName);
                    $data['contenido_html'] = $contenido;
                }
                if (empty($data['nombre']) || (empty($data['contenido_html']) && empty($data['contenido_texto']))) {
                    throw new Exception("Nombre y contenido son obligatorios");
                }

                $id = $plantillaModel->crearPlantilla($data);

                $response = [
                    "success"     => true,
                    "message"     => "Plantilla creada correctamente",
                    "idplantilla" => $id
                ];
                break;

            case 'actualizar':
                if (!isset($_POST['idplantilla'])) throw new Exception("ID de plantilla requerido");

                $data = $_POST;
                if (isset($_FILES['contenido_html']) && $_FILES['contenido_html']['error'] === UPLOAD_ERR_OK) {
                    $tmpName = $_FILES['contenido_html']['tmp_name'];
                    $contenido = file_get_contents($tmpName);
                    $data['contenido_html'] = $contenido;
                }

                $plantillaModel->actualizarPlantilla($_POST['idplantilla'], $data);
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
