<?php
require_once __DIR__ . "/../../models/ajustes/AjustesModel.php";
require_once __DIR__ . "/../../models/cambios/RegistroCambio.php";

header('Content-Type: application/json; charset=utf-8');

$response = ["success" => false, "message" => "Acción no válida"];

try {
    $pdo = Database::getConnection();
    $registroCambioModel = new RegistroCambioModel($pdo);
    $ajustesModel = new AjustesModel($pdo, $registroCambioModel);

    if (isset($_GET['action'])) {
        switch ($_GET['action']) {

            case 'listar_disponibilidad':
                if (!isset($_POST['iddisponibilidad'])) throw new Exception("ID requerido");
                $data = $ajustesModel->obtenerDisponibilidad($_POST['iddisponibilidad']);
                $response = $data;
                break;

            case 'crear_disponibilidad':
                $id = $ajustesModel->crearDisponibilidad($_POST, $_POST['idusuario'] ?? null);
                $response = [
                    "success" => true,
                    "message" => "Disponibilidad creada",
                    "id" => $id
                ];
                break;

            case 'actualizar_disponibilidad':
                if (!isset($_POST['iddisponibilidad'])) throw new Exception("ID requerido");
                $ajustesModel->actualizarDisponibilidad($_POST['iddisponibilidad'], $_POST, $_POST['idusuario'] ?? null);
                $response = [
                    "success" => true,
                    "message" => "Disponibilidad actualizada"
                ];
                break;

            case 'eliminar_disponibilidad':
                if (!isset($_POST['iddisponibilidad'])) throw new Exception("ID requerido");
                $ajustesModel->eliminarDisponibilidad($_POST['iddisponibilidad'], $_POST['idusuario'] ?? null);
                $response = [
                    "success" => true,
                    "message" => "Disponibilidad eliminada"
                ];
                break;
        }
    }
} catch (Exception $e) {
    $response = ["success" => false, "message" => $e->getMessage()];
}

echo json_encode($response);
